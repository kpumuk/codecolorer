=== CodeColorer ===
Contributors: kpumuk
Tags: code, snippet, syntax, highlight, color, geshi
Requires at least: 2.0.2
Tested up to: 2.7
Stable tag: 0.7.2

CodeColorer is the plugin which allows you to insert code snippets to your posts with nice syntax highlighting powered by GeSHi library.

== Description ==

CodeColorer is the plugin which allows you to insert code snippets into the post with nice syntax highlighting.

Plugin based on GeSHi library, which supports most languages. CodeColorer has various nice features:

* line numbers
* automatic links to the documentation inserting
* code block intelligent scroll detection (short code would have short block, for long one block height would be fixed and scrollbar would appear)
* predefined color themes (Slush &amp; Poppies, Blackboard, Dawn, Mac Classic, Twitlight, Vibrant Ink)
* syntax colors customization in CSS file
* syntax highlighting of the code in comments
* code protect from mangling by Wordpress (for example, quotes, double-dashes, etc would look just right as you entered)

= Changelog =

* v0.7.1 (Dec 30, 2008)
  * Fixed color scheme for XML language.
  * Added custom CSS styles block to the options page.
  * Removed <tt>codecolorer.css.in</tt> file as deprecated.
  * Fixed bug wich code block paddings when line numbers are disabled.
* v0.7.0 (Dec 29, 2008)
  * Added predefined color themes (Slush & Poppies, Blackboard, Dawn, Mac Classic, Twitlight, Vibrant Ink).
  * Added block width and height options, removed line height.
  * Fixed bug with plain text formatting.
  * Added ability to remove vertical scrollbar.
  * Line numbers will be rendered into the table, so they will not be copied to clipboard along with code.
  * Options page layout updated to Wordpress 2.7 look&feel.
  * GeSHi updated to version 1.0.8.2.

You can find complete changelog on the <a href="http://kpumuk.info/projects/wordpress-plugins/codecolorer/">plugin home page</a>.

== Installation ==

1. Download and unpack plugin files to <tt>wp-content/plugins/codecolorer</tt> directory.
2. Enable "CodeColorer" plugin on your <em>Plugins</em> page in <em>Site Admin</em>.
3. Go to the <em>Options/CodeColorer</em> page in <em>Site Admin</em> and change plugin's options as you wish.
4. Use <tt>[cc lang=&quot;lang&quot;]code[/cc]</tt> or <tt>&lt;code lang=&quot;lang&quot;&gt;code&lt;/cc&gt;</tt> syntax to insert code snippet into the post (you could skip <tt>lang=&quot;lang&quot;</tt>, in this case code would be in <tt>codecolorer</tt> block, but without syntax highlighting).
5. Have fun!

= Syntax =

To insert code snippet into your post (or comment) you should use <tt>[cc lang=&quot;lang&quot;]code[/cc]</tt> or <tt>&lt;code lang=&quot;lang&quot;&gt;code&lt;/cc&gt;</tt> syntax. Starting from version 0.6.0 you could specify additional CodeColorer options inside <tt>[cc]</tt> tag:

	[cc lang="php" tab_size="2" lines="40"]
	// some code
	[/cc]

Note: You should always use double quotes or single quotes around the parameter value. Boolean values could be passed using string <tt>true</tt> or <tt>false</tt>, number <tt>1</tt> or <tt>0</tt>.

Possible parameters:

* <tt>lang</tt> (string) -- source language.
* <tt>tab_size</tt> (integer) -- how many spaces would represent TAB symbol.
* <tt>line_numbers</tt> (boolean) -- when <tt>true</tt> line numbers will be added.
* <tt>no_links</tt> (boolean) -- when <tt>false</tt> keywords will be represented as links to manual.
* <tt>lines</tt> (integer) -- how many lines would be block height without scroll; could be set to <tt>-1</tt> to remove vertical scrollbar.
* <tt>width</tt> (integer) -- block width.
* <tt>height</tt> (integer) -- height in pixels; used when lines number is greater then "lines" value.
* <tt>theme</tt> (string) -- color theme (default, blackboard, dawn, mac-classic, twitlight, vibrant).

All of these parameters could be configured via the CodeColorer options page. 

== Screenshots ==

1. Ruby syntax highlighting without scrollbars.
2. CSS syntax highlighting with scrollbars.

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

== Supported languages ==

Here is list of supported by CodeColorer languages: abap, actionscript, actionscript3, ada, apache, applescript, apt_sources, asm, asp, autoit, avisynth, bash, basic4gl, bf, blitzbasic, bnf, boo, c, c_mac, caddcl, cadlisp, cfdg, cfm, cil, cobol, cpp-qt, cpp, csharp, css-gen.cfg, css, d, delphi, diff, div, dos, dot, eiffel, email, fortran, freebasic, genero, gettext, glsl, gml, gnuplot, groovy, haskell, hq9plus, html4strict, idl, ini, inno, intercal, io, java, java5, javascript, kixtart, klonec, klonecpp, latex, lisp, lolcode, lotusformulas, lotusscript, lscript, lua, m68k, make, matlab, mirc, mpasm, mxml, mysql, nsis, objc, ocaml-brief, ocaml, oobas, oracle11, oracle8, pascal, per, perl, php-brief, php, pic16, pixelbender, plsql, povray, powershell, progress, prolog, providex, python, qbasic, rails, reg, robots, ruby, sas, scala, scheme, scilab, sdlbasic, smalltalk, smarty, sql, tcl, teraterm, text, thinbasic, tsql, typoscript, vb, vbnet, verilog, vhdl, vim, visualfoxpro, visualprolog, whitespace, winbatch, xml, xorg_conf, xpp, yaml, z80.

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
