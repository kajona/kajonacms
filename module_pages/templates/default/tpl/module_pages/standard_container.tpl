<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <!-- Meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="description" content="%%description%%" />
    <meta name="keywords" content="%%keywords%%" />
    <meta name="viewport" content="width=device-width" />
    <meta name="robots" content="index, follow" />

    <!-- Template specific stylesheets: CSS and fonts -->   
    <link rel="stylesheet" href="_webpath_/templates/default/css/normalize.css?_system_browser_cachebuster_"  type="text/css" />
    <link rel="stylesheet" href="_webpath_/templates/default/css/main.css?_system_browser_cachebuster_"  type="text/css" />
    <link rel="stylesheet" href="_webpath_/templates/default/css/styles.css?_system_browser_cachebuster_" type="text/css" />
    <link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon" />
    
    <!-- IMPORTANT FOR SEO! Include canonicalUrl to tell search engines the correct URL handling -->
    <link rel="canonical" href="%%canonicalUrl%%" />

    <!-- IMPORTANT! Include the kajona_head!! -->    
    %%kajona_head%%

    <!-- Javascript -->
    <script src="_webpath_/templates/default/js/modernizr-2.6.2.min.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/templates/default/js/jquery.easing-sooper.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/templates/default/js/jquery.sooperfish.min.js?_system_browser_cachebuster_"></script>
    
    <!-- Title -->
    <title>%%additionalTitle%%%%title%% | Kajona</title>
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

                <kajona-blocks kajona-name="content">

                    <kajona-block kajona-name="Row light" kajona-name-de="Zeile hell">
                        <div class="row-light">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                            %%date_date%%
                        </div>
                    </kajona-block>

                    <kajona-block kajona-name="Row dark" kajona-name-de="Zeile dunkel">
                        <div class="row-dark">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                        </div>
                    </kajona-block>

                    <kajona-block kajona-name="Row 3 column" kajona-name-de="Zeile 3-spaltig">
                        <div class="row-3column">
                            <div>%%column1_richtext%%</div>
                            <div>%%column2_richtext%%</div>
                            <div>%%column3_richtext%%</div>
                        </div>
                    </kajona-block>

                </kajona-blocks>


                <kajona-blocks kajona-name="2ndcontent">

                    <kajona-block kajona-name="Row light" kajona-name-de="Zeile hell">
                        <div class="row-light">
                            %%date_date%%
                        </div>
                    </kajona-block>

                    <kajona-block kajona-name="Row dark" kajona-name-de="Zeile dunkel">
                        <div class="row-dark">
                            %%content_richtext%%
                        </div>
                    </kajona-block>

                </kajona-blocks>

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