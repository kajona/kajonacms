<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_adminskin_imageresolver.php 5495 2013-02-05 16:30:28Z sidler $                           *
********************************************************************************************************/

/**
 * Class class_adminskin_imageresolver
 *
 * @author sidler@mulchprod.de
 * @since 4.2
 * @package module_v4skin
 */
class class_adminskin_imageresolver implements interface_adminskin_imageresolver {

    /**
     * Converts the passed image-name into a real, resolvable code-fragment (such as an image-tag or an
     * i-tag with css-code).
     *
     * @param $strName
     * @param string $strAlt
     * @param bool $bitBlockTooltip
     * @param string $strEntryId
     *
     * @return string
     */
    public function getImage($strName, $strAlt = "", $bitBlockTooltip = false, $strEntryId = "") {

        $strName = uniStrReplace(".png", "", $strName);

        $strFA = $this->getFASomeImage($strName, ($bitBlockTooltip ? "" : $strAlt));
        if($strFA != null)
            return $strFA;


        if($strName == "loadingSmall")
            $strName .= ".gif";
        else
            $strName .= ".png";

        return "<img src=\""._skinwebpath_."/pics/".$strName."\"  alt=\"".$strAlt."\"  ".(!$bitBlockTooltip ? "rel=\"tooltip\" title=\"".$strAlt."\" " : "" )." ".($strEntryId != "" ? " id=\"".$strEntryId."\" " : "" )." data-kajona-icon='".$strName."' />";
    }


    private function getFASomeImage($strImage, $strTooltip) {

        $strName = uniStrReplace(array(".png", ".gif"), "", $strImage);
        if(isset(self::$arrFAImages[$strName] )) {
            if($strTooltip == "")
                return self::$arrFAImages[$strName];
            else
                return "<span rel=\"tooltip\" title=\"".$strTooltip."\" data-kajona-icon='".$strName."' >".self::$arrFAImages[$strName]."</span>";
        }
        return null;
    }
    




    
    private static $arrFAImages = array(

        "arrow_template"                   => "<i class='icon-arrow-up'></i>",
        "icon_accept"                      => "<i class='icon-ok'></i>",
        "icon_acceptDisabled"              => "<span class='icon-stack'><i class='icon-ok'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_arrowDown"                   => "<i class='icon-circle-arrow-down'></i>",
        "icon_arrowUp"                     => "<i class='icon-circle-arrow-up'></i>",
        "icon_aspect"                      => "<i class='icon-columns'></i>",
        "icon_binary"                      => "<i class='icon-file'></i>",
        "icon_blank"                       => "<i class='icon-file-alt'></i>",
        "icon_book"                        => "<i class='icon-book'></i>",
        "icon_bookLens"                    => "<span class='icon-stack'><i class='icon-book'></i><i class='icon-search icon-stack-base' ></i></span>",
        "icon_calendar"                    => "<i class='icon-calendar'></i>",
        "icon_comment"                     => "<i class='icon-comment'></i>",
        "icon_copy"                        => "<i class='icon-copy'></i>",
        "icon_crop"                        => "<i class='icon-crop'></i>",
        "icon_crop_accept"                 => "<span class='icon-stack'><i class='icon-crop'></i><i class='icon-ok icon-stack-base' style='color: green'></i></span>",
        "icon_crop_acceptDisabled"         => "<span class='icon-stack'><i class='icon-crop'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_delete"                      => "<i class='icon-trash'></i>",
        "icon_deleteDisabled"              => "<span class='icon-stack'><i class='icon-trash'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_deleteLocked"                => "<span class='icon-stack'><i class='icon-trash'></i><i class='icon-lock icon-stack-base' style='color: red'></i></span>",
        "icon_disabled"                    => "<i class='icon-eye-close' style='color: #FF0000;'></i>",
        "icon_dot"                         => "<i class='icon-star'></i>",
        "icon_downloads"                   => "<i class='icon-download-alt'></i>",
        "icon_earth"                       => "<i class='icon-globe '></i>",
        "icon_earthDisabled"               => "<span class='icon-stack'><i class='icon-globe'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_edit"                        => "<i class='icon-pencil'></i>",
        "icon_editDisabled"                => "<span class='icon-stack'><i class='icon-pencil'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_editLocked"                  => "<span class='icon-stack'><i class='icon-pencil'></i><i class='icon-lock icon-stack-base' style='color: red'></i></span>",
        "icon_enabled"                     => "<i class='icon-eye-open'></i>",
        "icon_event"                       => "<i class='icon-calendar-empty'></i>",
        "icon_excel"                       => "<i class='icon-ms-excel'></i>",
        "icon_externalBrowser"             => "<i class='icon-search'></i>",
        "icon_favorite"                    => "<i class='icon-bookmark'></i>",
        "icon_folderActionLevelup"         => "<span class='icon-stack'><i class='icon-folder-open-alt'></i><i class='icon-circle-arrow-up icon-stack-base' ></i></span>",
        "icon_folderActionOpen"            => "<span class='icon-stack'><i class='icon-folder-open-alt'></i><i class='icon-search icon-stack-base' ></i></span>",
        "icon_folderClosed"                => "<i class='icon-folder-close-alt'></i>",
        "icon_folderOpen"                  => "<i class='icon-folder-open-alt'></i>",
        "icon_gallery"                     => "<i class='icon-picture'></i>",
        "icon_group"                       => "<i class='icon-group'></i>",
        "icon_history"                     => "<i class='icon-time'></i>",
        "icon_image"                       => "<i class='icon-picture'></i>",
        "icon_install"                     => "<i class='icon-download-alt'></i>",
        "icon_installDisabled"             => "<span class='icon-stack'><i class='icon-download-alt'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_key"                         => "<span class='icon-stack'><i class='icon-group'></i><i class='icon-star icon-stack-base' style='color: #ffa500;'></i></span>",
        "icon_key_inherited"               => "<i class='icon-group'></i>",
        "icon_language"                    => "<i class='icon-microphone'></i>",
        "icon_lens"                        => "<i class='icon-search'></i>",
        "icon_lockerOpen"                  => "<i class='icon-unlock'></i>",
        "icon_mail"                        => "<i class='icon-envelope-alt'></i>",
        "icon_mailDisabled"                => "<span class='icon-stack'><i class='icon-envelope-alt'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_mailNew"                     => "<i class='icon-envelope'></i>",
        "icon_module"                      => "<i class='icon-hdd'></i>",
        "icon_movie"                       => "<i class='icon-film'></i>",
        "icon_new"                         => "<i class='icon-plus-sign'></i>",
        "icon_new_alias"                   => "<span class='icon-stack'><i class='icon-plus-sign'></i><i class='icon-chevron-right icon-stack-base'></i></span>",
        "icon_new_multi"                   => "<span class='icon-stack'><i class='icon-plus-sign'></i><i class='icon-chevron-down icon-stack-base'></i></span>",
        "icon_news"                        => "<i class='icon-quote-left'></i>",
        "icon_page"                        => "<i class='icon-file-alt'></i>",
        "icon_pageLocked"                  => "<span class='icon-stack'><i class='icon-file-alt'></i><i class='icon-lock icon-stack-base' style='color: red'></i></span>",
        "icon_page_alias"                  => "<span class='icon-stack'><i class='icon-file-alt'></i><i class='icon-chevron-right icon-stack-base'></i></span>",
        "icon_progressbar"                 => "<i class='icon-spinner icon-spin'></i>",
        "icon_question"                    => "<i class='icon-question-sign'></i>",
        "icon_rotate_left"                 => "<i class='icon-undo'></i>",
        "icon_rotate_right"                => "<i class='icon-repeat'></i>",
        "icon_rss"                         => "<i class='icon-rss'></i>",
        "icon_sitemap"                     => "<i class='icon-sitemap'></i>",
        "icon_sound"                       => "<i class='icon-music'></i>",
        "icon_sync"                        => "<i class='icon-retweet'></i>",
        "icon_systemtask"                  => "<i class='icon-tasks'></i>",
        "icon_tag"                         => "<i class='icon-tag'></i>",
        "icon_text"                        => "<i class='icon-file-text-alt'></i>",
        "icon_textDisabled"                => "<span class='icon-stack'><i class='icon-file-text-alt'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_treeBranchOpen"              => "<span class='icon-stack'><i class='icon-sitemap'></i><i class='icon-chevron-right icon-stack-base'></i></span>",
        "icon_treeBranchOpenDisabled"      => "<span class='icon-stack'><i class='icon-sitemap'></i><i class='icon-ban-circle icon-stack-base' style='color: red'></i></span>",
        "icon_treeLeaf"                    => "<i class='icon-sitemap'></i>",
        "icon_treeLevelUp"                 => "<span class='icon-stack'><i class='icon-sitemap'></i><i class='icon-chevron-up icon-stack-base'></i></span>",
        "icon_treeRoot"                    => "<i class='icon-sitemap'></i>",
        "icon_update"                      => "<i class='icon-cloud-download'></i>",
        "icon_updateDisabled"              => "<span class='icon-stack'><i class='icon-cloud-download'></i><i class='icon-ok icon-stack-base' style='color: green'></i></span>",
        "icon_updateError"                 => "<span class='icon-stack'><i class='icon-cloud-download'></i><i class='icon-warning-sign icon-stack-base'></i></span>",
        "icon_upload"                      => "<i class=' icon-upload-alt'></i>",
        "icon_user"                        => "<i class='icon-user'></i>",
        "icon_userswitch"                  => "<span class='icon-stack'><i class='icon-user'></i><i class='icon-play icon-stack-base'></i></span>",
        "icon_word"                        => "<i class='icon-ms-word'></i>",
        "icon_workflow"                    => "<i class='icon-cog'></i>",
        "icon_workflowExecuted"            => "<span class='icon-stack'><i class='icon-cog'></i><i class='icon-ok icon-stack-base' style='color: green'></i></span>",
        "icon_workflowNew"                 => "<span class='icon-stack'><i class='icon-cog'></i><i class='icon-star icon-stack-base' style='color: orange'></i></span>",
        "icon_workflowScheduled"           => "<span class='icon-stack'><i class='icon-cog'></i><i class='icon-pause icon-stack-base'  '></i></span>",
        "icon_workflowTrigger"             => "<span class='icon-stack'><i class='icon-cog'></i><i class='icon-play icon-stack-base' ></i></span>",
        "icon_workflow_ui"                 => "<i class='icon-list-alt'></i>",
        "icon_zoom_in"                     => "<i class='icon-zoom-in'></i>",
        "icon_zoom_out"                    => "<i class='icon-zoom-out'></i>",
        "loadingSmall"                     => "<i class='icon-spinner icon-spin'></i>",
    );


}
