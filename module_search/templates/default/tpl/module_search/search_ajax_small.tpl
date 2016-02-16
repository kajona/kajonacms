<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: action, search_term -->
<search_form>
    <form name="searchResultFormSmall" method="post" action="%%action%%" accept-charset="UTF-8" class="form-inline pull-xs-right">
        <input type="text" name="searchterm" id="resultSearchtermSmall" value="%%search_term%%" class="form-control" placeholder="[lang,searchterm_label,search]"
               onkeyup="KAJONA.portal.searchSmall.queryBackend();"
                autocomplete="off" />
        <div id="searchResultSmall" class="card " style="display: none; position: absolute; right: 10px;  border: 1px solid #979797;  max-width: 400px; min-width: 200px;"></div>
    </form>

    <div id="resultSetHeaderSmall" style="display: none;" >[lang,hitlist_text1,search] <span id="spanSearchtermSmall"></span> [lang,hitlist_text2,search] <span id="spanSearchamountSmall"></span> [lang,hitlist_text3,search]:</div>


    <script type="text/javascript">
    KAJONA.portal.searchSmall =  {
        strLastQuery : "",

        queryBackend : function() {
            var strCurrentQuery = $("#resultSearchtermSmall").val().trim();
            var searchRunning = false;
            var post_target = KAJONA_WEBPATH+"/xml.php?module=search&action=doSearch";
            var post_data = {
                searchterm : strCurrentQuery
            };

            if(strCurrentQuery.length >= 2 && strCurrentQuery != KAJONA.portal.searchSmall.strLastQuery) {

                if(searchRunning)
                    return;

                $('#plainListSmall').css("display", "none");

                searchRunning = true;
                KAJONA.portal.searchSmall.strLastQuery = strCurrentQuery;
                $('#searchResultSmall').css("display", "block").html("<div class='center-block text-xs-center' style='font-size: 1.5rem;'><i class='fa fa-spinner fa-spin'></i></div>");

                $.post(post_target, post_data, function(data, textStatus) {


                    $("#spanSearchtermSmall").html($(data).find("searchterm").html());
                    $("#spanSearchamountSmall").html($(data).find("nrofresults").html());

                    $('#searchResultSmall').html("<div class='card-block'>"+$("#resultSetHeaderSmall").html()+"<ul></ul></div>");

                    $(data).find("item").each(function() {
                        var objNode = $("<li></li>");
                        objNode.append("<a href='"+$(this).find("pagelink").find("a").attr("href")+"'>"+$(this).find("pagelink").find("a").text()+"</a>" );
                        objNode.append("<br />"+$(this).find("description").text());
                        $("#searchResultSmall ul").append(objNode);
                        searchRunning = false;
                    });
                }, "xml");
            }

            if(strCurrentQuery.length < 2) {
                $('#searchResultSmall').css('display', 'none');
            }
        }
    };
    </script>
</search_form>


    <!-- available placeholders: hitlist, search_term, search_nrresults, link_back, link_overview, link_forward -->
<search_hitlist>

</search_hitlist>

<!-- available placeholders: page_link, page_description -->
<search_hitlist_hit>
</search_hitlist_hit>
