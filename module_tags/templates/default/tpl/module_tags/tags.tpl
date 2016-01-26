
<!-- available placeholders: tags -->
<tags>
    <div class="tags">
        %%tags%%
    </div>
</tags>


<!-- available placeholders: tagname, linkcount, tagid, taglinks -->
<tagname>
    <div class="tagname"><a href="javascript:KAJONA.util.fold('tag_%%tagid%%');">%%tagname%% (%%linkcount%%)</a></div>
    <div id="tag_%%tagid%%" style="display: none;">
        <ul>
        %%taglinks%%
        </ul>
    </div>
</tagname>

<!-- available placeholders: taglink -->
<taglink>
    <li>%%taglink%%</li>
</taglink>

