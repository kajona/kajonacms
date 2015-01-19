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

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="_skinwebpath_/js/html5.js?_system_browser_cachebuster_"></script>
    <![endif]-->

    <link rel="shortcut icon" href="_skinwebpath_/img/favicon.png">
</head>

<body class="dialogBody">


<div class="container-fluid">
    <div class="row">

        <!-- CONTENT CONTAINER -->
        <div id="content" class="col-md-12">
            %%content%%
        </div>
    </div>
</div>



<script src="_skinwebpath_/js/jquery.ui.touch-punch.min.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/transition.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/alert.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/modal.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/dropdown.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/scrollspy.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/tab.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/tooltip.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/popover.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/button.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/collapse.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/carousel.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/affix.js?_system_browser_cachebuster_"></script>

<script src="_skinwebpath_/js/v4skin.js?_system_browser_cachebuster_"></script>


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


<script type="text/javascript">
    KAJONA.admin.loader.loadFile("_skinwebpath_/js/kajona_dialog.js", function() {
        KAJONA.admin.folderview.dialog = new KAJONA.admin.ModalDialog('folderviewDialog', 0, true, true);
    }, true);
</script>

<div id="jsStatusBox" class="" style="display: none; position: absolute;"><div class="jsStatusBoxHeader">Status-Info</div><div id="jsStatusBoxContent" class="jsStatusBoxContent"></div></div>

</body>
</html>