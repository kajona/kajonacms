<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: action, search_term -->
<search_form>

    <form name="searchResultForm" method="post" action="%%action%%" accept-charset="UTF-8">
        <fieldset class="form-group">
            <label for="resultSearchterm">[lang,searchterm_label,search]</label>
            <input type="text" name="searchterm" id="resultSearchterm" value="%%search_term%%" class="form-control" onkeyup="KAJONA.portal.search.queryBackend();" placeholder="[lang,searchterm_label,search]" />
        </fieldset>
        <fieldset class="form-group">
            <button type="submit" class="btn btn-primary">[lang,submit_label,search]</button>
        </fieldset>
    </form>
</search_form>

<!-- available placeholders: hitlist, search_term, search_nrresults, link_back, link_overview, link_forward -->
<search_hitlist></search_hitlist>

<!-- available placeholders: page_link, page_description -->
<search_hitlist_hit></search_hitlist_hit>