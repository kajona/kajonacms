<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: action, suche_term -->
<search_form>
    <div class="boxContent">
    <form name="searchform" method="post" action="%%action%%" accept-charset="UTF-8">
        <label for="searchterm">%%lang_searchterm_label%%</label><input type="text" name="searchterm" id="searchterm" value="%%suche_term%%" class="inputTextShort" />
        <input type="submit" name="Submit" value="%%lang_submit_label%%" class="buttonShort" />
    </form>
    </div>
</search_form>

<!-- available placeholders: hitlist, search_term, search_nrresults, link_back, link_overview, link_forward -->
<search_hitlist></search_hitlist>

<!-- available placeholders: page_link, page_description -->
<search_hitlist_hit></search_hitlist_hit>