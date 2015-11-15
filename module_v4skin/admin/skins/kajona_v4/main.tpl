<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kajona admin [%%webpathTitle%%]</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="generator" content="Kajona, www.kajona.de" />

    <link rel="stylesheet" href="_webpath_/core/module_system/admin/scripts/jqueryui/css/smoothness/jquery-ui.custom.css?_system_browser_cachebuster_" type="text/css" />

    <!-- KAJONA_BUILD_LESS_START -->
    <link href="_skinwebpath_/less/bootstrap.less?_system_browser_cachebuster_" rel="stylesheet/less">
    <script> less = { env:'development' }; </script>
    <script src="_skinwebpath_/less/less.min.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->

    <script src="_webpath_/core/module_system/admin/scripts/jquery/jquery.min.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js?_system_browser_cachebuster_"></script>
    %%head%%
    <script src="_webpath_/core/module_system/system/scripts/loader.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/core/module_system/admin/scripts/kajona.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/core/module_system/admin/scripts/lang.js?_system_browser_cachebuster_"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="_skinwebpath_/js/html5.js?_system_browser_cachebuster_"></script>
    <![endif]-->

    <link rel="shortcut icon" href="_skinwebpath_/img/favicon.png">
</head>

<body>

<div class="navbar-nav navbar-fixed-top hidden-print">
    <div class="navbar-topbar">
        <div class="container-fluid">
            <div class="row">
                <div class="sidebar-menu col-xs-1 col-sm-1 hidden-md hidden-lg">
                    <button type="button" class="" data-toggle="offcanvas"><i class="fa fa-bars"></i></button>
                </div>
                <div class="col-md-4 col-sm-2 col-xs-4">
                    %%login%%
                </div>
                <div class="col-md-3 col-sm-3 hidden-xs" style="text-align: right;">
                    <form class="navbar-search pull-left" action="_indexpath_">
                        <input type="hidden" name="admin" value="1" >
                        <input type="hidden" name="module" value="search">
                        <input type="hidden" name="action" value="search">

                        <div class="input-group">
                            <input type="text" name="search_query" class="form-control search-query" placeholder="[lang,globalSearchPlaceholder,dashboard]" id="globalSearchInput">
                            <span class="input-group-addon"><i class="fa fa-search" aria-hidden="true"></i></span>
                        </div>
                    </form>

                </div>
                <div class="col-md-5 col-sm-6 col-xs-7 navbar-dropdown-section pull-right">
                    <span>%%languageswitch%%</span>
                    <span class="">%%aspectChooser%%</span>
                    <a id="portaleditor" class="btn btn-default hidden-xs" href="_webpath_">
                        Portal
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid pathNaviContainer">
        <div class="row row row-offcanvas row-offcanvas-left">
            <div class="col-md-2 hidden-xs hidden-sm" style="z-index: 0 !important;">&nbsp;</div>
            <div class="col-md-10">
                %%path%%
            </div>
        </div>
    </div>
</div>

<div class="container-fluid main-container">
    <div class="row row-offcanvas row-offcanvas-left">

        <!-- MODULE NAVIGATION -->
        <div class="col-md-2 hidden-print mainnavi-container sidebar-offcanvas" id="sidebar">
            <div class="sidebar-nav">
                <div class="panel-group" id="moduleNavigation">
                    <div class="nav-header">Kajona V4</div>
                    %%moduleSitemap%%
                </div>
            </div>
        </div>

        <!-- CONTENT CONTAINER -->
        <div class="col-md-10" id="content">

            <div class="row contentTopbar clearfix hidden-print">
                <div class="col-md-6">
                    <h1 id="moduleTitle" class="pull-left">%%actionTitle%%</h1>%%quickhelp%%
                </div>
                <div class="col-md-6">
                    %%actiontoolbar%%
                </div>
            </div>

            %%content%%
        </div>
    </div>

    <footer>
        <p>powered by <a href="http://www.kajona.de/" target="_blank" title="Kajona - empowering your content">Kajona</a></p>
    </footer>

</div>

<!-- folderview container -->
<div class="modal fade" id="folderviewDialog" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 id="folderviewDialog_title" class="modal-title">BROWSER</h3>
            </div>
            <div class="modal-body">
                <div id="folderviewDialog_loading" class="loadingContainer loadingContainerBackground"></div>
                <div id="folderviewDialog_content"><!-- filled by js --></div>
            </div>
        </div>
    </div>
</div>

<!-- modal dialog container -->
<div class="modal fade" id="jsDialog_0">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 id="jsDialog_0_title"><!-- filled by js --></h3>
            </div>
            <div class="modal-body" id="jsDialog_0_content">
                <!-- filled by js -->
            </div>
        </div>
    </div>
</div>

<!-- confirmation dialog container -->
<div class="modal fade" id="jsDialog_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 id="jsDialog_1_title"><!-- filled by js --></h3>
            </div>
            <div class="modal-body" id="jsDialog_1_content">
                <!-- filled by js -->
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-default" data-dismiss="modal" id="jsDialog_1_cancelButton">[lang,dialog_cancelButton,system]</a>
                <a href="#" class="btn btn-default btn-primary" id="jsDialog_1_confirmButton">confirm</a>
            </div>
        </div>
    </div>
</div>

<!-- loading dialog container -->
<div class="modal fade" id="jsDialog_3">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="jsDialog_3_title">%%dialog_title%%</h3>
            </div>
            <div class="modal-body">
                <div id="dialogLoadingDiv" class="loadingContainer loadingContainerBackground"></div>
                <div id="jsDialog_3_content"><!-- filled by js --></div>
            </div>
        </div>
    </div>
</div>

<!-- raw dialog container -->
<div class="modal" id="jsDialog_2">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div id="jsDialog_2_content"><!-- filled by js --></div>
            </div>
        </div>
    </div>
</div>


<script src="_skinwebpath_/js/jquery.ui.touch-punch.min.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap.min.js?_system_browser_cachebuster_"></script>

<script type="text/javascript">var searchExtendText = '[lang,search_details,search]';</script>
<script src="_skinwebpath_/js/v4skin.js?_system_browser_cachebuster_"></script>

<script type="text/javascript">
    KAJONA.admin.loader.loadFile("_skinwebpath_/js/kajona_dialog.js", function() {
        KAJONA.admin.folderview.dialog = new KAJONA.admin.ModalDialog('folderviewDialog', 0);
        jsDialog_0 = new KAJONA.admin.ModalDialog('jsDialog_0', 0);
        jsDialog_1 = new KAJONA.admin.ModalDialog('jsDialog_1', 1);
        jsDialog_2 = new KAJONA.admin.ModalDialog('jsDialog_2', 2);
        jsDialog_3 = new KAJONA.admin.ModalDialog('jsDialog_3', 3);
    }, true);
</script>

<div id="jsStatusBox" class="" style="display: none; position: absolute;"><div class="jsStatusBoxHeader">Status-Info</div><div id="jsStatusBoxContent" class="jsStatusBoxContent"></div></div>

</body>
</html>