<!-- please note: this is an unofficial template! it requires a postacomment-template "postacomment_ajax.tpl" to be available! -->


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
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
-->
<news_list>
<div class="newsListBlog">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h2>
        </div>
        <div class="newsListMore">%%objDateStart%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div>
            <p class="newsTeaser" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
            <p data-kajona-editable="%%strSystemid%%#strText">%%strText%%</p>
            <div><a href="#" onclick="KAJONA.util.fold('pac_%%strSystemid%%'); return false;">Comments: %%news_nrofcomments%%</a></div>
            <div id="pac_%%strSystemid%%" style="display: none;">
                %%news_commentlist%%
            </div>
        </div>
    </div>
</div>
</news_list>


<!-- available placeholders: news_more_link, news_more_link_href, news_nrofcomments, news_commentlist 
    strSystemid, intLmTime, longCreateDate, strTitle, strImage, intHits, strIntro, strText, objDateStart, objDateEnd, objDateSpecial, objDateTimeStart, objDateTimeEnd, objDateTimeSpecial
-->
<news_list_image>
<div class="newsListBlog">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h2>
        </div>
        <div class="newsListMore">%%objDateStart%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div>
            <img src="[img,%%strImage%%,300,500]" alt="%%news_title%%" />
            <p class="newsTeaser" data-kajona-editable="%%strSystemid%%#strIntro#plain">%%strIntro%%</p>
            <p data-kajona-editable="%%strSystemid%%#strText">%%strText%%</p>
            <div><a href="#" onclick="KAJONA.util.fold('pac_%%strSystemid%%'); return false;">Comments: %%news_nrofcomments%%</a></div>
            <div id="pac_%%strSystemid%%" style="display: none;">
                %%news_commentlist%%
            </div>
        </div>
    </div>
</div>
</news_list_image>