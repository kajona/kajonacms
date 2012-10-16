<!-- see section "Template-API" of module manual for a list of available placeholders -->


<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_hits, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
        KAJONA.portal.loader.loadFile("/templates/default/js/rating.js");
    </script>
    <span class="inline-rating-bar">
    <ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="KAJONA.portal.tooltip.add(this, '%%rating_bar_title%%');">
        <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
        %%rating_icons%%
    </ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span> (<span id="kajona_rating_hits_%%system_id%%">%%rating_hits%%</span>)
</rating_bar>

<!-- available placeholders: rating_icon_number, rating_icon_onclick, rating_icon_title -->
<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="KAJONA.portal.tooltip.add(this, '%%rating_icon_title%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>

