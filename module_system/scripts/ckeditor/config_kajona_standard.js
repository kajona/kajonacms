/*
    This file customizes the CKEditor to the requirements of Kajona.

    $Id$
*/

CKEDITOR.editorConfig = function(config) {
    /*
        CKEditor offers a lot of toolbar buttons, here's a full list:
        
        config.toolbar_standard =
            [
                ['Source','-','Save','NewPage','Preview','-','Templates'],
                ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
                ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
                '/',
                ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Link','Unlink','Anchor'],
                ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
                '/',
                ['Styles','Format','Font','FontSize'],
                ['TextColor','BGColor'],
                ['Maximize', 'ShowBlocks','-','About']
            ];
    */
            
    //default toolbar, e.g. used for the page paragraphs
    config.toolbar_standard =
        [
            ['Source','Save','-','Cut','Copy','Paste','PasteText','-'],
            ['Undo','Redo','-','RemoveFormat'],
            ['Link','Unlink','Anchor'],
            ['Image','Table','HorizontalRule','SpecialChar'],['Maximize'],
            '/',
            ['Font','FontSize'],
            ['Bold','Italic','Underline','Strike','Subscript','Superscript'],
            ['NumberedList','BulletedList','-','Outdent','Indent'],
            ['TextColor','BGColor']
        ];
    
    //limited toolbar, e.g. used for text-input only
    config.toolbar_minimaltext =
        [
            ['Undo','Redo','-','RemoveFormat'],
            ['Bold','Italic','Underline'],
            ['NumberedList','BulletedList','-','Outdent','Indent']
        ];

    //limited toolbar, e.g. used for the downloads and the gallery
    config.toolbar_minimal =
        [
            ['Source','Save','-'],
            ['Undo','Redo','-','RemoveFormat'],
            ['Link','Unlink'],
            ['Bold','Italic','Underline'],
            ['NumberedList','BulletedList','-','Outdent','Indent']
        ];

    //limited toolbar but with support for images
    config.toolbar_minimalimage =
        [
            ['Source','Save','-'],
            ['Undo','Redo','-','RemoveFormat'],
            ['Link','Unlink', 'Image'],
            ['Bold','Italic','Underline'],
            ['NumberedList','BulletedList','-','Outdent','Indent']
        ];

    config.toolbar_pe_full =
        [
            ['Undo','Redo','-','RemoveFormat'],
            ['Bold','Italic','Underline','Strike','Subscript','Superscript'],
            ['NumberedList','BulletedList','-','Outdent','Indent'],
            ['Link','Unlink'],
            ['Image','Table']
        ];

    config.toolbar_pe_lite =
        [
            ['Undo','Redo']
        ];

    config.toolbar_minimal_nosource =
        [
            ['Undo','Redo','-','RemoveFormat'],
            ['Bold','Italic','Underline'],
            ['NumberedList','BulletedList','-','Image']
        ];

    //disable the conversion of special chars into html entities
    config.entities = false;
    config.entities_greek = false;
    config.entities_latin = false;
    config.autoParagraph = false;
    config.enterMode = CKEDITOR.ENTER_BR;
    config.width = 620;
    config.height = 250;
};