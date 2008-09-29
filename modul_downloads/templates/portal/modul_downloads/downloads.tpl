<list>
<script type="text/javascript">
kajonaAjaxHelper.loadAjaxBase();
</script>
<p>%%pathnavigation%%</p>
<p>%%folderlist%%</p>
<p>%%filelist%%</p>
</list>

<folder>
<table width="100%"  border="0" cellspacing="0" cellpadding="0" class="portalList">
  <tr class="portalListRow1">
    <td class="image"><img src="_webpath_/portal/pics/kajona/icon_folderClosed.gif" /></td>
    <td class="title"><a href="%%folder_href%%">%%folder_name%%</a></td>
    <td class="actions">%%folder_link%%</td>
  </tr>
  <tr class="portalListRow2">
    <td></td>
    <td colspan="3" class="description">%%folder_description%%</td>
  </tr>
</table>
</folder>

<file>
<table width="100%"  border="0" cellspacing="0" cellpadding="0" class="portalList">
  <tr class="portalListRow1">
    <td class="image"><img src="_webpath_/portal/pics/kajona/icon_downloads.gif" /></td>
    <td class="title">%%file_name%%</td>
    <td class="center">%%file_size%%</td>
    <td class="actions">%%file_link%%</td>
    <td class="rating">%%file_rating%%</td>
  </tr>
  <tr class="portalListRow2">
    <td></td>
    <td colspan="4" class="description">%%file_description%%</td>
  </tr>
</table>
</file>

<pathnavi_entry>
%%path_level%% >
</pathnavi_entry>

<rating_bar>
<script type="text/javascript">
<!--
kajonaAjaxHelper.addJavascriptFile("_webpath_/portal/scripts/rating.js");
//-->
</script>
%%rating_icons%% <span id="rating_rating_%%system_id%%">%%rating_rating%%</span>
</rating_bar>

<rating_icon_filled><a href="#" onclick="%%rating_icon_onclick%%"><img src="_webpath_/portal/pics/kajona/rating_filled.png" id="%%rating_icon_id%%" onmouseover="%%rating_icon_mouseover%%" onmouseout="%%rating_icon_mouseout%%" title="" /></a></rating_icon_filled>

<rating_icon_empty><a href="#" onclick="%%rating_icon_onclick%%"><img src="_webpath_/portal/pics/kajona/rating_empty.png" id="%%rating_icon_id%%" onmouseover="%%rating_icon_mouseover%%" onmouseout="%%rating_icon_mouseout%%" title="" /></a></rating_icon_empty>