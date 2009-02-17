<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: link_newentry, link_back, link_pages, link_forward, liste_posts -->
<list>
    %%link_newentry%%<br />
    <p>%%link_back%% - %%link_pages%% - %%link_forward%%</p>
    <p>%%liste_posts%%</p>
</list>

<!-- available placeholders: post_name_from, post_name, post_name_plain, post_mail_text, post_email, post_page_text, post_page, post_message_text, post_text, post_date -->
<post>
    <table width="100%" border="0">
        <tr>
            <td>%%post_name_from%%: %%post_name_plain%%</td>
            <td style="text-align: right;">%%post_date%%</td>
        </tr>
        <tr>
            <td colspan="2">%%post_page_text%%: %%post_page%%</td>
        </tr>
        <tr>
            <td>%%post_message_text%%:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">%%post_text%%</td>
        </tr>
    </table>
    <br /><br />
</post>

<!-- available placeholders: eintragen_fehler, post_name_text, gb_post_name, post_mail_text, gb_post_email, post_message_text, gb_post_text, post_page_text, gb_post_page, post_submit_text, post_code_text, action -->
<entry_form>
    <ul>%%eintragen_fehler%%</ul>
    <form name="form1" method="post" action="%%action%%" accept-charset="UTF-8">
        <div><label for="gb_post_name">%%post_name_text%%*:</label><input type="text" name="gb_post_name" id="gb_post_name" value="%%gb_post_name%%" class="inputText" /></div><br />
        <div><label for="gb_post_email">%%post_mail_text%%*:</label><input type="text" name="gb_post_email" id="gb_post_email" value="%%gb_post_email%%" class="inputText" /></div><br />
        <div><label for="gb_post_page">%%post_page_text%%:</label><input type="text" name="gb_post_page" id="gb_post_page" value="%%gb_post_page%%" class="inputText" /></div><br />
        <div><label for="gb_post_text">%%post_message_text%%*:</label><textarea name="gb_post_text" id="gb_post_text" class="inputTextarea">%%gb_post_text%%</textarea></div><br /><br />
        <div><label for="kajonaCaptcha"></label><img id="kajonaCaptcha" src="_webpath_/image.php?image=kajonaCaptcha&amp;maxWidth=180" /></div><br />
    	<div><label for="gb_post_captcha">Code*:</label><input type="text" name="gb_post_captcha" id="gb_post_captcha" class="inputText" /></div><br />
    	<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="%%post_code_text%%" class="button" /></div><br /><br />
    	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%post_submit_text%%" class="button" /></div><br />
    </form>
</entry_form>

<!-- available placeholders: error -->
<error_row>
    <li>%%error%%</li>
</error_row>