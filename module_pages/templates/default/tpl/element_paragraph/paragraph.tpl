<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- to be inserted in the other sections -->
<!-- available placeholders: paragraph_title -->
<paragraph_title_tag>
    <h2 data-kajona-editable="%%content_id%%#paragraph_title#plain">%%paragraph_title%%</h2>
</paragraph_title_tag>

<!-- available placeholders: paragraph_title_tag, paragraph_content -->
<paragraph>
    <div class="kjParagraph">
        %%paragraph_title_tag%%
        <div data-kajona-editable="%%content_id%%#paragraph_content">%%paragraph_content%%</div>
    </div>
</paragraph>

<!-- available placeholders: paragraph_title_tag, paragraph_content, paragraph_image -->
<paragraph_image>
    <div class="kjParagraph">
        %%paragraph_title_tag%%
        <img src="[img,%%paragraph_image%%,200,200]" alt="%%paragraph_title%%" class="element_paragraph_image" />
        <div data-kajona-editable="%%content_id%%#paragraph_content">%%paragraph_content%%</div>
        <div class="clearfix"></div>
    </div>
</paragraph_image>

<!-- available placeholders: paragraph_title_tag, paragraph_content, paragraph_link -->
<paragraph_link>
<div class="kjParagraph">
    %%paragraph_title_tag%%
    <div data-kajona-editable="%%content_id%%#paragraph_content">%%paragraph_content%%</div><br /><br />
    [lang,link_more_title,elements] <a href="%%paragraph_link%%">%%paragraph_link%%</a>
</div>
</paragraph_link>

<!-- available placeholders: paragraph_title_tag, paragraph_content, paragraph_image, paragraph_link -->
<paragraph_image_link>
<div class="kjParagraph">
    %%paragraph_title_tag%%
    <a href="%%paragraph_link%%" title="%%paragraph_title%%"><img src="[img,%%paragraph_image%%,200,200]" alt="%%paragraph_title%%" class="element_paragraph_image" /></a>
    <div data-kajona-editable="%%content_id%%#paragraph_content">%%paragraph_content%%</div><br /><br />
    [lang,link_more_title,elements] <a href="%%paragraph_link%%">%%paragraph_link%%</a>
    <div class="clearfix"></div>
</div>
</paragraph_image_link>