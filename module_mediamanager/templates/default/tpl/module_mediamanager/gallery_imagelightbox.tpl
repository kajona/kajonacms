<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: systemid, folderlist, filelist, pathnavigation, link_back, link_pages, link_forward, file_rating -->
<list>

    <script type="text/javascript">

        KAJONA.portal.loader.loadFile('/templates/default/js/fancybox/jquery.fancybox.css');
        KAJONA.portal.loader.loadFile('/templates/default/js/fancybox/helpers/jquery.fancybox-thumbs.css');

        KAJONA.portal.loader.loadFile(['/templates/default/js/fancybox/jquery.fancybox.pack.js'], function() {
            KAJONA.portal.loader.loadFile(['/templates/default/js/fancybox/helpers/jquery.fancybox-thumbs.js'], function() {
                $(document).ready(function() {
                    $(".fancybox-thumb").fancybox({
                        prevEffect	: 'none',
                        nextEffect	: 'none',
                        helpers	: {
                            title	: {
                                type: 'outside'
                            },
                            overlay	: {
                                opacity : 0.8
                            },
                            thumbs	: {
                                width	: 50,
                                height	: 50
                            }
                        },
                        type : 'image'
                    });
                });
            });

        });


    </script>

    <div class="mmPathnavigation">%%pathnavigation%%</div>
    %%folderlist%%
    <div id="pv_%%systemid%%">%%filelist%%</div>
    <p align="center">%%link_back%% %%link_pages%% %%link_forward%%</p>
    <div>Please note: This template makes use of the jQuery plugin "lightbox". Make sure to respect the projects <a href="http://fancyapps.com/fancybox/#license" target="_blank">licence</a>.</div>
</list>

<!-- available placeholders: folder_name, folder_id, folder_description, folder_subtitle, folder_href, folder_preview_image_src -->
<folderlist>
<div class="mmGalleryFolder">
    <div><a href="%%folder_href%%" data-kajona-editable="%%folder_id%%#strName#plain">%%folder_name%%</a></div>
    <div data-kajona-editable="%%folder_id%%#strDescription">%%folder_description%%</div>
</div>

</folderlist>

<folderlist_preview>

<div class="mmGalleryFolder">
    <div><a href="%%folder_href%%" data-kajona-editable="%%folder_id%%#strName#plain">%%folder_name%%</a></div>
    <div><img src="[img,%%folder_preview_image_src%%,50,50]" style="float:left;"/><p data-kajona-editable="%%folder_id%%#strDescription">%%folder_description%%</p></div>
</div>

</folderlist_preview>

<!-- the following section is used to wrap a list of files, e.g. in order to build a table.
     If you'd like to have a behaviour like rendering an unlimited list of files per row, use s.th.
     like < filelist >%%file_0%%</ filelist > -->
<!-- available placeholders: file_(nr) -->
<filelist>
%%file_0%%
%%file_1%%
%%file_2%%
</filelist>



<!-- represents a single file within a filelist
     available placeholders: image_detail_src, file_name, file_filename, file_subtitle, file_description, file_size, file_hits, file_details_href,
     file_owner, file_lmtime, file_link, file_link_href, file_id
-->
<filelist_file>
    <div class="mmGalleryImage">
        <div><a href="%%image_detail_src%%" title="%%file_name%% %%file_subtitle%%" class="fancybox-thumb" rel="fancybox-thumb"><img src="[img,%%file_filename%%,120,120,fixed]" alt="%%file_subtitle%%" /></a></div>
        <div data-kajona-editable="%%file_id%%#strName#plain">%%file_name%%</div>
    </div>
</filelist_file>


<!-- available placeholders:
   image_src, overview, pathnavigation, backlink, forwardlink, backlink_(1..3), forwardlink_(1..3), filestrip_current
   file_systemid, file_name, file_description, file_subtitle, file_filename, file_size, file_hits, file_rating (if module rating installed),
   file_owner, file_lmtime, file_link, file_link_href
-->
<filedetail>
    <!-- not used for imagelightbox -->
</filedetail>


<!-- available placeholder:
    file_name, file_system, file_detail_href
-->
<filedetail_strip>
    <!-- not used for imagelightbox -->
</filedetail_strip>

<!-- available placeholders: pathnavigation_point -->
<pathnavigation_level>
%%pathnavigation_point%% >
</pathnavigation_level>



