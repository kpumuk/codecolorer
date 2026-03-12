<?php
/*
CodeColorer WordPress integration tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once __DIR__ . '/CodeColorerWPTestCase.php';

class RenderingWPTest extends CodeColorerWPTestCase
{
    public function test_content_filter_renders_php_block()
    {
        $rendered = $this->renderContent('[cc lang="php"]$a = 10;[/cc]');

        $this->assertStringContainsString('class="codecolorer-container php default"', $rendered);
        $this->assertStringContainsString('<span class="re0">$a</span>', $rendered);
    }

    public function test_inline_shortcode_renders_inline_code()
    {
        $rendered = $this->renderContent('Inline example: [cci lang="javascript"]const answer = 42;[/cci]');

        $this->assertStringContainsString('<code class="codecolorer javascript default">', $rendered);
        $this->assertStringContainsString('const', $rendered);
    }

    public function test_dimension_payload_cannot_escape_style_attribute()
    {
        $rendered = $this->renderContent('[cc width=\'1px;" onmouseover="alert(1)\']function sized(){return 1;}[/cc]');

        $this->assertStringNotContainsString('onmouseover=', $rendered);
        $this->assertStringContainsString('function sized(){return 1;}', $rendered);
    }
}
