<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: image_src, image_title, image_width, image_height -->
<image>
    <script type="text/javascript" src="_webpath_/templates/default/js/lightbox/jquery.lightbox-0.5.min.js"></script>
    <link rel="stylesheet" type="text/css" href="_webpath_/templates/default/js/lightbox/css/jquery.lightbox-0.5.css" />

    <script type="text/javascript">
        $(function() {
            // Use this example, or...
            $('a.photoViewer').lightBox();

        });
    </script>
	
	<div class="imagelightbox">
	    <a href="[img,%%image_src%%,800,800]" class="photoViewer" title="%%image_title%%">
			<img src="[img,%%image_src%%,200,200]" alt="%%image_title%%" />
	    </a>
	</div>
</image>

<image_link>
    <script type="text/javascript" src="_webpath_/templates/default/js/lightbox/jquery.lightbox-0.5.min.js"></script>
    <link rel="stylesheet" type="text/css" href="_webpath_/templates/default/js/lightbox/css/jquery.lightbox-0.5.css" />

    <script type="text/javascript">
        $(function() {
            // Use this example, or...
            $('a.photoViewer').lightBox();

        });
    </script>

	<div class="imagelightbox">
	    <a href="[img,%%image_src%%,800,800]" class="photoViewer" title="%%image_title%%">
			<img src="[img,%%image_src%%,200,200]" alt="%%image_title%%" />
	    </a>
	</div>
</image_link>