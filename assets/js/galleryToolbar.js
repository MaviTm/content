/**/
+function ($) {
    var Plugins = {

        init: function () {

            var galOpt = JSON.parse($("#galleriesData").html().trim());

            $.FroalaEditor.DefineIcon('buttonIcon', {NAME: 'image'});
            $.FroalaEditor.RegisterCommand('lightGallery', {
                title: 'Gallery',
                icon: 'buttonIcon',
                undo: true,
                focus: true,
                type: 'dropdown',
                options: galOpt,
                refreshAfterCallback: true,
                callback: function (cmd, val, params) {
                    var inHtml = '<div class="galleries" data-label="'+galOpt[val]+'" data-gallery="'+val+'">[gallery]'+val+'-'+galOpt[val]+'[/gallery]</div>';
                    this.html.insert(inHtml);
                    this.undo.saveStep();
                },
                refresh: function ($btn) {
                    console.log(this.selection.element());
                }
            });

            $.oc.richEditorButtons.push('lightGallery')
        }
    }

    $(document).on('render', function () {
        Plugins.init();
    })
}(jQuery);
/**/