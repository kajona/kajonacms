<installer_main>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kajona Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="generator" content="Kajona, www.kajona.de" />

    <!-- KAJONA_BUILD_LESS_START -->
    <link href="_webpath_/core/module_installer/less/bootstrap.less" rel="stylesheet/less">
    <link href="_webpath_/core/module_installer/less/responsive.less" rel="stylesheet/less">
    <script> less = { env:'development' }; </script>
    <script src="_webpath_/core/module_installer/less/less.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->

    <script src="_webpath_/core/module_system/admin/scripts/jquery/jquery.min.js"></script>
    <script src="_webpath_/core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="_webpath_/core/module_installer/js/html5.js"></script>
    <![endif]-->
</head>


<body>

<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <ul class="nav">
                %%installer_progress%%
            </ul>
        </div>
    </div>
</div>

<div class="container-fluid">
<div class="row-fluid">




    <!-- CONTENT CONTAINER -->
    <div class="span12" id="content">

        <div class="modal" id="loginContainer">
            <div class="modal-header">
                <h3 id="loginContainer_title">Kajona Installer %%installer_version%%</h3>
            </div>
            <div class="modal-body">
                <div id="loginContainer_content">

                    %%installer_output%%

                    %%installer_logfile%%

                </div>
            </div>
            <div class="modal-footer">
                %%installer_backward%%
                %%installer_forward%%
            </div>
        </div>


    </div>
</div>

<footer>
    <a href="http://www.kajona.de" target="_blank">Kajona - empowering your content</a>
</footer>


</body>
</html>
</installer_main>

<installer_progress_entry>
    <li><a href="#">%%entry_name%%</a></li>
</installer_progress_entry>

<installer_progress_entry_current>
    <li class="active"><a href="#">%%entry_name%%</a></li>
</installer_progress_entry_current>

<installer_progress_entry_done>
    <li class="done"><a href="#">%%entry_name%%</a></li>
</installer_progress_entry_done>

<installer_log>
<div class="preText" id="systemlog">
	<pre class="code pre-scrollable"><b>%%systemlog%%</b><br />%%log_content%%</pre>
</div>
<script type="text/javascript">
var systemlogDiv = document.getElementById("systemlog");
systemlogDiv.scrollTop = systemlogDiv.scrollHeight;
</script>
</installer_log>

<configwizard_form>
    [lang,installer_config_intro,installer]

<script type="text/javascript">
function switchDriver() {
    var strValue = document.getElementById('driver').value;

    $('#cxWarning').html("");

    var strMysqliInfo = '%%mysqliInfo%%';
    var strPostgresInfo = '%%postgresInfo%%';
    var strSqlite3Info = '%%sqlite3Info%%';
    var strOci8Info = '%%oci8Info%%';

    if(strValue == "mysqli") {
        $('#dbInfo').html(strMysqliInfo);
    }
    else if(strValue == "sqlite3") {
        $('#dbInfo').html(strSqlite3Info);
    }
    else if(strValue == "postgres") {
        $('#dbInfo').html(strPostgresInfo);
    }
    else if(strValue == "oci8") {
        $('#dbInfo').html(strOci8Info);
    }

    if(strValue == "sqlite3") {
        document.getElementById('username').value = 'n.a.';
        document.getElementById('password').value = 'n.a.';
        document.getElementById('port').value = 'n.a.';
        document.getElementById('hostname').value = 'n.a.';

        document.getElementById('username').readOnly = true;
        document.getElementById('password').readOnly = true;
        document.getElementById('port').readOnly = true;
        document.getElementById('hostname').readOnly = true;
    } else {
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('port').value = '';
        document.getElementById('hostname').value = 'localhost';

        document.getElementById('username').readOnly = false;
        document.getElementById('password').readOnly = false;
        document.getElementById('port').readOnly = false;
        document.getElementById('hostname').readOnly = false;
    }
}
</script>
<br />
<form action="_webpath_/installer.php?step=config" method="POST" class="form-horizontal">
<input type="hidden" name="write" value="true" />
<div id="dbInfo">
    %%mysqliInfo%%
</div>
<div id="cxWarning">%%cxWarning%%</div>

    <div class="control-group">
        <label for="hostname" class="control-label">[lang,installer_config_dbhostname,installer]</label>
        <div class="controls">
            <input type="text" id="hostname" name="hostname" value="%%postHostname%%" class="input-xlarge">
        </div>
    </div>

    <div class="control-group">
        <label for="username" class="control-label">[lang,installer_config_dbusername,installer]</label>
        <div class="controls">
            <input type="text" id="username" name="username" value="%%postUsername%%" class="input-xlarge">
        </div>
    </div>

    <div class="control-group">
        <label for="password" class="control-label">[lang,installer_config_dbpassword,installer]</label>
        <div class="controls">
            <input type="password" id="password" name="password" value="" class="input-xlarge">
        </div>
    </div>

    <div class="control-group">
        <label for="dbname" class="control-label">[lang,installer_config_dbname,installer]</label>
        <div class="controls">
            <input type="text" id="dbname" name="dbname" value="%%postDbname%%" class="input-xlarge">
        </div>
    </div>

    <div class="control-group">
        <label for="dbprefix" class="control-label">[lang,installer_config_dbprefix,installer]</label>
        <div class="controls">
            <input type="text" id="dbprefix" name="dbprefix" value="%%postPrefix%%" class="input-xlarge">
        </div>
    </div>

    <div class="control-group">
        <label for="driver" class="control-label">[lang,installer_config_dbdriver,installer]</label>
        <div class="controls">
            <select name="driver" id="driver" class="input-xlarge" onchange="switchDriver();">
                <option value="mysqli" selected="selected">MySQL</option>
                <option value="mysqli">MariaDB</option>
                <option value="postgres">PostgreSQL</option>
                <option value="sqlite3">SQLite 3</option>
                <option value="oci8">Oracle (oci8)</option>
            </select>
        </div>
        <script type="text/javascript">$('#driver').val('%%postDbdriver%%');</script>
    </div>

    <div class="controls">
        <p class="help-block">[lang,installer_config_dbportinfo,installer]</p>
    </div>
    <div class="control-group">
        <label for="port" class="control-label">[lang,installer_config_dbport,installer]</label>
        <div class="controls">
            <input type="text" id="port" name="port" value="%%postDbport%%" class="input-xlarge">
        </div>
    </div>

    <div class="control-group">
        <button type="submit" class="btn savechanges">
            <span class="btn-text">[lang,installer_config_write,installer]</span>
            <span class="statusicon"></span>
        </button>
    </div>

</form>
</configwizard_form>


<modeselect_content>
    <h2>[lang,installer_step_modeselect,installer]</h2>
    <div>
        <a href="%%link_autoinstall%%">[lang,installer_mode_auto,installer]</a>
        <p>[lang,installer_mode_auto_hint,installer]</p>
    </div>
    <p><br /></p>
    <div>
        <a href="%%link_manualinstall%%">[lang,installer_mode_manual,installer]</a>
        <p>[lang,installer_mode_manual_hint,installer]</p>
        <div class="alert alert-info">[lang,installer_mode_hint,installer]</div>
    </div>
</modeselect_content>


<loginwizard_form>
<form action="_webpath_/installer.php?step=loginData" method="POST" class="form-horizontal">
<input type="hidden" name="write" value="true" />

<div class="control-group">
    <label for="username" class="control-label">[lang,installer_login_username,installer]</label>
    <div class="controls">
        <input type="text" id="username" name="username" value="" class="input-xlarge">
    </div>
</div>

<div class="control-group">
    <label for="password" class="control-label">[lang,installer_login_password,installer]</label>
    <div class="controls">
        <input type="password" id="password" name="password" value="" class="input-xlarge">
    </div>
</div>

<div class="control-group">
    <label for="email" class="control-label">[lang,installer_login_email,installer]</label>
    <div class="controls">
        <input type="text" id="email" name="email" value="" class="input-xlarge">
    </div>
</div>

<div class="control-group">
    <button type="submit" class="btn savechanges">
        <span class="btn-text">[lang,installer_login_save,installer]</span>
        <span class="statusicon"></span>
    </button>
</div>

</form>
</loginwizard_form>


<installer_forward_link>
<a href="%%href%%" class="btn btn-primary">%%text%%</a>
</installer_forward_link>

<installer_backward_link>
<a href="%%href%%" class="btn">%%text%%</a>
</installer_backward_link>

<installer_modules_form>
	<form action="_webpath_/installer.php?step=install" method="POST">
        <table class="table table-striped table-condensed table-hover" cellpadding="0" cellspacing="0">
	        %%module_rows%%
        </table>
        <div class="control-group">
            <button type="submit" class="btn savechanges">
                <span class="btn-text">[lang,installer_install,installer]</span>
                <span class="statusicon"></span>
            </button>
        </div>

	</form>
</installer_modules_form>

<installer_samplecontent_form>
    <form action="_webpath_/installer.php?step=samplecontent" method="POST">
        <table class="admintable table table-striped table-condensed table-hover" cellpadding="0" cellspacing="0">
       %%module_rows%%
        </table>
        <div class="control-group">
            <button type="submit" class="btn savechanges">
                <span class="btn-text">[lang,installer_install,installer]</span>
                <span class="statusicon"></span>
            </button>
        </div>

    </form>
</installer_samplecontent_form>

<installer_modules_row>
	        <tr>
	            <td width="30px;"></td>
	            <td valign="bottom">%%module_name%%</td>
	            <td>V %%module_version%%</td>
	            <td>%%module_hint%%</td>
	        </tr>
</installer_modules_row>

<installer_modules_row_installable>
	        <tr>
	            <td width="30px;"><input class="checkbox" type="checkbox" name="moduleInstallBox[installer_%%module_nameShort%%]" id="moduleInstallBox[installer_%%module_nameShort%%]" checked="checked"/></td>
	            <td onclick="document.getElementById('moduleInstallBox[installer_%%module_nameShort%%]').click();">%%module_name%%</td>
	            <td onclick="document.getElementById('moduleInstallBox[installer_%%module_nameShort%%]').click();">V %%module_version%%</td>
	            <td onclick="document.getElementById('moduleInstallBox[installer_%%module_nameShort%%]').click();">%%module_hint%%</td>
	        </tr>
</installer_modules_row_installable>



