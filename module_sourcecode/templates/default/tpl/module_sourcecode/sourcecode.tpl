<!-- see section "Template-API" of element manual for a list of available placeholders -->

<!-- available placeholders: code -->
<sourcecode>
    <script type="text/javascript">
        KAJONA.portal.loader.loadFile(["/templates/default/js/sourcecode/highlight.pack.js", "/templates/default/js/sourcecode/styles/github.css"], function() {
            hljs.configure({useBR: true});
            $('div.code').each(function(i, block) {
                hljs.highlightBlock(block);
            });
        });
    </script>
    <div class="code">%%code%%</div>
</sourcecode>