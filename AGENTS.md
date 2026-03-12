# CodeColorer

Mature WordPress plugin. GeSHi syntax highlighter. Supports posts, inline code, RSS, comments.

## Files

- `codecolorer.php`: loader/hooks/runtime guard
- `codecolorer-core.php`: shortcode parse, comment protect/restore, GeSHi render, wrappers
- `codecolorer-options.php`: shortcode/admin options, defaults, normalize
- `codecolorer-admin.php`: settings UI
- `tests/test_helper.php`: fake WP funcs + test helpers

## How it works

- Main shortcode: `[cc]...[/cc]`
- Inline shortcode: `[cci]...[/cci]`
- Comments path hides shortcode on `pre_comment_content`, restores later. Easy security footgun.
- Follow WordPress plugin best practices. Sanitize early, escape late, keep broad WP/PHP compatibility.
- When touching attrs/output, use WP helpers, not raw concat:
  - sanitize: `sanitize_key`, `sanitize_html_class`, `sanitize_text_field`
  - escape: `esc_attr`, `esc_html`

## Dev

- Tooling: `mise` + Composer + `pnpm`
- Install: `mise install && mise run bootstrap`
- WP local: `mise run wp-start`
- Site: `http://localhost:8888`

## Tests

- Main: `mise run test`
- Direct: `vendor/bin/phpunit --configuration tests/phpunit9.xml tests`
- Old PHP check: GH Actions matrix tests every minor `7.0` to `8.5`
- Local old PHP smoke: Docker `php:7.0-cli` + PHPUnit 6.5.14

## Notes

- Local WP env uses `wp-env`
- Package manager is `pnpm`, not `npm`
- CI is GitHub Actions, not CircleCI
- `.distignore` excludes dev files from plugin zip
