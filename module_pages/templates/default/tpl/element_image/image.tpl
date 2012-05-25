<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- This templates uses static image sizes to resize the image.
     Feel free to use the dynamic placeholders image_width/height which contain
     the values entered in the optional fields of the image element. -->

<!-- available placeholders: image_src, image_title, image_width, image_height -->
<image>
    <div class="element_image">
        <img src="[img,%%image_src%%,200,200]" alt="%%image_title%%" /><br />
        %%image_title%%
    </div>
</image>

<!-- available placeholders: link_href, image_src, image_title, image_width, image_height -->
<image_link>
    <div class="element_image">
        <a href="%%link_href%%"><img src="[img,%%image_src%%,200,200]" alt="%%image_title%%" /></a><br />
        %%image_title%%
    </div>
</image_link>