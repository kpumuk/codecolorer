# CodeColorer

[![CI](https://github.com/kpumuk/codecolorer/actions/workflows/ci.yml/badge.svg)](https://github.com/kpumuk/codecolorer/actions/workflows/ci.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/38191b47e77c9344e9c4/maintainability)](https://codeclimate.com/github/kpumuk/codecolorer/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/38191b47e77c9344e9c4/test_coverage)](https://codeclimate.com/github/kpumuk/codecolorer/test_coverage)
[![WordPress Plug-In Version](https://img.shields.io/wordpress/plugin/v/codecolorer.svg)](https://wordpress.org/plugins/codecolorer/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/codecolorer.svg)](https://wordpress.org/plugins/codecolorer/advanced/)
[![WordPress Version Supported](https://img.shields.io/wordpress/v/codecolorer.svg)](https://wordpress.org/plugins/codecolorer/)

![](https://ps.w.org/codecolorer/assets/banner-1544x500.png)

CodeColorer highlights code snippets in posts, comments, and RSS feeds with the
bundled GeSHi syntax highlighter. It supports block and inline code, themes,
line numbers, and comment-safe shortcode handling.

See the plugin page on [WordPress.org](https://wordpress.org/plugins/codecolorer/).

## Usage

Use `[cc]...[/cc]` for block code, `[cci]...[/cci]` for inline code, or
`<code>...</code>` for compatibility with existing content.

```text
[cc lang="php" line_numbers="true"]
<?php
echo "Hello, world!";
?>
[/cc]

Inline example: [cci lang="php"]$answer = 42;[/cci]
```

You can also use suffix shortcodes such as `[cc_php]...[/cc_php]` or
`[cci_javascript]...[/cci_javascript]`.

## Common Attributes

* `lang`: language slug such as `php`, `javascript`, `ruby`, or `css`.
* `line_numbers`: show or hide line numbers.
* `tab_size`: spaces per tab character.
* `lines` and `height`: control when vertical scrolling starts.
* `width` and `rss_width`: set block width on the site and in feeds.
* `theme`: choose a bundled theme such as `blackboard` or `solarized-dark`.
* `highlight`: highlight specific lines or ranges such as `1,5,8-11`.
* `escaped`: decode HTML entities before highlighting.
* `class`: append custom CSS classes to the wrapper element.
* `file`: load code from a path relative to the WordPress uploads directory for trusted content.

Always quote attribute values. Boolean attributes accept `true`/`false`,
`on`/`off`, or `1`/`0`. See [readme.txt](readme.txt) for the full parameter
reference and WordPress.org readme content.

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

WordPress.org releases are tag-driven. Create a plain Git tag such as `0.11.0`; do not use a `v` prefix.

The GitHub release workflow builds from the tag, applies [`.distignore`](.distignore),
publishes the plugin to WordPress.org SVN `trunk/`, and creates the matching SVN
tag.

Manual `workflow_dispatch` runs preflight by default. Set `perform_deploy` only when you want the workflow to commit to WordPress.org SVN.

Plugin-directory assets live in [`.wordpress-org/assets`](.wordpress-org/assets/)
and are synced to WordPress.org SVN `assets/` during a release. Source PSD files
stay in Git for editing, but are not published to WordPress.org SVN.

## Bundled GeSHi

CodeColorer vendors GeSHi `1.0.9.0` in [`lib/geshi.php`](lib/geshi.php)
(`GESHI_VERSION`). Repository history records the `1.0.9` vendor refresh and a
later PHP 8 compatibility backport, so treat `lib/geshi/` as upstream GeSHi
plus a small set of project-local maintenance patches.
