<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: systemid, folderlist, piclist, pathnavigation, link_back, link_pages, link_forward -->
<list>
    <p>%%pathnavigation%%</p>
    <p>%%folderlist%%</p>
    <p>%%piclist%%</p>
    <p align="center">%%link_back%% %%link_pages%% %%link_forward%%</p>
</list>

<!-- available placeholders: folder_name, folder_description, folder_subtitle, folder_link, folder_href -->
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

<!-- the following section is used, if theres a defined number of images per row.
     set the placeholders according to the number set in the admin -->
<!-- available placeholders: pic_(nr) -->
<piclist>
    <table width="100%" cellspacing="0">
        <tr>
            <td colspan="7">&nbsp;</td>
        </tr>
        <tr style="text-align: center;">
            <td></td>
            <td>%%pic_0%%</td>
            <td></td>
            <td>%%pic_1%%</td>
            <td></td>
            <td>%%pic_2%%</td>
            <td></td>
        </tr>
    </table>
</piclist>

<!-- represents a single image
     available placeholders: pic, pic_href, name, subtitle, pic_detail -->
<piclist_pic>
    <div style="text-align: center;">
        <div><a href="%%pic_href%%"><img src="%%pic%%" /></a></div>
        <div>%%name%%</div>
    </div>
</piclist_pic>


<!-- available placeholders: pic_url, backlink, backlink_image_(1..3), backlink_image_filename_(1..3), backlink_image_systemid_(1..3),
    fowardlink, forwardlink_image_(1..3), forwardlink_image_filename_(1..3), forwardlink_image_systemid_(1..3), overview, pathnavigation,
    systemid, pic_name, pic_description, pic_subtitle, pic_filename, pic_size, pic_hits, pic_small, pic_rating (if module rating installed)
 -->
<picdetail>
    <script type="text/javascript">
        bitKajonaRatingsAvailable = false;
        
        function enableRatingsWrapper() {
            if (bitKajonaRatingsAvailable) {
                kajonaAjaxHelper.loadAjaxBase(null, "rating.js");
            }
        }
        YAHOO.util.Event.onDOMReady(enableRatingsWrapper);
    </script>
    %%pathnavigation%%
    <table width="85%" border="0" style="text-align: center;">
        <tr>
            <td width="20%">&nbsp;</td>
            <td width="60%"><div style="float: left;">%%pic_name%%</div><div style="float: right;">%%pic_rating%%</div></td>
            <td width="20%">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" >%%pic_subtitle%%</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><img src="%%pic_url%%" border="0" /></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>%%pic_description%%</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>%%backlink%%</td>
            <td>%%overview%%</td>
            <td style="text-align: right;">%%forwardlink%%</td>
        </tr>
        <tr>
            <td colspan="3" class="picstrip">%%backlink_image_3%%%%backlink_image_2%%%%backlink_image_1%%%%pic_small%%%%forwardlink_image_1%%%%forwardlink_image_2%%%%forwardlink_image_3%%</td>
        </tr>
    </table>
</picdetail>

<!-- available placeholders: pathnavigation_point -->
<pathnavigation_level>
%%pathnavigation_point%% >
</pathnavigation_level>

<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
        bitKajonaRatingsAvailable = true;
    </script>
    <span class="inline-rating-bar">
    <ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="kajonaTooltip.add(this, '%%rating_bar_title%%');">
        <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
        %%rating_icons%%
    </ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span>
</rating_bar>

<!-- available placeholders: rating_icon_number, rating_icon_onclick, rating_icon_title -->
<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="kajonaTooltip.add(this, '%%rating_icon_title%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>