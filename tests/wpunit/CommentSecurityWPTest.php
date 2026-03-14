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
