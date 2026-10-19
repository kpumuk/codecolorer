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
