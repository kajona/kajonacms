<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kajona admin [%%webpathTitle%%]</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="generator" content="Kajona, www.kajona.de" />

    <link rel="stylesheet" href="_webpath_/[webpath,module_system]/admin/scripts/jqueryui/css/smoothness/jquery-ui.custom.css?_system_browser_cachebuster_" type="text/css" />
    <link rel="stylesheet" href="_webpath_/[webpath,module_system]/admin/scripts/qtip2/jquery.qtip.min.css?_system_browser_cachebuster_" type="text/css" />

    <!-- KAJONA_BUILD_LESS_START -->
    <link href="_webpath_/[webpath,module_v4skin]/admin/skins/kajona_v4/less/bootstrap.less?_system_browser_cachebuster_" rel="stylesheet/less">
    <script> less = { env:'development' }; </script>
    <script src="_webpath_/[webpath,module_v4skin]/admin/skins/kajona_v4/less/less.min.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->

    %%head%%

    <!-- BC layer while moving to requirejs -->
    <script src="_webpath_/[webpath,module_system]/admin/scripts/jquery/jquery.min.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/[webpath,module_system]/admin/scripts/jqueryui/jquery-ui.custom.min.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/[webpath,module_system]/system/scripts/loader.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/[webpath,module_system]/admin/scripts/kajona.js?_system_browser_cachebuster_"></script>
    <!-- BC layer -->

    <script type="text/javascript">var searchExtendText = '[lang,search_details,search]';</script>
    <script type="text/javascript">
        var require = %%requirejs_conf%%;

        // BC layer so that we fire document ready events only after requirejs has loaded all js files
        $.holdReady(true);
    </script>
    <script data-main="core/module_system/admin/scripts/app" src="_webpath_/[webpath,module_system]/admin/scripts/requirejs/require.js"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="_webpath_/[webpath,module_v4skin]/admin/skins/kajona_v4/js/html5.js?_system_browser_cachebuster_"></script>
    <![endif]-->

    <link rel="shortcut icon" href="_webpath_/[webpath,module_v4skin]/admin/skins/kajona_v4/img/favicon.png">
</head>

<body class="login">

<div class="container-fluid">
    <div class="row">

        <div class="col-md-5 center-block" id="content">

            <div class="panel panelDefault" id="loginContainer">
                <div class="panel-header">
                    <h3>Kajona V5</h3>
                </div>
                <div class="panel-body">
                    <div id="loginContainer_content">%%content%%</div>
                </div>
                <div class="panel-footer">
                    <a href="http://www.kajona.de" target="_blank">Kajona - empowering your content</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    document.getElementById('name').focus();
</script>

</body>
</html>