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

    <div id="resultSetHeader" style="display: none;">
        <div class="alert alert-success" role="alert">
        [lang,hitlist_text1,search] &quot;<span id="spanSearchterm"></span>&quot; [lang,hitlist_text2,search] <span id="spanSearchamount"></span> [lang,hitlist_text3,search]
        </div>
    </div>
    <div id="searchResult"></div>

    <script type="text/javascript">
    KAJONA.portal.search =  {
        strLastQuery : "",

        queryBackend : function() {
            var strCurrentQuery = $("#resultSearchterm").val().trim();
            var searchRunning = false;
            var post_target = KAJONA_WEBPATH+"/xml.php?module=search&action=doSearch";
            var post_data = {
                searchterm : strCurrentQuery
            };

            $('#searchResult').html('');
            $('#resultSetHeader').css("display", "none");
            $('#plainList').css("display", "none");

            if(strCurrentQuery.length >= 3 && strCurrentQuery != KAJONA.portal.search.strLastQuery) {
                if(searchRunning)
                    return;

                searchRunning = true;
                KAJONA.portal.search.strLastQuery = strCurrentQuery;
                $('#searchResult').html("<div class='center-block text-xs-center' style='font-size: 2rem;'><i class='fa fa-spinner fa-spin'></i></div>");

                $.post(post_target, post_data, function(data, textStatus) {
                    $('#searchResult').html('<dl class="row"></dl>');
                    $("#spanSearchterm").html($(data).find("searchterm").html());
                    $("#spanSearchamount").html($(data).find("nrofresults").html());
                    $('#resultSetHeader').css("display", "block");

                    $(data).find("item").each(function() {
                        var objNode = $("<dt class='col-sm-3'><a href='"+$(this).find("pagelink").find("a").attr("href")+"'>"+$(this).find("pagelink").find("a").text()+"</a></dt><dd class='col-sm-9'>"+$(this).find("description").text()+"&nbsp;</dd>");
                        $("#searchResult dl").append(objNode);
                        searchRunning = false;
                    });
                }, "xml");
            }
        }
    };
    </script>
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

