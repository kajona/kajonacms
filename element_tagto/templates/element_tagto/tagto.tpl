<!-- uses the external service AddThis.com, modify it to your needs / use a service of your choice -->
<!-- modified the original code, so it doesn't block the page rendering -->
<tagtos>
    <div class="element_tagto">
        <div id="addthis_toolbar" class="addthis_toolbox addthis_default_style" style="display: none;">
            <a href="http://www.addthis.com/bookmark.php?v=250" class="addthis_button_compact">Share</a>
            <span class="addthis_separator">|</span>
            <a class="addthis_button_facebook"></a>
            <a class="addthis_button_myspace"></a>
            <a class="addthis_button_google"></a>
            <a class="addthis_button_twitter"></a>
        </div>
        <script type="text/javascript">
            kajonaAjaxHelper.Loader.load(null, ["http://s7.addthis.com/js/250/addthis_widget.js"], function() {
                YAHOO.lang.later(100, this, function() {document.getElementById("addthis_toolbar").style.display = "block";});
            });
        </script>
    </div>
</tagtos>