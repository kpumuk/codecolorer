<?php
/*
CodeColorer plugin unit tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

require_once 'test_helper.php';

class WrapperDivTest extends CodeColorerTestCase {
  private $old_wp_options;
  private $old_wp_is_feed;

  protected function setUp(): void {
    $this->old_wp_options = $GLOBALS['wp_options'];
    $this->old_wp_is_feed = $GLOBALS['wp_is_feed'];
  }

  protected function tearDown(): void {
    $GLOBALS['wp_options'] = $this->old_wp_options;
    $GLOBALS['wp_is_feed'] = $this->old_wp_is_feed;
  }

  public function testContainerClass() {
    $base = '\s+<div class="codecolorer-container';
    $this->assertMatchesRegexCompat("#$base text default\"#", codecolorer_highlight('[cc]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("#$base text default\"#", codecolorer_highlight('[cc lang="text"]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("#$base text twitlight\"#", codecolorer_highlight('[cc theme="twitlight"]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("#$base php default\"#", codecolorer_highlight('[cc lang="php"]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("#$base php twitlight\"#", codecolorer_highlight('[cc lang="php" theme="twitlight"]$a = 10;[/cc]'));

    $GLOBALS['wp_options']['codecolorer_theme'] = 'twitlight';
    $this->assertMatchesRegexCompat("#$base text twitlight\"#", codecolorer_highlight('[cc]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("#$base php twitlight\"#", codecolorer_highlight('[cc lang="php"]$a = 10;[/cc]'));
  }

  public function testContainerStyles() {
    $base = '\s+<div class="codecolorer-container.*?" style="overflow:auto;white-space:nowrap';
    $this->assertMatchesRegexCompat("#$base;width:435px;\"#", codecolorer_highlight('[cc]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("#$base;\"#", codecolorer_highlight('[cc width=""]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("#$base;width:50em;\"#", codecolorer_highlight('[cc width="50em"]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("#$base;width:435px;\"#", codecolorer_highlight("[cc lines=\"1\"]\$a = 10;[/cc]"));
    $this->assertMatchesRegexCompat("#$base;width:435px;height:300px;\"#", codecolorer_highlight("[cc lines=\"1\"]\$a = 10;\n\$b = 20;[/cc]"));
    $this->assertMatchesRegexCompat("#$base;width:435px;height:5em;\"#", codecolorer_highlight("[cc lines=\"1\" height=\"5em\"]\$a = 10;\n\$b = 20;[/cc]"));
  }

  public function testRssContainer() {
    $GLOBALS['wp_is_feed'] = true;
    $base = '\s+<div class="codecolorer-container.*?" style="overflow:auto;white-space:nowrap;border:1px solid #9F9F9F';
    $this->assertMatchesRegexCompat("/$base;width:435px;\"/", codecolorer_highlight('[cc]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("/$base;width:435px;\"/", codecolorer_highlight('[cc width="40em"]$a = 10;[/cc]'));
    $this->assertMatchesRegexCompat("/$base;width:40em;\"/", codecolorer_highlight('[cc rss_width="40em"]$a = 10;[/cc]'));
  }
}
