<?php

echo "<pre>\n";
echo "<link rel=\"stylesheet\" href=\""._webpath_."/core/module_fontawesome/admin/scripts/fontawesome/css/font-awesome.min.css\">";
//echo "<link rel=\"stylesheet\" href=\""._webpath_."/core/module_fontawesome/admin/scripts/fontawesome/css/font-awesome-corp.css\">";
echo "<link rel=\"stylesheet\" href=\""._webpath_."/core/module_fontawesome/admin/scripts/fontawesome/css/font-awesome-ext.css\">";
//echo "<link rel=\"stylesheet\" href=\""._webpath_."/core/module_fontawesome/admin/scripts/fontawesome/css/font-awesome-social.css\">";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Font awesome comparison                                                       |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

$objCarrier = class_carrier::getInstance();

echo "| loaded.                                                                   |\n";
echo "+-------------------------------------------------------------------------------+\n\n";

$strIconPath = "/module_v4skin/admin/skins/kajona_v4/pics/";
echo "\nIcon-Path = ". $strIconPath."\n";

$objFilesystem = new class_filesystem();
$arrFiles = $objFilesystem->getFilelist(_corepath_.$strIconPath, array(".png"));
echo "Found ".count($arrFiles)."\n";


$p2f = array();

$p2f["arrow_template.png"]                  = "<i class='icon-arrow-up'></i>";
$p2f["icon_accept.png"]                     = "<i class='icon-ok'></i>";
$p2f["icon_acceptDisabled.png"]             = "<span class='icon-stack'><i class='icon-ok'></i><i class='icon-ban-circle icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_arrowDown.png"]                  = "<i class='icon-circle-arrow-down'></i>";
$p2f["icon_arrowUp.png"]                    = "<i class='icon-circle-arrow-up'></i>";
$p2f["icon_aspect.png"]                     = "<i class='icon-th-large'></i>";
$p2f["icon_binary.png"]                     = "<i class='icon-file'></i>";
$p2f["icon_blank.png"]                      = "<i class='icon-file-alt'></i>";
$p2f["icon_book.png"]                       = "<i class='icon-book'></i>";
$p2f["icon_bookLens.png"]                   = "<span class='icon-stack'><i class='icon-book'></i><i class='icon-search icon-stack-base' ></i></span>";
$p2f["icon_calendar.png"]                   = "<i class='icon-calendar'></i>";
$p2f["icon_comment.png"]                    = "<i class='icon-comment'></i>";
$p2f["icon_copy.png"]                       = "<i class='icon-copy'></i>";
$p2f["icon_crop.png"]                       = "<i class='icon-crop'></i>";
$p2f["icon_crop_accept.png"]                = "<span class='icon-stack'><i class='icon-crop'></i><i class='icon-ok icon-stack-base' style='color: green; '></i></span>";
$p2f["icon_crop_acceptDisabled.png"]        = "<span class='icon-stack'><i class='icon-crop'></i><i class='icon-ban-circle icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_delete.png"]                     = "<i class='icon-remove'></i>";
$p2f["icon_deleteDisabled.png"]             = "<span class='icon-stack'><i class='icon-remove'></i><i class='icon-ban-circle icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_deleteLocked.png"]               = "<span class='icon-stack'><i class='icon-remove'></i><i class='icon-lock icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_disabled.png"]                   = "<i class='icon-flag' style='color:#FF0000'></i>";
$p2f["icon_dot.png"]                        = "<i class='icon-star'></i>";
$p2f["icon_downloads.png"]                  = "<i class='icon-download-alt'></i>";
$p2f["icon_earth.png"]                      = "<i class='icon-globe '></i>";
$p2f["icon_earthDisabled.png"]              = "<span class='icon-stack'><i class='icon-globe'></i><i class='icon-ban-circle icon-stack-base' style='color: red;  '></i></span>";
$p2f["icon_edit.png"]                       = "<i class='icon-pencil'></i>";
$p2f["icon_editDisabled.png"]               = "<span class='icon-stack'><i class='icon-pencil'></i><i class='icon-ban-circle icon-stack-base' style='color: red;  '></i></span>";
$p2f["icon_editLocked.png"]                 = "<span class='icon-stack'><i class='icon-pencil'></i><i class='icon-lock icon-stack-base' style='color: red;'></i></span>";
$p2f["icon_enabled.png"]                    = "<i class='icon-flag' style='color: green'></i>";
$p2f["icon_event.png"]                      = "<i class='icon-calendar-empty'></i>";
$p2f["icon_excel.png"]                      = "<i class='icon-ms-excel'></i>";
$p2f["icon_externalBrowser.png"]            = "<i class='icon-search'></i>";
$p2f["icon_favorite.png"]                   = "<i class='icon-bookmark'></i>";
$p2f["icon_folderActionLevelup.png"]        = "<span class='icon-stack'><i class='icon-folder-open-alt'></i><i class='icon-circle-arrow-up icon-stack-base' ></i></span>";
$p2f["icon_folderActionOpen.png"]           = "<span class='icon-stack'><i class='icon-folder-open-alt'></i><i class='icon-search icon-stack-base' ></i></span>";
$p2f["icon_folderClosed.png"]               = "<i class='icon-folder-close-alt'></i>";
$p2f["icon_folderOpen.png"]                 = "<i class='icon-folder-open-alt'></i>";
$p2f["icon_gallery.png"]                    = "<i class='icon-picture'></i>";
$p2f["icon_group.png"]                      = "<i class='icon-group'></i>";
$p2f["icon_history.png"]                    = "<i class='icon-time'></i>";
$p2f["icon_image.png"]                      = "<i class='icon-picture'></i>";
$p2f["icon_install.png"]                    = "<i class='icon-download-alt'></i>";
$p2f["icon_installDisabled.png"]            = "<span class='icon-stack'><i class='icon-download-alt'></i><i class='icon-ban-circle icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_key.png"]                        = "<i class='icon-key'></i>";
$p2f["icon_key_inherited.png"]              = "<span class='icon-stack'><i class='icon-key'></i><i class='icon-circle-arrow-down icon-stack-base'></i></span>";
$p2f["icon_language.png"]                   = "<i class='icon-microphone'></i>";
$p2f["icon_lens.png"]                       = "<i class='icon-search'></i>";
$p2f["icon_lockerOpen.png"]                 = "<i class='icon-unlock'></i>";
$p2f["icon_mail.png"]                       = "<i class='icon-envelope-alt'></i>";
$p2f["icon_mailDisabled.png"]               = "<span class='icon-stack'><i class='icon-envelope-alt'></i><i class='icon-ban-circle icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_mailNew.png"]                    = "<i class='icon-envelope'></i>";
$p2f["icon_module.png"]                     = "<i class='icon-hdd'></i>";
$p2f["icon_movie.png"]                      = "<i class='icon-film'></i>";
$p2f["icon_new.png"]                        = "<i class=' icon-plus-sign'></i>";
$p2f["icon_new_alias.png"]                  = "<span class='icon-stack'><i class='icon-plus-sign'></i><i class='icon-chevron-right icon-stack-base'></i></span>";
$p2f["icon_new_multi.png"]                  = "<span class='icon-stack'><i class='icon-plus-sign'></i><i class='icon-chevron-down icon-stack-base'></i></span>";
$p2f["icon_news.png"]                       = "<i class='icon-quote-left'></i>";
$p2f["icon_page.png"]                       = "<i class='icon-file-alt'></i>";
$p2f["icon_pageLocked.png"]                 = "<span class='icon-stack'><i class='icon-file-alt'></i><i class='icon-lock icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_page_alias.png"]                 = "<span class='icon-stack'><i class='icon-file-alt'></i><i class='icon-chevron-right icon-stack-base'></i></span>";
$p2f["icon_progressbar.png"]                = "<i class='icon-spinner icon-spin'></i>";
$p2f["icon_question.png"]                   = "<i class='icon-question-sign'></i>";
$p2f["icon_rotate_left.png"]                = "<i class='icon-undo'></i>";
$p2f["icon_rotate_right.png"]               = "<i class='icon-repeat'></i>";
$p2f["icon_rss.png"]                        = "<i class='icon-rss'></i>";
$p2f["icon_sitemap.png"]                    = "<i class='icon-sitemap'></i>";
$p2f["icon_sound.png"]                      = "<i class='icon-music'></i>";
$p2f["icon_sync.png"]                       = "<i class='icon-retweet'></i>";
$p2f["icon_systemtask.png"]                 = "<i class='icon-tasks'></i>";
$p2f["icon_tag.png"]                        = "<i class='icon-tag'></i>";
$p2f["icon_text.png"]                       = "<i class='icon-align-justify '></i>";
$p2f["icon_textDisabled.png"]               = "<span class='icon-stack'><i class='icon-align-justify'></i><i class='icon-ban-circle icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_treeBranchOpen.png"]             = "<span class='icon-stack'><i class='icon-sitemap'></i><i class='icon-chevron-right icon-stack-base'></i></span>";
$p2f["icon_treeBranchOpenDisabled.png"]     = "<span class='icon-stack'><i class='icon-sitemap'></i><i class='icon-ban-circle icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_treeLeaf.png"]                   = "<i class='icon-sitemap'></i>";
$p2f["icon_treeLevelUp.png"]                = "<span class='icon-stack'><i class='icon-sitemap'></i><i class='icon-chevron-up icon-stack-base'></i></span>";
$p2f["icon_treeRoot.png"]                   = "<i class='icon-sitemap'></i>";
$p2f["icon_update.png"]                     = "<i class='icon-cloud-download'></i>";
$p2f["icon_updateDisabled.png"]             = "<span class='icon-stack'><i class='icon-cloud-download'></i><i class='icon-ban-circle icon-stack-base' style='color: red; '></i></span>";
$p2f["icon_updateError.png"]                = "<span class='icon-stack'><i class='icon-cloud-download'></i><i class='icon-warning-sign icon-stack-base'></i></span>";
$p2f["icon_upload.png"]                     = "<i class=' icon-upload-alt'></i>";
$p2f["icon_user.png"]                       = "<i class='icon-user'></i>";
$p2f["icon_userswitch.png"]                 = "<span class='icon-stack'><i class='icon-user'></i><i class='icon-play icon-stack-base'></i></span>";
$p2f["icon_word.png"]                       = "<i class='icon-ms-word'></i>";
$p2f["icon_workflow.png"]                   = "<i class='icon-cog'></i>";
$p2f["icon_workflowExecuted.png"]           = "<span class='icon-stack'><i class='icon-cog'></i><i class='icon-ok icon-stack-base' style='color: green'></i></span>";
$p2f["icon_workflowNew.png"]                = "<span class='icon-stack'><i class='icon-cog'></i><i class='icon-star icon-stack-base' style='color: orange;'></i></span>";
$p2f["icon_workflowScheduled.png"]          = "<span class='icon-stack'><i class='icon-cog'></i><i class='icon-pause icon-stack-base'  '></i></span>";
$p2f["icon_workflowTrigger.png"]            = "<span class='icon-stack'><i class='icon-cog'></i><i class='icon-play icon-stack-base' ></i></span>";
$p2f["icon_workflow_ui.png"]                = "<i class='icon-list-alt'></i>";
$p2f["icon_zoom_in.png"]                    = "<i class='icon-zoom-in'></i>";
$p2f["icon_zoom_out.png"]                   = "<i class='icon-zoom-out'></i>";
$p2f["template.png"]                        = "<i class=''></i>";


echo "Mapped ".count($p2f)."\n";

echo "<style>


[class^='icon-']:before, [class*=' icon-']:before {
 font-size: 120%;
}

.icon-stack {
position: relative;
display: inline-block;
width: 1em;
height: 1em;
line-height: 1em;
vertical-align: -35%;
}

.icon-stack .icon-stack-base {
    font-size: 70%;left: 5px; top: 4px;
//    text-shadow: -1px -1px 2px white;
    text-shadow: -1px 0 white, 0 1px white, 1px 0 white, 0 -1px white;
}

</style>";

echo "<table border=0 cellpadding=2>";
foreach ($arrFiles as $strOneFile) {
    echo "<tr><td><img src=\""._webpath_."/core/".$strIconPath.$strOneFile."\"></td>";
    echo "<td>".$strOneFile."</td>";
    //echo "<td>"."\$p2f[\"".$strOneFile."\"] = \"\";"."</td>";
    echo "<td><p>".$p2f[$strOneFile]."  ".htmlentities($p2f[$strOneFile])."</p></td>";
    echo "</tr>";
}
echo "</table>";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) STB :-)                                                                   |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";

