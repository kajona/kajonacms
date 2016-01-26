<!-- available placeholders: systemid, file, width, height -->
<flash>
   <script type="text/javascript">
        KAJONA.portal.loader.loadFile(["/templates/default/js/jquery.flash.js"], function() {

            $('#flash_%%systemid%%').flash({
                src: '%%file%%',
                width: %%width%%,
                height: %%height%%
            });
        } );
    </script>
    <div id="flash_%%systemid%%" style="width: %%width%%px; height: %%height%%px"><a href="http://www.adobe.com/go/getflashplayer" target="_blank">Get Adobe Flash player to view content</a></div>
</flash>