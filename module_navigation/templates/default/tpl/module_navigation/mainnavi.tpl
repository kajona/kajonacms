<!-- see section "Template-API" of module manual for a list of available placeholders -->
<!-- dynamic sections with schema: level_(x)_(active|inactive)[_first|_last]
     and wrapper sections: level_1_wrapper
     e.g. level_1_active, level_1_active_first, level_2_inactive_last -->

<!-- available placeholders in each section: link, text, href, target, image, image_src, page_intern, page_extern, level(x+1), lastmodified -->


<level_1_wrapper><nav id="mainnav"><ul class="nav">%%level1%%</ul></nav></level_1_wrapper>

<!-- Nav item level 1, active (currently open in your browser). Nav item level 2 is also shown -->
<level_1_active>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link active">%%text%%</a>%%level2%%</li>
</level_1_active>

<!-- Nav item level 1, inactive (NOT open in your browser at the moment). Nav item level 2 is also shown -->
<level_1_inactive>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a>%%level2%%</li>
</level_1_inactive>

<!-- Nav item level 1 with child, same as before, but it can decide if it has child pages! -->
<!-- Useful if you want to style items in a different way it they have sub pages. E.g. with a triangle symbol -->
<level_1_inactive_withchilds>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a>%%level2%%</li>
</level_1_inactive_withchilds>


<!-- Hint: If you do NOT want to show child items under inactive items remove %%level2%% in the "_inactive-sections"!! -->


<!-- Same as before, but active -->
<level_1_active_withchilds>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a>%%level2%%</li>
</level_1_active_withchilds>




<level_2_wrapper><ul>%%level2%%</ul></level_2_wrapper>

<level_2_active>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link active">%%text%%</a>%%level3%%</li>
</level_2_active>

<!-- Hint: If you do NOT want to show child items under inactive items remove %%level3%% in the "_inactive-sections"!! -->
<level_2_inactive>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a>%%level3%%</li>
</level_2_inactive>




<level_3_wrapper><ul>%%level3%%</ul></level_3_wrapper>

<level_3_active>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link active">%%text%%</a></li>
</level_3_active>

<level_3_inactive>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a></li>
</level_3_inactive>