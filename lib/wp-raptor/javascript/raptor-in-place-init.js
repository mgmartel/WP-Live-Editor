raptor(function($) {
    if (!$('.raptor-editable-post').length) return;
    $('.raptor-editable-post').editor({
        uiOrder: [
            ['save', 'cancel'],
            ['dock'],
            ['showGuides'],
            ['viewSource'],
            ['undo', 'redo'],
            ['alignLeft', 'alignCenter', 'alignJustify', 'alignRight'],
            ['textBold', 'textItalic', 'textUnderline', 'textStrike'],
            ['textSub', 'textSuper'],
            ['listUnordered', 'listOrdered'],
            ['hr', 'quoteBlock'],
            ['fontSizeInc', 'fontSizeDec'],
            ['wordpressMediaLibrary'],
            ['link', 'unlink'],
            ['insertFile'],
            ['floatLeft', 'floatNone', 'floatRight'],
            ['tagMenu']
        ],
        enableUi: false,
        ui: {
            wordpressMediaLibrary: true,
            showGuides: true,
            dock: true,
            viewSource: true,
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
            floatLeft: true,
            floatRight: true,
            floatNone: true,
            fontSizeInc: true,
            fontSizeDec: true,
            hr: true,
            undo: true,
            redo: true,
            link: true,
            unlink: true,
            listUnordered: true,
            listOrdered: true,
            tagMenu: true,
            save: true,
            cancel: true
        },
        plugins: {
            dock: {
                docked: true,
                dockUnder: '#wpadminbar'
            },
            saveJson: {
                showResponse: true,
                id: {
                    attr: 'data-post_id'
                },
                postName: raptorInPlace.action,
                ajax: {
                    url: raptorInPlace.url,
                    type: 'post',
                    cache: false,
                    data: function(id, contentData) {
                        var data = {
                            action: raptorInPlace.action,
                            posts: contentData,
                            nonce: raptorInPlace.nonce
                        };
                        return data;
                    }
                }
            },
            imageResize: {
                allowOversizeImages: raptorInPlace.allowOversizeImages
            }
        }
    });
});