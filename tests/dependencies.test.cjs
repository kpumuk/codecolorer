'use strict';

const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const { createRequire } = require('node:module');
const os = require('node:os');
const path = require('node:path');
const { after, test } = require('node:test');
const { pathToFileURL } = require('node:url');

const envRoot = path.dirname(require.resolve('@wordpress/env/package.json'));
const envRequire = createRequire(path.join(envRoot, 'package.json'));
const { simpleGit, GitPluginError } = envRequire('simple-git');
const playgroundRequire = createRequire(envRequire.resolve('@wp-playground/cli'));
const expressRequire = createRequire(playgroundRequire.resolve('express'));
const qs = expressRequire('qs');
const storageRequire = createRequire(playgroundRequire.resolve('@wp-playground/storage'));
const isomorphicGit = storageRequire('isomorphic-git');
const blueprintsRequire = createRequire(playgroundRequire.resolve('@wp-playground/blueprints'));
const Ajv = blueprintsRequire('ajv');
const ajvRequire = createRequire(blueprintsRequire.resolve('ajv'));
const uri = ajvRequire('fast-uri');
const rimrafRequire = createRequire(envRequire.resolve('rimraf'));
const { glob } = rimrafRequire('glob');
const { Minimatch } = createRequire(rimrafRequire.resolve('glob'))('minimatch');
const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'codecolorer-dependencies-'));
after(() => fs.rmSync(directory, { recursive: true, force: true }));

function git(cwd, ...args) {
  return execFileSync('git', ['-c', 'commit.gpgsign=false', ...args], {
    cwd, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'],
  }).trim();
}

test('minimatch bounds globstar-parent pattern amplification', () => {
  for (const count of [7, 8]) {
    const pattern = Array(count).fill('**/../a/b').join('/');
    assert.equal(new Minimatch(pattern, {
      optimizationLevel: 2, braceExpandMax: 64,
    }).set.length, 64);
  }
  const matcher = new Minimatch('**/../a/b/**/../c/d', { optimizationLevel: 2 });
  assert.equal(matcher.match('../a/b/../c/d'), true);
  assert.equal(matcher.match('x/a/b/y/c/d'), true);
  assert.equal(matcher.match('unrelated.txt'), false);
});

test('glob stops filesystem traversal after cancellation', async () => {
  const root = path.join(directory, 'glob-abort');
  fs.mkdirSync(path.join(root, 'one/two/three'), { recursive: true });
  const controller = new AbortController();
  const reason = new Error('cancel traversal');
  let reads = 0;
  const filesystem = {
    ...fs,
    readdir(...args) {
      reads++;
      if (reads === 1) controller.abort(reason);
      return fs.readdir(...args);
    },
  };
  await assert.rejects(glob('**', {
    cwd: root, signal: controller.signal, fs: filesystem,
  }), (error) => error === reason);
  await new Promise((resolve) => setTimeout(resolve, 100));
  assert.equal(reads, 1);
  await assert.rejects(glob('**/*', {
    cwd: root, signal: controller.signal, fs: filesystem,
  }), (error) => error === reason);
  assert.equal(reads, 1);
});

test('wp-env rimraf preserves literal paths and explicit glob cleanup', async () => {
  const { rimraf } = envRequire('rimraf');
  const root = path.join(directory, 'rimraf');
  const literal = path.join(root, '[plugin]');
  fs.mkdirSync(literal, { recursive: true });
  fs.writeFileSync(path.join(literal, 'plugin.php'), '<?php');
  fs.writeFileSync(path.join(root, 'remove.tmp'), 'temporary');
  fs.writeFileSync(path.join(root, 'keep.php'), '<?php');
  assert.equal(await rimraf(literal), true);
  assert.equal(fs.existsSync(literal), false);
  assert.equal(await rimraf(path.join(root, '*.tmp'), { glob: true }), true);
  assert.equal(fs.existsSync(path.join(root, 'remove.tmp')), false);
  assert.equal(fs.existsSync(path.join(root, 'keep.php')), true);
});

test('wp-env clones and updates a Git source with simple-git 4', async () => {
  const { downloadGitSource } = require(path.join(envRoot, 'lib/download-sources'));
  const origin = path.join(directory, 'origin');
  fs.mkdirSync(origin);
  git(origin, 'init', '-b', 'main');
  const commit = (content) => {
    fs.writeFileSync(path.join(origin, 'plugin.php'), content);
    git(origin, 'add', 'plugin.php');
    git(origin, '-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '-m', content);
  };
  commit('first');
  const clonePath = path.join(directory, 'clone');
  const source = { url: origin, clonePath, ref: 'main' };
  const options = { onProgress() {}, debug: false };
  await downloadGitSource(source, options);
  assert.equal(fs.readFileSync(path.join(clonePath, 'plugin.php'), 'utf8'), 'first');
  commit('second');
  await downloadGitSource(source, options);
  assert.equal(git(clonePath, 'rev-parse', 'HEAD'), git(origin, 'rev-parse', 'HEAD'));
  assert.equal(fs.readFileSync(path.join(clonePath, 'plugin.php'), 'utf8'), 'second');
});

test('Playground reads Git packs and rejects traversal trees in both module formats', async (t) => {
  const origin = path.join(directory, 'packed-origin');
  fs.mkdirSync(path.join(origin, 'src'), { recursive: true });
  git(origin, 'init', '-b', 'main');
  const first = 'CodeColorer\n'.repeat(200);
  const second = `${first}updated\n`;
  const revisions = [];
  for (const content of [first, second]) {
    fs.writeFileSync(path.join(origin, 'src/plugin.php'), content);
    git(origin, 'add', '.');
    git(origin, '-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '-m', 'update');
    revisions.push(git(origin, 'rev-parse', 'HEAD'));
  }
  const safePack = execFileSync('git', ['pack-objects', '--stdout', '--all'], { cwd: origin });
  const unsafeTree = execFileSync('git', ['hash-object', '-t', 'tree', '--literally', '-w', '--stdin'], {
    cwd: origin, encoding: 'utf8', input: Buffer.concat([
      Buffer.from('40000 ..\0'), Buffer.from(git(origin, 'rev-parse', 'HEAD:src'), 'hex'),
    ]),
  }).trim();
  const unsafeCommit = git(origin, '-c', 'user.name=Test', '-c', 'user.email=test@example.com',
    'commit-tree', unsafeTree, '-m', 'unsafe');
  git(origin, 'update-ref', 'refs/heads/unsafe', unsafeCommit);
  const unsafePack = execFileSync('git', ['pack-objects', '--stdout', '--all'], { cwd: origin });
  let pack;
  t.mock.method(globalThis, 'fetch', async () => {
    const payload = Buffer.concat([Buffer.from([1]), pack]);
    return new Response(Buffer.concat([
      Buffer.from('0008NAK\n'), Buffer.from((payload.length + 4).toString(16).padStart(4, '0')),
      payload, Buffer.from('0000'),
    ]));
  });
  const storagePath = playgroundRequire.resolve('@wp-playground/storage');
  for (const storage of [
    playgroundRequire('@wp-playground/storage'),
    await import(pathToFileURL(path.join(path.dirname(storagePath), 'index.js'))),
  ]) {
    pack = safePack;
    assert.deepEqual(await storage.listGitFiles('https://git.example.test/repo', revisions[1]), [
      { name: 'src', type: 'folder', children: [{ name: 'plugin.php', type: 'file' }] },
    ]);
    for (const [index, content] of [first, second].entries()) {
      const result = await storage.sparseCheckout('https://git.example.test/repo', revisions[index],
        ['src/plugin.php'], { withObjects: true });
      assert.equal(Buffer.from(result.files['src/plugin.php']).toString(), content);
      assert.ok(result.packfiles.every((file) => file.pack.length > 0 && file.index.length > 0));
      assert.ok(result.objects.some((object) => object.oid === revisions[index] && object.type === 'commit'));
    }
    pack = unsafePack;
    await assert.rejects(storage.listGitFiles('https://git.example.test/repo', unsafeCommit), {
      code: 'UnsafeFilepathError',
    });
  }
});

test('isomorphic-git checkout refuses writes through a leading directory symlink', async () => {
  const dir = path.join(directory, 'symlink-checkout');
  const outside = path.join(directory, 'symlink-outside');
  fs.mkdirSync(path.join(dir, 'src'), { recursive: true });
  fs.mkdirSync(outside);
  fs.writeFileSync(path.join(outside, 'plugin.php'), 'keep outside');
  git(dir, 'init', '-b', 'main');
  fs.writeFileSync(path.join(dir, 'src/plugin.php'), 'original');
  git(dir, 'add', '.');
  git(dir, '-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '-m', 'original');
  const original = git(dir, 'rev-parse', 'HEAD');
  fs.writeFileSync(path.join(dir, 'src/plugin.php'), 'untrusted content');
  git(dir, 'add', '.');
  git(dir, '-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '-m', 'add source');
  git(dir, 'checkout', '--detach', original);
  fs.rmSync(path.join(dir, 'src'), { recursive: true });
  fs.symlinkSync(outside, path.join(dir, 'src'), 'dir');
  await assert.rejects(isomorphicGit.checkout({ fs, dir, ref: 'main', force: true }),
    (error) => (error.errors || [error]).some((inner) => inner.code === 'UnsafeFilepathError'));
  assert.equal(fs.readFileSync(path.join(outside, 'plugin.php'), 'utf8'), 'keep outside');
  fs.unlinkSync(path.join(dir, 'src'));
  fs.mkdirSync(path.join(dir, 'src'));
  fs.writeFileSync(path.join(dir, 'src/plugin.php'), 'original');
  await isomorphicGit.checkout({ fs, dir, ref: 'main', force: true });
  assert.equal(fs.readFileSync(path.join(dir, 'src/plugin.php'), 'utf8'), 'untrusted content');
});

test('wp-env reads generated Docker YAML and checks cached images with js-yaml 4', async () => {
  const yaml = envRequire('js-yaml');
  const { findMissingImages } = require(path.join(envRoot, 'lib/runtime/docker/missing-images'));
  const composePath = path.join(directory, 'docker-compose.yml');
  fs.writeFileSync(composePath, yaml.dump({ services: {
    mysql: { image: 'mariadb:lts' },
    'tests-mysql': { image: 'mariadb:lts' },
    wordpress: { build: '.' },
  } }));
  assert.deepEqual(await findMissingImages(composePath, async () => false), [
    { image: 'mariadb:lts', services: ['mysql', 'tests-mysql'] },
  ]);
  assert.deepEqual(await findMissingImages(composePath, async () => true), []);
});

for (const key of ['trailer.audit.cmd', 'trailer.audit.command', 'include.path', 'includeIf.gitdir:.path']) {
  test(`simple-git rejects unsafe ${key} configuration`, async () => {
    await assert.rejects(
      simpleGit({ baseDir: directory, config: [`${key}=unused`] }).raw('version'),
      GitPluginError
    );
  });
}

test('simple-git rejects an explicit VISUAL editor', async () => {
  await assert.rejects(
    simpleGit(directory).env({ ...process.env, VISUAL: 'unused' }).raw('version'),
    GitPluginError
  );
});

test('simple-git blocks abbreviated executable push options before commands run', async () => {
  const repository = path.join(directory, 'push-repository');
  const remote = path.join(directory, 'remote.git');
  const marker = path.join(directory, 'executed');
  fs.mkdirSync(repository);
  git(repository, 'init', '-b', 'main');
  git(repository, '-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '--allow-empty', '-m', 'initial');
  git(directory, 'init', '--bare', remote);
  for (const flag of ['--receive-p', '--receive-pa', '--exe']) {
    await assert.rejects(
      simpleGit(repository).push([remote, 'HEAD:refs/heads/main', `${flag}=touch ${marker};`]),
      /disallowed abbreviated or ambiguous option/
    );
    assert.equal(fs.existsSync(marker), false);
  }
  await simpleGit(repository).push([remote, 'HEAD:refs/heads/main']);
  assert.equal(git(remote, 'rev-parse', 'main'), git(repository, 'rev-parse', 'HEAD'));
});

test('qs enforces array limits on comma-separated bracket keys', () => {
  assert.throws(() => qs.parse('a[]=1,2,3,4', {
    comma: true, arrayLimit: 3, throwOnLimitExceeded: true,
  }), RangeError);
  assert.deepEqual(qs.parse('a[]=1,2', { comma: true, arrayLimit: 3 }), { a: [['1', '2']] });
});

test('qs safely round-trips a query containing constructor.isBuffer', () => {
  const input = 'x%5Bconstructor%5D%5BisBuffer%5D=y';
  assert.equal(qs.stringify(qs.parse(input, { plainObjects: true })), input);
  assert.equal(qs.stringify(qs.parse('name=CodeColorer')), 'name=CodeColorer');
});

test('body-parser finishes compressed JSON with trailing bytes without crashing or dropping consumers', () => {
  // Isolate the old parser's unhandled decompression error from the test runner.
  execFileSync(process.execPath, ['--eval', `
    const assert = require('node:assert/strict');
    const { PassThrough } = require('node:stream');
    const { createHash } = require('node:crypto');
    const { deflateSync, gzipSync } = require('node:zlib');
    const express = require(process.argv[1]);
    async function check(encoding, compress) {
      const request = new PassThrough();
      const first = Buffer.concat([compress('{"name":"CodeColorer"}'), Buffer.from([0])]);
      const trailing = Buffer.alloc(1024, 0x58);
      request.headers = {
        'content-type': 'application/json',
        'content-encoding': encoding,
        'content-length': String(first.length + trailing.length),
      };
      const hash = createHash('sha256');
      request.pipe(hash);
      await new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error('Request did not finish')), 2000);
        hash.on('finish', () => {
          clearTimeout(timer);
          try {
            assert.deepEqual(request.body, { name: 'CodeColorer' });
            assert.equal(hash.read().toString('hex'),
              createHash('sha256').update(first).update(trailing).digest('hex'));
            resolve();
          } catch (error) { reject(error); }
        });
        express.json()(request, {}, (error) => {
          if (error) { clearTimeout(timer); reject(error); return; }
          request.end(trailing);
        });
        request.write(first);
      });
    }
    (async () => {
      await check('deflate', deflateSync);
      await check('gzip', gzipSync);
    })().catch((error) => { console.error(error); process.exitCode = 1; });
  `, playgroundRequire.resolve('express')], { timeout: 10000, stdio: 'pipe' });
});

test('fast-uri rejects encoded schemes and ambiguous hosts', () => {
  for (const input of ['%6Aavascript:alert(1)', '%u006Aavascript:alert(1)',
    'http://a@b@evil.example/', 'http://exa\tmple.com/', 'http://exa\nmple.com/']) {
    assert.ok(uri.parse(input).error, input);
  }
  assert.equal(uri.normalize('https://example.com/a'), 'https://example.com/a');
});

test('fast-uri preserves relative-reference resolution and escapes suffix colons', () => {
  const base = 'https://example.com/one/two/';
  for (const reference of ['../x', 'a/../b']) {
    assert.equal(uri.resolve(base, uri.normalize(reference)), uri.resolve(base, reference));
  }
  assert.equal(uri.equal('../x', 'x'), false);
  assert.equal(uri.serialize({ path: 'javascript:alert(1)' }, { reference: 'suffix' }),
    'javascript%3Aalert(1)');
});

test('Ajv still resolves relative schema references and validates ordinary data', () => {
  const ajv = new Ajv();
  ajv.addSchema({ $id: 'https://example.com/schemas/count.json', type: 'integer', minimum: 1 });
  const validate = ajv.compile({
    $id: 'https://example.com/schemas/plugin.json',
    type: 'object', required: ['count'], properties: { count: { $ref: 'count.json' } },
  });
  assert.equal(validate({ count: 2 }), true);
  assert.equal(validate({ count: 0 }), false);
  assert.equal(validate({ count: '2' }), false);
});
