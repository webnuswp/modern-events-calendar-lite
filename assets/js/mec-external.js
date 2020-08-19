// TinyMce Plugins
if(jQuery('.mec-fes-form').length < 1)
{
    var items = '';
    if(typeof mec_admin_localize !== "undefined") items = JSON.parse(mec_admin_localize.mce_items);

    var menu = [];
    if(items && typeof tinymce !== 'undefined')
    {
        tinymce.PluginManager.add('mec_mce_buttons', function(editor, url)
        {
            items.shortcodes.forEach(function(e, i)
            {
                menu.push(
                {
                    text: items.shortcodes[i]['PN'].replace(/-/g, ' '),
                    id: items.shortcodes[i]['ID'],
                    classes: 'mec-mce-items',
                    onselect: function(e)
                    {
                        editor.insertContent(`[MEC id="${e.control.settings.id}"]`);
                    }
                });
            });

            // Add menu button
            editor.addButton('mec_mce_buttons',
            {
                text: items.mce_title,
                icon: false,
                type: 'menubutton',
                menu: menu
            });
        });
    }
}

(function(wp, $)
{
    // Block Editor
    console.log(items);
    if(items && wp && wp.blocks)
    {
        items.shortcodes.forEach(function(e, i)
        {
            wp.blocks.registerBlockType(`mec/blockeditor-${i}`, 
            {
                title: items.shortcodes[i]['PN'].toLowerCase().replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g, function(s)
                {
                    return s.toUpperCase().replace(/-/g,' ');
                }),
                icon: 'calendar-alt',
                category: 'mec.block.category',
                edit: function()
                {
                    return `[MEC id="${(items.shortcodes[i]['ID'])}"]`;
                },
                save: function()
                {
                    return `[MEC id="${(items.shortcodes[i]['ID'])}"]`;
                }
            });
        });
    }
})(window.wp, jQuery);