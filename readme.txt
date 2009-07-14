=== CodeColorer ===
Contributors: kpumuk
Tags: code, snippet, syntax, highlight, color, geshi
Requires at least: 2.0.2
Tested up to: 2.8.1
Stable tag: 0.8.4

CodeColorer is the plugin which allows you to insert code snippets to your posts with nice syntax highlighting powered by GeSHi library.

== Description ==

CodeColorer is the plugin which allows you to insert code snippets into the post with nice syntax highlighting.

Plugin based on GeSHi library, which supports most languages. CodeColorer has various nice features:

* syntax highlighting in RSS feeds
* syntax highlighting of single line of code (inline)
* syntax highlighting of code in comments
* line numbers
* automatic links to the documentation inserting
* code block intelligent scroll detection (short code would have short block, for long one block height would be fixed and scrollbar would appear)
* predefined color themes (Slush &amp; Poppies, Blackboard, Dawn, Mac Classic, Twitlight, Vibrant Ink)
* syntax colors customization in CSS file
* code protect from mangling by Wordpress (for example, quotes, double-dashes, etc would look just right as you entered)

= Translations =

Thank you all guys, who submitted translations to your language. CodeColorer is currently available in following languages:

* English
* Russian - Русский
* Ukrainian - Українська
* Italian - Italiano (thanks to <a href="http://codesnippet.altervista.org/">CodeSnippet</a>)
* Belarusian - Беларуский (thanks to <a href="http://www.fatcow.com">И. Фадков</a>)
* Simplified Chinese - 简化字 (thanks to <a href="http://ixiezi.com">liuxiangqian</a>)

= Changelog =

* v0.8.4 (Development)
  * Fixed inline code blocks formatting.
  * Added special tag [cci] which works just like [cc], but with inline forced.
  * Fixed problem when line numbers could not be disabled (thanks to miaow).
* v0.8.3 (Jul 14, 2009)
  * Added a new option "strict" to enable or disable strict mode.
  * Added a new option "inline" which forces code block to render inside &lt;code&gt; tag. Used to paste a single line of code into the regular text.
  * Trim only empty lines in the beginning of code, leave spaces untouched (thanks to FeepingCreature -- contact me please and I will add your link here).
  * Allow "on" and "off" as boolean parameter value.
  * Added Italian translation (thanks to <a href="http://codesnippet.altervista.org/">CodeSnippet</a>).
  * Added Belarusian translation (thanks to <a href="http://www.fatcow.com">И. Фадков</a>).
* v0.8.2 (Jul 14, 2009)
  * Fixed problem with width and heigth specified at the same time.
  * Added a new option "nowrap" to disable horizontal scrollbar.
  * Added a new option "noborder" to disable a border around code block.
  * Fixed inner table borders in some WordPress themes.
  * Geshi updated to 1.0.8.4.
* v0.8.1 (Jul 13, 2009)
  * Fixed problem with missing CodeColorer styles when no custom CSS entered (thanks to <a href="http://codesnippet.altervista.org/">CodeSnippet</a>).
* v0.8.0 (Jul 13, 2009)
  * Added more styles for comments.
  * Added ability to specify width and height in px, em, percents. Also values could be omitted to use whole width and height available.
  * Fixed problem which blocked the access to CodeColorer options page in WordPress 2.8.1.
  * Use plain old CSS files instead of slow styles.php.
  * Fixed code blocks formatting in RSS feeds. Added rss_width option.
  * Added Chinese translation (thanks to <a href="http://ixiezi.com">liuxiangqian</a>).

You can find complete changelog on the <a href="http://kpumuk.info/projects/wordpress-plugins/codecolorer/">plugin home page</a>.

== Installation ==

1. Download and unpack plugin files to <tt>wp-content/plugins/codecolorer</tt> directory.
2. Enable "CodeColorer" plugin on your <em>Plugins</em> page in <em>Site Admin</em>.
3. Go to the <em>Options/CodeColorer</em> page in <em>Site Admin</em> and change plugin's options as you wish.
4. Use <tt>[cc lang=&quot;lang&quot;]code[/cc]</tt> or <tt>&lt;code lang=&quot;lang&quot;&gt;code&lt;/cc&gt;</tt> syntax to insert code snippet into the post (you could skip <tt>lang=&quot;lang&quot;</tt>, in this case code would be in <tt>codecolorer</tt> block, but without syntax highlighting). Also you can use <tt>[cci lang="lang"]code[/cci]</tt> to format inline code (see the "inline" option description).
5. Have fun!

= Syntax =

To insert code snippet into your post (or comment) you should use <tt>[cc lang=&quot;lang&quot;]code[/cc]</tt> or <tt>&lt;code lang=&quot;lang&quot;&gt;code&lt;/cc&gt;</tt> syntax. Starting from version 0.6.0 you could specify additional CodeColorer options inside <tt>[cc]</tt> tag:

  [cc lang="php" tab_size="2" lines="40"]
  // some code
  [/cc]

Note: You should always use double quotes or single quotes around the parameter value. Boolean values could be passed using string <tt>true</tt> or <tt>false</tt>, <tt>on</tt> or <tt>off</tt>, number <tt>1</tt> or <tt>0</tt>.

Possible parameters:

* <tt>lang</tt> (string) -- source language.
* <tt>tab_size</tt> (integer) -- how many spaces would represent TAB symbol.
* <tt>line_numbers</tt> (boolean) -- when <tt>true</tt> line numbers will be added.
* <tt>first_line</tt> (integer) -- a number of the first line in the block.
* <tt>no_links</tt> (boolean) -- when <tt>false</tt> keywords will be represented as links to manual.
* <tt>lines</tt> (integer) -- how many lines would be block height without scroll; could be set to <tt>-1</tt> to remove vertical scrollbar.
* <tt>width</tt> (integer or string) -- block width.
* <tt>height</tt> (integer or string) -- height in pixels; used when lines number is greater then "lines" value.
* <tt>rss_width</tt> (integer or string) -- block width in RSS feeds.
* <tt>theme</tt> (string) -- color theme (default, blackboard, dawn, mac-classic, twitlight, vibrant).
* <tt>inline</tt> (boolean) -- when <tt>true</tt> forces code block to render inside &lt;code&gt;. Used to paste a single line of code into the regular text.
* <tt>strict</tt> (boolean) -- when <tt>true</tt> <a href="http://qbnz.com/highlighter/geshi-doc.html#using-strict-mode">strict mode</a> will be enabled. By default CodeColorer tries to guess whether strict mode is needed, so this option allows to force it on or off when automatic suggestion is wrong.
* <tt>nowrap</tt> (boolean) -- when <tt>false</tt> no horizontal scrollbar will be shown; instead code will be wrapped in the end of code box.
* <tt>noborder</tt> (boolean) -- when <tt>true</tt> no border will be shown around the code block.
* <tt>no_cc</tt> (boolean) -- when <tt>true</tt> the syntax in code block will not be highlighted, code will be rendered inside &lt;code&gt;&lt;/code&gt; tag.

You can use special tag [cci] instead of [cc] to force inline mode:

  [cci lang="php"]some code[/cci]

Most of these parameters could be configured via the CodeColorer options page.

== Screenshots ==

1. Ruby syntax highlighting without scrollbars (Vibrant theme).
2. Ruby syntax highlighting with scrollbars (Twitlight theme).

== Frequently Asked Questions ==

*Q*. How do I can customize CodeColorer CSS rules?  
*A*. Go to the <em>Options/CodeColorer</em> page in <em>Site Admin</em> and change the "Custom CSS Styles" option.

*Q*. I see &amp;lt; instead of &lt; (or other HTML entities like &gt;, &amp;, &quot;) in my code.  
*A*. You are should not use the visual editor when writing code into the post.

*Q*. Does it highlights my code on server or client side?  
*A*. CodeColorer performs code highlighting on the server, you could see HTML of the highlighted code in page source.

*Q*. Is it produces valid XHTML source?  
*A*. Yes, resulting XHTML is completely valid.

*Q*. Could my visitors insert their code snippets in comments?  
*A*. Yes, CodeColorer supports code highlighting in comments using the same syntax, as you use in your blog posts.

*Q*. How can I disable syntax highlighting for a particular &lt;code&gt; block?  
*A*. Use <tt>no_cc="true"</tt> option for your code block.

*Q*. I have updated the plugin to the newest version and now I keep getting following warnings:

  Warning: array_keys() [function.array-keys]: The first argument should be an array in /home/wordpress/wp-content/plugins/codecolorer/lib/geshi.php on line 3599
  
*A*. Remove all files from codecolorer folder and unpack an archive with plugin again (thanks to <a href="http://blog.t-l-k.com/">Anatoliy 'TLK' Kolesnick</a>).

== Supported languages ==

Here is list of supported by CodeColorer languages: abap, actionscript, actionscript3, ada, apache, applescript, apt\_sources, asm, asp, autoit, avisynth, bash, basic4gl, bf, bibtex, blitzbasic, bnf, boo, c, c\_mac, caddcl, cadlisp, cfdg, cfm, cil, cmake, cobol, cpp-qt, cpp, csharp, css, d, dcs, delphi, diff, div, dos, dot, eiffel, email, erlang, fo, fortran, freebasic, genero, gettext, glsl, gml, gnuplot, groovy, haskell, hq9plus, html4strict, idl, ini, inno, intercal, io, java, java5, javascript, kixtart, klonec, klonecpp, latex, lisp, locobasic, lolcode, lotusformulas, lotusscript, lscript, lsl2, lua, m68k, make, matlab, mirc, modula3, mpasm, mxml, mysql, nsis, oberon2, objc, ocaml-brief, ocaml, oobas, oracle11, oracle8, pascal, per, perl, php-brief, php, pic16, pixelbender, plsql, povray, powershell, progress, prolog, properties, providex, python, qbasic, rails, rebol, reg, robots, ruby, sas, scala, scheme, scilab, sdlbasic, smalltalk, smarty, sql, tcl, teraterm, text, thinbasic, tsql, typoscript, vb, vbnet, verilog, vhdl, vim, visualfoxpro, visualprolog, whitespace, whois, winbatch, xml, xorg\_conf, xpp, yaml, z80.

== Customization ==

Syntax coloring is highly customizable: you could change color scheme for all languages or for specific language. You could find CodeColorer CSS in <tt>wp-content/plugins/codecolorer/codecolorer.css</tt> file. To change colors for all languages edit lines below <em>Color scheme</em> section. Usually you would use only following CSS classes:

* *kw1*, *kw2*, *kw3* - keywords
* *co1*, *co2*, *coMULTI* - comments
* *es0* - escaped chars
* *br0* - brackets
* *st0* - strings
* *nu0* - numbers
* *me0* - methods

To change colors for specific language copy default values and add language name with a period before it. For example, you could use following color scheme for PHP:

	.php .codecolorer .kw1 { color: #FF6600; font-weight: bolder; }
	.php .codecolorer .kw2 { color: #339999; }
	.php .codecolorer .kw3 { color: #FF6600; }
	.php .codecolorer .kw4 { color: #DDE93D; }
	.php .codecolorer .kw5 { color: #999966; }
	.php .codecolorer .st0 { color: #66FF00; }
	.php .codecolorer .es0 { color: #42A500; }
	.php .codecolorer .br0 { color: Olive; }
	.php .codecolorer .nu0 { color: #CCFF33; font-weight: bolder; }
	.php .codecolorer .re0 { color: #339999; }
	.php .codecolorer .re1 { color: #FFCC00; }
	.php .codecolorer .re3 * { color: #FFFFFF; }
	.php .codecolorer .re4, .php .codecolorer .re4 * {
		color: #64A2FF;
	}
	.php .codecolorer .co1, .php .codecolorer .co2,
	.php .codecolorer .coMULTI { color: #9933CC; }

Also you could change width of the code block in the top of CSS file (there are different values for different situations, for example when you code is places under <tt>&lt;blockquote&gt;</tt>).
