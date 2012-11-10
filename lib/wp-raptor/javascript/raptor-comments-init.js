raptor(function($) {
    if (!$('#comment').length) return;
    $('#comment').editor({
        uiOrder: [
            ['viewSource'],
            ['undo', 'redo'],
            ['textBold', 'textItalic', 'textUnderline', 'textStrike'],
            ['listUnordered', 'listOrdered'],
            ['quoteBlock'],
            ['link', 'unlink']
        ],
        autoEnable: true,
        enableUi: false,
        replace: true,
        ui: {
            viewSource: true,
            textBold: true,
            textItalic: true,
            textUnderline: true,
            textStrike: true,
            quoteBlock: true,
            undo: true,
            redo: true,
            link: true,
            unlink: true,
            listUnordered: true,
            listOrdered: true,
            tagMenu: true
        },
        disablePlugins: [
            'unsavedEditWarning'
        ],
        plugins: {
            dock: {
                docked: true,
                dockToElement: true
            },
            save: false,
            unsavedEditWarning: false,
            placeholder: {
                content: 'What have you got to say?'
            }
        }
    });
});
