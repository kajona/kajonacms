<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: action, search_term -->
<search_form>
    <form name="searchForm" method="post" action="%%action%%" accept-charset="UTF-8">
        <label for="searchterm">[lang,searchterm_label,search]</label><input type="text" name="searchterm" id="searchterm" value="%%search_term%%" class="inputTextShort" />
        <input type="submit" name="Submit" value="[lang,submit_label,search]" class="buttonShort" />
    </form>
</search_form>

<!-- available placeholders: hitlist, search_term, search_nrresults, link_back, link_overview, link_forward -->
<search_hitlist></search_hitlist>

<!-- available placeholders: page_link, page_description -->
<search_hitlist_hit></search_hitlist_hit>