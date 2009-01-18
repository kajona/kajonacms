<faqs_list>
<script type="text/javascript">
kajonaAjaxHelper.loadAjaxBase();
</script>
<div class="faqsList">
    <br />%%faq_categories%%
</div>
</faqs_list>


<faq_category>
    <div class="faqCategory">
        <div class="faqCategoryTitle"><h3>%%faq_cat_title%%</h3></div>
        %%faq_faqs%%
    </div>
</faq_category>

<faq_faq>
    <div class="faqFaq">
        <div class="faqFaqQuestion">
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td width="25px;"><img src="_webpath_/portal/pics/kajona/icon_question.gif" /></td>
                    <td style="padding-left: 2px;"><a href="javascript:fold('%%faq_systemid%%');">%%faq_question%%</a></td>
                    <td width="90px">%%faq_rating%%</td>
                </tr>
            </table>
        </div>
        <div class="faqFaqAnswer" id="%%faq_systemid%%" style="display: none;"><a name="%%faq_systemid%%"></a>%%faq_answer%%</div>
    </div>
</faq_faq>

<rating_bar>
<script type="text/javascript">
<!--
kajonaAjaxHelper.addJavascriptFile("_webpath_/portal/scripts/rating.js");
//-->
</script>
<span class="inline-rating-bar">
<ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="htmlTooltip(this, '%%rating_bar_title%%');">
    <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
    %%rating_icons%%
</ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span>
</rating_bar>

<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="htmlTooltip(this, '%%rating_icon_title%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>
