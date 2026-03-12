# CodeColorer

[![CI](https://github.com/kpumuk/codecolorer/actions/workflows/ci.yml/badge.svg)](https://github.com/kpumuk/codecolorer/actions/workflows/ci.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/38191b47e77c9344e9c4/maintainability)](https://codeclimate.com/github/kpumuk/codecolorer/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/38191b47e77c9344e9c4/test_coverage)](https://codeclimate.com/github/kpumuk/codecolorer/test_coverage)
[![WordPress Plug-In Version](https://img.shields.io/wordpress/plugin/v/codecolorer.svg)](https://wordpress.org/plugins/codecolorer/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/codecolorer.svg)](https://wordpress.org/plugins/codecolorer/advanced/)
[![WordPress Version Supported](https://img.shields.io/wordpress/v/codecolorer.svg)](https://wordpress.org/plugins/codecolorer/)

![](https://ps.w.org/codecolorer/assets/banner-1544x500.png)

CodeColorer is a syntax highlighting plug-in which allows inserting code snippets
into blog posts. The plug-in supports color themes, code samples in RSS, comments.

Please check our WordPress page https://wordpress.org/plugins/codecolorer/

## Development

Local development is pinned with `mise` and the CI matrix runs on GitHub Actions.

```sh
mise trust
mise install
composer install
pnpm install
mise run test
bin/install-wp-tests.sh codecolorer_test root root localhost 6.9
mise run test-wp
pnpm run wp-env:start
```

`mise run test` runs the fast PHP-only suite.
`mise run test-wp` runs WordPress integration tests against the official WordPress test suite after `bin/install-wp-tests.sh` has set up WordPress core/tests and a MySQL database.
The `wp-env` setup provides a local WordPress instance for plugin development and manual testing.

## Releases

WordPress.org releases are tag-driven. Create a plain Git tag such as `0.10.2`; do not use a `v` prefix.

The GitHub release workflow builds from the tag, applies [`.distignore`](/Users/dmytro/work/github/codecolorer/.distignore), publishes the plugin to WordPress.org SVN `trunk/`, and creates the matching SVN tag.

Manual `workflow_dispatch` runs preflight by default. Set `perform_deploy` only when you want the workflow to commit to WordPress.org SVN.

Plugin-directory assets live in [`.wordpress-org/assets`](/Users/dmytro/work/github/codecolorer/.wordpress-org/assets) and are synced to WordPress.org SVN `assets/` during a release. Treat Git as the source of truth and avoid manual SVN edits except for emergency recovery.
