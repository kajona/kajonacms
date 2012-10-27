<portalregistration_userdataform>
<form name="formUserdata" method="post" action="%%formaction%%" accept-charset="UTF-8" autocomplete="off" class="element_portalregistration">
    %%formErrors%%
    <div><label for="username">[lang,pr_usernameTitle,elements]</label><input type="text" name="username" id="username" value="%%username%%" class="inputText" /></div>
    <div><label for="password">[lang,pr_passwordTitle,elements]</label><input type="password" name="password" id="password" value="%%password%%" class="inputText" /></div>
    <div><label for="password2">[lang,pr_passwordTitle2,elements]</label><input type="password" name="password2" id="password2" value="%%password2%%" class="inputText" /></div>
    <div><label for="email">[lang,pr_emailTitle,elements]</label><input type="text" name="email" id="email" value="%%email%%" class="inputText" /></div>
    <div><label for="forename">[lang,pr_forenameTitle,elements]</label><input type="text" name="forename" id="forename" value="%%forename%%" class="inputText" /></div>
    <div><label for="name">[lang,pr_nameTitle,elements]</label><input type="text" name="name" id="name" value="%%name%%" class="inputText" /></div>
    <div><label for="kajonaCaptcha_portalreg"></label><span id="kajonaCaptcha_portalreg"><script type="text/javascript">KAJONA.portal.loadCaptcha('portalreg', 180);</script></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('portalreg', 180); return false;">[lang,commons_captcha_reload,elements]</a>)</div>
    <div><label for="form_captcha">[lang,commons_captcha,elements]</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" autocomplete="off" /></div>
    <div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,pr_userDataSubmit,elements]" class="button" /></div>
    <input type="hidden" name="submitUserForm" value="1" /> 
</form>
</portalregistration_userdataform>


<errorRow>
&middot; %%error%%<br />
</errorRow>