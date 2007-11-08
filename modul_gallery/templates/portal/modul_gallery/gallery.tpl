<list>
<p>%%pathnavigation%%</p>
<p>%%folderlist%%</p>
<p>%%piclist%%</p>
<p align="center">%%link_back%% %%link_pages%% %%link_forward%%</p>
</list>

<folderlist>
<table width="100%"  border="0" cellspacing="0" cellpadding="0" class="portalList">
  <tr class="portalListRow1">
    <td class="image"><img src="_webpath_/portal/pics/kajona/icon_folderClosed.gif" /></td>
    <td class="title"><a href="%%folder_href%%">%%folder_name%%</a></td>
    <td class="actions">%%folder_link%%</td>
  </tr>
  <tr class="portalListRow2">
    <td></td>
    <td colspan="2" class="description">%%folder_description%%</td>
  </tr>
</table>
</folderlist>

<!-- the following section is used, if theres a defined number of images per row.
     set the placeholders according to the number set in the admin -->
<piclist>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>&nbsp;</td>
    <td><div align="center"></div></td>
    <td><div align="center"></div></td>
    <td><div align="center"></div></td>
    <td><div align="center"></div></td>
    <td><div align="center"></div></td>
    <td><div align="center"></div></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><div align="center">%%pic_0%%</div></td>
    <td><div align="center"></div></td>
    <td><div align="center">%%pic_1%%</div></td>
    <td><div align="center"></div></td>
    <td><div align="center">%%pic_2%%</div></td>
    <td><div align="center"></div></td>
  </tr>
  <tr>
    <td width="1%">&nbsp;</td>
    <td width="28%"><div align="center">%%name_0%%</div></td>
    <td width="3%"><div align="center"></div></td>
    <td width="34%"><div align="center">%%name_1%%</div></td>
    <td width="4%"><div align="center"></div></td>
    <td width="27%"><div align="center">%%name_2%%</div></td>
    <td width="3%"><div align="center"></div></td>
  </tr>
</table>
</piclist>

<!-- the following section is used, if theres no defined number of images per row.
     This section is called for each image -->
<piclist_unlimited>
<div>
    <div align="center">%%pic%%</div>
    <div align="center">%%name%%</div>
</div>
</piclist_unlimited>

<picdetail>
%%pathnavigation%%
<table width="85%"  border="0">
  <tr>
    <td width="20%">&nbsp;</td>
    <td width="60%"><div align="center">%%pic_name%%</div></td>
    <td width="20%">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><div align="center">%%pic_subtitle%%</div></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><div align="center">%%pic_url%%</div></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><div align="center">%%pic_description%%</div></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>%%backlink%%</td>
    <td><div align="center">%%overview%%</div></td>
    <td><div align="right">%%forwardlink%%</div></td>
  </tr>
  <tr>
    <td colspan="3" align="center"><div align="center">%%backlink_image_3%%%%backlink_image_2%%%%backlink_image_1%% | %%pic_small%% | %%forwardlink_image_1%%%%forwardlink_image_2%%%%forwardlink_image_3%%</div></td>
  </tr>
</table>
</picdetail>

<pathnavigation_level>
%%pathnavigation_point%% >
</pathnavigation_level>