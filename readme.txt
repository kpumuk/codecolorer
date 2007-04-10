=== CodeColorer ===
Contributors: kpumuk
Tags: code, snippet, syntax, highlight, color, geshi
Requires at least: 2.0.2
Tested up to: 2.1
Stable tag: 0.5.1

CodeColorer is the plugin which allows you to insert code snippets into the post with nice syntax highlighting.

== Description ==

Plugin based on GeSHi library, which supports most languages. CodeColorer has various nice features:

* line numbers
* automatic links to the documentation inserting
* code block size calculating (short code would have short block, for long one block height would be fixed and scrollbar would appear)
* code block style customization on Site Admin
* syntax colors customization in CSS file
* syntax highlighting of the code in comments
* code protect from mangling by Wordpress (for example, quotes, double-dashes, etc would look just right as you entered)

== Installation ==

1. Download and unpack plugin files to <tt>wp-content/plugins/codecolorer</tt> directory.
2. If you have not used CodeColorer early, rename <tt>wp-content/plugins/codecolorer/codecolorer.css.in</tt> to <tt>wp-content/plugins/codecolorer/codecolorer.css</tt>.
3. Enable "CodeColorer" plugin on your <em>Plugins</em> page in <em>Site Admin</em>.
4. Go to the <em>Options/CodeColorer</em> page in <em>Site Admin</em> and change plugin's options as you wish.
5. Use <tt>[cc lang=&quot;lang&quot;]code[/cc]</tt> or <tt>&lt;code lang=&quot;lang&quot;&gt;code&lt;/cc&gt;</tt> syntax to insert code snippet into the post (you could skip <tt>lang=&quot;lang&quot;</tt>, in this case code would be in <tt>codecolorer</tt> block, but without syntax highlighting).
6. Have fun!

== Screenshots ==

1. PHP syntax highlighting without scrollbars.
2. CSS syntax highlighting with scrollbars.

== Supported languages ==

Here is list of supported by CodeColorer languages: actionscript, ada, apache, applescript, asm, asp, autoit, bash, blitzbasic, bnf, c, caddcl, cadlisp, cfdg, cfm, cpp-qt, cpp, csharp, css-gen.cfg, css, c_mac, d, delphi, diff, div, dos, eiffel, fortran, freebasic, gml, groovy, html, idl, ini, inno, io, java, java5, javascript, latex, lisp, lua, matlab, mirc, mpasm, mysql, nsis, objc, ocaml-brief, ocaml, oobas, oracle8, pascal, perl, php-brief, php, plsql, python, qbasic, rails, reg, robots, ruby, sas, scheme, sdlbasic, smalltalk, smarty, sql, tcl, text, thinbasic, tsql, vb, vbnet, vhdl, visualfoxpro, winbatch, xml, xpp, z80.

== Customization ==

Syntax coloring is highly customizable: you could change color scheme for all languages or for specific language. You could find CodeColorer CSS in <tt>wp-content/plugins/codecolorer/codecolorer.css</tt> file. To change colors for all languages edit lines below <em>Color scheme</em> section. Usually you would use only following CSS classes:

* *kw1*, *kw2*, *kw3* - keywords
* *co1*, *co2*, *coMULTI* - comments
* *es0* - escaped chars
* *br0* - brackets
* *st0* - strings
* *nu0* - numbers
* *me0* - methods

To change colors for specific language copy default values and add language name with a period before it. For example, in this blog I'm using following color scheme for PHP:

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
	.php .codecolorer .re4, .php .codecolorer .re4 * { color: #64A2FF; }
	.php .codecolorer .co1, .php .codecolorer .co2, .php .codecolorer .coMULTI { color: #9933CC; }

Also you could change width of the code block in the top of CSS file (there are different values for different situations, for example when you code is places under <tt>&lt;blockquote&gt;</tt>).
