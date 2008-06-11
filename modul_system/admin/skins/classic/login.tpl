<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Kajona Admin, www.kajona.de</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="SHORTCUT ICON" href="_webpath_/favicon.ico">
<link href="_skinwebpath_/css.php" rel="stylesheet" type="text/css">
<script language="Javascript" type="text/javascript" src="_webpath_/admin/scripts/kajona.js"></script>
<script language="Javascript" type="text/javascript" src="_webpath_/admin/scripts/yui/yahoo-dom-event/yahoo-dom-event.js"></script>
<script language="Javascript" type="text/javascript">
	function loginStartup() { document.getElementById('name').focus(); }
	addLoadEvent(loginStartup);
	kajonaAjaxHelper.loadAjaxBase();
	kajonaAjaxHelper.loadAutocompleteBase();
	kajonaAjaxHelper.loadAnimationBase()
	kajonaAjaxHelper.loadDragNDropBase();
</script>
</head>

<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr bgcolor="#FFFFFF">
    <td>
		<table width="100%"  border="0" cellspacing="0" cellpadding="0" style="background-image: url(_skinwebpath_/header.png)">
	 	 <tr>
			<td><img src="_skinwebpath_/trans.gif" width="18" height="80"></td>
	     </tr>
	   </table>

	</td>
  </tr>
  <tr>
    <td align="center" valign="top" class="text1"><img src="_skinwebpath_/trans.gif" width="400" height="10"><br />
        %%content%%
        <br />
    <img src="_skinwebpath_/trans.gif" width="400" height="10"></td>
  </tr>
   <tr bgcolor="#FFFFFF">
    <td height="5"></td>
  </tr>
  <tr bgcolor="#000099">
    <td height="1"></td>
  </tr>
  <tr bgcolor="#FFFFFF">
    <td><div align="center" class="text1">&copy; www.kajona.de | _gentime_ | <a href="http://board.openkaktena.de/index.php?c=7" target="_blank">Support</a></div></td>
  </tr>
</table>
</body>
</html>
