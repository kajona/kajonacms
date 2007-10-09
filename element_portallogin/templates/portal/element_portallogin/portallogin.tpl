<portallogin_loginform>
<form name="formContact" method="post" action="%%action%%" accept-charset="UTF-8">
	<div><label for="portallogin_username">%%username%%</label><input type="text" name="portallogin_username" id="portallogin_username" value="%%portallogin_username%%" class="inputText" /></div><br />
	<div><label for="portallogin_password">%%password%%</label><input type="password" name="portallogin_password" id="portallogin_password" value="%%portallogin_password%%" class="inputText" /></div><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%login%%" class="button" /></div><br />
	<input type="hidden" name="action" value="%%portallogin_action%%" />
</form>
</portallogin_loginform>

<portallogin_status>
<p>Logged in as: %%username%%</p>
<p>%%logoutlink%%</p>
</portallogin_status>