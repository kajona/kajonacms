<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: action, search_term -->
<search_form>
</search_form>

<!-- available placeholders: hitlist, search_term, search_nrresults, link_back, link_overview, link_forward -->
<search_hitlist>
    <div class="searchHitList">
        <!--<div>[lang,hitlist_text1,search] "%%search_term%%" [lang,hitlist_text2,search] %%search_nrresults%% [lang,hitlist_text3,search]:</div><br />-->
        <ul>%%hitlist%%</ul>
        <div align="center">%%link_back%%&nbsp;&nbsp;%%link_overview%%&nbsp;&nbsp;%%link_forward%%</div>
    </div>
</search_hitlist>

<!-- available placeholders: page_link, page_description -->
<search_hitlist_hit>
<li>%%page_link%%<br />%%page_description%%</li>
</search_hitlist_hit>

<class_module_faqs_faq>
    <li class="faqFaq">
        <div class="faqFaqQuestion">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td><img src="_webpath_/templates/default/pics/default/icon_question.gif" /></td>
                    <td style="padding-left: 2px;" data-kajona-editable="%%strSystemid%%#strQuestion#plain">%%strQuestion%%</td>
                </tr>
            </table>
        </div>
        <div class="faqFaqAnswer"><a name="%%faq_systemid%%"></a><span data-kajona-editable="%%strSystemid%%#strAnswer">%%strAnswer%%</span></div>
        <div>%%page_link%%</div>
    </li>
</class_module_faqs_faq>


<class_module_news_news>
    <li class="newsListTeaser">
        <div>
            <p class="newsTeaser" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
            <p data-kajona-editable="%%strSystemid%%#strText">%%strText%%</p>
            <div><a href="#" onclick="KAJONA.util.fold('pac_%%strSystemid%%'); return false;">Comments: %%news_nrofcomments%%</a></div>
            <div id="pac_%%strSystemid%%" style="display: none;">
                %%news_commentlist%%
            </div>
        </div>
    </li>
</class_module_news_news>