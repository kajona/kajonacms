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
    <link href="_webpath_/[webpath,module_installer]/less/bootstrap.less" rel="stylesheet/less">
    <!--<script> less = { env:'development' }; </script>-->
    <script src="_webpath_/[webpath,module_installer]/less/less.min.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->

    <link rel="stylesheet" href="_webpath_/[webpath,module_installer]/fonts/fontawesome/css/font-awesome.min.css">

    <script src="_webpath_/[webpath,module_system]/scripts/jquery/jquery.min.js"></script>
    <script src="_webpath_/[webpath,module_system]/scripts/jqueryui/jquery-ui.custom.min.js"></script>

</head>


<body>

<div class="navbar navbar-fixed-top hidden-sm hidden-xs">
      <div class="container">
            <ul class="navbar-nav">
                %%installer_progress%%
            </ul>
        </div>
 </div>

<div class="container-fluid">
<div class="row">

    <!-- CONTENT CONTAINER -->
    <div class="col-md-8 center-block" id="content">
	
       <div class="panel panel-default" id="loginContainer">
            <div class="panel-header">
                <h3 id="loginContainer_title">Kajona Installer %%installer_version%%</h3>
            </div>
            <div class="panel-body">
                <div id="loginContainer_content">

                    %%installer_output%%

                    %%installer_logfile%%

                </div>
            </div>
            <div class="panel-footer">
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
<div class="col-sm-12" id="systemlog">
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
<div id="cxWarning">%%cxWarning%%</div>
<div id="dbInfo">
    %%mysqliInfo%%
</div>
<form action="_webpath_/installer.php?step=config" method="POST" class="form-horizontal col-sm-10">
<input type="hidden" name="write" value="true" />

    <div class="form-group">
        <label for="hostname" class="col-sm-5 control-label">[lang,installer_config_dbhostname,installer]</label>
     <div class="col-sm-6">
	    <input type="text" id="hostname" name="hostname" value="%%postHostname%%" class="form-control">
     </div>
    </div>
	 

     <div class="form-group">
        <label for="username" class="col-sm-5 control-label">[lang,installer_config_dbusername,installer]</label>
        <div class="col-sm-6">
            <input type="text" id="username" name="username" value="%%postUsername%%" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="password" class="col-sm-5 control-label">[lang,installer_config_dbpassword,installer]</label>
        <div class="col-sm-6">
            <input type="password" id="password" name="password" value="" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="dbname" class="col-sm-5 control-label">[lang,installer_config_dbname,installer]</label>
        <div class="col-sm-6">
            <input type="text" id="dbname" name="dbname" value="%%postDbname%%" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="dbprefix" class="col-sm-5 control-label">[lang,installer_config_dbprefix,installer]</label>
        <div class="col-sm-6">
            <input type="text" id="dbprefix" name="dbprefix" value="%%postPrefix%%" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="driver" class="col-sm-5 control-label">[lang,installer_config_dbdriver,installer]</label>
        <div class="col-sm-6">
            <select name="driver" id="driver" class="form-control" onchange="switchDriver();">
                <option value="mysqli" selected="selected">MySQL</option>
                <option value="mysqli">MariaDB</option>
                <option value="postgres">PostgreSQL</option>
                <option value="sqlite3">SQLite 3</option>
                <option value="oci8">Oracle (oci8)</option>
            </select>
        </div>
        <script type="text/javascript">if('%%postDbdriver%%' != '') $('#driver').val('%%postDbdriver%%');</script>
    </div>


    <div class="form-group">
        <label class="col-sm-5 control-label"></label>
        <div class="col-sm-6">
            <span class="help-block">[lang,installer_config_dbportinfo,installer]</span>
        </div>
        <label for="port" class="col-sm-5">[lang,installer_config_dbport,installer]</label>
        <div class="col-sm-6">
            <input type="text" id="port" name="port" value="%%postDbport%%" class="form-control">
        </div>
    </div>

    <div class="form-group">
	<label class="col-sm-5"></label>
	<div class="col-sm-6">
		<button type="submit" class="btn savechanges">[lang,installer_config_write,installer]</button>
	</div>
    </div>

</form>
</configwizard_form>


<modeselect_content>
    <h2>[lang,installer_step_autoinstall,installer]</h2>
    <div>
        <div class="alert alert-success">
            <p id="statusintro">[lang,installer_start_installation_hint,installer]</p>
            <p id="statusinfo" class="hidden"><i class="fa fa-spinner fa-spin"></i> [lang,installer_start_statusinfo_intro,installer] <span id="statuscurmodule"></span></p>
            <div class="form-group">
                <label class="col-sm-4"></label>
                <div class="col-sm-6">
                    <button type="submit" onclick="startInstaller(this);return false;" class="btn savechanges">[lang,installer_start_installation,installer]</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>

    </div>

    <table class="table table-striped">
        <tr>
            <th>[lang,installer_package_title,installer]</th>
            <th>[lang,installer_package_version,installer]</th>
            <th>[lang,installer_package_installation,installer]</th>
            <th>[lang,installer_package_samplecontent,installer]</th>
            <th>[lang,installer_package_hint,installer]</th>
        </tr>
        %%packagerows%%

    </table>

    <script type="text/javascript">

        function startInstaller(objButton) {
            $(objButton).on('click', function() {return false;} );
            $(objButton).attr('disabled', 'disabled');
            $('#statusinfo').removeClass('hidden');
            $('#installer-cli').removeClass('hide');
            triggerNextInstaller();
        }

        function triggerNextInstaller() {
            $.post(
                '_webpath_/installer.php',
                { step : 'getNextAutoInstall'},
                function(data) {
                    if(data == '' || data == null) {
                        triggerNextSamplecontent();
                        return;
                    }

                    $('tr[data-package="'+data+'"] td.spinner-module').html('<i class="fa fa-spinner fa-spin"></i>');
                    triggerModuleInstaller(data);
                }
            );
        }


        function triggerModuleInstaller(strModule) {
            $('#statuscurmodule').html("module "+strModule);
            $('tr[data-package="'+strModule+'"]').addClass('info');

            $.post(
                '_webpath_/installer.php',
                { step : 'triggerNextAutoInstall', module: strModule}
            )
            .done(function(data) {

                if(data.status == 'success') {
                    $('tr[data-package="'+data.module+'"]').removeClass('info');
                    $('tr[data-package="'+data.module+'"] td.spinner-module').html('<i class="fa fa-check"></i>');
                    $('#installer-cli pre').append(data.log);
                    $("#installer-cli pre").animate({ scrollTop: $('#installer-cli pre').prop("scrollHeight")}, 100);
                    triggerNextInstaller();
                }
            })
            .fail(function(data) {
                $('tr[data-package="'+strModule+'"]').removeClass('info').addClass('danger');
                $('tr[data-package="'+strModule+'"] td.spinner-module').html('<i class="fa fa-times"></i>');
            })
            .always(function(data) {
                $('#installer-cli pre').append(data.log ? data.log : data.responseText);
                $("#installer-cli pre").animate({ scrollTop: $('#installer-cli pre').prop("scrollHeight")}, 100);
            });
        }


        function triggerNextSamplecontent() {
            $.post(
                '_webpath_/installer.php',
                { step : 'getNextAutoSamplecontent'}
            )
            .done(function(data) {

                if(data == '' || data == null) {
                    $('#statusinfo').addClass('hidden');
                    document.location = '_webpath_/installer.php?step=finish';
                    return;
                }

                $('tr[data-package="'+data.module+'"] td.spinner-samplecontent').html('<i class="fa fa-spinner fa-spin"></i>');
                triggerAutoSamplecontent(data.module);
            })
            .fail(function(data) {
                $('tr[data-package="'+strModule+'"]').removeClass('info').addClass('danger');
                $('tr[data-package="'+strModule+'"] td.spinner-module').html('<i class="fa fa-times"></i>');
            })
            .always(function(data) {
                $('#installer-cli pre').append(data.log ? data.log : data.responseText);
                $("#installer-cli pre").animate({ scrollTop: $('#installer-cli pre').prop("scrollHeight")}, 100);
            });
        }


        function triggerAutoSamplecontent(strModule) {
            $('#statuscurmodule').html("samplecontent "+strModule);
            $('tr[data-package="'+strModule+'"]').addClass('info');
            $.post(
                '_webpath_/installer.php',
                { step : 'triggerNextAutoSamplecontent', module: strModule}
            )
            .done(function(data) {
                if(data.status == 'success') {
                    $('tr[data-package="'+data.module+'"]').removeClass('info');
                    $('tr[data-package="'+data.module+'"] td.spinner-samplecontent').html('<i class="fa fa-check"></i>');
                    $('#installer-cli pre').append(data.log);
                    $("#installer-cli pre").animate({ scrollTop: $('#installer-cli pre').prop("scrollHeight")}, 100);
                    triggerNextSamplecontent();
                }
                else {
                    $('tr[data-package="'+data.module+'"]').removeClass('info').addClass('danger');
                    console.log('installation failed ');
                    $('tr[data-package="'+data.module+'"] td.spinner-samplecontent').html('<i class="fa fa-times"></i>');

                }
            })
            .fail(function(data) {
                $('tr[data-package="'+strModule+'"]').removeClass('info').addClass('danger');
                $('tr[data-package="'+strModule+'"] td.spinner-samplecontent').html('<i class="fa fa-times"></i>');
            })
            .always(function(data) {
                $('#installer-cli pre').append(data.log ? data.log : data.responseText);
                $("#installer-cli pre").animate({ scrollTop: $('#installer-cli pre').prop("scrollHeight")}, 100);
            });

        }

    </script>
</modeselect_content>

<autoinstall_row>
    <tr data-package="%%packagename%%">
        <td>%%packageuiname%%</td>
        <td>%%packageversion%%</td>
        <td class="spinner-module">%%packageinstaller%%</td>
        <td class="spinner-samplecontent">%%packagesamplecontent%%</td>
        <td class="text-muted">%%packagehint%%</td>
    </tr>
</autoinstall_row>

<autoinstall_cli>
    <div id="installer-cli" class="hide">
        <pre></pre>
    </div>
</autoinstall_cli>



<loginwizard_form>
<form action="_webpath_/installer.php?step=loginData" method="POST" class="form-horizontal col-sm-10">
<input type="hidden" name="write" value="true" />

    <div class="form-group">
        <label for="username" class="col-sm-4 control-label">[lang,installer_login_username,installer]</label>
        <div class="col-sm-6">
            <input type="text" id="username" name="username" value="" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="password" class="col-sm-4 control-label">[lang,installer_login_password,installer]</label>
        <div class="col-sm-6">
            <input type="password" id="password" name="password" value="" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="email" class="col-sm-4 control-label">[lang,installer_login_email,installer]</label>
        <div class="col-sm-6">
            <input type="text" id="email" name="email" value="" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label"></label>
        <div class="col-sm-6">
            <button type="submit" class="btn savechanges">
                <span class="btn-text">[lang,installer_login_save,installer]</span>
                <span class="statusicon"></span>
            </button>
        </div>
    </div>

</form>
</loginwizard_form>


<installer_forward_link>
<a href="%%href%%" class="btn btn-primary pull-right">%%text%%</a>
</installer_forward_link>

<installer_backward_link>
<a href="%%href%%" class="btn btn-default">%%text%%</a>
</installer_backward_link>

<installer_modules_form>
	<form action="_webpath_/installer.php?step=install" method="POST">
        <table class="table table-striped table-condensed table-hover" cellpadding="0" cellspacing="0">
	        %%module_rows%%
        </table>
        <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="col-sm-6">
                <button type="submit" class="btn savechanges">
                    <span class="btn-text">[lang,installer_install,installer]</span>
                    <span class="statusicon"></span>
                </button>
            </div>
        </div>

	</form>
</installer_modules_form>

<installer_samplecontent_form>
    <form action="_webpath_/installer.php?step=samplecontent" method="POST">
        <table class="admintable table table-striped table-condensed table-hover" cellpadding="0" cellspacing="0">
       %%module_rows%%
        </table>
        <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="col-sm-6">
                <button type="submit" class="btn savechanges">
                    <span class="btn-text">[lang,installer_install,installer]</span>
                    <span class="statusicon"></span>
                </button>
            </div>
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




