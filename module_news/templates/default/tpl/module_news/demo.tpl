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


<!-- available placeholders: news_more_link, news_more_link_href, news_nrofcomments, news_commentlist 
     strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial
-->
<news_list>
<div class="newsList">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2><a href="%%news_more_link_href%%" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</a></h2>
        </div>
        <div class="newsListMore">%%objDateStart%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div><span data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</span> %%news_more_link%%</div>
    </div>
</div>
</news_list>


<!-- available placeholders: news_more_link, news_more_link_href, news_nrofcomments, news_commentlist 
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial
-->
<news_list_image>
<div class="newsList">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2><a href="%%news_more_link_href%%" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</a></h2>
        </div>
        <div class="newsListMore">%%objDateStart%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div>
	        <img src="[img,%%strImage%%,150,150]" alt="%%news_title%%" />
	        <span data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</span> %%news_more_link%%
        </div>
    </div>
</div>
</news_list_image>


<!-- available placeholders: news_back_link, news_nrofcomments, news_commentlist
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial
 -->
<news_detail>
<div class="newsDetail">
    <h2 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h2> %%objDateStart%%
    <p class="newsTeaser" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
    <p data-kajona-editable="%%strSystemid%%#strText">%%strText%%</p>
    <p>%%news_back_link%%</p>
    <div>%%news_commentlist%%</div>
</div>
</news_detail>


<!-- available placeholders: news_back_link, news_nrofcomments, news_commentlist
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial
-->
<news_detail_image>
<div class="newsDetail">
    <h2>%%strTitle%%</h2> %%objDateStart%%
    <p class="newsTeaser">%%strIntro%%</p>
    <img src="[img,%%strImage%%,300,500]" alt="%%strTitle%%" />
    %%strText%%
    <p>%%news_back_link%%</p>
    <div>%%news_commentlist%%</div>
</div>
</news_detail_image>