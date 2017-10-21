<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: faq_categories -->
<faqs_list>
    <div class="faqsList">
        %%faq_categories%%
    </div>
</faqs_list>

<!-- available placeholders: strTitle, faq_faqs -->
<faq_category>
    <div class="faqCategory">
        <div class="faqCategoryTitle"><h3 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h3></div>
        <div id="accordion" role="tablist" aria-multiselectable="true">
            %%faq_faqs%%
        </div>
    </div>
</faq_category>

<!-- available placeholders: strQuestion, strAnswer, strSystemid, faq_rating (if module rating is installed) -->
<faq_faq>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading%%strSystemid%%">
            <span class="pull-right">%%faq_rating%%</span>
            <h4 class="panel-title">
                <span data-kajona-editable="%%strSystemid%%#strQuestion#plain">%%strQuestion%%</span>
            </h4>
        </div>
        <div id="collapse%%strSystemid%%" class="" role="tabpanel" aria-labelledby="heading%%strSystemid%%">
            <span data-kajona-editable="%%strSystemid%%#strAnswer">%%strAnswer%%</span>
        </div>
    </div>

</faq_faq>