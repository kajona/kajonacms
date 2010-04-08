<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: paragraph_title, paragraph_content -->
<paragraph>
    <h2>%%paragraph_title%%</h2>
    <p>
        %%paragraph_content%%
    </p>
</paragraph>

<!-- available placeholders: paragraph_title, paragraph_content, paragraph_image -->
<paragraph_image>
    <h2>%%paragraph_title%%</h2>
    <p>
        <img src="_webpath_/image.php?image=%%paragraph_image%%&amp;maxWidth=200&amp;maxHeight=200" alt="%%paragraph_title%%" class="element_paragraph_image" />
        %%paragraph_content%%
    </p>
    <div class="clearer"></div>
</paragraph_image>

<!-- available placeholders: paragraph_title, paragraph_content, paragraph_link -->
<paragraph_link>
    <h2>%%paragraph_title%%</h2>
    <p>
        %%paragraph_content%%<br /><br />
        %%lang_link_more_title%% <a href="%%paragraph_link%%">%%paragraph_link%%</a>
    </p>
</paragraph_link>

<!-- available placeholders: paragraph_title, paragraph_content, paragraph_image, paragraph_link -->
<paragraph_image_link>
    <h2>%%paragraph_title%%</h2>
    <p>
        <a href="%%paragraph_link%%" title="%%paragraph_title%%"><img src="_webpath_/image.php?image=%%paragraph_image%%&amp;maxWidth=200&amp;maxHeight=200" alt="%%paragraph_title%%" class="element_paragraph_image" /></a>
        %%paragraph_content%%<br /><br />
        %%lang_link_more_title%% <a href="%%paragraph_link%%">%%paragraph_link%%</a>
    </p>
    <div class="clearer"></div>
</paragraph_image_link>