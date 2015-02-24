<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: action, search_term -->
<search_form>
</search_form>

<!-- available placeholders: hitlist, search_term, search_nrresults, link_back, link_overview, link_forward -->
<search_hitlist>
    <div class="searchHitList">
        <!--<div>[lang,hitlist_text1,search] "%%search_term%%" [lang,hitlist_text2,search] %%search_nrresults%% [lang,hitlist_text3,search]:</div><br />-->
        <ul>%%hitlist%%</ul>
        <div align="center">%%link_back%%&nbsp;&nbsp;%%link_overview%%&nbsp;&nbsp;%%link_forward%%</div>
    </div>
</search_hitlist>

<!-- available placeholders: page_link, page_description -->
<search_hitlist_hit>
<li>%%page_link%%<br />%%page_description%%</li>
</search_hitlist_hit>