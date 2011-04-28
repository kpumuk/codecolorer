=== CodeColorer ===
Contributors: kpumuk
Tags: code, snippet, syntax, highlight, highlighting, color, geshi
Requires at least: 2.7.0
Tested up to: 3.1.2
Stable tag: 0.9.9

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
  Twitlight, Vibrant Ink, Railscasts)
* syntax colors customization in CSS file
* code protect from mangling by Wordpress (for example, quotes, double-dashes,
  and others would look just right as you entered)

= Translations =

Thank you all guys, who submitted translations to your language. CodeColorer
is currently available in following languages:

* English
* Russian – Русский
* Ukrainian – Українська
* Arabic – العربية (thanks to <a href="http://amine27.zici.fr/">Amine Roukh</a>)
* Belarusian – Беларуский (thanks to <a href="http://www.fatcow.com">И. Фадков</a>)
* Czech – Čeština (thanks to <a href="http://lelkoun.cz/">Lelkoun</a>)
* Danish – Dansk (thanks to <a href="http://www.klysner.com/">Hans Klysner</a>)
* Dutch – Nederlands (thanks to <a href="http://www.helixsoft.nl/blog/">Martijn van Iersel</a>)
* French – Français (thanks to <a href="http://www.flyingcode.com/">Valentin PRUGNAUD</a>, <a href="http://fanta78.free.fr/">fanta78</a>, <a href="http://blog.zabe.fr/">Sylvain Zabé</a>, and <a href="http://blogs.wittwer.fr/whiler/">Whiler</a>)
* Georgian – ქართული ენა (thanks to <a href="http://sysadmin.softgen.ge/">Nika Chkhikvishvili</a>)
* German – German (Deutsch) (thanks to <a href="http://www.fabianschulz.net/">Fabian Schulz</a> and <a href="http://michael-gutbier.de/">Michael Gutbier</a>)
* Hebrew – עִבְרִית (thanks to <a href="http://www.gadgetguru.co.il/">Yaron Ofer</a>)
* Hungarian — magyar nyelv (thanks to <a href="http://dassad.com/">daSSad</a>)
* Italian – Italiano (thanks to <a href="http://codesnippet.altervista.org/">CodeSnippet</a>)
* Japanese – 日本語 (thanks to <a href="http://www.kuroneko-square.net/">Kuroneko Square</a>)
* Malay – Bahasa Melayu (thanks to <a href="http://www.losebellyfat365.com/">Shareef Sam</a>)
* Persian – فارسی, پارسی, دری (thanks to <a href="http://www.7sal.com/">Hamed Momeni</a>)
* Polish – Polski (thanks to <a href="http://www.andrzej.net.pl/">Andrzej Pindor</a>)
* Brazilian Portuguese – Português Brasileiro (thanks to <a href="http://www.jeveaux.com/">Paulo César M. Jeveaux</a>, <a href="">Fabricio Bortoluzzi</a>, and <a href="http://www.rodolfoleao.com/">Rodolfo Leão</a>)
* Romanian - Română (thanks to <a href="http://www.sphynxsoft.com/">Bogdan M. Botezatu</a>)
* Simplified Chinese – 汉语 (thanks to <a href="http://ixiezi.com">liuxiangqian</a> and <a href="http://tihope.com/">KenSai</a>)
* Slovak — Slovenský (thanks to <a href="http://cstudio.sk/">ceco</a>)
* Spanish – Español (thanks to <a href="http://seich.martianwabbit.com/">Sergio Díaz</a>)
* Spanish – Español (Argentina) (thanks to <a href="http://www.lordblacksuca.com.ar/">Diego Sucaria</a>)
* Spanish – Español (Colombia) (thanks to <a href="http://blog.lasumo.com.co/">Diego Alberto Bernal</a>)
* Swedish – Svenska (thanks to <a href="http://blog.lhli.net/">LHLI</a>)
* Traditional Chinese — 漢語 (thanks to <a href="http://jhcheng.byethost3.com/">Horace Cheng</a>)
* Turkish – Türkçe (thanks to <a href="http://www.hasanakgoz.com/">Hasan Akgöz</a>)

Want to help me with translation? It's easy!

1. Install <a href="http://www.poedit.net/download.php">Poedit</a>.
2. Download <a href="http://svn.wp-plugins.org/codecolorer/trunk/languages/codecolorer.pot">codecolorer.pot</a> file.
3. Click *File/New catalog from .pot file* and select *codecolorer.pop*
   you've just downloaded.
4. Enter project name (something like **CodeColorer 0.9.9**), your name
   and email address, select a language you want to translate to and
   click *OK*.
5. Enter a filename like **codecolorer-en_EN** and click *Save*.
6. Translate all strings one by one.
7. Send me a `.po` file with a translation to <a href="mailto:kpumuk@kpumuk.info">kpumuk@kpumuk.info</a>.
   Do not forget a link to add to CodeColorer project home page.
8. Thank you!

To fix existing translation, just open corresponding <tt>.po</tt> file
from <em>codecolorer/languages</em> folder in Poedit, and add missing or
update existing strings.

= Support =

If you have any suggestions, found a bug, wanted to contribute a
translation to your language, or just wanted to say "thank
you",– feel free to email me <a href="mailto:kpumuk@kpumuk.info">kpumuk@kpumuk.info</a>.
Promise, I will answer every email I received.

If you want to contribute your code, see the *Development* section under
the *Other Notes* tab.

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

More examples could be found on the <a href="http://kpumuk.info/projects/wordpress-plugins/codecolorer/examples">CodeColorer Examples</a>
page. You can find modes explained below.

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
  twitlight, vibrant, geshi).
* **first_line** (*integer*) – a number of the first line in the block.
* **highlight** (*string*) — a comma-separated list of line numbers or ranges
  of line numbers to highlight (e.g. `1,5,8-11`).
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
* **class** (*string*) – additional CSS classes to add to the wrapper HTML element.
* **file** (*string*) — when specified, code will be loaded from external file.
  Should be a relative to uploads folder path, only files from uploads are
  allowed to be embedded.

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

= How to insert code from an external file?

You can upload this file using WordPress upload or put it somewhere in uploads folder,
and the specify relative path using `file="relative/path/to/file"` attribute:

    [cc_ruby file="test_project/main.rb"][/cc_ruby]

This snippet will insert code from the UPLOADS_DIR/test_project/main.rb file.

== Screenshots ==

1. Ruby syntax highlighting without scrollbars (Vibrant theme).
2. Ruby syntax highlighting with scrollbars (Twitlight theme).
3. Inline code syntax highlighting.
4. Settings page.

== Changelog ==

= 0.9.9 (April 28, 2011) =
* Added ability to highlight ranges of lines (thanks to <a href="http://www.deltanova.co.uk/670/">DELTA NOVA</a>).
* GeSHi updated to 1.0.8.10 (now with Google Go support!).
* Updated Simplified Chinese translation (thanks to <a href="http://tihope.com/">KenSai</a>).
* Added Hungarian translation (thanks to <a href="http://dassad.com/">daSSad</a>).
* Added Traditional Chinese translation (thanks to <a href="http://jhcheng.byethost3.com/">Horace Cheng</a>).
* Added Romanian translation (thanks to <a href="http://www.sphynxsoft.com/">Bogdan M. Botezatu</a>).
* Added `file="file"` attribute to load code from external files (thanks to Mészáros Márton).
* Added Slovak translation (thanks to <a href="http://cstudio.sk/">ceco</a>).
* Added Malay translation (thanks to <a href="http://www.losebellyfat365.com/">Shareef Sam</a>).
* Fix for notice 'has_cap was called with an argument that is deprecated since version 2.0!' (thanks to <a href="https://github.com/lenon">Lenon Marcel</a>).
* Fixed undefined index warnings (thanks to <a href="https://github.com/lenon">Lenon Marcel</a>).
* Added Railscasts theme (thanks to <a href="https://github.com/ankit">Ankit Ahuja</a>).
* Fixed Twitlight theme (strings and symbols coloring was broken).

= 0.9.8 (March 23, 2010) =
* Added an icon to the admin options page.
* Updated Arabic translation (thanks to <a href="http://amine27.zici.fr/">Amine Roukh</a>).
* GeSHi updated to 1.0.8.6.
* Added ability to highlight specified lines (thanks to <a href="http://www.deltanova.co.uk/641/">DELTA NOVA</a>).
* Added Czech translation (thanks to <a href="http://lelkoun.cz/">Lelkoun</a>).
* Added Georgian translation (thanks to <a href="http://sysadmin.softgen.ge/">Nika Chkhikvishvili</a>).
* Added Persian translation (thanks to <a href="http://www.7sal.com/">Hamed Momeni</a>).
* Some unit tests added.

= 0.9.7 (December 19, 2009) =
* Fixed `theme="geshi"` attribute bug.
* Added ability to highlight arbitary piece of code from PHP.
* Use `wp_enqueue_style` instead of echoing plain HTML.
* Fixed problem with escaped code blocks, when some entities were not unescaped.
* Fixed compatibility with WordPress 2.9.

= 0.9.6 (December 18, 2009) =
* Added French translation (thanks to <a href="http://www.flyingcode.com/">Valentin PRUGNAUD</a>, <a href="http://fanta78.free.fr/">fanta78</a>, <a href="http://blog.zabe.fr/">Sylvain Zabé</a>, and <a href="http://blogs.wittwer.fr/whiler/">Whiler</a>).
* Added Brazilian Portuguese translation (thanks to <a href="http://www.jeveaux.com/">Paulo César M. Jeveaux</a>, <a href="">Fabricio Bortoluzzi</a>, and <a href="http://www.rodolfoleao.com/">Rodolfo Leão</a>).
* Added Swedish translation (thanks to <a href="http://blog.lhli.net/">LHLI</a>).
* Fixed XHTML validation problems on the CodeColorer options page (thanks to Brett Zamir).
* Added Japanese translation (thanks to <a href="http://www.kuroneko-square.net/">Kuroneko Square</a>).
* Added Danish translation (thanks to <a href="http://www.klysner.com/">Hans Klysner</a>).
* Added GeSHi theme.
* Added ability to specify custom CSS class for the wrapper HTML element.

= 0.9.5 (August 27, 2009) =
* Added Dutch translation (thanks to <a href="http://www.helixsoft.nl/blog/">Martijn van Iersel</a>).
* Added Spanish (Argentina) translation (thanks to <a href="http://www.lordblacksuca.com.ar/">Diego Sucaria</a>).
* Added Arabic translation (thanks to <a href="http://amine27.zici.fr/">Amine Roukh</a>).
* Fixed bug in Safari 4 caused by text-align=justify in parent container.

You can find complete changelog on the <a href="http://kpumuk.info/projects/wordpress-plugins/codecolorer/history/">CodeColorer history</a>
page.

== Supported languages ==

Here is list of supported by CodeColorer languages: 4cs, abap, actionscript, actionscript3, ada, apache, applescript, apt\_sources, asm, asp, autoconf, autohotkey, autoit, avisynth, awk, bash, basic4gl, bf, bibtex, blitzbasic, bnf, boo, c, c\_mac, caddcl, cadlisp, cfdg, cfm, cil, clojure, cmake, cobol, cpp-qt, cpp, csharp, css, cuesheet, d, dcs, delphi, diff, div, dos, dot, ecmascript, eiffel, email, erlang, fo, fortran, freebasic, fsharp, gambas, gdb, genero, gettext, glsl, gml, gnuplot, groovy, haskell, hq9plus, html4strict, idl, ini, inno, intercal, io, j, java, java5, javascript, jquery, kixtart, klonec, klonecpp, latex, lisp, locobasic, logtalk, lolcode, lotusformulas, lotusscript, lscript, lsl2, lua, m68k, make, mapbasic, matlab, mirc, mmix, modula3, mpasm, mxml, mysql, newlisp, nsis, oberon2, objc, ocaml-brief, ocaml, oobas, oracle11, oracle8, oxygene, pascal, per, perl, perl6, pf, php-brief, php, pic16, pike, pixelbender, plsql, povray, powerbuilder, powershell, progress, prolog, properties, providex, purebasic, python, qbasic, rails, rebol, reg, robots, rsplus, ruby, sas, scala, scheme, scilab, sdlbasic, smalltalk, smarty, sql, systemverilog, tcl, teraterm, text, thinbasic, tsql, typoscript, vb, vbnet, verilog, vhdl, vim, visualfoxpro, visualprolog, whitespace, whois, winbatch, xml, xorg\_conf, xpp, yaml, z80.

== Development ==

Sources of this plugin are available both in SVN and Git:

* <a href="http://svn.wp-plugins.org/codecolorer/">WordPress SVN repository</a>
* <a href="http://github.com/kpumuk/codecolorer/">GitHub</a>

Feel free to check them out, make your changes and send me patches.
Promise, I will apply every patch (of course, if they add a value to the
product). Email for patches, suggestions, or bug reports:
<a href="mailto:kpumuk@kpumuk.info">kpumuk@kpumuk.info</a>.

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