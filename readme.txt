=== CodeColorer ===
Contributors: kpumuk
Tags: code, snippet, syntax, highlight, highlighting, color, geshi
Requires at least: 2.0.2
Tested up to: 2.8.2
Stable tag: 0.8.7

CodeColorer is a syntax highlighting plugin which allows to insert code
snippets into blog posts. Supports color themes, code in RSS, comments.

== Description ==

CodeColorer is the plugin which allows you to insert code snippets into the
post with nice syntax highlighting.

Plugin based on GeSHi library, which supports most languages. CodeColorer has
various nice features:

* syntax highlighting in RSS feeds
* syntax highlighting of single line of code (inline)
* syntax highlighting of code in comments
* line numbers
* automatic links to the documentation inserting
* code block intelligent scroll detection (short code would have short block,
  for long one block height would be fixed and scrollbar would appear)
* predefined color themes (Slush &amp; Poppies, Blackboard, Dawn, Mac Classic,
  Twitlight, Vibrant Ink)
* syntax colors customization in CSS file
* code protect from mangling by Wordpress (for example, quotes, double-dashes,
  and others would look just right as you entered)

= Translations =

Thank you all guys, who submitted translations to your language. CodeColorer
is currently available in following languages:

* English
* Russian - Русский
* Ukrainian - Українська
* Italian - Italiano (thanks to <a href="http://codesnippet.altervista.org/">CodeSnippet</a>)
* Belarusian - Беларуский (thanks to <a href="http://www.fatcow.com">И. Фадков</a>)
* Simplified Chinese - 简化字 (thanks to <a href="http://ixiezi.com">liuxiangqian</a>)

== Installation ==

1. Download and unpack plugin files to **wp-content/plugins/codecolorer**
   directory.
2. Enable **CodeColorer** plugin on your *Plugins* page in *Site Admin*.
3. Go to the *Options/CodeColorer* page in *Site Admin* and change plugin's
   options as you wish.
4. Use `[cc lang="lang"]code[/cc]` or `<code lang="lang">code</cc>` syntax to
   insert code snippet into the post (you could skip `lang="lang"`, in this
   case code would be in CodeColorer's code block, but without syntax
   highlighting). Also you can use `[cci lang="lang"]code[/cci]` to format
   inline code (see the "inline" option description).
5. Have fun!

= Syntax =

To insert code snippet into your post (or comment) you should use
`[cc lang="lang"]code[/cc]` or `<code lang="lang">code</cc>` syntax. Starting
from version 0.6.0 you could specify additional CodeColorer options inside
`[cc]` tag:

    [cc lang="php" tab_size="2" lines="40"]
    // some code
    [/cc]

Note: You should always use double quotes or single quotes around the parameter
value. Boolean values could be passed using string *true* or *false*, *on* or
*off*, number *1* or *0*.

= Short codes =

Starting from CodeColorer 0.8.6 you can use short codes to insert code
snippets. The short code in common looks like `[ccM_LANG]`, where **LANG** is
your programming language, and **M** is the one or more of following modes:

* **i** – *inline*
* **e** – *escaped*
* **s** – *strict*
* **n** – *line_numbers*
* **b** – *no_border*
* **w** – *no_wrap*
* **l** – *no_links*

Small letter means **enabled**, capital – **disabled**. Examples:

*PHP code with links enabled and line numbers disabled:*

    [cclN_php]
    echo "hello"
    [/cclN_php]

*Already escaped HTML code:*

    [ccie_html]&lt;html&gt;[/ccie_html]

*Ruby code without wrapping having tab size equal to 4:*

    [ccW_ruby tab_size="4"]
    attr_accessor :title
    [/ccW_ruby]

You can find modes explained below.

= Possible parameters =

* **lang** (*string*) – source language.
* **tab_size** (*integer*) – how many spaces would represent TAB symbol.
* **lines** (*integer*) – how many lines would be block height without scroll;
  could be set to *-1* to remove vertical scrollbar.
* **width** (*integer* or *string*) – block width.
* **height** (*integer* or *string*) – height in pixels; used when lines number
  is greater then "lines" value.
* **rss_width** (*integer* or *string*) – block width in RSS feeds.
* **theme** (*string*) – color theme (default, blackboard, dawn, mac-classic,
  twitlight, vibrant).
* **first_line** (*integer*) – a number of the first line in the block.
* **escaped** (*boolean*) – when *true* special HTML sequences like `&lt;` or
  `&#91;` will be treated as encoded (in this example as `<` and `[`
  respectively.)
* **line_numbers** (*boolean*) – when *true* line numbers will be added.
* **no_links** (*boolean*) – when *false* keywords will be represented as links
  to manual.
* **inline** (*boolean*) – when *true* forces code block to render inside
  `<code>`. Used to paste a single line of code into the regular text.
* **strict** (*boolean*) – when *true* <a href="http://qbnz.com/highlighter/geshi-doc.html#using-strict-mode">strict mode</a>
  will be enabled. By default CodeColorer tries to guess whether strict mode is
  needed, so this option allows to force it on or off when automatic suggestion
  is wrong.
* **nowrap** (*boolean*) – when *false* no horizontal scrollbar will be shown;
  instead code will be wrapped in the end of code box.
* **noborder** (*boolean*) – when *true* no border will be shown around the
  code block.
* **no_cc** (*boolean*) – when *true* the syntax in code block will not be
  highlighted, code will be rendered inside `<code></code>` tag.

You can use special tag `[cci]` instead of `[cc]` to force inline mode:

    [cci lang="php"]some code[/cci]

Most of these parameters could be configured via the CodeColorer options page.

To insert example of CodeColorer short codes you can use something like this:

    [cce_bash]
    &amp;#91;cc lang="html"]
    <title>CodeColorer short code colorized</title>
    &amp;#91;/cc]
    [/cce_bash]

== Frequently Asked Questions ==

= How do I can customize CodeColorer CSS rules? =

Go to the *Options/CodeColorer* page in <em>Site Admin</em> and change the
"Custom CSS Styles" option.

= I see &amp;lt; instead of &lt; (or other HTML entities like &gt;, &amp;, &quot;) in my code. =

You should use `[cc escaped="true"]` or `[cce]` in the visual editor when
inserting code into the post.

= Does it highlights my code on server or client side? =

CodeColorer performs code highlighting on the server, you could see HTML of
the highlighted code in page source.

= Is it produces valid XHTML source? =

Yes, resulting XHTML is completely valid.

= Could my visitors insert their code snippets in comments? =

Yes, CodeColorer supports code highlighting in comments using the same syntax,
as you use in your blog posts.

= How can I disable syntax highlighting for a particular `<code>` block? =

Use `<code no_cc="true">` option for your code block.

= I have updated the plugin to the newest version and now I keep getting following warnings: =

    Warning: array_keys() [function.array-keys]: The first argument should be an array in /home/wordpress/wp-content/plugins/codecolorer/lib/geshi.php on line 3599
  
Remove all files from the **wp-content/plugins/codecolorer** folder and unpack
an archive with plugin again (thanks to 
<a href="http://blog.t-l-k.com/">Anatoliy 'TLK' Kolesnick</a>).

== Screenshots ==

1. Ruby syntax highlighting without scrollbars (Vibrant theme).
2. Ruby syntax highlighting with scrollbars (Twitlight theme).
3. Inline code syntax highlighting.
4. Settings page.

== Changelog ==

= 0.8.7 (July 23, 2009) =
* Fixed inline code blocks theming (thanks to <a href="http://blog.t-l-k.com/">Anatoliy 'TLK' Kolesnick</a>).
* Fixed spaces before and after inline blocks (thanks to <a href="http://blog.t-l-k.com/">Anatoliy 'TLK' Kolesnick</a>).
* Added section "Short codes" to readme.txt.
* Added two more screenshots: inline code syntax highlighting and settings page.

= 0.8.6 (July 22, 2009) =
* Added a new option "escaped" to process code blocks with special HTML chars escaped (&lt; – &amp;lt;).
* Fixed regular expressions for PHP and some other languages.
* Use `html_entity_decode` instead of `htmlspecialchars_decode` if `escaped="true"`.
* Added advanced syntax `[ccMODE_LANG]`, where **MODE** is set of modes, and **LANG** is language.

= 0.8.5 (July 20, 2009) =
* Fixed bug occured when `[cc]` block goes just right after the `[cci]`.
* Fixed bug with `tab_size` option saving (thanks to <a href="http://www.marclove.com/">Marc Love</a>).

= 0.8.4 (July 14, 2009) =
* Fixed inline code blocks formatting.
* Added special tag `[cci]` which works just like `[cc]`, but with inline forced.
* Fixed problem when line numbers could not be disabled (thanks to miaow).

= 0.8.3 (July 14, 2009) =
* Added a new option "strict" to enable or disable strict mode.
* Added a new option "inline" which forces code block to render inside &lt;code&gt; tag. Used to paste a single line of code into the regular text.
* Trim only empty lines in the beginning of code, leave spaces untouched (thanks to FeepingCreature – contact me please and I will add your link here).
* Allow "on" and "off" as boolean parameter value.
* Added Italian translation (thanks to <a href="http://codesnippet.altervista.org/">CodeSnippet</a>).
* Added Belarusian translation (thanks to <a href="http://www.fatcow.com">И. Фадков</a>).

= 0.8.2 (July 14, 2009) =
* Fixed problem with width and heigth specified at the same time.
* Added a new option "nowrap" to disable horizontal scrollbar.
* Added a new option "noborder" to disable a border around code block.
* Fixed inner table borders in some WordPress themes.
* Geshi updated to 1.0.8.4.

You can find complete changelog on the <a href="http://kpumuk.info/projects/wordpress-plugins/codecolorer/">plugin home page</a>.

== Supported languages ==

Here is list of supported by CodeColorer languages: abap, actionscript, actionscript3, ada, apache, applescript, apt\_sources, asm, asp, autoit, avisynth, bash, basic4gl, bf, bibtex, blitzbasic, bnf, boo, c, c\_mac, caddcl, cadlisp, cfdg, cfm, cil, cmake, cobol, cpp-qt, cpp, csharp, css, d, dcs, delphi, diff, div, dos, dot, eiffel, email, erlang, fo, fortran, freebasic, genero, gettext, glsl, gml, gnuplot, groovy, haskell, hq9plus, html4strict, idl, ini, inno, intercal, io, java, java5, javascript, kixtart, klonec, klonecpp, latex, lisp, locobasic, lolcode, lotusformulas, lotusscript, lscript, lsl2, lua, m68k, make, matlab, mirc, modula3, mpasm, mxml, mysql, nsis, oberon2, objc, ocaml-brief, ocaml, oobas, oracle11, oracle8, pascal, per, perl, php-brief, php, pic16, pixelbender, plsql, povray, powershell, progress, prolog, properties, providex, python, qbasic, rails, rebol, reg, robots, ruby, sas, scala, scheme, scilab, sdlbasic, smalltalk, smarty, sql, tcl, teraterm, text, thinbasic, tsql, typoscript, vb, vbnet, verilog, vhdl, vim, visualfoxpro, visualprolog, whitespace, whois, winbatch, xml, xorg\_conf, xpp, yaml, z80.

== Customization ==

Syntax coloring is highly customizable: you could change color scheme for all
languages or for specific language. You could find CodeColorer CSS in 
**wp-content/plugins/codecolorer/codecolorer.css** file. To change colors for
all languages edit lines below *Color scheme* section.

There is simple mapping exists between Textmate color themes and CodeColorer
ones:

    /* "Slush & Poppies" color scheme (default) */
    .codecolorer-container, .codecolorer { color: #000000; background-color: #F1F1F1; }
    /* Comment */
    .codecolorer .co0, .codecolorer .co1, .codecolorer .co2, .codecolorer .co3, .codecolorer .co4, .codecolorer .coMULTI { color: #406040; font-style: italic; }
    /* Constant */
    .codecolorer .nu0, .codecolorer .re3 { color: #0080A0; }
    /* String */
    .codecolorer .st0, .codecolorer .st_h, .codecolorer .es0, .codecolorer .es1 { color: #C03030; }
    /* Entity */
    .codecolorer .me1, .codecolorer .me2 { color: #0080FF; }
    /* Keyword */
    .codecolorer .kw1, .codecolorer .kw2, .codecolorer .sy1 { color: #2060A0; }
    /* Storage */
    .codecolorer .kw3, .codecolorer .kw4, .codecolorer .kw5, .codecolorer .re2 { color: #008080; }
    /* Variable */
    .codecolorer .re0, .codecolorer .re1 { color: #A08000; }
    /* Global color */
    .codecolorer .br0, .codecolorer .sy0 { color: #000000; }

Check the **codecolorer.css** file to get more examples.