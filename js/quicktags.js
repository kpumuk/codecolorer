(function($) {
  $.fn.codeColorerQuickTags = function(options) {
    var codeTagIndex = -1;

    for (var i = 0; i < edButtons.length; i++) {
      if (edButtons[i].id == 'ed_code') {
        edButtons[i].id = 'ed_cc';
        edButtons[i].display = 'cc';
        edButtons[i].tagStart = '';
        edButtons[i].tagEnd = '[/cc]';
        
        codeTagIndex = i;
        break;
      }
    }

    var button = '<input type="button" id="ed_cc" accesskey="c" class="ed_button" value="cc">';
    $(this).replaceWith(button);
    
    $('#ed_cc').bind('click', insertCodeColorer);

    function insertCodeColorer() {
      if (!edCheckOpenTags(codeTagIndex)) {
        var URL = prompt(codeColorerL10n.enterLanguage, '');
        if (URL) {
          edButtons[codeTagIndex].tagStart = '[cc lang="' + URL + '"]';
          edInsertTag(edCanvas, codeTagIndex);
        }
      } else {
        edInsertTag(edCanvas, codeTagIndex);
      }
    }
  }
})(jQuery);

jQuery('#ed_code').codeColorerQuickTags();
