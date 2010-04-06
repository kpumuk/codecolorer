<?php
/*
CodeColorer plugin unit tests
http://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once 'test_helper.php';

class WrapperDivTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->old_wp_options = $GLOBALS['wp_options'];
  }
  
  public function tearDown() {
    $this->restoreWPOptions();
  }
  
  public function testContainerClass() {
    $base = '\s+<div class="codecolorer-container';
    $this->assertRegExp("#$base text default\"#", codecolorer_highlight('[cc]$a = 10;[/cc]'));
    $this->assertRegExp("#$base text default\"#", codecolorer_highlight('[cc lang="text"]$a = 10;[/cc]'));
    $this->assertRegExp("#$base text twitlight\"#", codecolorer_highlight('[cc theme="twitlight"]$a = 10;[/cc]'));
    $this->assertRegExp("#$base php default\"#", codecolorer_highlight('[cc lang="php"]$a = 10;[/cc]'));
    $this->assertRegExp("#$base php twitlight\"#", codecolorer_highlight('[cc lang="php" theme="twitlight"]$a = 10;[/cc]'));
    
    $GLOBALS['wp_options']['codecolorer_theme'] = 'twitlight';
    $this->assertRegExp("#$base text twitlight\"#", codecolorer_highlight('[cc]$a = 10;[/cc]'));
    $this->assertRegExp("#$base php twitlight\"#", codecolorer_highlight('[cc lang="php"]$a = 10;[/cc]'));
  }
  
  public function testContainerStyles() {
    $base = '\s+<div class="codecolorer-container.*?" style="overflow:auto;white-space:nowrap';
    $this->assertRegExp("#$base;width:435px;\"#", codecolorer_highlight('[cc]$a = 10;[/cc]'));
    $this->assertRegExp("#$base;\"#", codecolorer_highlight('[cc width=""]$a = 10;[/cc]'));
    $this->assertRegExp("#$base;width:50em;\"#", codecolorer_highlight('[cc width="50em"]$a = 10;[/cc]'));
    $this->assertRegExp("#$base;width:435px;\"#", codecolorer_highlight("[cc lines=\"1\"]\$a = 10;[/cc]"));
    $this->assertRegExp("#$base;width:435px;height:300px;\"#", codecolorer_highlight("[cc lines=\"1\"]\$a = 10;\n\$b = 20;[/cc]"));
    $this->assertRegExp("#$base;width:435px;height:5em;\"#", codecolorer_highlight("[cc lines=\"1\" height=\"5em\"]\$a = 10;\n\$b = 20;[/cc]"));
  }
  
  public function testRssContainer() {
    $GLOBALS['wp_is_feed'] = true;
    $base = '\s+<div class="codecolorer-container.*?" style="overflow:auto;white-space:nowrap;border:1px solid #9F9F9F';
    $this->assertRegExp("/$base;width:435px;\"/", codecolorer_highlight('[cc]$a = 10;[/cc]'));
    $this->assertRegExp("/$base;width:435px;\"/", codecolorer_highlight('[cc width="40em"]$a = 10;[/cc]'));
    $this->assertRegExp("/$base;width:40em;\"/", codecolorer_highlight('[cc rss_width="40em"]$a = 10;[/cc]'));
  }
  
  public function restoreWPOptions() {
    $GLOBALS['wp_options'] = $this->old_wp_options;
  }
}