<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <meta name="description" content="%%description%%"/>
    <meta name="keywords" content="%%keywords%%"/>
    <meta name="robots" content="index, follow"/>

    <!-- Title -->
    <title>%%additionalTitle%%%%title%% | Kajona</title>

    <!-- IMPORTANT FOR SEO! Include canonicalUrl to tell search engines the correct URL handling -->
    <link rel="canonical" href="%%canonicalUrl%%"/>

    <!-- Template specific stylesheets: CSS and fonts -->
    <link rel="stylesheet" href="_webpath_/templates/default/css/bootstrap.min.css?_system_browser_cachebuster_" type="text/css"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon"/>


    <!-- IMPORTANT! Include the kajona_head!! This injects jQuery, too-->
    %%kajona_head%%

    <style type="text/css">
        /* minor tweaks to the boostrap 4 vanilla styles */
        /* fix the padding of navigational elements */
        .nav-link {
            padding: 0.1em;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-toggleable-md navbar-light bg-faded">
    <a class="navbar-brand" href="_webpath_">Kajona CMS</a>
    %%masterportalnavi_navigation%%
    %%mastersearch_search%%
    %%masterlanguageswitch_languageswitch%%
</nav>


%%masterpathnavi_navigation%%
<div class="container">

    <div class="row">
        <div class="col-sm-3"><div>%%mastermainnavi_navigation%%</div><div>%%mastertopnews_news%%</div></div>
        <div class="col-sm-9">


            <kajona-blocks kajona-name="Headline">

                <kajona-block kajona-name="Headline">
                    <div class="page-header">
                        <h1>%%headline_plaintext%%</h1>
                    </div>
                </kajona-block>

            </kajona-blocks>


            <kajona-blocks kajona-name="Page Intro">

                <kajona-block kajona-name="Header and Text">
                    <h3>%%headline_plaintext%%</h3>
                    <p>%%content_richtext%%</p>
                </kajona-block>

                <kajona-block kajona-name="Two Columns Header and Text">

                    <div class="row">
                        <div class="col-sm-6">
                            <h3>%%headlineleft_plaintext%%</h3>
                            <p>%%contentleft_richtext%%</p>
                        </div>

                        <div class="col-sm-6">
                            <h3>%%headlineright_plaintext%%</h3>
                            <p>%%contentright_richtext%%</p>
                        </div>
                    </div>

                </kajona-block>


                <kajona-block kajona-name="Two Columns Large Text and Image">

                    <div class="row">
                        <div class="col-sm-9">
                            <h3>%%headlineleft_plaintext%%</h3>
                            <p>%%contentleft_richtext%%</p>
                        </div>

                        <div class="col-sm-3">
                            <img src="[img,%%imageright_imagesrc%%,300,400]" />
                        </div>
                    </div>

                </kajona-block>


                <kajona-block kajona-name="Three Columns Text and Image">

                    <div class="row">
                        <div class="col-sm-4">
                            <h3>%%headlineleft_plaintext%%</h3>
                            <p>%%contentleft_richtext%%</p>
                        </div>

                        <div class="col-sm-4">
                            <h3>%%headlinecenter_plaintext%%</h3>
                            <p>%%contentcenter_richtext%%</p>
                        </div>

                        <div class="col-sm-4">
                            <img src="[img,%%imageright_imagesrc%%,300,400]" />
                        </div>
                    </div>

                </kajona-block>


                <kajona-block kajona-name="Text Only">
                    <p>%%content_richtext%%</p>
                </kajona-block>

            </kajona-blocks>

            <kajona-blocks kajona-name="Special Content">

                <kajona-block kajona-name="News">
                    <div class="row">
                        <div class="col-sm-12">
                            %%news_news%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Guestbook">
                    <div class="row">
                        <div class="col-sm-12">
                            %%guestbook_guestbook%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Downloads">
                    <div class="row">
                        <div class="col-sm-12">
                            %%downloads_downloads%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Gallery">
                    <div class="row">
                        <div class="col-sm-12">
                            %%gallery_gallery%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Form">
                    <div class="row">
                        <div class="col-sm-12">
                            %%contact_form%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Maps">
                    <div class="row">
                        <div class="col-sm-12">
                            %%maps_maps%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Search">
                    <div class="row">
                        <div class="col-sm-12">
                            %%search_search%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Sitemap">
                    <div class="row">
                        <div class="col-sm-12">
                            %%sitemap_navigation%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Faqs">
                    <div class="row">
                        <div class="col-sm-12">
                            %%faqs_faqs%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Votings">
                    <div class="row">
                        <div class="col-sm-12">
                            %%votings_votings%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Feed">
                    <div class="row">
                        <div class="col-sm-12">
                            %%feed_rssfeed%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Portallogin">
                    <div class="row">
                        <div class="col-sm-12">
                            %%login_portallogin%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Portalregistration">
                    <div class="row">
                        <div class="col-sm-12">
                            %%register_portalregistration%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Portalupload">
                    <div class="row">
                        <div class="col-sm-12">
                            %%upload_portalupload%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Eventmanager">
                    <div class="row">
                        <div class="col-sm-12">
                            %%events_eventmanager%%
                        </div>
                    </div>
                </kajona-block>


            </kajona-blocks>

            <kajona-blocks kajona-name="Footer Area">

                <kajona-block kajona-name="Postacomment">
                    <div class="row">
                        <div class="col-sm-12">
                            %%postacomment_postacomment%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="TagTo and Lastmodified">
                    <div class="row">
                        <div class="col-sm-6">
                            %%changed_lastmodified%%
                        </div>

                        <div class="col-sm-6">
                            %%tagto_tagto%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Footer Text">
                    <p>%%footer_richtext%%</p>
                </kajona-block>

            </kajona-blocks>

        </div>

    </div>





    <p class="text-xs-center">%%copyright%%</p>

</div>

<script src="_webpath_/templates/default/js/tether.min.js?_system_browser_cachebuster_"></script>
<script src="_webpath_/templates/default/js/bootstrap.min.js?_system_browser_cachebuster_"></script>

<script type="text/javascript">
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

</script>
</body>
</html>