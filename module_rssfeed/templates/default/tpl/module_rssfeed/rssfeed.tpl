<!-- see section "Template-API" of element manual for a list of available placeholders -->

<!-- available placeholders: feed_title, feed_link, feed_description, feed_content -->
<rssfeed_feed>
    <div class="element_rssfeed">
        <p>
            [lang,commons_title,elements] %%feed_title%% (%%feed_link%%)<br />
            [lang,commons_description,elements] %%feed_description%%
        </p>
        <dl>%%feed_content%%</dl>
    </div>
</rssfeed_feed>

<!-- available placeholders: post_date, post_datetime, post_link, post_title, post_description -->
<rssfeed_post>
        <dt><a href="%%post_link%%" target="_blank">%%post_title%%</a> <span class="text-muted">(%%post_date%%)</span></dt>
        <dd>%%post_description%%</dd>
</rssfeed_post>