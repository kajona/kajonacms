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


<!-- available placeholders: news_id, news_start_date, news_title, news_intro, news_text, news_more_link, news_more_link_href -->
<news_list>
<div class="newsListBlog">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2 data-kajona-editable="%%news_id%%#strTitle#plain">%%news_title%%</h2>
        </div>
        <div class="newsListMore">%%news_start_date%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div>
            <p class="newsTeaser" data-kajona-editable="%%news_id%%#strIntro#plain">%%news_intro%%</p>
            <p data-kajona-editable="%%news_id%%#strText">%%news_text%%</p>
            <div><a href="#" onclick="KAJONA.util.fold('pac_%%news_id%%'); return false;">Comments: %%news_nrofcomments%%</a></div>
            <div id="pac_%%news_id%%" style="display: none;">
                %%news_commentlist%%
            </div>
        </div>
    </div>
</div>
</news_list>


<!-- available placeholders: news_id, news_start_date, news_title, news_intro, news_text, news_image, news_more_link, news_more_link_href -->
<news_list_image>
<div class="newsListBlog">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2 data-kajona-editable="%%news_id%%#strTitle#plain">%%news_title%%</h2>
        </div>
        <div class="newsListMore">%%news_start_date%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div>
            <img src="_webpath_/image.php?image=%%news_image%%&amp;maxWidth=400&amp;maxHeight=600" alt="%%news_title%%" />
            <p class="newsTeaser" data-kajona-editable="%%news_id%%#strIntro#plain">%%news_intro%%</p>
            <p data-kajona-editable="%%news_id%%#strText">%%news_text%%</p>
            <div><a href="#" onclick="KAJONA.util.fold('pac_%%news_id%%'); return false;">Comments: %%news_nrofcomments%%</a></div>
            <div id="pac_%%news_id%%" style="display: none;">
                %%news_commentlist%%
            </div>
        </div>
    </div>
</div>
</news_list_image>