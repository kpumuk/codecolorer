<?php
/*
CodeColorer plugin unit tests
http://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once 'test_helper.php';

class LanguagesTest extends PHPUnit_Framework_TestCase {
  public function testTextWithoutCode() {
    $this->assertEquals('hello, world', codecolorer_highlight('hello, world'));
  }
  
  public function testTextWithCodeNoLanguage() {
    $this->assertEquals("\n\n<div class=\"codecolorer-container text default\" style=\"overflow:auto;white-space:nowrap;width:435px;\"><div class=\"text codecolorer\">hello, world</div></div>\n\n", codecolorer_highlight('[cc]hello, world[/cc]'));
  }

  public function testTextWithCodeTextLanguage() {
    $this->assertEquals("\n\n<div class=\"codecolorer-container text default\" style=\"overflow:auto;white-space:nowrap;width:435px;\"><div class=\"text codecolorer\">hello, world</div></div>\n\n", codecolorer_highlight('[cc lang="text"]hello, world[/cc]'));
  }

  public function testTextWithCodePhpLanguage() {
    $this->assertEquals("\n\n<div class=\"codecolorer-container php default\" style=\"overflow:auto;white-space:nowrap;width:435px;\"><div class=\"php codecolorer\"><span class=\"re0\">\$a</span> <span class=\"sy0\">=</span> <span class=\"nu0\">10</span><span class=\"sy0\">;</span></div></div>\n\n", codecolorer_highlight('[cc lang="php"]$a = 10;[/cc]'));
  }
}
