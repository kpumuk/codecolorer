(function($) {
    var ccButtonIndex = 110;
    while (edButtons[ccButtonIndex] !== undefined) ccButtonIndex++;
    QTags.addButton('code', 'cc', '[cc]', '[/cc]', 'c', 'CodeColorer', ccButtonIndex);
    edButtons[ccButtonIndex].callback = function(element, canvas, ed) {
        var t = this;
        if (t.isOpen(ed) === false) {
            var lang = prompt(codeColorerL10n.enterLanguage, '');
            if (lang) {
                t.tagStart = '[cc lang="' + lang + '"]';
            } else {
                t.tagStart = '[cc]';
            }
        }
        QTags.TagButton.prototype.callback.call(t, element, canvas, ed);
    };
})(jQuery);
