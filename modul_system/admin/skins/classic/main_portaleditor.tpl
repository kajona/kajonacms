<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link href="_skinwebpath_/styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="_webpath_/admin/scripts/yui/yuiloader-dom-event/yuiloader-dom-event.js"></script>
%%head%%
<script type="text/javascript" src="_webpath_/admin/scripts/kajona.js"></script>
<title>Kajona Admin, www.kajona.de</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function() {
    new YAHOO.util.KeyListener(document, { keys:27 }, parent.KAJONA.admin.portaleditor.closeDialog).enable();
});
</script>
</head>

<body>
<div class="imgPreload">
	<img src="_skinwebpath_/loading.gif" alt="" title="" />
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">

  <tr>
    <td align="center" valign="top" class="text1"><img src="_skinwebpath_/trans.gif" width="400" height="10"><br />
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td class="modulhead" align="left">%%moduletitle%%</td>
          </tr>
            <tr>
               <td class="modullinie"></td>
          </tr>
          <tr>
            <td></td>
          </tr>
          <tr>
            <td><table width="100%" class="listenframe">
                <tr>
                  <td class="text1" align="left">%%content%%</td>
                </tr>
              </table></td>
          </tr>
        </table>
       <br />
    <img src="_skinwebpath_/trans.gif" width="400" height="10"> </td>
    <td width="10" align="center" valign="top"><img src="_skinwebpath_/trans.gif" width="10" height="75"></td>
  </tr>
</table>

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