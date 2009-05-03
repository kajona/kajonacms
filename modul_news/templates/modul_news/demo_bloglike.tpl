<!-- please note: this is an unofficial template! it requires a postacomment-template "postacomment_ajax.tpl" to be available! -->


<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: news_id, news_start_date, news_title, news_intro, news_text, news_image, news_more_link, news_more_link_href -->
<news_list>
<div class="newsList">
    <div class="newsListHeader">
        <div class="newsListTitle">
            <h3>%%news_title%%</h3>
        </div>
        <div class="newsListMore">%%news_start_date%%</div>
        <div class="clearer"></div>
    </div>
    <div class="newsListTeaser">
        <div id="cont_%%news_id%%">
            <div>%%news_intro%%</div>
            <div>%%news_text%%</div>
            <div><a href="javascript:fold('pac_%%news_id%%');">Comments: %%news_nrofcomments%%</a></div>
            <div id="pac_%%news_id%%" style="display: none;">
                %%news_commentlist%%
            </div>
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
