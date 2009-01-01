<search_form>
<form name="searchResultForm" method="post" action="%%action%%" accept-charset="UTF-8">
      <div><label for="resultSearchterm">Suchbegriff:</label><input type="text" name="searchterm" id="resultSearchterm" value="%%suche_term%%" class="inputText" /></div><br />
      <div><label for="Submit">&nbsp;</label><input type="submit" name="Submit" value="Suche" class="button" /></div><br />
</form>
</search_form>

<search_hitlist>
<br /><br />
<div>
    <div>Die Suche nach "%%search_term%%" ergab %%search_nrresults%% Treffer:</div><br />
    <ul>%%hitlist%%</ul>
    <div align="center">%%link_back%%&nbsp;&nbsp;%%link_overview%%&nbsp;&nbsp;%%link_forward%%</div>
</div>
</search_hitlist>

<search_hitlist_hit>
<li>%%page_link%%<br />%%page_description%%</li>
</search_hitlist_hit>