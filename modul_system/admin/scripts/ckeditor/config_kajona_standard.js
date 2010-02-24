/*
    This file customizes the CKEditor to the requirements of Kajona.

    $Id: fckedit_kajona_standard.js 2720 2009-04-26 17:31:16Z sidler $
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
            ['Source','Save','-','Cut','Copy','Paste','PasteText','PasteFromWord','-'],
            ['Undo','Redo','-','RemoveFormat'],['Maximize'],
            ['Link','Unlink','Anchor'],
            ['Image','Table','HorizontalRule','SpecialChar'],
            '/',
            ['Font','FontSize'],
            ['Bold','Italic','Underline','Strike','Subscript','Superscript'],
            ['NumberedList','BulletedList','-','Outdent','Indent'],
            ['TextColor','BGColor']
        ];
    
    //limited toolbar, e.g. used for the downloads and the gallery
    config.toolbar_minimal =
        [
            ['Source','Save','-'],
            ['Undo','Redo','-','RemoveFormat'],
            ['Link','Unlink','Anchor'],
            ['Bold','Italic','Underline'],
            ['NumberedList','BulletedList','-','Outdent','Indent']
        ];

    //disable the conversion of special chars into html entities
    config.entities = false;
    config.entities_greek = false;
    config.entities_latin = false;

    //flipping default enter / shift-enter behaviour. This avoids <p> wrappers by default
    config.enterMode = CKEDITOR.ENTER_BR;
    config.shiftEnterMode = CKEDITOR.ENTER_P;
    
    //add URLs to Kajona folderview for selecting pages and images
    config.filebrowserBrowseUrl = KAJONA_WEBPATH+'/index.php?admin=1&module=folderview&action=pagesFolderBrowser&pages=1&form_element=ckeditor&bit_link=1';
    config.filebrowserImageBrowseUrl = KAJONA_WEBPATH+'/index.php?admin=1&module=folderview&action=list&suffix=.jpg|.gif|.png&form_element=ckeditor&bit_link=1';
    config.filebrowserWindowWidth = 500;
    config.filebrowserWindowHeight = 500;

};