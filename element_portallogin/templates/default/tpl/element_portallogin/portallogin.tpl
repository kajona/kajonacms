<portallogin_loginform>
<form name="formPortallogin" method="post" action="%%action%%" accept-charset="UTF-8">
	<div><label for="portallogin_username">[lang,username,elements]</label><input type="text" name="portallogin_username" id="portallogin_username" value="%%portallogin_username%%" class="inputText" /></div>
	<div><label for="portallogin_password">[lang,password,elements]</label><input type="password" name="portallogin_password" id="portallogin_password" value="%%portallogin_password%%" class="inputText" /></div>
	<div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,login,elements]" class="button" /></div><br />
	<input type="hidden" name="action" value="%%portallogin_action%%" />
	<input type="hidden" name="pl_systemid" value="%%portallogin_elsystemid%%" />
    <div><label></label>%%portallogin_forgotpwdlink%%</div><br />
</form>
</portallogin_loginform>

<portallogin_status>
<p>%%loggedin_label%%: %%username%%</p>
<p>%%logoutlink%% &nbsp; %%editprofilelink%%</p>
</portallogin_status>


<portallogin_userdataform_minimal>
<form name="formUserdata" method="post" action="%%formaction%%" accept-charset="UTF-8">
    %%formErrors%%
    <div><label for="username">[lang,usernameTitle,elements]</label><input type="text" name="username" id="username" value="%%username%%" class="inputText" disabled="disabled" /></div>
    <div><label for="password">[lang,passwordTitle,elements]</label><input type="password" name="password" id="password" value="%%password%%" class="inputText" /></div>
    <div><label for="password2">[lang,passwordTitle2,elements]</label><input type="password" name="password2" id="password2" value="%%password2%%" class="inputText" /></div>
    <div><label for="email">[lang,emailTitle,elements]</label><input type="text" name="email" id="email" value="%%email%%" class="inputText" /></div>
    <div><label for="forename">[lang,forenameTitle,elements]</label><input type="text" name="forename" id="forename" value="%%forename%%" class="inputText" /></div>
    <div><label for="name">[lang,nameTitle,elements]</label><input type="text" name="name" id="name" value="%%name%%" class="inputText" /></div>
    <div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,userDataSubmit,elements]" class="button" /></div>
    <input type="hidden" name="submitUserForm" value="1" />
    <input type="hidden" name="pl_systemid" value="%%portallogin_elsystemid%%" />
</form>
</portallogin_userdataform_minimal>

<portallogin_userdataform_complete>
<form name="formUserdata" method="post" action="%%formaction%%" accept-charset="UTF-8">
    %%formErrors%%
    <div><label for="username">[lang,usernameTitle,elements]</label><input type="text" name="username" id="username" value="%%username%%" class="inputText" disabled="disabled" /></div>
    <div><label for="password">[lang,passwordTitle,elements]</label><input type="password" name="password" id="password" value="%%password%%" class="inputText" /></div>
    <div><label for="password2">[lang,passwordTitle2,elements]</label><input type="password" name="password2" id="password2" value="%%password2%%" class="inputText" /></div>
    <div><label for="email">[lang,emailTitle,elements]</label><input type="text" name="email" id="email" value="%%email%%" class="inputText" /></div>
    <div><label for="forename">[lang,forenameTitle,elements]</label><input type="text" name="forename" id="forename" value="%%forename%%" class="inputText" /></div>
    <div><label for="name">[lang,nameTitle,elements]</label><input type="text" name="name" id="name" value="%%name%%" class="inputText" /></div>
    <div><label for="street">[lang,streetTitle,elements]</label><input type="text" name="street" id="street" value="%%street%%" class="inputText" /></div>
    <div><label for="postal">[lang,postalTitle,elements]</label><input type="text" name="postal" id="postal" value="%%postal%%" class="inputText" /></div>
    <div><label for="city">[lang,cityTitle,elements]</label><input type="text" name="city" id="city" value="%%city%%" class="inputText" /></div>
    <div><label for="phone">[lang,phoneTitle,elements]</label><input type="text" name="phone" id="phone" value="%%phone%%" class="inputText" /></div>
    <div><label for="mobile">[lang,mobileTitle,elements]</label><input type="text" name="mobile" id="mobile" value="%%mobile%%" class="inputText" /></div>
    <div><label for="date_day">[lang,dateTitle,elements]</label>
        <input type="text" name="date_day" id="date_day" value="%%date_day%%" class="inputText" style="width: 34px;" maxlength="2" />&nbsp;
        <input type="text" name="date_month" id="date_month" value="%%date_month%%" class="inputText" style="width: 34px;" maxlength="2" />&nbsp;
        <input type="text" name="date_year" id="date_year" value="%%date_year%%" class="inputText" style="width: 60px;" maxlength="4" />
    </div>
    <div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,userDataSubmit,elements]" class="button" /></div>
    <input type="hidden" name="submitUserForm" value="1" />
    <input type="hidden" name="pl_systemid" value="%%portallogin_elsystemid%%" />
</form>
</portallogin_userdataform_complete>


<errorRow>
&middot; %%error%%<br />
</errorRow>


<portallogin_resetform>
    [lang,resetHint,elements]
<form name="portalFormResetPwd" method="post" action="%%action%%" accept-charset="UTF-8">
	<div><label for="portallogin_username">[lang,username,elements]</label><input type="text" name="portallogin_username" id="portallogin_username" value="%%portallogin_username%%" class="inputText" /></div><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,resetPwd,elements]" class="button" /></div><br />
	<input type="hidden" name="action" value="%%portallogin_action%%" />
	<input type="hidden" name="reset" value="1" />
    <input type="hidden" name="pl_systemid" value="%%portallogin_elsystemid%%" />
</form>
</portallogin_resetform>


<portallogin_newpwdform>
    [lang,pwdHint,elements]
<form name="portalFormNewPwd" method="post" action="%%action%%" accept-charset="UTF-8">
	<div><label for="portallogin_password1">[lang,password1,elements]</label><input type="password" name="portallogin_password1" id="portallogin_password1" value="" class="inputText" /></div><br />
	<div><label for="portallogin_password2">[lang,password2,elements]</label><input type="password" name="portallogin_password2" id="portallogin_password2" value="" class="inputText" /></div><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,changePwd,elements]" class="button" /></div><br />
	<input type="hidden" name="action" value="%%portallogin_action%%" />
	<input type="hidden" name="systemid" value="%%portallogin_systemid%%" />
	<input type="hidden" name="authcode" value="%%portallogin_authcode%%" />
	<input type="hidden" name="reset" value="1" />
    <input type="hidden" name="pl_systemid" value="%%portallogin_elsystemid%%" />
</form>
</portallogin_newpwdform>
