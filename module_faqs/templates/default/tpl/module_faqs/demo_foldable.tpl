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
            <table cellspacing="0" class="portalList">
                <tr class="portalListRow1">
                    <td class="image"><img src="_webpath_/templates/default/pics/default/icon_question.gif" /></td>
                    <td class="title"><a href="#" onclick="KAJONA.util.fold('%%faq_systemid%%'); return false;" data-kajona-editable="%%strSystemid%%#strQuestion#plain">%%strQuestion%%</a></td>
                    <td class="rating">%%faq_rating%%</td>
                </tr>
            </table>
        </div>
        <div class="faqFaqAnswer" id="%%strSystemid%%" style="display: none;" ><a name="%%faq_systemid%%"></a><span data-kajona-editable="%%strSystemid%%#strAnswer">%%strAnswer%%</span></div>
    </div>
</faq_faq>

