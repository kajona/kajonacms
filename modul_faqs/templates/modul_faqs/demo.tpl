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
                    <td style="padding-left: 2px;">%%faq_question%%</td>
                </tr>
            </table>
        </div>
        <div class="faqFaqAnswer"><a name="%%faq_systemid%%"></a>%%faq_answer%%</div>
    </div>
</faq_faq>

