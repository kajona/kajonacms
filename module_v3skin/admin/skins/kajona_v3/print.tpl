<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" href="_skinwebpath_/styles.css?_system_browser_cachebuster_" type="text/css" />
    <script type="text/javascript" src="_webpath_/core/module_system/admin/scripts/jquery/jquery.min.js?_system_browser_cachebuster_"></script>
    %%head%%
	<script type="text/javascript" src="_webpath_/admin/scripts/kajona.js?_system_browser_cachebuster_"></script>
    <script type="text/javascript" src="_skinwebpath_/js/v3skin.js?_system_browser_cachebuster_"></script>
	<title>Kajona³ admin [%%webpathTitle%%]</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="generator" content="Kajona³, www.kajona.de" />
	<link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon" />
</head>
<body style="background-image: none">

<table cellspacing="0" cellpadding="0">
	<tbody>


		<tr>

			<td id="contentMain" style="">
				<h1>%%moduletitle%%</h1>
				<div id="contentBox">
					%%content%%
				</div>
			</td>
		</tr>

		<tr>
			<td  id="copyright">&copy; 2012 <a href="http://www.kajona.de" target="_blank" title="Kajona³ CMS - empowering your content">Kajona³</a></td>
		</tr>
	</tbody>
</table>

<div id="jsStatusBox" style="display: none; position: absolute;"><div class="jsStatusBoxHeader">Status-Info</div><div id="jsStatusBoxContent" class="jsStatusBoxContent"></div></div>


<div class="folderviewDialog" id="folderviewDialog">
    <div class="hd"><span id="folderviewDialog_title">BROWSER</span><div class="close"><a href="#" onclick="KAJONA.admin.folderview.dialog.hide(); KAJONA.admin.folderview.dialog.setContentRaw(''); return false;">X</a></div></div>
    <div class="bd" id="folderviewDialog_content">
        <!-- filled by js -->
    </div>
</div>

<script type="text/javascript">
    KAJONA.admin.loader.loadFile("_skinwebpath_/js/kajona_dialog.js", function() {
        KAJONA.admin.folderview.dialog = new KAJONA.admin.ModalDialog('folderviewDialog', 0, true, true);
    }, true);


</script>
</body>
</html>