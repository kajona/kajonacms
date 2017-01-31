<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kajona admin [%%webpathTitle%%]</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="generator" content="Kajona, www.kajona.de" />

    <link rel="stylesheet" href="_webpath_/[webpath,module_system]/scripts/jqueryui/css/smoothness/jquery-ui.custom.css?_system_browser_cachebuster_" type="text/css" />
    <link rel="stylesheet" href="_webpath_/[webpath,module_system]/scripts/qtip2/jquery.qtip.min.css?_system_browser_cachebuster_" type="text/css" />

    <!-- KAJONA_BUILD_LESS_START -->
    <link href="_webpath_/[webpath,module_v4skin]/admin/skins/kajona_v4/less/bootstrap.less?_system_browser_cachebuster_" rel="stylesheet/less">
    <!--<script> less = { env:'development' }; </script>-->
    <script src="_webpath_/[webpath,module_v4skin]/admin/skins/kajona_v4/less/less.min.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->

    <script src="_webpath_/[webpath,module_system]/scripts/jquery/jquery.min.js?_system_browser_cachebuster_"></script>
    %%head%%
    <script src="_webpath_/[webpath,module_system]/scripts/requirejs/require.js?_system_browser_cachebuster_"></script>
    <script type="text/javascript">
        require(['app'], function() {});
    </script>

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
                    <!--[if lt IE 9]>
                    <div class="alert alert-danger">
                        You are using an outdated version of Internet Explorer. Please use a modern webbrowser like Mozilla Firefox or Google Chrome, or upgrade your Internet Explorer installation to access this application.<br /><br />
                        Sie verwenden eine veraltete Version des Internet Explorers. Bitte verwenden Sie einen modernen Webbrowser wie Mozilla Firefox oder Google Chrome oder aktualisieren Sie Ihre Internet Explorer Installation um auf diese Anwendung zuzugreifen.
                    </div>
                    <![endif]-->
                    <!--[if lt IE 9]><style type="text/css"> #loginContainer_content {display: none;} </style><![endif]-->
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