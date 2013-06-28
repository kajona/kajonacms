<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>%%additionalTitle%%%%title%% | Kajona</title>
    <meta name="description" content="%%description%%" />
    <meta name="keywords" content="%%keywords%%" />
    <meta name="viewport" content="width=device-width" />
    <meta name="robots" content="index, follow" />

    <link rel="canonical" href="%%canonicalUrl%%" />
    <link rel="stylesheet" href="_webpath_/templates/default/css/normalize.css?_system_browser_cachebuster_" />
    <link rel="stylesheet" href="_webpath_/templates/default/css/main.css?_system_browser_cachebuster_" />

    <link href="_webpath_/templates/default/css/styles.css?_system_browser_cachebuster_" rel="stylesheet" type="text/css" />

    %%kajona_head%%
    <script src="_webpath_/templates/default/js/modernizr-2.6.2.min.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/templates/default/js/jquery.easing-sooper.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/templates/default/js/jquery.sooperfish.min.js?_system_browser_cachebuster_"></script>
    <link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon" />
</head>
<body>
<!--[if lt IE 7]>
<p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
<![endif]-->

<div id="contentWrapper">

    <header>
        <div id="headerLogo"><a href="_webpath_"><img src="_webpath_/templates/default/pics/default/logo.jpg" /></a></div>
    </header>

    <section>
        <div>
            <div class="topNavi">%%mastermainnavi_navigation%%</div>
            <div class="topSearch">%%mastersearch_search%%</div>
            <div class="clearer"></div>
        </div>
        <div id="headerImage"></div>
        %%masterpathnavi_navigation%%

        <div>
            <div class="contentLeft">
                %%mastertopnews_news%%
                <div><a href="_webpath_/xml.php?module=news&amp;action=newsFeed&amp;feedTitle=kajona_news" ><img src="_webpath_/templates/default/pics/default/rss.png" /></a></div>
            </div>
            <div class="contentRight">
                <!-- Please note that the following list is only for demo-purposes.
                When using the template for "real" installations, the list of
                placeholders should be stripped down to a minimum. -->
                %%headline_row%%
                %%text_paragraph%%
                %%picture1_image%%
                %%news_news%%
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
            <div class="clearfix"></div>
        </div>






    </section>
    <footer>
        <div class="portalnavi">%%masterportalnavi_navigation%%%%masterlanguageswitch_languageswitch%%</div>
        <div class="copyright">%%copyright%%</div>
        <div class="clearfix"></div>
    </footer>

    <script type="text/javascript">
    $(function() {
        $('ul.mainnavul').sooperfish();
    });
    </script>
</div>



</body>
</html>