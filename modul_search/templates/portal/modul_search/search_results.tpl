<search_form>
<form name="form1" method="post" action="%%action%%" accept-charset="UTF-8">
      <div><label for="searchterm">Suchbegriff:</label><input type="text" name="searchterm" id="searchterm" value="%%suche_term%%" class="inputText" /></div><br />
      <div><label for="Submit">&nbsp;</label><input type="submit" name="Submit" value="Suche" class="button" /></div><br />
</form>
</search_form>


<search_hitlist>
<div>
    <div>Suche nach: %%search_term%%</div><br />
    <div>Treffer: %%search_nrresults%%</div><br />
    <ul>%%hitlist%%</ul>
    <div align="center">%%link_back%%&nbsp;&nbsp;%%link_overview%%&nbsp;&nbsp;%%link_forward%%</div>
</div>
</search_hitlist>

<search_hitlist_hit>
<li>%%page_link%%<br />%%page_description%%</li>
</search_hitlist_hit>