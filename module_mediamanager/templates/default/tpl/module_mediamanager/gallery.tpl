<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: systemid, folderlist, filelist, pathnavigation, link_back, link_pages, link_forward -->
<list>
    <ol class="breadcrumb">%%pathnavigation%%</ol>
    <div class="card-deck-wrapper">
        <div class="card-deck" id="pv_%%systemid%%">
            %%folderlist%%%%filelist%%
        </div>
    </div>

    <nav class="text-xs-center">
        <ul class=" pagination pagination-sm">%%link_back%% %%link_pages%% %%link_forward%%</ul>
    </nav>
</list>

<!-- available placeholders: folder_name, folder_id, folder_description, folder_subtitle, folder_href, folder_preview -->
<folderlist>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-block">
                <div><a href="%%folder_href%%" data-kajona-editable="%%folder_id%%#strName#plain">%%folder_name%%</a></div>
                <div data-kajona-editable="%%folder_id%%#strDescription">%%folder_description%%</div>
            </div>
        </div>
    </div>
</folderlist>

<folderlist_preview>
    <div class="col-sm-4">
        <div class="card">
            <a href="%%folder_href%%"><img src="[img,%%folder_preview_image_src%%,220,220,fixed]" class="card-img-top" alt="%%folder_name%%" /></a>
            <div class="card-block">
                <div data-kajona-editable="%%folder_id%%#strName#plain">%%folder_name%%</div>
            </div>
        </div>
    </div>
</folderlist_preview>

<!-- the following section is used to wrap a list of files, e.g. in order to build a table.
If you'd like to have a behaviour like rendering an unlimited list of files per row, use s.th.
like < filelist >%%file_0%%</ filelist > -->
<!-- available placeholders: file_(nr) -->
<filelist>
    %%file_0%%
</filelist>

<!-- represents a single file
     available placeholders: image_detail_src, file_filename, file_name, file_subtitle, file_description, file_size, file_hits, file_details_href,
                             file_owner, file_lmtime, file_link, file_link_href, file_id, file_rating, file_elementid
-->
<filelist_file>
    <div class="col-sm-4">
        <div class="card">
            <a href="%%file_details_href%%" title="%%file_name%% %%file_subtitle%%" class="fancybox-thumb" rel="fancybox-thumb"><img src="[img,%%file_filename%%,220,220,fixed]" class="card-img-top" alt="%%file_subtitle%%" /></a>
            <div class="card-block">
                <div data-kajona-editable="%%file_id%%#strName#plain">%%file_name%%</div>
            </div>
        </div>
    </div>
</filelist_file>


<!-- available placeholders:
    image_src, overview, pathnavigation, backlink, forwardlink, backlink_(1..3), forwardlink_(1..3), filestrip_current
    file_systemid, file_name, file_description, file_subtitle, file_filename, file_size, file_hits, file_rating (if module rating installed),
    file_owner, file_lmtime, file_link, file_link_href, file_elementid
 -->
<filedetail>
    <ol class="breadcrumb">%%pathnavigation%%</ol>

    <div class="row">
        <div class="col-sm-10" data-kajona-editable="%%file_systemid%%#strName#plain">%%file_name%%</div>
        <div class="col-sm-2">%%file_rating%%</div>
    </div>
    <div class="row">
        <div class="col-sm-12" data-kajona-editable="%%file_systemid%%#strSubtitle#plain">
            %%file_subtitle%%
        </div>
    </div>

    <div class="row">
        <div class="col-sm-1"></div>
        <div class="col-sm-10 text-xs-center">
            <img src="%%image_src%%" alt="%%file_name%%" class="img-fluid  center-block" />
        </div>
        <div class="col-sm-1"></div>
    </div>

    <div class="row" >
        <div class="col-sm-12" data-kajona-editable="%%file_systemid%%#strDescription">
            %%file_description%%
        </div>
    </div>

    <div class="row text-xs-center">
        <ul class="pagination pagination-sm"><li class="page-item page-link">%%backlink%%</li><li class="page-item page-link">%%overview%%</li><li class="page-item page-link">%%forwardlink%%</li></ul>
    </div>
    <div class="row text-xs-center">
        <div class="col-sm-12">
            <div class="btn-group" role="group">
                %%backlink_3%%%%backlink_2%%%%backlink_1%%%%filestrip_current%%%%forwardlink_1%%%%forwardlink_2%%%%forwardlink_3%%
            </div>
        </div>
    </div>

</filedetail>

<!-- available placeholder:
    file_name, file_systemid, file_detail_href, file_elementid
-->
<filedetail_strip>
    <button type="button" class="btn btn-secondary"><a href="%%file_detail_href%%"><img src="[img,%%file_filename%%,60,30]" class="img-fluid" /></a></button>
</filedetail_strip>



<!-- available placeholders: pathnavigation_point -->
<pathnavigation_level>
    <li>%%pathnavigation_point%%</li>
</pathnavigation_level>
