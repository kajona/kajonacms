<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: faq_categories -->
<faqs_list>
    <div class="faqsList">
        <br />%%faq_categories%%
    </div>
</faqs_list>

<!-- available placeholders: strTitle, faq_faqs -->
<faq_category>
    <div class="faqCategory">
        <div class="faqCategoryTitle"><h3 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h3></div>
        %%faq_faqs%%
    </div>
</faq_category>

<!-- available placeholders: strQuestion, strAnswer, strSystemid, faq_rating (if module rating installed) -->
<faq_faq>
    <div class="faqFaq">
        <div class="faqFaqQuestion">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td><img src="_webpath_/templates/default/pics/default/icon_question.gif" /></td>
                    <td style="padding-left: 2px;"><a href="#%%strSystemid%%"><span data-kajona-editable="%%strSystemid%%#strQuestion#plain">%%strQuestion%%</span></a></td>
                </tr>
            </table>
        </div>
    </div>
</faq_faq>
