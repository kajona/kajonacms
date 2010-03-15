<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: systemid, folderlist, piclist, pathnavigation, link_back, link_pages, link_forward -->
<list>
    <script type="text/javascript">
        if (YAHOO.lang.isUndefined(arrViewers)) {
            var arrViewers = new Array();

            YAHOO.util.Event.onDOMReady(function () {
                YAHOO.namespace("YAHOO.photoViewer");
                YAHOO.photoViewer.config = { viewers: {} };

                //init all viewers
                for (var i=0; i<arrViewers.length; i++) {
                    YAHOO.photoViewer.config.viewers[arrViewers[i]] = {
                        properties: {
                            id: arrViewers[i],
                            grow: 0.2,
                            fade: 0.2,
                            modal: true, 
                            dragable: false,
                            fixedcenter: true,
                            loadFrom: "html",
                            position: "absolute",
                            buttonText: {
                                next: " ",
                                prev: " ",
                                close: "X"
                            },
                            /* remove/rename the slideShow property to disable slideshow feature */
                            slideShow: {
                            	autoStart: false,
                            	duration: 3500,
                            	controlsText: {
                            	    play: " ",
                            	    pause: " ",
                            	    stop: " ",
                            	    display: "{0}/{1}"
	                            }
                            }
                        }
                    };
                }
            });
            
            kajonaAjaxHelper.Loader.load(
                ["dragdrop", "animation", "container"],
            	[KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base.js",
            	 KAJONA_WEBPATH+"/portal/scripts/photoviewer/assets/skins/kajona/kajona.css"]
            );
        }

        //add viewer
        arrViewers.push("pv_%%systemid%%");
    </script>
    
    <p>%%pathnavigation%%</p>
    %%folderlist%%
    <div id="pv_%%systemid%%">%%piclist%%</div>
    <p align="center">%%link_back%% %%link_pages%% %%link_forward%%</p>
</list>

<!-- available placeholders: folder_name, folder_description, folder_subtitle, folder_link, folder_href, folder_preview -->
<folderlist>
    <table cellspacing="0" class="portalList">
        <tr class="portalListRow1">
            <td class="image"><img src="_webpath_/portal/pics/kajona/icon_folderClosed.gif" /></td>
            <td class="title"><a href="%%folder_href%%">%%folder_name%%</a></td>
            <td class="actions">%%folder_link%%</td>
        </tr>
        <tr class="portalListRow2">
            <td></td>
            <td colspan="2" class="description">%%folder_description%%</td>
        </tr>
    </table>
</folderlist>

<!-- the following section is used to wrap a list of images, e.g. in order to build a table.
     If you'd like to have a behaviour like rendering an unlimited list of images per row, use s.th.
     like < piclist >%%pic_0%%</ piclist > -->
<!-- available placeholders: pic_(nr) -->
<piclist>
    <table width="100%" cellspacing="0">
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr style="text-align: center;">
            <td width="33%">%%pic_0%%</td>
            <td width="33%">%%pic_1%%</td>
            <td width="33%">%%pic_2%%</td>
        </tr>
    </table>
</piclist>

<!-- represents a single image 
     available placeholders: pic, pic_href, name, subtitle, pic_detail -->
<piclist_pic>
    <div style="text-align: center;">
        <div><a href="%%pic_detail%%" title="%%name%%" class="photoViewer"><img src="%%pic%%" alt="%%subtitle%%" /></a></div>
        <div>%%name%%</div>
    </div>
</piclist_pic>



<!-- available placeholders: pic_url, backlink, backlink_image_(1..3), backlink_image_filename_(1..3), backlink_image_systemid_(1..3),
    fowardlink, forwardlink_image_(1..3), forwardlink_image_filename_(1..3), forwardlink_image_systemid_(1..3), overview, pathnavigation,
    systemid, pic_name, pic_description, pic_subtitle, pic_filename, pic_size, pic_hits, pic_small, pic_rating (if module rating installed)
 -->
<picdetail>
    <!-- not used for imagelightbox -->
</picdetail>

<!-- available placeholders: pathnavigation_point -->
<pathnavigation_level>
%%pathnavigation_point%% >
</pathnavigation_level>

<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_hits, rating_hits, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
	    if (typeof bitKajonaRatingsLoaded == "undefined") {
	        kajonaAjaxHelper.loadAjaxBase(null, "rating.js");
	        var bitKajonaRatingsLoaded = true;
	    }
    </script>
    <span class="inline-rating-bar">
    <ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="kajonaTooltip.add(this, '%%rating_bar_title%%');">
        <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
        %%rating_icons%%
    </ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span> (<span id="kajona_rating_hits_%%system_id%%">%%rating_hits%%</span>)
</rating_bar>

<!-- available placeholders: rating_icon_number, rating_icon_onclick, rating_icon_title -->
<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="kajonaTooltip.add(this, '%%rating_icon_title%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>