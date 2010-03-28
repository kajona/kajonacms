<portalregistration_userdataform>
<form name="formUserdata" method="post" action="%%formaction%%" accept-charset="UTF-8" autocomplete="off">
    %%formErrors%%
    <div><label for="username">%%lang_pr_usernameTitle%%</label><input type="text" name="username" id="username" value="%%username%%" class="inputText" /></div><br />
    <div><label for="password">%%lang_pr_passwordTitle%%</label><input type="password" name="password" id="password" value="%%password%%" class="inputText" /></div><br />
    <div><label for="password2">%%lang_pr_passwordTitle2%%</label><input type="password" name="password2" id="password2" value="%%password2%%" class="inputText" /></div><br />
    <div><label for="email">%%lang_pr_emailTitle%%</label><input type="text" name="email" id="email" value="%%email%%" class="inputText" /></div><br />
    <div><label for="forename">%%lang_pr_forenameTitle%%</label><input type="text" name="forename" id="forename" value="%%forename%%" class="inputText" /></div><br />
    <div><label for="name">%%lang_pr_nameTitle%%</label><input type="text" name="name" id="name" value="%%name%%" class="inputText" /></div><br />
    <div><label for="kajonaCaptcha_portalreg"></label><span id="kajonaCaptcha_portalreg"><script type="text/javascript">KAJONA.portal.loadCaptcha('portalreg', 180);</script></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('portalreg', 180); return false;">%%lang_captcha_reload%%</a>)</div><br />
    <div><label for="form_captcha">%%lang_captcha_label%%</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" autocomplete="off" /></div><br />
    <div><label for="Submit"></label><input type="submit" name="Submit" value="%%lang_pr_userDataSubmit%%" class="button" /></div><br />
    <input type="hidden" name="submitUserForm" value="1" /> 
</form>
</portalregistration_userdataform>


<errorRow>
&middot; %%error%%<br />
</errorRow>