<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: action, suche_term, form_searchterm_label, form_submit_label -->
<search_form>
    <form name="searchResultForm" method="post" action="%%action%%" accept-charset="UTF-8">
        <div><label for="resultSearchterm">%%lang_searchterm_label%%:</label><input type="text" name="searchterm" id="resultSearchterm" value="%%suche_term%%" class="inputText" /></div><br />
        <div><label for="Submit">&nbsp;</label><input type="submit" name="Submit" value="%%lang_submit_label%%" class="button" /></div><br />
    </form>
</search_form>

<!-- available placeholders: hitlist, search_term, search_nrresults, link_back, link_overview, link_forward, hitlist_text1, hitlist_text2, hitlist_text3 -->
<search_hitlist>
    <br /><br />
    <div>
        <div>%%hitlist_text1%% "%%search_term%%" %%hitlist_text2%% %%search_nrresults%% %%hitlist_text3%%:</div><br />
        <ul>%%hitlist%%</ul>
        <div align="center">%%link_back%%&nbsp;&nbsp;%%link_overview%%&nbsp;&nbsp;%%link_forward%%</div>
    </div>
</search_hitlist>

<!-- available placeholders: page_link, page_description -->
<search_hitlist_hit>
<li>%%page_link%%<br />%%page_description%%</li>
</search_hitlist_hit>