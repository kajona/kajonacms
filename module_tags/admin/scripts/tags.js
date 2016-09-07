//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

/**
 * Tags-handling
 */
define(['jquery', 'qtip'], function ($, qtip) {
    var tags = {};

    tags.createFavorite = function(strSystemid, objLink) {

        KAJONA.admin.ajax.genericAjaxCall("tags", "addFavorite", strSystemid, function(data, status, jqXHR) {

            KAJONA.admin.tooltip.removeTooltip($(objLink).find("[rel='tooltip']"));

            if($(objLink).find("[data-kajona-icon='icon_favorite']").size() > 0) {
                $(objLink).html(KAJONA.admin.tags.createFavoriteDisabledIcon);//createFavoriteDisabledIcon set via class_module_tags_admin->renderAdditionalActions
            }
            else {
                $(objLink).html(KAJONA.admin.tags.createFavoriteEnabledIcon);//createFavoriteEnabledIcon set via class_module_tags_admin->renderAdditionalActions
            }

            KAJONA.admin.tooltip.addTooltip($(objLink).find("[rel='tooltip']"));

            KAJONA.admin.ajax.regularCallback(data, status, jqXHR);
        });

    };

    tags.saveTag = function(strTagname, strSystemid, strAttribute) {
        KAJONA.admin.ajax.genericAjaxCall("tags", "saveTag", strSystemid+"&tagname="+strTagname+"&attribute="+strAttribute, function(data, status, jqXHR) {
            if(status == 'success') {
                KAJONA.admin.tags.reloadTagList(strSystemid, strAttribute);
                document.getElementById('tagname').value='';
            }
            else {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
            }
        });
    };

    tags.reloadTagList = function(strSystemid, strAttribute) {

        $("#tagsLoading_"+strSystemid).addClass("loadingContainer");

        KAJONA.admin.ajax.genericAjaxCall("tags", "tagList", strSystemid+"&attribute="+strAttribute, function(data, status, jqXHR) {
            if(status == 'success') {
                var intStart = data.indexOf("<tags>")+6;
                var strContent = data.substr(intStart, data.indexOf("</tags>")-intStart);
                $("#tagsLoading_"+strSystemid).removeClass("loadingContainer");
                $("#tagsWrapper_"+strSystemid).html(strContent);
                KAJONA.util.evalScript(strContent);
            }
            else {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
                $("#tagsLoading_"+strSystemid).removeClass("loadingContainer");
            }
        });
    };

    tags.removeTag = function(strTagId, strTargetSystemid, strAttribute) {
        KAJONA.admin.ajax.genericAjaxCall("tags", "removeTag", strTagId+"&targetid="+strTargetSystemid+"&attribute="+strAttribute, function(data, status, jqXHR) {
            if(status == 'success') {
                KAJONA.admin.tags.reloadTagList(strTargetSystemid, strAttribute);
                document.getElementById('tagname').value='';
            }
            else {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
            }
        });
    };

    tags.loadTagTooltipContent = function(strTargetSystemid, strAttribute, strTargetContainer) {
        $("#"+strTargetContainer).addClass("loadingContainer");

        KAJONA.admin.ajax.genericAjaxCall("tags", "tagList", strTargetSystemid+"&attribute="+strAttribute+"&delete=false", function(data, status, jqXHR) {
            if(status == 'success') {
                var intStart = data.indexOf("<tags>")+6;
                var strContent = data.substr(intStart, data.indexOf("</tags>")-intStart);
                $("#"+strTargetContainer).removeClass("loadingContainer");
                $("#"+strTargetContainer).html(strContent);
                KAJONA.util.evalScript(strContent);
            }
            else {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
                $("#"+strTargetContainer).removeClass("loadingContainer");
            }
        });
    };

    return tags;
});

