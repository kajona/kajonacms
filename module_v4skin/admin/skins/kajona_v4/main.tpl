<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kajona admin [%%webpathTitle%%]</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="generator" content="Kajona, www.kajona.de" />

    <!-- KAJONA_BUILD_LESS_START -->
    <link href="_skinwebpath_/less/bootstrap.less?_system_browser_cachebuster_" rel="stylesheet/less">
    <link href="_skinwebpath_/less/responsive.less?_system_browser_cachebuster_" rel="stylesheet/less">
    <script> less = { env:'development' }; </script>
    <script src="_skinwebpath_/less/less.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->

    <script src="_webpath_/core/module_system/admin/scripts/jquery/jquery.min.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js?_system_browser_cachebuster_"></script>
    %%head%%
    <script src="_webpath_/core/module_system/system/scripts/loader.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/core/module_system/admin/scripts/kajona.js?_system_browser_cachebuster_"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="_skinwebpath_/js/html5.js?_system_browser_cachebuster_"></script>
    <![endif]-->

    <link rel="shortcut icon" href="_skinwebpath_/img/favicon.png">
    <!--
    <link rel="apple-touch-icon" href="_skinwebpath_/img/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="_skinwebpath_/img/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="_skinwebpath_/img/apple-touch-icon-114x114.png">
    -->
</head>

<body>

<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span4" style="padding:5px 0 0 10px;">
                    %%login%%
                </div>
                <div class="span8" style="text-align: right;">
                    <form class="navbar-search pull-left" action="">
                        <input type="hidden" name="admin" value="1" >
                        <input type="hidden" name="module" value="search">
                        <input type="hidden" name="action" value="search">
                        <i id="icon-lupe" class="icon-search"></i>
                        <input type="text" name="search_query" class="search-query" placeholder="[lang,globalSearchPlaceholder,dashboard]" id="globalSearchInput">
                    </form>
                    %%languageswitch%%

                    %%aspectChooser%%
                    <a id="portaleditor" class="btn" href="_webpath_">
                        Portal
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid pathNaviContainer">
        <div class="row-fluid">
            <div class="span2 hidden-phone hidden-tablet" style="z-index: 0 !important;">&nbsp;</div>
            <div class="span10">
                %%path%%
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row-fluid">

        <!-- MODULE NAVIGATION -->
        <div class="span2 ">
            <div class="sidebar-nav hidden-phone hidden-tablet ">
                <div class="accordion" id="moduleNavigation">
                    %%moduleSitemap%%
                </div>
            </div>
        </div>

        <!-- CONTENT CONTAINER -->
        <div class="span10" id="content">

            <div class="contentTopbar clearfix">
                <h1 id="moduleTitle" class="pull-left">%%actionTitle%%</h1>%%quickhelp%%
                %%actiontoolbar%%
            </div>

            %%content%%
        </div>
    </div>

    <footer>
        <p>powered by <a href="http://www.kajona.de/" target="_blank" title="Kajona - empowering your content">Kajona</a></p>
    </footer>

</div>

<script src="_skinwebpath_/js/jquery.ui.touch-punch.min.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-transition.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-alert.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-modal.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-dropdown.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-scrollspy.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-tab.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-tooltip.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-popover.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-button.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-collapse.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-carousel.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-affix.js?_system_browser_cachebuster_"></script>

<script src="_skinwebpath_/js/v4skin.js?_system_browser_cachebuster_"></script>


<div class="modal hide fade fullsize" id="folderviewDialog" role="dialog">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3 id="folderviewDialog_title">BROWSER</h3>
    </div>
    <div class="modal-body">
        <div id="folderviewDialog_loading" class="loadingContainer loadingContainerBackground"></div>
        <div id="folderviewDialog_content"><!-- filled by js --></div>
    </div>
</div>


<script type="text/javascript">
    KAJONA.admin.loader.loadFile("_skinwebpath_/js/kajona_dialog.js", function() {
        KAJONA.admin.folderview.dialog = new KAJONA.admin.ModalDialog('folderviewDialog', 0, true, true);
    }, true);
</script>

<div id="jsStatusBox" class="" style="display: none; position: absolute;"><div class="jsStatusBoxHeader">Status-Info</div><div id="jsStatusBoxContent" class="jsStatusBoxContent"></div></div>

</body>
</html>