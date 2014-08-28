<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: news, link_forward, link_pages, link_back -->
<news_list_wrapper>
    <div class="newsListContainer">%%news%%</div>
    <div>
        <table border="0">
            <tr>
                <td align="left">%%link_back%%</td>
                <td align="center">%%link_pages%%</td>
                <td align="right">%%link_forward%%</td>
            </tr>
        </table>
    </div>
</news_list_wrapper>


<!-- available placeholders: news_more_link, news_more_link_href, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
 -->
<news_list>
<div class="newsList">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2><a href="#" onclick="KAJONA.util.fold('cont_%%strSystemid%%'); return false;" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</a></h2>
        </div>
        <div class="newsListMore">%%objDateStart%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div id="cont_%%strSystemid%%" style="display: none;">
            <div><span data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</span> %%news_more_link%%</div>
        </div>
    </div>
</div>
</news_list>


<!-- available placeholders: news_more_link, news_more_link_href, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
 -->
<news_list_image>
<div class="newsList">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2><a href="#" onclick="KAJONA.util.fold('cont_%%strSystemid%%'); return false;" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</a></h2>
        </div>
        <div class="newsListMore">%%objDateStart%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div id="cont_%%strSystemid%%" style="display: none;">
            <div>
                <img src="[img,%%strImage%%,150,150]" alt="%%news_title%%" />
                <span data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</span> %%news_more_link%%
            </div>
        </div>
    </div>
</div>
</news_list_image>


<!-- available placeholders: news_back_link, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
 -->
<news_detail>
<div class="newsDetail">
    <h2 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h2> %%objDateStart%%
    <p class="newsTeaser" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
    <p data-kajona-editable="%%strSystemid%%#strText">%%strText%%</p>
    <p>%%news_back_link%%</p>
</div>
</news_detail>


<!-- available placeholders: news_back_link, news_nrofcomments, news_commentlist, news_rating, news_categories, strOwner
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
 -->
<news_detail_image>
<div class="newsDetail">
    <h2 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h2> %%objDateStart%%
    <p class="newsTeaser" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
    <img src="[img,%%strImage%%,300,500]" alt="%%news_title%%" />
    <p data-kajona-editable="%%strSystemid%%#strText">%%strText%%</p>
    <p>%%news_back_link%%</p>
</div>
</news_detail_image>


<!-- available placeholders: strTitle -->
<categories_category>
    <li>%%strTitle%%</li>
</categories_category>

<!-- available placeholders: categories -->
<categories_wrapper>
    [lang,news_categories,news]<ul class="newsCategories">%%categories%%</ul>
</categories_wrapper>



<!-- available placeholders: pageHref -->
<pager_fwd>
    <a href="%%pageHref%%">[lang,commons_next,system]</a>
</pager_fwd>

<!-- available placeholders: pageHref -->
<pager_back>
    <a href="%%pageHref%%">[lang,commons_back,system]</a>
</pager_back>

<!-- available placeholders: pageHref, pageNumber -->
<pager_entry>
    <a href="%%pageHref%%">[%%pageNumber%%]</a>
</pager_entry>

<!-- available placeholders: pageHref, pageNumber -->
<pager_entry_active>
    <span style="font-weight: bold"><a href="%%pageHref%%">[%%pageNumber%%]</a></span>
</pager_entry_active>