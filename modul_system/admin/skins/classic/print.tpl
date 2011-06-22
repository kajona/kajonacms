<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link href="_skinwebpath_/styles.css?_system_browser_cachebuster_" rel="stylesheet" type="text/css">
<script type="text/javascript" src="_webpath_/admin/scripts/yui/yuiloader-dom-event/yuiloader-dom-event.js?_system_browser_cachebuster_"></script>
%%head%%
<script type="text/javascript" src="_webpath_/admin/scripts/kajona.js?_system_browser_cachebuster_"></script>
<title>Kajona Admin, www.kajona.de</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon" />

</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">

  <tr>
    <td width="" valign="top" class="text2_padding" style="padding-left: 2px;"></td>
    <td align="center" valign="top" class="text1"><img src="_skinwebpath_/trans.gif" width="400" height="10"><br />
        <table width="95%"  border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td><table width="100%">
                <tr>
                  <td class="text1" align="left"><br />%%content%%</td>
                </tr>
              </table></td>
          </tr>
        </table>
        <br />
    <img src="_skinwebpath_/trans.gif" width="400" height="10"> </td>
    <td width="10" align="center" valign="top"><img src="_skinwebpath_/trans.gif" width="10" height="75"></td>
  </tr>
   <tr bgcolor="#FFFFFF">
    <td colspan="4" height="5"></td>
  </tr>
  <tr bgcolor="#FFFFFF">
    <td colspan="4"><div align="center" class="text1">&copy; www.kajona.de | _gentime_ | <a href="http://board.kajona.de/" target="_blank">Support</a></div></td>
  </tr>
</table>
<div id="jsStatusBox" style="display: none; position: absolute;"><div class="jsStatusBoxHeader">Status-Info</div><div id="jsStatusBoxContent" class="jsStatusBoxContent"></div></div>

<div class="folderviewDialog" id="folderviewDialog">
    <div class="hd"><span id="folderviewDialog_title">BROWSER</span><div class="close"><a href="#" onclick="KAJONA.admin.folderview.dialog.hide(); KAJONA.admin.folderview.dialog.setContentRaw(''); return false;">X</a></div></div>
    <div class="bd" id="folderviewDialog_content">
        <!-- filled by js -->
    </div>
</div>

<script type="text/javascript">
    KAJONA.admin.loader.loadDialogBase(function() {
        KAJONA.admin.folderview.dialog = new KAJONA.admin.ModalDialog('folderviewDialog', 0, true, true);
    });
</script>
</body>
</html>