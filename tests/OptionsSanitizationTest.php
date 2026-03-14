<?php
/*
CodeColorer plugin unit tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once 'test_helper.php';
require_once 'codecolorer-options.php';

class OptionsSanitizationTest extends CodeColorerTestCase {
    public function testDimensionOptionRejectsInjectedAttributePayload() {
        $this->assertSame('', CodeColorerOptions::sanitizeDimensionOption('1px;" onmouseover="alert(1)'));
    }

    public function testThemeOptionAllowsKnownThemesOnly() {
        $this->assertSame('twitlight', CodeColorerOptions::sanitizeThemeOption('twitlight'));
        $this->assertSame('', CodeColorerOptions::sanitizeThemeOption('unknown-theme'));
        $this->assertSame('', CodeColorerOptions::sanitizeThemeOption('default'));
    }

    public function testCustomClassOptionKeepsOnlySafeClassNames() {
        $this->assertSame('code-sample other_class', CodeColorerOptions::sanitizeCustomClassOption('code-sample bad$class other_class code-sample'));
    }

    public function testCssStyleSanitizerRemovesStyleBreakingMarkup() {
        $sanitized = CodeColorerOptions::sanitizeCssStyle("body { color: red; }\n</style><script>alert(1)</script>");

        $this->assertStringContainsCompat('body { color: red; }', $sanitized);
        $this->assertStringNotContainsCompat('</style>', $sanitized);
        $this->assertStringNotContainsCompat('<script>', $sanitized);
    }

    public function testTabSizeFallsBackToDefaultWhenInvalid() {
        $this->assertSame(4, CodeColorerOptions::sanitizeTabSize(0));
        $this->assertSame(8, CodeColorerOptions::sanitizeTabSize(8));
    }
}
