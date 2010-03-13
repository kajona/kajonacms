<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: link_href, image_src, image_title, image_x, image_y -->
<imageWithLink>
    <div class="element_image">
        <a href="%%link_href%%"><img src="%%image_src%%" alt="%%image_title%%" /></a><br />
        %%image_title%%
    </div>
</imageWithLink>

<!-- available placeholders: image_src, image_title, image_x, image_y -->
<imageWithoutLink>
    <div class="element_image">
        <img src="%%image_src%%" alt="%%image_title%%" /><br />
        %%image_title%%
    </div>
</imageWithoutLink>