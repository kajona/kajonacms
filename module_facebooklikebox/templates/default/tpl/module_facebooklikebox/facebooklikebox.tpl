<!-- uses the external service from Facebook, check the following pages for more information:
     http://developers.facebook.com/docs/reference/plugins/like-box
     http://developers.facebook.com/docs/reference/javascript/

     DON'T FORGET TO ADD YOUR PROFILE ID!
     Feel free to adjust the code to your needs.
     The Like Box can be styled via the css file /portal/css/element_facebooklikebox.css.
     Increase the cache-buster in the CSS url to flush Facebooks cache (done via the backend).
-->
<facebooklikebox>
	<div id="fblikebox"></div>
    <script type="text/javascript">
		window.fbAsyncInit = function() {
        FB.init({xfbml: true});
		};
		      
		var portalLanguage = "%%portallanguage%%";
		var languageToLoad = "en_US";
		if (portalLanguage == "de") {
		    languageToLoad = "de_DE";
		} else if (portalLanguage == "en") {
		    languageToLoad = "en_US";
		} else if (portalLanguage == "fr") {
		    languageToLoad = "fr_FR";
		} else if (portalLanguage == "it") {
		    languageToLoad = "it_IT";
		}

        KAJONA.portal.loader.loadFile([document.location.protocol + "//connect.facebook.net/"+languageToLoad+"/all.js"], function() {
		    document.getElementById('fblikebox').innerHTML = '<fb:fan profile_id="156841314360532" height="556" width="292" connections="10" stream="true" header="false" colorscheme="light" css="_webpath_/templates/default/css/element_facebooklikebox.css?_system_browser_cachebuster_"></fb:fan>';
		}, true);
    </script>
</facebooklikebox>