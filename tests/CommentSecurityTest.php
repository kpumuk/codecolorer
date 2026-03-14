<?php
/*
CodeColorer plugin unit tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once 'test_helper.php';

class CommentSecurityTest extends CodeColorerTestCase {
    private $payload = "[cc class='\\\"><script>window.__codecolorer_poc=\"comment\"</script><div ']function x(){return 1;}[/cc]";
    private $disabledPayload = "[cc no_cc='true']<script>window.__codecolorer_poc=\"disabled\"</script>[/cc]";
    private $widthPayload = "[cc width='1px;\" onmouseover=\"alert(1)']function sized(){return 1;}[/cc]";

    private function runWithUploadDir($callback) {
        $old_upload_basedir = $GLOBALS['wp_upload_basedir'];
        $upload_basedir = sys_get_temp_dir() . '/codecolorer-comment-security-' . uniqid('', true);

        mkdir($upload_basedir, 0777, true);
        $GLOBALS['wp_upload_basedir'] = $upload_basedir;

        try {
            $callback($upload_basedir);
        } finally {
            $this->removeDirectory($upload_basedir);
            $GLOBALS['wp_upload_basedir'] = $old_upload_basedir;
        }
    }

    private function removeDirectory($directory) {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }

    private function assertNeutralizedScriptPayload($rendered, $code) {
        $this->assertStringNotContainsCompat('<script>', $rendered);
        $this->assertStringNotContainsCompat('codecolorer_poc', $rendered);
        $this->assertStringContainsCompat($code, $rendered);
    }

    private function assertRenderedLiteralHtmlIsPreserved($rendered, $literal_text) {
        $this->assertStringNotContainsCompat('<b>', $rendered);
        $this->assertStringContainsCompat($literal_text, $rendered);
        $this->assertStringContainsCompat('&lt;', $rendered);
        $this->assertStringContainsCompat('&lt;/', $rendered);
    }

  public function testMaliciousClassPayloadIsNotRenderedAsScript() {
    $rendered = codecolorer_highlight($this->payload);

    $this->assertNeutralizedScriptPayload($rendered, 'function x(){return 1;}');
  }

  public function testCommentPipelineDoesNotRenderInjectedScript() {
    $rendered = codecolorer_simulate_comment_render($this->payload);

    $this->assertNeutralizedScriptPayload($rendered, 'function x(){return 1;}');
  }

    public function testDisabledBlockEscapesHtmlFromComments() {
        $rendered = codecolorer_simulate_comment_render($this->disabledPayload);

        $this->assertStringNotContainsCompat('<script>', $rendered);
        $this->assertStringContainsCompat('&lt;script&gt;', $rendered);
        $this->assertStringContainsCompat('window.__codecolorer_poc=&quot;disabled&quot;', $rendered);
    }

    public function testCommentShortCodePhpBlockPreservesLiteralHtml() {
        $payload = '[cc_php]<b>tag</b>[/cc_php]';
        $submitted = codecolorer_simulate_comment_submission($payload);
        $rendered = codecolorer_render_comment($submitted);

        $this->assertSame($payload, $submitted);
        $this->assertRenderedLiteralHtmlIsPreserved($rendered, 'tag');
    }

    public function testCommentInlineShortCodePhpBlockPreservesLiteralHtml() {
        $payload = '[cci_php]<b>tag</b>[/cci_php]';
        $submitted = codecolorer_simulate_comment_submission($payload);
        $rendered = codecolorer_render_comment($submitted);

        $this->assertSame($payload, $submitted);
        $this->assertRenderedLiteralHtmlIsPreserved($rendered, 'tag');
    }

    public function testCommentPipelineKeepsFileAttributeForProtectedShortcodes() {
        $this->runWithUploadDir(function ($upload_basedir) {
            file_put_contents($upload_basedir . '/secret.txt', 'top-secret');

            $payload = '[cc file="secret.txt"][/cc]';
            $submitted = codecolorer_simulate_comment_submission($payload);
            $rendered = codecolorer_render_comment($submitted);

            $this->assertSame($payload, $submitted);
            $this->assertStringContainsCompat('top-secret', $rendered);
        });
    }

    public function testDimensionAttributeCannotBreakContainerStyle() {
        $rendered = codecolorer_highlight($this->widthPayload);

        $this->assertStringNotContainsCompat('onmouseover=', $rendered);
        $this->assertStringContainsCompat('function sized(){return 1;}', $rendered);
  }
}
