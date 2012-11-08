<!-- see section "Template-API" of module manual for a list of available placeholders -->
<!-- dynamic sections with schema: level_(x)_(active|inactive)[_first|_last]
     and wrapper sections: level_1_wrapper
     e.g. level_1_active, level_1_active_first, level_2_inactive_last -->

<!-- available placeholders in each section: link, text, href, target, image, image_src, page_intern, page_extern, level(x+1), lastmodified -->


<level_1_wrapper><nav id="mainnav"><ul class="mainnavul">%%level1%%</ul></nav></level_1_wrapper>

<level_1_active>
<li class="active"><a href="%%href%%" target="%%target%%" class="active">%%text%%</a>%%level2%%</li>
</level_1_active>

<level_1_inactive>
<li><a href="%%href%%" target="%%target%%">%%text%%</a>%%level2%%</li>
</level_1_inactive>




<level_2_wrapper><ul class="level2">%%level2%%</ul></level_2_wrapper>

<level_2_active>
<li><a href="%%href%%" target="%%target%%" class="active">%%text%%</a>%%level3%%</li>
</level_2_active>

<level_2_inactive>
<li><a href="%%href%%" target="%%target%%">%%text%%</a>%%level3%%</li>
</level_2_inactive>




<level_3_wrapper><ul class="level3">%%level3%%</ul></level_3_wrapper>

<level_3_active>
<li><a href="%%href%%" target="%%target%%" class="active">%%text%%</a></li>
</level_3_active>

<level_3_inactive>
<li><a href="%%href%%" target="%%target%%">%%text%%</a></li>
</level_3_inactive>