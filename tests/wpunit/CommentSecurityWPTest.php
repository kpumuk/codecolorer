<?php
/*
CodeColorer WordPress integration tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once __DIR__ . '/CodeColorerWPTestCase.php';

class CommentSecurityWPTest extends CodeColorerWPTestCase
{
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
}
