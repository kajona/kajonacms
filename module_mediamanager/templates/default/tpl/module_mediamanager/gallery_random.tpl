<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders:
    image_src, overview, pathnavigation, backlink, forwardlink, backlink_(1..3), forwardlink_(1..3), filestrip_current
    file_systemid, file_name, file_description, file_subtitle, file_filename, file_size, file_hits, file_rating (if module rating installed),
    file_owner, file_lmtime, file_link, file_link_href
 -->
<filedetail>
    <div class="galleryRandom">
        %%file_name%% %%file_subtitle%%<br />
        <img src="%%image_src%%" alt="%%file_name%%" /><br />
        %%file_rating%%<br />
        %%file_description%%
    </div>
</filedetail>


<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_hits, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
        if (typeof bitKajonaRatingsLoaded == "undefined") {
            $.getScript(KAJONA_WEBPATH+"/templates/default/js/rating.js?"+_system_browser_cachebuster_);
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