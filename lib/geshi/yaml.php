<?php
/*************************************************************************************
 * yaml.php
 * --------
 * Author: Dmytro Shteflyuk (kpumuk@kpumuk.info)
 * Copyright: (c) 2007 Dmytro Shteflyuk
 * Release Version: 1.0.8.2
 * Date Started: 2007/04/03
 *
 * YAML Ain't Markup Language language file for GeSHi.
 *
 * CHANGES
 * -------
 * 04/03/2007 (0.1.0)
 *  -  Syntax File Created
 *
 * TODO (updated 04/03/2007)
 * -------------------------
 * 
 * 

 ************************************************************************************/
$language_data = array (
	'LANG_NAME' => 'Text',
	'COMMENT_SINGLE' => array( ),
	'COMMENT_MULTI' => array( ),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array(),
	'ESCAPE_CHAR' => '',
	'KEYWORDS' => array( ),
	'SYMBOLS' => array( ),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false
		),
	'STYLES' => array(
		'KEYWORDS' => array(),
		'COMMENTS' => array(),
		'ESCAPE_CHAR' => array(),
		'BRACKETS' => array(),
		'STRINGS' => array(),
		'NUMBERS' => array(),
		'METHODS' => array(),
		'SYMBOLS' => array(),
		'SCRIPT' => array(),
		'REGEXPS' => array()
		),
	'OOLANG' => false,
	'OBJECT_SPLITTERS' => array(),
	'REGEXPS' => array(	
    0 => array(
      GESHI_SEARCH => '^([ \t]+[a-zA-Z_][a-zA-Z_0-9]+):',
			GESHI_REPLACE => '\\1',
			GESHI_AFTER => ':',
      GESHI_MODIFIERS => 'm'
      ),
    1 => array(
      GESHI_SEARCH => '^([a-zA-Z_][a-zA-Z_0-9]+):',
			GESHI_REPLACE => '\\1',
			GESHI_AFTER => ':',
      GESHI_MODIFIERS => 'm'
      )
    ),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array( ),
	'HIGHLIGHT_STRICT_BLOCK' => array( )
);
?>
