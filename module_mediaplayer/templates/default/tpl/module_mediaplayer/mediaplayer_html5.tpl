<!-- This element uses the JW Player from Longtail - please check their license before using -->
<!-- available placeholders: systemid, file, preview, width, height -->
<mediaplayer>  
    <video src="%%file%%" poster="%%preview%%" width="%%width%%" height="%%height%%" id="mp_%%systemid%%" controls></video>
    
    <script type="text/javascript">
        KAJONA.portal.loader.load(["/templates/default/js/jwplayer/jwplayer.js"], function() {
            jwplayer("mp_%%systemid%%").setup({
                flashplayer: "_webpath_/templates/default/js/jwplayer/player.swf"
            });
        } );
    </script>
</mediaplayer>