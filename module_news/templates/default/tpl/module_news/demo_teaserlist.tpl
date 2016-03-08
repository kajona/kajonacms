<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: news, link_forward, link_pages, link_back -->
<news_list_wrapper>
    <div class="newsListContainer">
        %%news%%
        <nav class="text-xs-center">
            <ul class=" pagination pagination-sm">%%link_back%% %%link_pages%% %%link_forward%%</ul>
        </nav>
    </div>
</news_list_wrapper>


<!-- available placeholders: news_more_link, news_more_link_href, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
     strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
-->
<news_list>
<div class="card">
    <div class="card-block">
        <div><a href="%%news_more_link_href%%" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</a></div>
        <span class="text-muted"><small>%%objDateStart%%</small></span>
        <div class="card-text"><span data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</span></div>
    </div>
    <div class="card-footer">
        <span>%%news_more_link%%</span>
    </div>
</div>
</news_list>


<!-- available placeholders: news_more_link, news_more_link_href, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
-->
<news_list_image>
    <div class="card">
        <div class="card-block">
            <div><a href="%%news_more_link_href%%" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</a></div>
            <span class="text-muted"><small>%%objDateStart%%</small></span>
            <div class="card-text"><span data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</span></div>
        </div>
        <div class="card-footer">
            <span>%%news_more_link%%</span>
        </div>
    </div>
</news_list_image>




<!-- available placeholders: pageHref -->
<pager_fwd>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[lang,commons_next,system]</a></li>
</pager_fwd>

<!-- available placeholders: pageHref -->
<pager_back>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[lang,commons_back,system]</a></li>
</pager_back>

<!-- available placeholders: pageHref, pageNumber -->
<pager_entry>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[%%pageNumber%%]</a></li>
</pager_entry>

<!-- available placeholders: pageHref, pageNumber -->
<pager_entry_active>
    <li class="page-item active"><a href="%%pageHref%%" class="page-link">[%%pageNumber%%]</a></li>
</pager_entry_active>
