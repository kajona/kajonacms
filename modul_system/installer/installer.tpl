<installer_main>
<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Kajona³ installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="robots" content="noindex, nofollow" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta name="generator" content="Kajona³, www.kajona.de" />
	<link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="_webpath_/installer/installer.css" type="text/css" />
</head>
<body>

<div id="installerBox">
	<div class="logo"></div>
	<div class="topRight">
		<div class="topLeft">
			<div class="bottomRight">
				<div class="bottomLeft">
					<div class="content">
						<h1>Kajona Installer %%installer_version%%</h1>
						<div>
						%%installer_output%%
						</div>
						<pre>
						%%installer_logfile%%
						</pre>
						<div>
						  <div style="float: left;">%%installer_backward%%</div>
						  <div style="float: right;">%%installer_forward%%</div>
						  <div style="clear: both;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="copyright">&copy; 2008 <a href="http://www.kajona.de" target="_blank" title="Kajona³ CMS - empowering your content">Kajona³</a></div>
</div>

</body>
</html>
</installer_main>

<installer_log>
<b>%%systemlog%%</b><br />%%log_content%%
</installer_log>

<configwizard_form>
%%config_intro%%

<form action="_webpath_/installer/installer.php" method="POST">
<input type="hidden" name="write" value="true" />
<div><label for="hostname">%%config_hostname%%</label><input type="text" name="hostname" id="hostname" value="localhost" class="inputText" /></div><br />
<div><label for="username">%%config_username%%</label><input type="text" name="username" id="username" class="inputText" /></div><br />
<div><label for="password">%%config_password%%</label><input type="password" name="password" id="password" class="inputText" /></div><br />
<div><label for="dbname">%%config_dbname%%</label><input type="text" name="dbname" id="dbname" class="inputText" /></div><br />
<div><label for="dbprefix">%%config_prefix%%</label><input type="text" name="dbprefix" id="dbprefix" value="kajona_" class="inputText" /></div><br />
<div><label for="driver">%%config_driver%%</label><select name="driver" id="driver" class="inputDropdown">
                <option value="mysql" selected="selected">mysql</option>
                <option value="mysqli">mysqli</option>
                <option value="postgres">postgres</option>
             </select></div><br />
<div><label for="port">%%config_port%%</label><input type="text" name="port" id="port" class="inputText" /></div><br />
<div><label for="port"></label>%%config_portinfo%%</div><br />
<div><label for="submit"></label><input type="submit" value="%%config_save%%" class="inputSubmit" /></div><br />
</form>
</configwizard_form>



<loginwizard_form>
<form action="_webpath_/installer/installer.php?step=loginData" method="POST">
<input type="hidden" name="write" value="true" />
<div><label for="username">%%login_username%%</label><input type="text" name="username" id="username" class="inputText" /><div /><br />
<div><label for="password">%%login_password%%</label><input type="password" name="password" id="password" class="inputText" /><div /><br />
<div><label for="submit"></label><input type="submit" value="%%login_save%%" class="inputSubmit" /></div><br />
</form>
</loginwizard_form>

<installer_forward_link>
<a href="%%href%%"><b>%%text%%</b></a>
</installer_forward_link>

<installer_backward_link>
<a href="%%href%%">%%text%%</a>
</installer_backward_link>

<installer_modules_form>
	<form action="_webpath_/installer/installer.php?step=install" method="POST">
	  
	   %%module_rows%%
	   <br />
	   <div><label for="submit"></label><input type="submit" value="%%button_install%%" class="inputSubmit" /></div><br /> 
	</form>
</installer_modules_form>

<installer_samplecontent_form>
    <form action="_webpath_/installer/installer.php?step=samplecontent" method="POST">
      
       %%module_rows%%
       <br />
       <div><label for="submit"></label><input type="submit" value="%%button_install%%" class="inputSubmit" /></div><br /> 
    </form>
</installer_samplecontent_form>

<installer_modules_row>
	    <table class="moduleRowTable" cellpadding="0" cellspacing="0">
	        <tr valign="bottom">
	            <td rowspan="2" width="30px;"></td>
	            <td colspan="2" valign="bottom">
	                %%module_name%%
	            </td>
	        </tr>
	        <tr>
	            <td width="180px" style="text-indent: 20px;" valign="middle">V %%module_version%%</td>
	            <td align="left">%%module_hint%%</td>
	        </tr>
	    </table>
</installer_modules_row>

<installer_modules_row_installable>
	    <table class="moduleRowTable installable" cellpadding="0" cellspacing="0" onclick="document.getElementById('moduleInstallBox[installer_%%module_nameShort%%]').click();">
	        <tr valign="bottom">
	            <td rowspan="2" width="30px;"><input class="checkbox" type="checkbox" name="moduleInstallBox[installer_%%module_nameShort%%]" id="moduleInstallBox[installer_%%module_nameShort%%]" /></td>
	            <td colspan="2" valign="bottom">
	                %%module_name%%
	            </td>
	        </tr>
	        <tr>
	            <td width="180px" style="text-indent: 20px;" valign="middle">V %%module_version%%</td>
	            <td align="left">%%module_hint%%</td>
	        </tr>
	    </table>
</installer_modules_row_installable>

<installer_elements_form>
    <form action="_webpath_/installer/installer.php?step=postInstall" method="POST">
       
       %%module_rows%%
       <br />
       <div><label for="submit"></label><input type="submit" value="%%button_install%%" class="inputSubmit" /></div><br /> 
    </form>
</installer_elements_form>

<installer_elements_row>
    <table class="moduleRowTable" cellpadding="0" cellspacing="0">
        <tr valign="bottom">
	        <td rowspan="2" width="30px;"></td>
			<td colspan="2" valign="bottom">
                %%module_name%%
            </td>
        </tr>
        <tr>
            <td width="180px" style="text-indent: 20px;" valign="middle">V %%module_version%%</td>
            <td align="left">%%module_hint%%</td>
        </tr>
    </table>
</installer_elements_row>

<installer_elements_row_installable>
    <table class="moduleRowTable installable" cellpadding="0" cellspacing="0" onclick="document.getElementById('moduleInstallBox[installer_%%module_nameShort%%]').click();">
        <tr valign="bottom">
	        <td rowspan="2" width="30px;"><input class="checkbox" type="checkbox" name="moduleInstallBox[installer_%%module_nameShort%%]" id="moduleInstallBox[installer_%%module_nameShort%%]" checked="checked" /></td>
			<td colspan="2" valign="bottom">
                %%module_name%%
            </td>
        </tr>
        <tr>
            <td width="180px" style="text-indent: 20px;" valign="middle">V %%module_version%%</td>
            <td align="left">%%module_hint%%</td>
        </tr>
    </table>
</installer_elements_row_installable>