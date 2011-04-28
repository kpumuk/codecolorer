(function() {
  // Load plugin specific language pack
  tinymce.PluginManager.requireLangPack('codecolorer');

  tinymce.create('tinymce.plugins.CodeColorerPlugin', {
    /**
     * Initializes the plugin, this will be executed after the plugin has been created.
     * This call is done before the editor instance has finished it's initialization so use the onInit event
     * of the editor instance to intercept that event.
     *
     * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
     * @param {string} url Absolute URL to where the plugin is located.
     */
    init : function(ed, url) {
      // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
      ed.addCommand('mceCodeColorer', function() {
        ed.windowManager.open({
          file : url + '/dialog.html',
          width : 460 + parseInt(ed.getLang('codecolorer.delta_width', 0)),
          height : 410 + parseInt(ed.getLang('codecolorer.delta_height', 0)),
          inline : 1
        }, {
          plugin_url : url
        });
      });

      // Register example button
      ed.addButton('codecolorer', {
        title : 'cc',
        cmd : 'mceCodeColorer',
        image : url + '/../codecolorer_button.gif'
      });
    },

    

    /**
     * Returns information about the plugin as a name/value array.
     * The current keys are longname, author, authorurl, infourl and version.
     *
     * @return {Object} Name/value array containing information about the plugin.
     */
    getInfo : function() {
      return {
        longname : 'CodeColorer plugin',
        author : 'Dmytro Shtefluk',
        authorurl : 'http://kpumuk.info/',
        infourl : 'http://kpumuk.info/projects/wordpress-plugins/codecolorer/',
        version : '0.9.9'
      };
    }
  });

  // Register plugin
  tinymce.PluginManager.add('codecolorer', tinymce.plugins.CodeColorerPlugin);
})();
