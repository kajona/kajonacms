<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: faq_categories -->
<faqs_list>
    <div class="faqsList">
        <br />%%faq_categories%%
    </div>
</faqs_list>

<!-- available placeholders: faq_cat_title, faq_faqs -->
<faq_category>
    <div class="faqCategory">
        <div class="faqCategoryTitle"><h3>%%faq_cat_title%%</h3></div>
        %%faq_faqs%%
    </div>
</faq_category>

<!-- available placeholders: faq_question, faq_answer, faq_systemid, faq_rating (if module rating installed) -->
<faq_faq>
    <div class="faqFaq">
        <div class="faqFaqQuestion">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td><img src="_webpath_/portal/pics/kajona/icon_question.gif" /></td>
                    <td style="padding-left: 2px;"><a href="#%%faq_systemid%%">%%faq_question%%</a></td>
                </tr>
            </table>
        </div>
    </div>
</faq_faq>

<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_hits, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
        if (typeof bitKajonaRatingsLoaded == "undefined") {
        	KAJONA.portal.loader.loadAjaxBase(null, "rating.js");
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