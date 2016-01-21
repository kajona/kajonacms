<!-- see section "Template-API" of element manual for a list of available placeholders -->

<!-- available placeholders: feed_title, feed_link, feed_description, feed_content -->
<rssfeed_feed>
    <div class="element_rssfeed">
        <p>
            [lang,commons_title,elements] %%feed_title%% (%%feed_link%%)<br />
            [lang,commons_description,elements] %%feed_description%%
        </p>
        <ul>%%feed_content%%</ul>
    </div>
</rssfeed_feed>

<!-- available placeholders: post_date, post_link, post_title, post_description -->
<rssfeed_post>
    <li>
        <div><a href="%%post_link%%" target="_blank">%%post_title%%</a> (%%post_date%%)</div>
        <div>%%post_description%%</div>
    </li>
</rssfeed_post>