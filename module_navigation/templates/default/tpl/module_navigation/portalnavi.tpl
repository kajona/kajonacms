<!-- see section "Template-API" of module manual for a list of available placeholders -->
<!-- dynamic sections with schema: level_(x)_(active|inactive)[_first|_last]
     and wrapper sections: level_1_wrapper
     e.g. level_1_active, level_1_active_first, level_2_inactive_last -->

<!-- available placeholders in each section: link, text, href, target, image, image_src, page_intern, page_extern, level(x+1), lastmodified -->


<level_1_wrapper><nav id="portalnav"><ul>%%level1%%</ul></nav></level_1_wrapper>

<level_1_active>
<li>%%link%%</li>
</level_1_active>

<level_1_inactive>
<li>%%link%%</li>
</level_1_inactive>

<level_1_inactive_last>
<li>%%link%%</li>
</level_1_inactive_last>

<level_1_active_last>
<li>%%link%%</li>
</level_1_active_last>