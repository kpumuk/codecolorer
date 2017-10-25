=== CodeColorer ===
Contributors: kpumuk
Tags: code, snippet, syntax, highlight, highlighting, color, geshi
Requires at least: 4.0
Tested up to: 4.8.1
Stable tag: 0.9.14

CodeColorer is a syntax highlighting plugin which allows inserting code snippets into blog posts. The plugin supports color themes, code samples in RSS, comments.

== Description ==

CodeColorer is the plugin which allows you to insert code snippets into the post with nice syntax highlighting.

Plugin based on GeSHi library, which supports most languages. CodeColorer has various nice features:

* syntax highlighting in RSS feeds
* syntax highlighting of a single line of code (inline)
* syntax highlighting of code in comments
* line numbers
* automatic links to the documentation inserting
* code block intelligent scroll detection (short code would have a short block, for a long one the block height would be fixed and a scrollbar would appear)
* predefined color themes (Slush &amp; Poppies, Blackboard, Dawn, Mac Classic, Twitlight, Vibrant Ink, Railscasts, Solarized Light, Solarized Dark)
* syntax colors customization in CSS file
* code protect from mangling by Wordpress (for example, quotes, double-dashes, and others would look just right as you entered)

= Support =

If you have any suggestions, found a bug, wanted to contribute a translation to your language, or just wanted to say "thank you",– feel free to email me [kpumuk@kpumuk.info](mailto:kpumuk@kpumuk.info). Promise, I will answer every email I received.

If you want to contribute your code, see the *Development* section under the *Other Notes* tab.

== Installation ==

1. Download and unpack plugin files to **wp-content/plugins/codecolorer** directory.
2. Enable **CodeColorer** plugin on your *Plugins* page in *Site Admin*.
3. Go to the *Options/CodeColorer* page in *Site Admin* and change plugin's options as you wish.
4. Use `[cc lang="lang"]code[/cc]` or `<code lang="lang">code</cc>` syntax to insert a code snippet into the post (you could skip `lang="lang"`, in this case code would be in CodeColorer's code block, but without syntax highlighting). Also you can use `[cci lang="lang"]code[/cci]` to format inline code (see the "inline" option description).
5. Have fun!

= Syntax =

To insert a code snippet into your post (or comment) you would use `[cc lang="lang"]code[/cc]` or `<code lang="lang">code</cc>` syntax. Starting from version 0.6.0 you could specify additional CodeColorer options inside `[cc]` tag:

    [cc lang="php" tab_size="2" lines="40"]
    // some code
    [/cc]

Note: You should always use double quotes or single quotes around the parameter value. Boolean values could be passed using string *true* or *false*, *on* or *off*, number *1* or *0*.

= Short codes =

Starting from CodeColorer 0.8.6 you can use short codes to insert code snippets. The short code in common looks like `[ccM_LANG]`, where **LANG** is your programming language, and **M** is the one or more of following modes:

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

More examples could be found on the [CodeColorer Examples](https://kpumuk.info/projects/wordpress-plugins/codecolorer/examples) page. You can find modes explained below.

= Possible parameters =

* **lang** (*string*) – source language.
* **tab_size** (*integer*) – how many spaces would represent TAB symbol.
* **lines** (*integer*) – how many lines would be block height without scroll; could be set to *-1* to remove vertical scrollbar.
* **width** (*integer* or *string*) – block width.
* **height** (*integer* or *string*) – height in pixels; used when lines number is greater then "lines" value.
* **rss_width** (*integer* or *string*) – block width in RSS feeds.
* **theme** (*string*) – color theme (default, blackboard, dawn, mac-classic, twitlight, vibrant, geshi, railscasts, solarized-light, solarized-dark).
* **first_line** (*integer*) – a number of the first line in the block.
* **highlight** (*string*) — a comma-separated list of line numbers or ranges of line numbers to highlight (e.g. `1,5,8-11`).
* **escaped** (*boolean*) – when *true* special HTML sequences like `&lt;` or `&#91;` will be treated as encoded (in this example as `<` and `[` respectively.)
* **line_numbers** (*boolean*) – when *true* line numbers will be added.
* **no_links** (*boolean*) – when *false* keywords will be represented as links to manual.
* **inline** (*boolean*) – when *true* forces code block to render inside `<code>`. Used to paste a single line of code into the regular text.
* **strict** (*boolean*) – when *true* [strict mode](http://qbnz.com/highlighter/geshi-doc.html#using-strict-mode) will be enabled. By default CodeColorer tries to guess whether strict mode is needed, so this option allows to force it on or off when automatic suggestion is wrong.
* **nowrap** (*boolean*) – when *false* no horizontal scrollbar will be shown; instead code will be wrapped in the end of code box.
* **noborder** (*boolean*) – when *true* no border will be shown around the code block.
* **no_cc** (*boolean*) – when *true* the syntax in code block will not be highlighted, code will be rendered inside `<code></code>` tag.
* **class** (*string*) – additional CSS classes to add to the wrapper HTML element.
* **file** (*string*) — when specified, code will be loaded from external file. Should be a relative to uploads folder path, only files from uploads are allowed to be embedded.

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

Go to the *Options/CodeColorer* page in <em>Site Admin</em> and change the "Custom CSS Styles" option.

= I see &amp;lt; instead of &lt; (or other HTML entities like &gt;, &amp;, &quot;) in my code. =

You should use `[cc escaped="true"]` or `[cce]` in the visual editor when inserting code into the post.

= Does it highlights my code on server or client side? =

CodeColorer performs code highlighting on the server; you could see HTML of the highlighted code in the page source.

= Is it produces valid XHTML source? =

Yes, resulting XHTML is completely valid.

= Could my visitors insert their code snippets in comments? =

Yes, CodeColorer supports code highlighting in comments using the same syntax, as you use in your blog posts.

= How can I disable syntax highlighting for a particular `<code>` block? =

Use `<code no_cc="true">` option for your code block.

= I have updated the plugin to the newest version and now I keep getting following warnings: =

    Warning: array_keys() [function.array-keys]: The first argument should be an array in /home/wordpress/wp-content/plugins/codecolorer/lib/geshi.php on line 3599

Remove all files from the **wp-content/plugins/codecolorer** folder and unpack an archive with plugin again (thanks to [Anatoliy 'TLK' Kolesnick](http://blog.t-l-k.com/)).

= How to insert code from an external file?

You can upload this file using WordPress upload or put it somewhere in uploads folder, and the specify relative path using `file="relative/path/to/file"` attribute:

    [cc_ruby file="test_project/main.rb"][/cc_ruby]

This snippet will insert code from the UPLOADS_DIR/test_project/main.rb file.

== Screenshots ==

1. Ruby syntax highlighting without scrollbars (Vibrant theme).
2. Ruby syntax highlighting with scrollbars (Twitlight theme).
3. Inline code syntax highlighting.
4. Settings page.

== Changelog ==

= 0.9.14 (October 25, 2017) =
* IMPORTANT: Another line numbers column width issue for numbers bigger than 1000.

= 0.9.13 (October 24, 2017) =
* IMPORTANT: Fixed line numbers column width issue, introduces in the previous version.
* Fixed line numbers column position on RTL pages.

= 0.9.12 (October 12, 2017) =
* Fixed XML syntax highlighting colors.
* Fixed line highlighting color for dark themes.
* Added support for TablePress plugin.
* Line highlighting affects the whole block width, not only the code text.

= 0.9.11 (August 8, 2017) =
* Fixed an issue with TinyMCE when CodeColorer options were removed in the editor (thanks to [Jonathan Stassen](https://github.com/TheBox193) for the suggestion).
* New art for the WordPress plugins page.
* Moved translations to https://translate.wordpress.org/projects/wp-plugins/codecolorer. WordPress should automatically download language packs now.
* Lots of code style issues, should resolve all warnings in PHP logs.

= 0.9.10 (July 28, 2017) =
* Fixed a bug with large code blocks margins.
* Added Portuguese translation (thanks to [Luis Coutinho](http://lfscoutinho.net/)).
* Added Indonesian translation (thanks to [Masino Sinaga](http://www.openscriptsolution.com/)).
* Fixed PHP 7 compatibility issues (thanks to [Steve Kamerman](https://github.com/kamermans) and [Robert Felty](https://github.com/robfelty)).
* Fixed WordPress 4+ compatibility (editor button, settings page layout).
* Added "Solarized Light" and "Solarized Dark" themes (thanks to [Matt Kirman](https://github.com/mattkirman)).

You can find complete changelog on the [CodeColorer history](https://kpumuk.info/projects/wordpress-plugins/codecolorer/history/) page.

== Supported languages ==

Here is list of supported by CodeColorer languages: 4cs, 6502acme, 6502kickass, 6502tasm, 68000devpac, abap, actionscript, actionscript3, ada, aimms, algol68, apache, applescript, apt_sources, arm, asm, asp, asymptote, autoconf, autohotkey, autoit, avisynth, awk, bascomavr, bash, basic4gl, batch, bf, biblatex, bibtex, blitzbasic, bnf, boo, c, c_loadrunner, c_mac, c_winapi, caddcl, cadlisp, ceylon, cfdg, cfm, chaiscript, chapel, cil, clojure, cmake, cobol, coffeescript, cpp-qt, cpp-winapi, cpp, csharp, css, cuesheet, d, dart, dcl, dcpu16, dcs, delphi, diff, div, dos, dot, e, ecmascript, eiffel, email, epc, erlang, euphoria, ezt, f1, falcon, fo, fortran, freebasic, freeswitch, fsharp, gambas, gdb, genero, genie, gettext, glsl, gml, gnuplot, go, groovy, gwbasic, haskell, haxe, hicest, hq9plus, html4strict, html5, icon, idl, ini, inno, intercal, io, ispfpanel, j, java, java5, javascript, jcl, jquery, julia, julia, kixtart, klonec, klonecpp, kotlin, latex, lb, ldif, lisp, llvm, locobasic, logtalk, lolcode, lotusformulas, lotusscript, lscript, lsl2, lua, m68k, magiksf, make, mapbasic, mathematica, matlab, mercury, metapost, mirc, mk-61, mmix, modula2, modula3, mpasm, mxml, mysql, nagios, netrexx, newlisp, nginx, nimrod, nsis, oberon2, objc, objeck, ocaml-brief, ocaml, octave, oobas, oorexx, oracle11, oracle8, oxygene, oz, parasail, parigp, pascal, pcre, per, perl, perl6, pf, phix, php-brief, php, pic16, pike, pixelbender, pli, plsql, postgresql, postscript, povray, powerbuilder, powershell, proftpd, progress, prolog, properties, providex, purebasic, pycon, pys60, python, q, qbasic, qml, racket, rails, rbs, rebol, reg, rexx, robots, rpmspec, rsplus, ruby, rust, sas, sass, scala, scheme, scilab, scl, sdlbasic, smalltalk, smarty, spark, sparql, sql, standardml, stonescript, swift, systemverilog, tcl, tclegg, teraterm, texgraph, text, thinbasic, tsql, twig, typoscript, unicon, upc, urbi, uscript, vala, vb, vbnet, vbscript, vedit, verilog, vhdl, vim, visualfoxpro, visualprolog, whitespace, whois, winbatch, xbasic, xml, xojo, xorg_conf, xpp, xyscript, yaml, z80, zxbasic.

== Development ==

Sources of this plugin are available both in SVN and Git:

* [WordPress SVN repository](https://plugins.svn.wordpress.org/codecolorer/)
* [GitHub](https://github.com/kpumuk/codecolorer/)

Feel free to check them out, make your changes and send me patches or pull requests. Promise, I will apply every patch (of course, if they add a value to the product). Email for patches, suggestions, or bug reports: [kpumuk@kpumuk.info](mailto:kpumuk@kpumuk.info).

If you're interested in translating CodeColorer to your language, please check out the [translation page](https://translate.wordpress.org/projects/wp-plugins/codecolorer) for the plugin.

== Customization ==

Syntax coloring is highly customizable: you could change the  color scheme for all languages or a specific language. You could find CodeColorer CSS in **wp-content/plugins/codecolorer/codecolorer.css** file. To change colors for all languages edit lines below *Color scheme* section.

There is simple mapping exists between Textmate color themes and CodeColorer ones:

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
