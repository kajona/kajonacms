<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: action, search_term -->
<search_form>
</search_form>

<!-- available placeholders: hitlist, search_term, search_nrresults, link_back, link_overview, link_forward -->
<search_hitlist>
    <dl class="row" id="plainList">
        %%hitlist%%
    </dl>

    <nav class="text-xs-center">
        <ul class=" pagination pagination-sm">%%link_back%% %%link_overview%% %%link_forward%%</ul>
    </nav>

</search_hitlist>

<!-- available placeholders: page_link, page_description -->
<search_hitlist_hit>
    <dt class="col-sm-3">%%page_link%%</dt>
    <dd class="col-sm-9">%%page_description%%&nbsp;</dd>
</search_hitlist_hit>

<!-- available placeholders: pageHref -->
<pager_fwd>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[lang,commons_next,system]</a></li>
</pager_fwd>

<!-- available placeholders: pageHref -->
<pager_back>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[lang,commons_back,system]</a></li>
</pager_back>

<!-- available placeholders: pageHref, pageNumber -->
<pager_entry>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[%%pageNumber%%]</a></li>
</pager_entry>

<!-- available placeholders: pageHref, pageNumber -->
<pager_entry_active>
    <li class="page-item active"><a href="%%pageHref%%" class="page-link">[%%pageNumber%%]</a></li>
</pager_entry_active>

