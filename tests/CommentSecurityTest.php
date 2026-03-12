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

  private function assertNeutralizedScriptPayload($rendered, $code) {
    $this->assertStringNotContainsCompat('<script>', $rendered);
    $this->assertStringNotContainsCompat('codecolorer_poc', $rendered);
    $this->assertStringContainsCompat($code, $rendered);
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

  public function testDimensionAttributeCannotBreakContainerStyle() {
    $rendered = codecolorer_highlight($this->widthPayload);

    $this->assertStringNotContainsCompat('onmouseover=', $rendered);
    $this->assertStringContainsCompat('function sized(){return 1;}', $rendered);
  }
}
