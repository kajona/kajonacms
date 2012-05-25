<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: image_src, image_title, image_width, image_height -->
<image>
	<script type="text/javascript">
	    if (YAHOO.lang.isUndefined(arrViewers)) {
	        var arrViewers = new Array();
	
	        //add viewer: all images with class "photoViewer" in the div with the id "contentContainer"
	        arrViewers.push("contentContainer");
	
	        YAHOO.util.Event.onDOMReady(function () {
	            YAHOO.namespace("YAHOO.photoViewer");
	            YAHOO.photoViewer.config = { viewers: {} };
	
	            //init all viewers
	            for (var i=0; i<arrViewers.length; i++) {
	                YAHOO.photoViewer.config.viewers[arrViewers[i]] = {
	                    properties: {
	                        id: arrViewers[i],
	                        grow: 0.2,
	                        fade: 0.2,
	                        modal: true,
	                        dragable: false,
	                        fixedcenter: true,
	                        loadFrom: "html",
	                        position: "absolute",
	                        buttonText: {
	                            next: " ",
	                            prev: " ",
	                            close: "X"
	                        },
	                        /* remove/rename the slideShow property to disable slideshow feature */
	                        slideShow: {
	                            autoStart: false,
	                            duration: 3500,
	                            controlsText: {
	                                play: " ",
	                                pause: " ",
	                                stop: " ",
	                                display: "{0}/{1}"
	                            }
	                        }
	                    }
	                };
	            }
	        });
	
	        KAJONA.portal.loader.load(
	            ["dragdrop", "animation", "container"],
	            [KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base.js",
	             KAJONA_WEBPATH+"/portal/scripts/photoviewer/assets/skins/kajona/kajona.css"]
	        );
	    }
	</script>
	
	<div class="imagelightbox">
	    <a href="[img,%%image_src%%,800,800]" class="photoViewer" title="%%image_title%%">
			<img src="[img,%%image_src%%,200,200]" alt="%%image_title%%" />
	    </a>
	</div>
</image>

<image_link>
	<script type="text/javascript">
	    if (YAHOO.lang.isUndefined(arrViewers)) {
	        var arrViewers = new Array();

	        //add viewer: all images with class "photoViewer" in the div with the id "contentContainer"
	        arrViewers.push("contentContainer");

	        YAHOO.util.Event.onDOMReady(function () {
	            YAHOO.namespace("YAHOO.photoViewer");
	            YAHOO.photoViewer.config = { viewers: {} };

	            //init all viewers
	            for (var i=0; i<arrViewers.length; i++) {
	                YAHOO.photoViewer.config.viewers[arrViewers[i]] = {
	                    properties: {
	                        id: arrViewers[i],
	                        grow: 0.2,
	                        fade: 0.2,
	                        modal: true,
	                        dragable: false,
	                        fixedcenter: true,
	                        loadFrom: "html",
	                        position: "absolute",
	                        buttonText: {
	                            next: " ",
	                            prev: " ",
	                            close: "X"
	                        },
	                        /* remove/rename the slideShow property to disable slideshow feature */
	                        slideShow: {
	                            autoStart: false,
	                            duration: 3500,
	                            controlsText: {
	                                play: " ",
	                                pause: " ",
	                                stop: " ",
	                                display: "{0}/{1}"
	                            }
	                        }
	                    }
	                };
	            }
	        });

	        KAJONA.portal.loader.load(
	            ["dragdrop", "animation", "container"],
	            [KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base.js",
	             KAJONA_WEBPATH+"/portal/scripts/photoviewer/assets/skins/kajona/kajona.css"]
	        );
	    }
	</script>

	<div class="imagelightbox">
	    <a href="[img,%%image_src%%,800,800]" class="photoViewer" title="%%image_title%%">
			<img src="[img,%%image_src%%,200,200]" alt="%%image_title%%" />
	    </a>
	</div>
</image_link>