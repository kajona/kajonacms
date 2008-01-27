

<portalregistration_userdataform>
<form name="formUserdata" method="post" action="%%formaction%%" accept-charset="UTF-8" autocomplete="off">
    %%formErrors%%
    <div><label for="username">%%usernameTitle%%</label><input type="text" name="username" id="username" value="%%username%%" class="inputText" /></div><br />
    <div><label for="password">%%passwordTitle%%</label><input type="password" name="password" id="password" value="%%password%%" class="inputText" /></div><br />
    <div><label for="password2">%%passwordTitle2%%</label><input type="password" name="password2" id="password2" value="%%password2%%" class="inputText" /></div><br />
    <div><label for="email">%%emailTitle%%</label><input type="text" name="email" id="email" value="%%email%%" class="inputText" /></div><br />
    <div><label for="forename">%%forenameTitle%%</label><input type="text" name="forename" id="forename" value="%%forename%%" class="inputText" /></div><br />
    <div><label for="name">%%nameTitle%%</label><input type="text" name="name" id="name" value="%%name%%" class="inputText" /></div><br />
    <div><label for="kajonaCaptcha"></label><img id="kajonaCaptcha" src="_webpath_/image.php?image=kajonaCaptcha&maxWidth=180" /></div><br />
    <div><label for="form_captcha">Code*:</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" /></div><br />
    <div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="Neuer Code" class="button" /></div><br /><br />
    <div><label for="Submit"></label><input type="submit" name="Submit" value="%%submitTitle%%" class="button" /></div><br />
    <input type="hidden" name="submitUserForm" value="1" /> 
</form>
</portalregistration_userdataform>


<errorRow>
<p>&middot; %%error%%</p>
</errorRow>