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
<span class="inline-rating-bar">
<ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="htmlTooltip(this, '%%rating_bar_title%%');">
	<li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
	%%rating_icons%%
</ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span>
</rating_bar>

<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="htmlTooltip(this, '%%rating_icon_title%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>
