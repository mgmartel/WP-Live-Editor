raptor(function($) {
    if (!$('#quick-press #content').length) return;
    $('#quick-press #content').css({ width: '100%' }).editor({
        replace: true,
        autoEnable: true,
        uiOrder: [
            ['viewSource'],
            ['undo', 'redo'],
            ['textBold', 'textItalic', 'textUnderline', 'textStrike'],
            ['quoteBlock'],
            ['fontSizeInc', 'fontSizeDec'],
            ['link', 'unlink']
        ],
        enableUi: false,
        disabledPlugins: [
            'unsavedEditWarning'
        ],
        ui: {
            textBold: true,
            textItalic: true,
            textUnderline: true,
            textStrike: true,
            textSub: true,
            textSuper: true,
            alignLeft: true,
            alignRight: true,
            alignCenter: true,
            alignJustify: true,
            quoteBlock: true,
            hr: true,
            undo: true,
            redo: true,
            link: true,
            unlink: true
        },
        plugins: {
            unsavedEditWarning: false,
            dock: {
                docked: true,
                dockToElement: true
            },
            placeholder: {
                content: ''
            },
            imageResize: {
                allowOversizeImages: raptorQuickpress.allowOversizeImages
            }
        }
    });
});
