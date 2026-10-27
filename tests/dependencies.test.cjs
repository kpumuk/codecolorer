'use strict';

const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const { createRequire } = require('node:module');
const os = require('node:os');
const path = require('node:path');
const { after, test } = require('node:test');

const envRoot = path.dirname(require.resolve('@wordpress/env/package.json'));
const envRequire = createRequire(path.join(envRoot, 'package.json'));
const { simpleGit, GitPluginError } = envRequire('simple-git');
const playgroundRequire = createRequire(envRequire.resolve('@wp-playground/cli'));
const expressRequire = createRequire(playgroundRequire.resolve('express'));
const qs = expressRequire('qs');
const blueprintsRequire = createRequire(playgroundRequire.resolve('@wp-playground/blueprints'));
const Ajv = blueprintsRequire('ajv');
const ajvRequire = createRequire(blueprintsRequire.resolve('ajv'));
const uri = ajvRequire('fast-uri');
const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'codecolorer-dependencies-'));
after(() => fs.rmSync(directory, { recursive: true, force: true }));

function git(cwd, ...args) {
  return execFileSync('git', ['-c', 'commit.gpgsign=false', ...args], {
    cwd, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'],
  }).trim();
}

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
