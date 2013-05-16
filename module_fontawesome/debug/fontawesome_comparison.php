<?php

echo "<pre>\n";
echo "<link rel=\"stylesheet\" href=\""._webpath_."/core/module_fontawesome/admin/scripts/fontawesome/css/font-awesome.min.css\">";
echo "<link rel=\"stylesheet\" href=\""._webpath_."/core/module_fontawesome/admin/scripts/fontawesome/css/font-awesome-corp.css\">";
echo "<link rel=\"stylesheet\" href=\""._webpath_."/core/module_fontawesome/admin/scripts/fontawesome/css/font-awesome-ext.css\">";
echo "<link rel=\"stylesheet\" href=\""._webpath_."/core/module_fontawesome/admin/scripts/fontawesome/css/font-awesome-social.css\">";
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

$p2f["arrow_template.png"] = "icon-arrow-up";
$p2f["icon_accept.png"] = "icon-ok";
$p2f["icon_acceptDisabled.png"] = "icon-ok icon-muted";
$p2f["icon_arrowDown.png"] = "icon-circle-arrow-down";
$p2f["icon_arrowUp.png"] = "icon-circle-arrow-up";
$p2f["icon_aspect.png"] = "icon-th-large";
$p2f["icon_binary.png"] = "";
$p2f["icon_blank.png"] = "icon-file\" style=\"text-shadow: black 1px 1px 3px";
$p2f["icon_book.png"] = "icon-book";
$p2f["icon_bookLens.png"] = "";
$p2f["icon_calendar.png"] = "icon-calendar";
$p2f["icon_comment.png"] = "icon-comment\" style=\"text-shadow: black 1px 1px 3px";
$p2f["icon_copy.png"] = "icon-copy";
$p2f["icon_crop.png"] = "";
$p2f["icon_crop_accept.png"] = "";
$p2f["icon_crop_acceptDisabled.png"] = "";
$p2f["icon_delete.png"] = "icon-remove";
$p2f["icon_deleteDisabled.png"] = "icon-remove icon-muted";
$p2f["icon_deleteLocked.png"] = "";
$p2f["icon_disabled.png"] = "icon-flag\" style=\"color:#FF0000";
$p2f["icon_dot.png"] = "icon-star\" style=\"text-shadow: black 1px 1px 3px";
$p2f["icon_downloads.png"] = "icon-download-alt";
$p2f["icon_earth.png"] = "icon-globe icon-spin";
$p2f["icon_earthDisabled.png"] = "icon-globe icon-spin icon-muted";
$p2f["icon_edit.png"] = "icon-edit";
$p2f["icon_editDisabled.png"] = "icon-edit icon-muted";
$p2f["icon_editLocked.png"] = "icon-lock";
$p2f["icon_enabled.png"] = "icon-flag\" style=\"color:#09FF00";
$p2f["icon_event.png"] = "";
$p2f["icon_excel.png"] = "icon-ms-excel";
$p2f["icon_externalBrowser.png"] = "";
$p2f["icon_favorite.png"] = "icon-bookmark";
$p2f["icon_folderActionLevelup.png"] = "";
$p2f["icon_folderActionOpen.png"] = "";
$p2f["icon_folderClosed.png"] = "icon-folder-close";
$p2f["icon_folderOpen.png"] = "icon-folder-open";
$p2f["icon_gallery.png"] = "";
$p2f["icon_group.png"] = "icon-group";
$p2f["icon_history.png"] = "icon-time";
$p2f["icon_image.png"] = "icon-picture";
$p2f["icon_install.png"] = "";
$p2f["icon_installDisabled.png"] = "";
$p2f["icon_key.png"] = "icon-key";
$p2f["icon_key_inherited.png"] = "";
$p2f["icon_language.png"] = "";
$p2f["icon_lens.png"] = "icon-search";
$p2f["icon_lockerOpen.png"] = "icon-unlock";
$p2f["icon_mail.png"] = "icon-envelope";
$p2f["icon_mailDisabled.png"] = "icon-envelope icon-muted";
$p2f["icon_mailNew.png"] = "";
$p2f["icon_module.png"] = "icon-puzzle-piece";
$p2f["icon_movie.png"] = "icon-film";
$p2f["icon_new.png"] = " icon-plus-sign";
$p2f["icon_new_alias.png"] = "";
$p2f["icon_new_multi.png"] = "";
$p2f["icon_news.png"] = "";
$p2f["icon_page.png"] = "icon-file-alt";
$p2f["icon_pageLocked.png"] = "";
$p2f["icon_page_alias.png"] = "";
$p2f["icon_progressbar.png"] = "icon-spinner icon-spin";
$p2f["icon_question.png"] = "icon-question-sign";
$p2f["icon_rotate_left.png"] = "icon-undo";
$p2f["icon_rotate_right.png"] = "icon-repeat";
$p2f["icon_rss.png"] = "icon-rss";
$p2f["icon_sitemap.png"] = "icon-sitemap";
$p2f["icon_sound.png"] = "icon-music";
$p2f["icon_sync.png"] = "icon-refresh icon-spin";
$p2f["icon_systemtask.png"] = "";
$p2f["icon_tag.png"] = "icon-tag";
$p2f["icon_text.png"] = "icon-align-justify icon-border";
$p2f["icon_textDisabled.png"] = "icon-align-justify icon-border icon-muted";
$p2f["icon_treeBranchOpen.png"] = "";
$p2f["icon_treeBranchOpenDisabled.png"] = "";
$p2f["icon_treeLeaf.png"] = "";
$p2f["icon_treeLevelUp.png"] = "";
$p2f["icon_treeRoot.png"] = "";
$p2f["icon_update.png"] = "";
$p2f["icon_updateDisabled.png"] = "";
$p2f["icon_updateError.png"] = "";
$p2f["icon_upload.png"] = " icon-upload-alt";
$p2f["icon_user.png"] = "icon-user";
$p2f["icon_userswitch.png"] = "";
$p2f["icon_word.png"] = "icon-ms-word";
$p2f["icon_workflow.png"] = "icon-cogs";
$p2f["icon_workflowExecuted.png"] = "";
$p2f["icon_workflowNew.png"] = "";
$p2f["icon_workflowScheduled.png"] = "";
$p2f["icon_workflowTrigger.png"] = "";
$p2f["icon_workflow_ui.png"] = "";
$p2f["icon_zoom_in.png"] = "icon-zoom-in";
$p2f["icon_zoom_out.png"] = "icon-zoom-out";
$p2f["template.png"] = "";


echo "Mapped ".count($p2f)."\n";

echo "<table border=0 cellpadding=2>";
foreach ($arrFiles as $strOneFile) {
    echo "<tr><td><img src=\""._webpath_."/core/".$strIconPath.$strOneFile."\"></td>";
    echo "<td>".$strOneFile."</td>";
    //echo "<td>"."\$p2f[\"".$strOneFile."\"] = \"\";"."</td>";
    echo "<td><p><i class=\"".$p2f[$strOneFile]."\"></i> ".$p2f[$strOneFile]."</p></td>";
    echo "</tr>";
}
echo "</table>";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) STB :-)                                                                   |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";

?>