<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: systemid, folderlist, filelist, pathnavigation, link_back, link_pages, link_forward -->
<list>
    <p>%%pathnavigation%%</p>
    <p>
        <table cellspacing="0" class="portalList">
            %%folderlist%%
        </table>
    </p>
    <p>
        <table cellspacing="0" class="portalList">
            %%filelist%%
        </table>
    </p>
    %%link_back%% %%link_pages%% %%link_forward%%
</list>

    <!-- available placeholders: folder_name, folder_description, folder_subtitle, folder_href, folder_preview -->
<folderlist>
    <tr class="portalListRow1">
        <td class="image"><img src="_webpath_/templates/default/pics/kajona/icon_folderClosed.gif" /></td>
        <td class="title"><a href="%%folder_href%%">%%folder_name%%</a></td>
    </tr>
    <tr class="portalListRow2">
        <td></td>
        <td class="description">%%folder_description%%</td>
    </tr>
</folderlist>


<!-- the following section is used to wrap a list of files, e.g. in order to build a table.
    If you'd like to have a behaviour like rendering an unlimited list of files per row, use s.th.
    like < filelist >%%file_0%%</ filelist > -->
<!-- available placeholders: file_(nr) -->
<filelist>
     %%file_0%%
</filelist>

<!-- represents a single file
    available placeholders: image_detail_src, file_filename, file_name, file_subtitle, file_description, file_size, file_hits, file_details_href,
                            file_owner, file_lmtime, file_link, file_link_href, file_id
-->
<filelist_file>
    <tr class="portalListRow1">
        <td class="image"><img src="_webpath_/portal/pics/kajona/icon_downloads.gif" /></td>
        <td class="title"><a href="%%file_details_href%%">%%file_name%%</a></td>
        <td class="center">%%file_size%%</td>
        <td class="actions">%%file_link%%</td>
        <td class="rating">%%file_rating%%</td>
    </tr>
    <tr class="portalListRow2">
        <td></td>
        <td colspan="4" class="description">%%file_description%%</td>
    </tr>
</filelist_file>

<!-- available placeholders: pathnavigation_point -->
<pathnavigation_level>
%%pathnavigation_point%% >
</pathnavigation_level>

<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_hits, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
        if (typeof bitKajonaRatingsLoaded == "undefined") {
            $.getScript(KAJONA_WEBPATH+"/templates/default/js/rating.js");
            var bitKajonaRatingsLoaded = true;
        }
    </script>
    <span class="inline-rating-bar">
    <ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="KAJONA.portal.tooltip.add(this, '%%rating_bar_title%%');">
        <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
        %%rating_icons%%
    </ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span> (<span id="kajona_rating_hits_%%system_id%%">%%rating_hits%%</span>)
</rating_bar>

<!-- available placeholders: rating_icon_number, rating_icon_onclick, rating_icon_title -->
<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="KAJONA.portal.tooltip.add(this, '%%rating_icon_title%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>



<!-- available placeholders:
   image_src, overview, pathnavigation, backlink, forwardlink, backlink_(1..3), forwardlink_(1..3), filestrip_current
   file_systemid, file_name, file_description, file_subtitle, file_filename, file_size, file_hits, file_rating (if module rating installed),
   file_owner, file_lmtime, file_link, file_link_href
-->
<filedetail>
    %%pathnavigation%%
    <div>
        <div>
            <div style="float: left;">%%file_name%%</div><div style="float: right;">%%file_rating%%</div>
            <div style="clear: both;"></div>
        </div>
        <div>
            <div style="float: left;">%%file_size%%</div><div style="float: right;">%%file_link%%</div>
            <div style="clear: both;"></div>
        </div>
        <div>
            <div style="float: left;">%%file_filename%%</div><div style="float: right;"></div>
            <div style="clear: both;"></div>
        </div>
        <div>
            %%file_description%%
            %%file_preview%%
        </div>
    </div>
</filedetail>


<!-- available placeholders: img_filename, img_title -->
<img_preview>
    <img src="[img,%%img_filename%%,150,100]" alt="%%img_title%%" />
</img_preview>