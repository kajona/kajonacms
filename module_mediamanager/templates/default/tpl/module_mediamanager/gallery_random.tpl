<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders:
    image_src, overview, pathnavigation, backlink, forwardlink, backlink_(1..3), forwardlink_(1..3), filestrip_current
    file_systemid, file_name, file_description, file_subtitle, file_filename, file_size, file_hits, file_rating (if module rating installed),
    file_owner, file_lmtime, file_link, file_link_href
 -->
<filedetail>
    <div class="galleryRandom">
        <div data-kajona-editable="%%file_systemid%%#strName#plain">%%file_name%%</div>
        <span  data-kajona-editable="%%file_systemid%%#strSubtitle#plain">%%file_subtitle%%</span><br />
        <img src="%%image_src%%" alt="%%file_name%%" /><br />
        %%file_rating%%<br />
        <span data-kajona-editable="%%file_systemid%%#strDescription">%%file_description%%</span>
    </div>
</filedetail>


