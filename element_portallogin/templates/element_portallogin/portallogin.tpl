<portallogin_loginform>
<form name="formContact" method="post" action="%%action%%" accept-charset="UTF-8">
	<div><label for="portallogin_username">%%lang_username%%</label><input type="text" name="portallogin_username" id="portallogin_username" value="%%portallogin_username%%" class="inputText" /></div><br />
	<div><label for="portallogin_password">%%lang_password%%</label><input type="password" name="portallogin_password" id="portallogin_password" value="%%portallogin_password%%" class="inputText" /></div><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%lang_login%%" class="button" /></div><br />
	<input type="hidden" name="action" value="%%portallogin_action%%" />
    <div><label></label>%%portallogin_forgotpwdlink%%</div><br />
</form>
</portallogin_loginform>

<portallogin_status>
<p>%%loggedin_label%%: %%username%%</p>
<p>%%logoutlink%% &nbsp; %%editprofilelink%%</p>
</portallogin_status>


<portallogin_userdataform>
<form name="formUserdata" method="post" action="%%formaction%%" accept-charset="UTF-8">
    %%formErrors%%
    <div><label for="username">%%lang_usernameTitle%%</label><input type="text" name="username" id="username" value="%%username%%" class="inputText" disabled="disabled" /></div><br />
    <div><label for="password">%%lang_passwordTitle%%</label><input type="password" name="password" id="password" value="%%password%%" class="inputText" /></div><br />
    <div><label for="password2">%%lang_passwordTitle2%%</label><input type="password" name="password2" id="password2" value="%%password2%%" class="inputText" /></div><br />
    <div><label for="email">%%lang_emailTitle%%</label><input type="text" name="email" id="email" value="%%email%%" class="inputText" /></div><br />
    <div><label for="forename">%%lang_forenameTitle%%</label><input type="text" name="forename" id="forename" value="%%forename%%" class="inputText" /></div><br />
    <div><label for="name">%%lang_nameTitle%%</label><input type="text" name="name" id="name" value="%%name%%" class="inputText" /></div><br />
    <div><label for="Submit"></label><input type="submit" name="Submit" value="%%lang_userDataSubmit%%" class="button" /></div><br />
    <input type="hidden" name="submitUserForm" value="1" /> 
</form>
</portallogin_userdataform>


<errorRow>
&middot; %%error%%<br />
</errorRow>


<portallogin_resetform>
    %%lang_resetHint%%
<form name="portalFormResetPwd" method="post" action="%%action%%" accept-charset="UTF-8">
	<div><label for="portallogin_username">%%lang_username%%</label><input type="text" name="portallogin_username" id="portallogin_username" value="%%portallogin_username%%" class="inputText" /></div><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%lang_resetPwd%%" class="button" /></div><br />
	<input type="hidden" name="action" value="%%portallogin_action%%" />
	<input type="hidden" name="reset" value="1" />
</form>
</portallogin_resetform>