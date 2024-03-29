<?php
/*
CodeColorer plugin unit tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once 'test_helper.php';

use PHPUnit\Framework\TestCase;

class HighlightTest extends TestCase {
  private $shtag;
  private $ehtag;

  protected function setUp(): void {
    $this->shtag = "<span class=\"xtra ln-xtra\">";
    $this->ehtag = "</span>";
  }

  public function testHighlightOneLine() {
    $this->assertMatchesRegularExpression("#hello<br />\n{$this->shtag}world<br />{$this->ehtag}!#", codecolorer_highlight("[cc highlight=\"2\"]hello\nworld\n![/cc]"));
  }

  public function testHighlightTwoLines() {
    $this->assertMatchesRegularExpression("#{$this->shtag}hello<br />{$this->ehtag}{$this->shtag}world<br />{$this->ehtag}!#", codecolorer_highlight("[cc highlight=\"2,1\"]hello\nworld\n![/cc]"));
  }

  public function testHighlightRange() {
    $this->assertMatchesRegularExpression("#{$this->shtag}hello<br />{$this->ehtag}{$this->shtag}world<br />{$this->ehtag}{$this->shtag}!{$this->ehtag}#", codecolorer_highlight("[cc highlight=\"2-3,1\"]hello\nworld\n![/cc]"));
  }

  public function testHighlightWithCodePhpLanguage() {
    $this->assertMatchesRegularExpression("#{$this->shtag}<span class=\"re0\">\\\$a</span> <span class=\"sy0\">=</span> <span class=\"nu0\">10</span><span class=\"sy0\">;</span><br />{$this->ehtag}<span class=\"re0\">\\\$b</span> <span class=\"sy0\">=</span> <span class=\"nu0\">20</span><span class=\"sy0\">;</span>#", codecolorer_highlight("[cc lang=\"php\" highlight=\"1\"]\$a = 10;\n\$b = 20;[/cc]"));
  }

  public function testHighlightWithGeshiTheme() {
    $this->shtag = "<span style=\"display:block;background-color:\\#ffff66\">";
    $this->assertMatchesRegularExpression("#hello<br />\n{$this->shtag}world<br />{$this->ehtag}!#", codecolorer_highlight("[cc theme=\"geshi\" highlight=\"2\"]hello\nworld\n![/cc]"));
  }
}
