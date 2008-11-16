/*
    This file customizes the fckeditor to the requirements of kajona.

    $Id$
*/

/*
    Toolbars.
    The set of toolbars should get the "kajona" key
*/

/*
    Sampleconfig: a lot of options, but too many for most cases

FCKConfig.ToolbarSets["kajona"] = [
	['Source','DocProps','-','Save','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],['FitWindow'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['Image','Table','Rule','SpecialChar'],
	'/',
	['FontFormat','FontName','FontSize'],
	['TextColor','BGColor']

] ;
*/

/*
    defaultconfig
*/
FCKConfig.ToolbarSets["standard"] = [
	['Source','Save','-','Cut','Copy','Paste','PasteText','PasteWord','-'],
	['Undo','Redo','-','RemoveFormat'],['FitWindow'],
	['Link','Unlink','Anchor'],
	['Image','Table','Rule','SpecialChar'],
	'/',
	['FontName','FontSize'],
	['Bold','Italic','Underline','StrikeThrough','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['TextColor','BGColor']
] ;


/*
    limited toolbars, e.g. used for the downloads and the gallery
*/
FCKConfig.ToolbarSets["minimal"] = [
	['Source','Save','-'],
	['Undo','Redo','-','RemoveFormat'],
	['Link','Unlink','Anchor'],
	['Bold','Italic','Underline'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
] ;


/*
    Disable uploads
*/
FCKConfig.ImageUpload = false;
FCKConfig.LinkUpload = false;