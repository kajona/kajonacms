<!-- This element uses the JW Player from Longtail - please check their license before using -->
<!-- available placeholders: systemid, file, preview, width, height -->
<mediaplayer>
    <script type="text/javascript">
        KAJONA.portal.loader.loadFile(["/templates/default/js/jquery.flash.js"], function() {

            $('#mp_%%systemid%%').flash({
                src: '_webpath_/templates/default/js/jwplayer/player.swf',
                width: %%width%%,
                height: %%height%%,
                flashvars: {
                    file: "%%file%%",
                        image: "%%preview%%",
                        controlbar: "over"
                }
            });
        });


    </script>
    <div id="mp_%%systemid%%" style="width: %%width%%px; height: %%height%%px"><a href="http://www.adobe.com/go/getflashplayer" target="_blank">Get Adobe Flash player to play media</a></div>
</mediaplayer>