<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/
namespace Kajona\V4Skin\Admin\Skins\Kajona_V4;

use Kajona\System\Admin\AdminskinImageresolverInterface;

/**
 * @author sidler@mulchprod.de
 * @since 4.2
 * @package module_v4skin
 */
class AdminskinImageresolver implements AdminskinImageresolverInterface {

    /**
     * Converts the passed image-name into a real, resolvable code-fragment (such as an image-tag or an
     * i-tag with css-code).
     *
     * @param string $strName
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


        return "<img src=\""._skinwebpath_."/pics/".$strName."\"  alt=\"".$strAlt."\"  ".(!$bitBlockTooltip ? "rel=\"tooltip\" title=\"".$strAlt."\" " : "" )." ".($strEntryId != "" ? " id=\"".$strEntryId."\" " : "" )." data-kajona-icon='".$strName."' />";
    }


    /**
     * @param string $strImage
     * @param string $strTooltip
     *
     * @return null|string
     */
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
    




    
    public static $arrFAImages = array(

        "icon_accept"                      => "<i class='kj-icon fa fa-check'></i>",
        "icon_acceptDisabled"              => "<span class='kj-icon fa-stack'><i class='fa fa-check'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_arrowDown"                   => "<i class='kj-icon fa fa-arrow-circle-down'></i>",
        "icon_arrowUp"                     => "<i class='kj-icon fa fa-arrow-circle-up'></i>",
        "icon_aspect"                      => "<i class='kj-icon fa fa-columns'></i>",
        "icon_balance"                      => "<i class='kj-icon fa fa-balance-scale'></i>",
        "icon_binary"                      => "<i class='kj-icon fa fa-file'></i>",
        "icon_blank"                       => "<i class='kj-icon fa fa-file-o'></i>",
        "icon_book"                        => "<i class='kj-icon fa fa-book'></i>",
        "icon_bookLens"                    => "<span class='kj-icon fa-stack'><i class='fa fa-book'></i><i class='fa fa-search fa-stack-1x kj-stack' ></i></span>",
        "icon_calendar"                    => "<i class='kj-icon fa fa-calendar'></i>",
        "icon_comment"                     => "<i class='kj-icon fa fa-comment'></i>",
        "icon_copy"                        => "<i class='kj-icon fa fa-files-o'></i>",
        "icon_crop"                        => "<i class='kj-icon fa fa-crop'></i>",
        "icon_crop_accept"                 => "<span class='kj-icon fa-stack'><i class='fa fa-crop'></i><i class='fa fa-check fa-stack-1x kj-stack' style='color: green'></i></span>",
        "icon_crop_acceptDisabled"         => "<span class='kj-icon fa-stack'><i class='fa fa-crop'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_delete"                      => "<i class='kj-icon fa fa-trash-o'></i>",
        "icon_deleteDisabled"              => "<span class='kj-icon fa-stack'><i class='fa fa-trash-o'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_deleteLocked"                => "<span class='kj-icon fa-stack'><i class='fa fa-trash-o'></i><i class='fa fa-lock fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_disabled"                    => "<i class='kj-icon fa fa-eye-slash' style='color: #FF0000;'></i>",
        "icon_dot"                         => "<i class='kj-icon fa fa-star'></i>",
        "icon_downloads"                   => "<i class='kj-icon fa fa-download'></i>",
        "icon_earth"                       => "<i class='kj-icon fa fa-globe '></i>",
        "icon_earthDisabled"               => "<span class='kj-icon fa-stack'><i class='fa fa-globe'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_edit"                        => "<i class='kj-icon fa fa-pencil'></i>",
        "icon_editDisabled"                => "<span class='kj-icon fa-stack'><i class='fa fa-pencil'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_editLocked"                  => "<span class='kj-icon fa-stack'><i class='fa fa-pencil'></i><i class='fa fa-lock fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_enabled"                     => "<i class='kj-icon fa fa-eye'></i>",
        "icon_event"                       => "<i class='kj-icon fa fa-calendar-o'></i>",
        "icon_eventLocked"                 => "<span class='kj-icon fa-stack'><i class='fa fa-calendar-o'></i><i class='fa fa-lock fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_excel"                       => "<i class='kj-icon fa fa-file-excel-o'></i>",
        "icon_externalBrowser"             => "<i class='kj-icon fa fa-search'></i>",
        "icon_favorite"                    => "<i class='kj-icon fa fa-bookmark'></i>",
        "icon_favoriteDisabled"            => "<i class='kj-icon fa fa-bookmark-o'></i>",
        "icon_folderActionLevelup"         => "<span class='kj-icon fa-stack'><i class='fa fa-folder-open-o'></i><i class='fa fa-arrow-circle-up fa-stack-1x kj-stack' ></i></span>",
        "icon_folderActionOpen"            => "<span class='kj-icon fa-stack'><i class='fa fa-folder-open-o'></i><i class='fa fa-search fa-stack-1x kj-stack' ></i></span>",
        "icon_folderClosed"                => "<i class='kj-icon fa fa-folder-o'></i>",
        "icon_folderOpen"                  => "<i class='kj-icon fa fa-folder-open-o'></i>",
        "icon_gallery"                     => "<i class='kj-icon fa fa-picture-o'></i>",
        "icon_group"                       => "<i class='kj-icon fa fa-users'></i>",
        "icon_history"                     => "<i class='kj-icon fa fa-clock-o'></i>",
        "icon_image"                       => "<i class='kj-icon fa fa-picture-o'></i>",
        "icon_install"                     => "<i class='kj-icon fa fa-download'></i>",
        "icon_installDisabled"             => "<span class='kj-icon fa-stack'><i class='fa fa-download'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_key"                         => "<span class='kj-icon fa-stack'><i class='fa fa-users'></i><i class='fa fa-star fa-stack-1x kj-stack' style='color: #ffa500;'></i></span>",
        "icon_key_inherited"               => "<i class='kj-icon fa fa-users'></i>",
        "icon_language"                    => "<i class='kj-icon fa fa-microphone'></i>",
        "icon_lens"                        => "<i class='kj-icon fa fa-search'></i>",
        "icon_lockerOpen"                  => "<i class='kj-icon fa fa-unlock'></i>",
        "icon_lockerClosed"                => "<i class='kj-icon fa fa-lock'></i>",
        "icon_mail"                        => "<i class='kj-icon fa fa-envelope-o'></i>",
        "icon_mailDisabled"                => "<span class='kj-icon fa-stack'><i class='fa fa-envelope-o'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_mailNew"                     => "<i class='kj-icon fa fa-envelope'></i>",
        "icon_module"                      => "<i class='kj-icon fa fa-hdd-o'></i>",
        "icon_movie"                       => "<i class='kj-icon fa fa-film'></i>",
        "icon_new"                         => "<i class='kj-icon fa fa-plus-circle'></i>",
        "icon_new_alias"                   => "<span class='kj-icon fa-stack'><i class='fa fa-plus-circle'></i><i class='fa fa-link fa-stack-1x kj-stack'></i></span>",
        "icon_new_multi"                   => "<span class='kj-icon fa-stack'><i class='fa fa-plus-circle'></i><i class='fa fa-chevron-down fa-stack-1x kj-stack'></i></span>",
        "icon_news"                        => "<i class='kj-icon fa fa-quote-left'></i>",
        "icon_page"                        => "<i class='kj-icon fa fa-file-o'></i>",
        "icon_pageLocked"                  => "<span class='kj-icon fa-stack'><i class='fa fa-file-o'></i><i class='fa fa-lock fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_page_alias"                  => "<span class='kj-icon fa-stack'><i class='fa fa-file-o'></i><i class='fa fa-chevron-right fa-stack-1x kj-stack'></i></span>",
        "icon_phar"                        => "<i class='kj-icon fa fa-file-archive-o'></i>",
        "icon_progressbar"                 => "<i class='kj-icon fa fa-spinner icon-spin'></i>",
        "icon_question"                    => "<i class='kj-icon fa fa-question-circle'></i>",
        "icon_reply"                       => "<i class='kj-icon fa fa-reply'></i>",
        "icon_rotate_left"                 => "<i class='kj-icon fa fa-undo'></i>",
        "icon_rotate_right"                => "<i class='kj-icon fa fa-repeat'></i>",
        "icon_rss"                         => "<i class='kj-icon fa fa-rss'></i>",
        "icon_sitemap"                     => "<i class='kj-icon fa fa-sitemap'></i>",
        "icon_sound"                       => "<i class='kj-icon fa fa-music'></i>",
        "icon_sync"                        => "<i class='kj-icon fa fa-refresh'></i>",
        "icon_systemtask"                  => "<i class='kj-icon fa fa-tasks'></i>",
        "icon_tag"                         => "<i class='kj-icon fa fa-tag'></i>",
        "icon_text"                        => "<i class='kj-icon fa fa-file-text-o'></i>",
        "icon_textDisabled"                => "<span class='kj-icon fa-stack'><i class='fa fa-file-text-o'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_treeBranchOpen"              => "<span class='kj-icon fa-stack'><i class='fa fa-sitemap'></i><i class='fa fa-chevron-right fa-stack-1x kj-stack'></i></span>",
        "icon_treeBranchOpenDisabled"      => "<span class='kj-icon fa-stack'><i class='fa fa-sitemap'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_treeLeaf"                    => "<i class='kj-icon fa fa-sitemap'></i>",
        "icon_treeLeaf_link"               => "<span class='kj-icon fa-stack'><i class='fa fa-sitemap'></i><i class='fa fa-link fa-stack-1x kj-stack'></i></span>",
        "icon_treeLink"                    => "<i class='kj-icon fa fa-link'></i>",
        "icon_treeLevelUp"                 => "<span class='kj-icon fa-stack'><i class='fa fa-sitemap'></i><i class='fa fa-chevron-up fa-stack-1x kj-stack'></i></span>",
        "icon_treeRoot"                    => "<i class='kj-icon fa fa-sitemap'></i>",
        "icon_undo"                        => "<i class='kj-icon fa fa-undo'></i>",
        "icon_undoDisabled"                => "<span class='kj-icon fa-stack'><i class='fa fa-undo'></i><i class='fa fa-ban fa-stack-1x kj-stack' style='color: red'></i></span>",
        "icon_update"                      => "<i class='kj-icon fa fa-cloud-download'></i>",
        "icon_updateDisabled"              => "<span class='kj-icon fa-stack'><i class='fa fa-cloud-download'></i><i class='fa fa-check fa-stack-1x kj-stack' style='color: green'></i></span>",
        "icon_updateError"                 => "<span class='kj-icon fa-stack'><i class='fa fa-cloud-download'></i><i class='fa fa-exclamation-triangle fa-stack-1x kj-stack'></i></span>",
        "icon_upload"                      => "<i class='kj-icon fa fa-upload'></i>",
        "icon_user"                        => "<i class='kj-icon fa fa-user'></i>",
        "icon_userswitch"                  => "<span class='kj-icon fa-stack'><i class='fa fa-user'></i><i class='fa fa-play fa-stack-1x kj-stack'></i></span>",
        "icon_word"                        => "<i class='kj-icon fa fa-ms-word'></i>",
        "icon_workflow"                    => "<i class='kj-icon fa fa-cog'></i>",
        "icon_workflowExecuted"            => "<span class='kj-icon fa-stack'><i class='fa fa-cog'></i><i class='fa fa-check fa-stack-1x kj-stack' style='color: green'></i></span>",
        "icon_workflowNew"                 => "<span class='kj-icon fa-stack'><i class='fa fa-cog'></i><i class='fa fa-star fa-stack-1x kj-stack' style='color: orange'></i></span>",
        "icon_workflowScheduled"           => "<span class='kj-icon fa-stack'><i class='fa fa-cog'></i><i class='fa fa-pause fa-stack-1x kj-stack'  '></i></span>",
        "icon_workflowTrigger"             => "<span class='kj-icon fa-stack'><i class='fa fa-cog'></i><i class='fa fa-play fa-stack-1x kj-stack' ></i></span>",
        "icon_workflow_ui"                 => "<i class='kj-icon fa fa-list-alt'></i>",
        "icon_zoom_in"                     => "<i class='kj-icon fa fa-search-plus'></i>",
        "icon_zoom_out"                    => "<i class='kj-icon fa fa-search-minus'></i>",
        "loadingSmall"                     => "<i class='kj-icon fa fa-spinner fa-spin'></i>",
    );


}
