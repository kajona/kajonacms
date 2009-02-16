<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: news_id, news_start_date, news_title, news_intro, news_text, news_image, news_more_link, news_more_link_href -->
<news_list>
<div class="newsList">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h3><a href="javascript:fold('cont_%%news_id%%');">%%news_title%%</a></h3>
        </div>
        <div class="newsListMore">%%news_start_date%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div id="cont_%%news_id%%" style="display: none;">
            <div>%%news_intro%% %%news_more_link%%</div>
        </div>
    </div>
</div>
</news_list>

<!-- available placeholders: news, link_forward, link_pages, link_back -->
<news_list_wrapper>
    %%news%%
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


<!-- available placeholders: news_id, news_start_date, news_title, news_intro, news_text, news_image, news_back_link -->
<news_detail>
<div class="newsDetail">
    <h3>%%news_title%%</h3> %%news_start_date%%
    <p class="newsTeaser">%%news_intro%%</p>
    %%news_image%%
    <p>%%news_text%%</p>
    <p>%%news_back_link%%</p>
</div>
</news_detail>