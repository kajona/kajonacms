<!DOCTYPE HTML>
<html>

<head>
    <title>%%additionalTitle%%%%title%% | Kajona</title>
  <meta name="description" content="website description" />
  <meta name="keywords" content="website keywords, website keywords" />
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <link href="_webpath_/templates/css3_design_two/css/style.css?_system_browser_cachebuster_" rel="stylesheet" type="text/css" />

  <!-- modernizr enables HTML5 elements and feature detects -->
  <script type="text/javascript" src="_webpath_/templates/css3_design_two/js/modernizr-1.5.min.js"></script>
  %%kajona_head%%
</head>

<body>
  <div id="main">
    <header>
      <div id="logo">
        <!-- class="logo_colour", allows you to change the colour of the text -->
        <h1><a href="_webpath_">CSS3<span class="logo_colour">design</span>_two</a></h1>
      </div>
      <nav id="nav">
          %%mastermainnavi_navigation%%
      </nav>
    </header>
    <div id="site_content">
      <div id="sidebar_container">
        <div class="sidebar">
          <h1>Latest News</h1>
            %%news_news%%
        </div>
        <div class="sidebar">
          <h1>Special Offers</h1>
          <h2>20% Discount</h2>
          <p>For the month of July 2012, we are offering a 20% discount for all new visitors.</p>
        </div>
      </div>
      <div id="content">
        <ul class="slideshow">
          <li class="show"><img width="706" height="316" src="_webpath_/templates/css3_design_two/images/1.jpg" alt="image one" /></li>
          <li><img width="706" height="316" src="_webpath_/templates/css3_design_two/images/2.jpg" alt="image two" /></li>
          <li><img width="706" height="316" src="_webpath_/templates/css3_design_two/images/3.jpg" alt="image three" /></li>
          <li><img width="706" height="316" src="_webpath_/templates/css3_design_two/images/4.jpg" alt="image four" /></li>
          <li><img width="706" height="316" src="_webpath_/templates/css3_design_two/images/5.jpg" alt="image five" /></li>
        </ul>
        <div id="content_item">

            %%headline_row%%
            %%text_paragraph%%
            %%picture1_image%%
            %%gb1_guestbook%%
            %%dl1_downloads%%
            %%bilder_gallery%%
            %%bilder2_galleryRandom%%
            %%formular_form|tellafriend%%
            %%results_search%%
            %%sitemap_navigation%%
            %%faqs_faqs%%
            %%comments_postacomment%%
            %%mixed_rssfeed|tagto|imagelightbox|portallogin|portalregistration|lastmodified|rendertext|tagcloud|downloadstoplist|textticker%%
            %%mixed2_portalupload|directorybrowser%%
            %%mixed3_flash|mediaplayer|tags|eventmanager%%
            %%list_userlist%%
            %%votings_votings%%
            %%maps_maps%%

            <div align="right">
                <div id="fb-root"></div>
                <script>(function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) {return;}
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/en_US/all.js#appId=141503865945925&xfbml=1";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>

                <div class="fb-like" data-href="https://www.facebook.com/pages/Kajona%C2%B3/156841314360532" data-send="false" data-layout="button_count" data-width="60" data-show-faces="false"></div>
            </div>


        </div>
      </div>
    </div>
    <footer>
        %%masterportalnavi_navigation%%
      <p>Copyright &copy; CSS3_design_two | Photos by <a href="http://www.fotogrph.com">Fotogrph</a> | <a href="http://www.css3templates.co.uk">design from css3templates.co.uk</a></p>
    </footer>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
  </div>
  <!-- javascript at the bottom for fast page loading -->
  <script type="text/javascript" src="_webpath_/templates/css3_design_two/js/jquery.easing-sooper.js"></script>
  <script type="text/javascript" src="_webpath_/templates/css3_design_two/js/jquery.sooperfish.js"></script>
  <script type="text/javascript" src="_webpath_/templates/css3_design_two/js/image_fade.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('#nav > ul').sooperfish();
    });
  </script>
</body>
</html>
