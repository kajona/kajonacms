<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: systemid, folderlist, filelist, pathnavigation, link_back, link_pages, link_forward -->
<list>
    <div class="mmPathnavigation">%%pathnavigation%%</div>
    %%folderlist%%
    %%filelist%%
    <p align="center">%%link_back%% %%link_pages%% %%link_forward%%</p>
</list>

<!-- available placeholders: folder_name, folder_id, folder_description, folder_subtitle, folder_href, folder_preview -->
<folderlist>
    <table cellspacing="0" class="portalList">
        <tr class="portalListRow1">
            <td class="image"><img src="_webpath_/templates/default/pics/default/icon_folderClosed.gif" /></td>
            <td class="title"><a href="%%folder_href%%" data-kajona-editable="%%folder_id%%#strName#plain">%%folder_name%%</a></td>
        </tr>
        <tr class="portalListRow2">
            <td><img src="[img,%%folder_preview_image_src%%,50,50]" /></td>
            <td class="description">%%folder_description%%</td>
        </tr>
    </table>
</folderlist>

<!-- the following section is used to wrap a list of files, e.g. in order to build a table.
If you'd like to have a behaviour like rendering an unlimited list of files per row, use s.th.
like < filelist >%%file_0%%</ filelist > -->
<!-- available placeholders: file_(nr) -->
<filelist>
<table width="100%" cellspacing="0">
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr style="text-align: center;">
        <td width="33%">%%file_0%%</td>
        <td width="33%">%%file_1%%</td>
        <td width="33%">%%file_2%%</td>
    </tr>
</table>
</filelist>

<!-- represents a single file
     available placeholders: image_detail_src, file_filename, file_name, file_subtitle, file_description, file_size, file_hits, file_details_href,
                             file_owner, file_lmtime, file_link, file_link_href, file_id, file_rating
-->
<filelist_file>
    <div style="text-align: center;">
        <div><a href="%%file_details_href%%"><img src="[img,%%file_filename%%,100,100]" alt="%%file_subtitle%%" /></a></div>
        <div  data-kajona-editable="%%file_id%%#strName#plain">%%file_name%%</div>
    </div>
</filelist_file>


<!-- available placeholders:
    image_src, overview, pathnavigation, backlink, forwardlink, backlink_(1..3), forwardlink_(1..3), filestrip_current
    file_systemid, file_name, file_description, file_subtitle, file_filename, file_size, file_hits, file_rating (if module rating installed),
    file_owner, file_lmtime, file_link, file_link_href
 -->
<filedetail>
    <p>%%pathnavigation%%</p>
    <table width="85%" border="0" style="text-align: center;">
        <tr>
            <td width="20%">&nbsp;</td>
            <td width="60%"><div style="float: left;" data-kajona-editable="%%file_systemid%%#strName#plain">%%file_name%%</div><div style="float: right;">%%file_rating%%</div></td>
            <td width="20%">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3"  data-kajona-editable="%%file_systemid%%#strSubtitle#plain">%%file_subtitle%%</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><img src="%%image_src%%" alt="%%file_name%%" /></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td data-kajona-editable="%%file_systemid%%#strDescription">%%file_description%%</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>%%backlink%%</td>
            <td>%%overview%%</td>
            <td style="text-align: right;">%%forwardlink%%</td>
        </tr>
        <tr>
            <td colspan="3" class="picstrip">%%backlink_3%%%%backlink_2%%%%backlink_1%%%%filestrip_current%%%%forwardlink_1%%%%forwardlink_2%%%%forwardlink_3%%</td>
        </tr>
    </table>
</filedetail>

<!-- available placeholder:
    file_name, file_system, file_detail_href
-->
<filedetail_strip>
    <a href="%%file_detail_href%%"><img src="[img,%%file_filename%%,60,30]" /></a>
</filedetail_strip>







<!-- available placeholders: pathnavigation_point -->
<pathnavigation_level>
%%pathnavigation_point%% >
</pathnavigation_level>
