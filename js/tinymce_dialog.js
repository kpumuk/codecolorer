tinyMCEPopup.requireLangPack();

var CodeColorerDialog = {
  init: function() {
    var ed = tinyMCEPopup.editor, f = document.forms[0];

    var node = ed.selection.getNode();
    if (node.nodeName == 'PRE' || node.nodeName == 'SPAN') {
      ed.selection.select(node);
    }

    var code = ed.selection.getContent().replace(/^\s*<pre>|^\s*<span>|<\/span>\s*$|<\/pre>\s*$/g, '');
    code = CodeColorerDialog.parseOptions(f, code);
    code = CodeColorerDialog.unescapeHTML(code);
    code = CodeColorerDialog.trim(code);
    f.code.value = code;

    tinyMCEPopup.resizeToInnerSize();
  },
  
  insert: function() {
    var ed = tinyMCEPopup.editor, t, h, f = document.forms[0], st = '';

    t = 'cce'
    
    // Inline block
    if (f.inline.checked) t += 'i';
    
    // Line numbers
    if (f.line_numbers.value == 'on') {
      t += 'n';
    } else if (f.line_numbers.value == 'off') {
      t += 'N';
    }
    
    // Disable keyword linking
    if (f.disable_keyword_linking.value == 'on') {
      t += 'l';
    } else if (f.disable_keyword_linking.value == 'off') {
      t += 'L';
    }

    if (f.language_custom.value) {
      t += '_' + f.language_custom.value;
    } else if (f.language.value) {
      t += '_' + f.language.value;
    }

    h = '[' + t;
    
    if (f.tab_size.value) {
      h += ' tab_size="' + f.tab_size.value + '"';
    }
    
    if (f.theme.value) {
      h += ' theme="' + f.theme.value + '"';
    }

    h += ']';
    
    if (f.inline.checked) {
      h += CodeColorerDialog.escapeHTML(f.code.value);
    } else {
      lines = CodeColorerDialog.escapeHTML(f.code.value).split(/\r?\n/);
      if (lines.length > 1) {
        h += '<br />';
        tinymce.each(lines, function(row) {
          h += CodeColorerDialog.escapeSpaces(row) + '<br />';
        });
      } else {
        h += lines[0];
      }
    }
    
    h += '[/' + t + ']';

    if (f.inline.checked) {
      h = '<span>' + h + '</span>';
    } else {
      h = '<pre>' + h + '</pre>';
    }

    ed.execCommand('mceInsertContent', false, h);

    // tinyMCEPopup.editor.execCommand('mceRepaint');
    tinyMCEPopup.close();
  },
  
  parseOptions: function(f, code) {
    var matches = code.match(/(\s*)\[cc([^\s\]_]*(?:_[^\s\]]*)?)([^\]]*)\](.*?)\[\/cc\2\](\s*)/);
    if (matches) {
      var options = matches[3].split(/\s+/);
      for (var i = options.length; i--; ) {
        var option = options[i].match(/([a-z_-]*?)\s*=\s*(["\'])(.*?)\2/);

        if (option) {
          if (option[1] == 'tab_size') f.tab_size.value = option[3];
          if (option[1] == 'theme') f.theme.value = option[3];
        }
      }
      
      var parts = matches[2].split('_');

      if (CodeColorerDialog.stringContains(parts[0], 'i')) {
        f.inline.checked = true;
      }
      
      if (CodeColorerDialog.stringContains(parts[0], 'n')) {
        f.line_numbers.value = 'on';
      } else if (CodeColorerDialog.stringContains(parts[0], 'N')) {
        f.line_numbers.value = 'off';
      }
      
      if (CodeColorerDialog.stringContains(parts[0], 'l')) {
        f.disable_keyword_linking.value = 'on';
      } else if (CodeColorerDialog.stringContains(parts[0], 'L')) {
        f.disable_keyword_linking.value = 'off';
      }
      
      if (parts[1]) {
        f.language.value = parts[1];
        if (f.language.value != parts[1]) {
          f.language_custom = parts[1];
        }
      }
      
      return matches[4];
    }
    return code;
  },
  
  stringContains: function(string, char) {
    for (var i = string.length; i--; ) {
      if (string.charAt(i) == char) {
        return true
      }
    }
    return false;
  },
  
  findOption: function(options, option) {
    for (var i = options.length; i--; ) {
      if (options[i][1] == option) {
        return option[i][3];
      }
    }
    return '';
  },
  
  escapeHTML: function(text) {
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  },
  
  escapeSpaces: function(text) {
    return text.replace(/^ /g, '&nbsp;')
  },
  
  unescapeHTML: function(text) {
    return text.replace(/<br \/>/g, '\n').replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&');
  },
  
  trim: function(text) {
    return text.replace(/(?:^(?:\s*[\r\n])+|\s+$)/g, '');
  }
}

tinyMCEPopup.onInit.add(CodeColorerDialog.init);