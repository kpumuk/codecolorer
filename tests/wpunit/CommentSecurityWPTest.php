<?php
/*
CodeColorer WordPress integration tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once __DIR__ . '/CodeColorerWPTestCase.php';

class CommentSecurityWPTest extends CodeColorerWPTestCase
{
    private function assertRenderedLiteralHtmlIsPreserved($rendered, $literal_text)
    {
        $this->assertStringNotContainsString('<b>', $rendered);
        $this->assertStringContainsString($literal_text, $rendered);
        $this->assertStringContainsString('&lt;', $rendered);
        $this->assertStringContainsString('&lt;/', $rendered);
    }

    private function withUploadsFile($relative_path, $contents, $callback)
    {
        $uploads = wp_upload_dir();
        $file_path = path_join($uploads['basedir'], $relative_path);
        $directory = dirname($file_path);

        wp_mkdir_p($directory);
        file_put_contents($file_path, $contents);

        try {
            $callback($relative_path);
        } finally {
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            while ($directory !== $uploads['basedir'] && is_dir($directory)) {
                @rmdir($directory);
                $directory = dirname($directory);
            }
        }
    }

    public function test_comment_submission_preserves_shortcode_but_renders_safely()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $payload = '[cc class=\'"><script>window.__codecolorer_poc="comment"</script><div \']function x(){return 1;}[/cc]';

        $comment_id = $this->createApprovedComment($post_id, $payload);
        $comment = get_comment($comment_id);
        $rendered = $this->renderComment($comment_id);

        $this->assertSame($payload, $comment->comment_content);
        $this->assertStringNotContainsString('<script>', $rendered);
        $this->assertStringNotContainsString('codecolorer_poc', $rendered);
        $this->assertStringContainsString('function x(){return 1;}', $rendered);
    }

    public function test_disabled_code_block_escapes_html_in_comments()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $payload = '[cc no_cc="true"]<script>window.__codecolorer_poc="disabled"</script>[/cc]';

        $comment_id = $this->createApprovedComment($post_id, $payload);
        $rendered = $this->renderComment($comment_id);

        $this->assertStringNotContainsString('<script>', $rendered);
        $this->assertStringContainsString('&lt;script&gt;', $rendered);
    }

    public function test_comment_short_code_php_block_preserves_literal_html()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $payload = '[cc_php]<b>tag</b>[/cc_php]';

        $comment_id = $this->createApprovedComment($post_id, $payload);
        $comment = get_comment($comment_id);
        $rendered = $this->renderComment($comment_id);

        $this->assertSame($payload, $comment->comment_content);
        $this->assertRenderedLiteralHtmlIsPreserved($rendered, 'tag');
    }

    public function test_comment_inline_short_code_php_block_preserves_literal_html()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $payload = '[cci_php]<b>tag</b>[/cci_php]';

        $comment_id = $this->createApprovedComment($post_id, $payload);
        $comment = get_comment($comment_id);
        $rendered = $this->renderComment($comment_id);

        $this->assertSame($payload, $comment->comment_content);
        $this->assertRenderedLiteralHtmlIsPreserved($rendered, 'tag');
    }

    public function test_kses_cannot_synthesize_shortcode_around_protected_code()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $payload = '[c<x>c]text <code>[/cc]<img src=x onerror="alert(document.domain)"></code>';

        $comment_id = $this->createApprovedComment($post_id, $payload);
        $rendered = $this->renderComment($comment_id);

        $this->assertStringNotContainsString('<img', $rendered);
        $this->assertStringNotContainsString('onerror="', $rendered);
    }

    public function test_code_block_inside_attribute_cannot_break_out_of_tag()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $payload = '<a title="[cc]x[/cc]">q</a> <a href="https://x.test/ onmouseover=alert(1) x=">r</a>';

        $comment_id = $this->createApprovedComment($post_id, $payload);
        $rendered = $this->renderComment($comment_id);

        $this->assertStringNotContainsString('title="', $rendered);
    }

    public function test_html_after_comment_edit_is_filtered_on_display()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $comment_id = $this->createApprovedComment($post_id, 'first version');

        wp_update_comment(
            wp_slash(
                array(
                    'comment_ID' => $comment_id,
                    'comment_content' => '[cc]text <code>[/cc&#129;]<img src=x onerror="alert(1)"></code>',
                )
            )
        );
        $rendered = $this->renderComment($comment_id);

        $this->assertStringNotContainsString('<img', $rendered);
    }

    public function test_unfiltered_html_author_keeps_html_around_code()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $admin_id = self::factory()->user->create(array('role' => 'administrator'));
        if (is_multisite()) {
            grant_super_admin($admin_id);
        }
        $comment_id = self::factory()->comment->create(
            array(
                'comment_post_ID' => $post_id,
                'user_id' => $admin_id,
                'comment_content' => '<img src="https://example.com/a.png" alt="a"> [cc]x[/cc]',
            )
        );
        $rendered = $this->renderComment($comment_id);

        $this->assertStringContainsString('<img src="https://example.com/a.png"', $rendered);
        $this->assertStringContainsString('codecolorer-container', $rendered);
    }

    public function test_unfiltered_html_author_comment_is_still_filtered_for_scripts()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $admin_id = self::factory()->user->create(array('role' => 'administrator'));
        if (is_multisite()) {
            grant_super_admin($admin_id);
        }
        // An author without unfiltered_html may edit an admin's comment through kses.
        $comment_id = self::factory()->comment->create(
            array(
                'comment_post_ID' => $post_id,
                'user_id' => $admin_id,
                'comment_content' => '[cc]text <code>[/cc]<img src=x onerror="alert(1)"></code>',
            )
        );
        $rendered = $this->renderComment($comment_id);

        $this->assertStringNotContainsString('onerror', $rendered);
    }

    public function test_span_classes_are_reduced_to_note_mentions()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $comment_id = $this->createApprovedComment($post_id, '[c<x>c]a <code>[/cc]<span class="wp-note-mention user-1 screen-reader-text">b</span></code>');
        $rendered = $this->renderComment($comment_id);

        $this->assertStringNotContainsString('screen-reader-text', $rendered);
    }

    public function test_link_uncovered_at_display_gets_ugc_rel()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $comment_id = $this->createApprovedComment($post_id, '[c<x>c]a <code>[/cc]<a href="https://example.com/" rel="author">b</a></code>');
        $rendered = $this->renderComment($comment_id);

        $this->assertStringContainsString('nofollow ugc"', $rendered);
    }

    public function test_link_with_apostrophe_keeps_its_url()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $comment_id = $this->createApprovedComment($post_id, "<a href=\"https://example.com/it's\">x</a>");
        $rendered = $this->renderComment($comment_id);

        $this->assertStringNotContainsString('&amp;', $rendered);
        $this->assertStringContainsString('nofollow ugc', $rendered);
    }

    public function test_allowed_html_filter_replacing_the_list_keeps_code_blocks()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        $comment_id = $this->createApprovedComment($post_id, '<b>hi</b> [cc]echo 1;[/cc]');
        $replace = function () {
            return array('b' => array());
        };

        add_filter('codecolorer_comment_allowed_html', $replace);
        $rendered = $this->renderComment($comment_id);
        remove_filter('codecolorer_comment_allowed_html', $replace);

        $this->assertStringContainsString('codecolorer-container', $rendered);
        $this->assertStringNotContainsString('CODECOLORER_BLOCK', $rendered);
    }

    public function test_allowed_html_filter_widens_display_allowlist()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );
        // An imported comment saved without kses and without a user.
        $comment_id = self::factory()->comment->create(
            array(
                'comment_post_ID' => $post_id,
                'comment_content' => '<img src="https://example.com/cat.jpg" alt="cat"> [cc]x[/cc]',
            )
        );
        $allow_img = function ($allowed) {
            $allowed['img'] = array('src' => true, 'alt' => true);
            return $allowed;
        };

        $this->assertStringNotContainsString('<img', $this->renderComment($comment_id));

        add_filter('codecolorer_comment_allowed_html', $allow_img);
        $rendered = $this->renderComment($comment_id);
        remove_filter('codecolorer_comment_allowed_html', $allow_img);

        $this->assertStringContainsString('<img src="https://example.com/cat.jpg"', $rendered);
    }

    public function test_comment_file_attribute_reads_from_uploads_folder()
    {
        $post_id = self::factory()->post->create(
            array(
                'post_status' => 'publish',
                'comment_status' => 'open',
            )
        );

        $this->withUploadsFile('codecolorer-tests/comment-secret.txt', 'top-secret', function ($relative_path) use ($post_id) {
            $payload = '[cc file="' . $relative_path . '"][/cc]';

            $comment_id = $this->createApprovedComment($post_id, $payload);
            $comment = get_comment($comment_id);
            $rendered = $this->renderComment($comment_id);

            $this->assertSame($payload, $comment->comment_content);
            $this->assertStringContainsString('top-secret', $rendered);
        });
    }
}
