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
        <div class="card-header">
            <span class="pull-right">%%objDateStart%%</span>
            <h4><a onclick="KAJONA.util.fold('cont_%%strSystemid%%'); return false;" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</a></h4>
        </div>
        <div id="cont_%%strSystemid%%" style="display: none;">
            <div class="card-block">
                <p class="card-text"><span data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</span></p>
            </div>
            <div class="card-footer">
                <span>%%news_more_link%%</span>
            </div>
        </div>
    </div>


</news_list>


<!-- available placeholders: news_more_link, news_more_link_href, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
 -->
<news_list_image>

    <div class="card">
        <div class="card-header">
            <span class="pull-right">%%objDateStart%%</span>
            <h4><a onclick="KAJONA.util.fold('cont_%%strSystemid%%'); return false;" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</a></h4>
        </div>
        <div id="cont_%%strSystemid%%" style="display: none;">
            <div class="card-block row">
                <div class="col-xs-9">
                    <p class="lead" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
                </div>
                <div class="col-xs-12 col-md-3">
                    <img src="[img,%%strImage%%,150,150]" alt="%%news_title%%" class="img-fluid"/>
                </div>
            </div>
            <div class="card-footer">
                <span>%%news_more_link%%</span>
            </div>
        </div>
    </div>


</news_list_image>


<!-- available placeholders: news_back_link, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
 -->
<news_detail>
    <div class="card">
        <div class="card-header">
            <span class="pull-right">%%objDateStart%%</span>
            <h4 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h4>

        </div>
        <div class="card-block">
            <p class="lead" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
            <div data-kajona-editable="%%strSystemid%%#strText">%%strText%%</div>
        </div>
        <div class="card-block">
            <div class="card-link">%%news_back_link%%</div>
        </div>
        <div class="card-block">
            <div>%%news_categories%%</div>
        </div>
        <div class="card-footer">
            <div>%%news_commentlist%%</div>
        </div>
    </div>
</news_detail>


<!-- available placeholders: news_back_link, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
 -->
<news_detail_image>
    <div class="card">
        <div class="card-header">
            <span class="pull-right">%%objDateStart%%</span>
            <h4 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h4>

        </div>
        <div class="card-block">
            <p class="lead" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
            <img src="[img,%%strImage%%,300,500]" alt="%%strTitle%%" class="pull-right img-fluid"/>
            <div data-kajona-editable="%%strSystemid%%#strText">%%strText%%</div>
        </div>
        <div class="card-block">
            <div class="card-link">%%news_back_link%%</div>
        </div>
        <div class="card-block">
            <div>%%news_categories%%</div>
        </div>
        <div class="card-footer">
            <div>%%news_commentlist%%</div>
        </div>
    </div>
</news_detail_image>


<!-- available placeholders: strTitle -->
<categories_category>
    <span class="badge badge-default">%%strTitle%%</span>
</categories_category>

<!-- available placeholders: categories -->
<categories_wrapper>
    [lang,news_categories,news]%%categories%%
</categories_wrapper>


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
e>