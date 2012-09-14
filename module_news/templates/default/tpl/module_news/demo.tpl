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
<div class="newsList">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2><a href="%%news_more_link_href%%" data-kajona-editable="%%news_id%%#strTitle#plain">%%news_title%%</a></h2>
        </div>
        <div class="newsListMore">%%news_start_date%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div><span data-kajona-editable="%%news_id%%#strIntro#plain">%%news_intro%%</span> %%news_more_link%%</div>
    </div>
</div>
</news_list>


<!-- available placeholders: news_id, news_start_date, news_title, news_intro, news_text, news_image, news_more_link, news_more_link_href -->
<news_list_image>
<div class="newsList">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h2><a href="%%news_more_link_href%%" data-kajona-editable="%%news_id%%#strTitle#plain">%%news_title%%</a></h2>
        </div>
        <div class="newsListMore">%%news_start_date%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div>
	        <img src="_webpath_/image.php?image=%%news_image%%&amp;maxWidth=150&amp;maxHeight=150" alt="%%news_title%%" />
	        <span data-kajona-editable="%%news_id%%#strIntro#plain">%%news_intro%%</span> %%news_more_link%%
        </div>
    </div>
</div>
</news_list_image>


<!-- available placeholders: news_id, news_start_date, news_title, news_intro, news_text, news_back_link -->
<news_detail>
<div class="newsDetail">
    <h2 data-kajona-editable="%%news_id%%#strTitle#plain">%%news_title%%</h2> %%news_start_date%%
    <p class="newsTeaser" data-kajona-editable="%%news_id%%#strIntro#plain">%%news_intro%%</p>
    <p data-kajona-editable="%%news_id%%#strText">%%news_text%%</p>
    <p>%%news_back_link%%</p>
</div>
</news_detail>


<!-- available placeholders: news_id, news_start_date, news_title, news_intro, news_text, news_image, news_back_link -->
<news_detail_image>
<div class="newsDetail">
    <h2>%%news_title%%</h2> %%news_start_date%%
    <p class="newsTeaser">%%news_intro%%</p>
    <img src="_webpath_/image.php?image=%%news_image%%&amp;maxWidth=400&amp;maxHeight=600" alt="%%news_title%%" />
    %%news_text%%
    <p>%%news_back_link%%</p>
</div>
</news_detail_image>