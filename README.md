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
pnpm run wp-env:start
```

The PHPUnit suite is a lightweight unit harness and does not require a full WordPress install.
The `wp-env` setup provides a local WordPress instance for plugin development and manual testing.
