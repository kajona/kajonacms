<portalregistration_loginform>
<form name="formContact" method="post" action="%%action%%" accept-charset="UTF-8">
	<div><label for="portalregistration_username">%%username%%</label><input type="text" name="portalregistration_username" id="portalregistration_username" value="%%portalregistration_username%%" class="inputText" /></div><br />
	<div><label for="portalregistration_password">%%password%%</label><input type="password" name="portalregistration_password" id="portalregistration_password" value="%%portalregistration_password%%" class="inputText" /></div><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%login%%" class="button" /></div><br />
	<input type="hidden" name="action" value="%%portalregistration_action%%" />
</form>
</portalregistration_loginform>

<portalregistration_status>
<p>Logged in as: %%username%%</p>
<p>%%logoutlink%%</p>
<p>%%editprofilelink%%</p>
</portalregistration_status>


<portalregistration_userdataform>
<form name="formUserdata" method="post" action="%%formaction%%" accept-charset="UTF-8">
    %%formErrors%%
    <div><label for="username">%%usernameTitle%%</label><input type="text" name="username" id="username" value="%%username%%" class="inputText" /></div><br />
    <div><label for="password">%%passwordTitle%%</label><input type="password" name="password" id="password" value="%%password%%" class="inputText" /></div><br />
    <div><label for="password2">%%passwordTitle2%%</label><input type="password" name="password2" id="password2" value="%%password2%%" class="inputText" /></div><br />
    <div><label for="email">%%emailTitle%%</label><input type="text" name="email" id="email" value="%%email%%" class="inputText" /></div><br />
    <div><label for="forename">%%forenameTitle%%</label><input type="text" name="forename" id="forename" value="%%forename%%" class="inputText" /></div><br />
    <div><label for="name">%%nameTitle%%</label><input type="text" name="name" id="name" value="%%name%%" class="inputText" /></div><br />
    <div><label for="Submit"></label><input type="submit" name="Submit" value="%%submitTitle%%" class="button" /></div><br />
    <input type="hidden" name="submitUserForm" value="1" /> 
</form>
</portalregistration_userdataform>


<errorRow>
<p>&middot; %%error%%</p>
</errorRow>