<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: link_newentry, link_back, link_pages, link_forward, liste_posts -->
<list>
    <div class="guestbook">
        %%link_newentry%%<br />
        <p>%%link_back%% %%link_pages%% %%link_forward%%</p>
        <div class="posts">%%liste_posts%%</div>
    </div>
</list>

<!-- available placeholders: post_name, post_name_plain, post_email, post_page, post_text, post_date -->
<post>
    <table class="guestbookPost">
        <tr>
            <td>[lang,post_name_from,guestbook]: %%post_name_plain%%</td>
            <td style="text-align: right;">%%post_date%%</td>
        </tr>
        <tr>
            <td colspan="2">[lang,post_page_text,guestbook]: %%post_page%%</td>
        </tr>
        <tr>
            <td>[lang,post_message_text,guestbook]:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">%%post_text%%</td>
        </tr>
    </table>
</post>

<!-- available placeholders: eintragen_fehler, gb_post_name, gb_post_email, gb_post_text, gb_post_page, action -->
<entry_form>
    <ul>%%eintragen_fehler%%</ul>
    <form name="form1" method="post" action="%%action%%" accept-charset="UTF-8" class="guestbookForm">
        <div><label for="gb_post_name">[lang,post_name_text,guestbook]*:</label><input type="text" name="gb_post_name" id="gb_post_name" value="%%gb_post_name%%" class="inputText" /></div>
        <div><label for="gb_post_email">[lang,post_mail_text,guestbook]*:</label><input type="text" name="gb_post_email" id="gb_post_email" value="%%gb_post_email%%" class="inputText" /></div>
        <div><label for="gb_post_page">[lang,post_page_text,guestbook]:</label><input type="text" name="gb_post_page" id="gb_post_page" value="%%gb_post_page%%" class="inputText" /></div>
        <div><label for="gb_post_text">[lang,post_message_text,guestbook]*:</label><textarea name="gb_post_text" id="gb_post_text" class="inputTextarea">%%gb_post_text%%</textarea></div><br />
        <div><label for="kajonaCaptcha_gb"></label><span id="kajonaCaptcha_gb"><script type="text/javascript">KAJONA.portal.loadCaptcha('gb', 180);</script></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('gb', 180); return false;">[lang,commons_captcha_reload,guestbook]</a>)</div>
    	<div><label for="gb_post_captcha">[lang,commons_captcha,guestbook]*:</label><input type="text" name="gb_post_captcha" id="gb_post_captcha" class="inputText" /></div><br />
    	<div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,post_submit_text,guestbook]" class="button" /></div>
    </form>
</entry_form>

<!-- available placeholders: error -->
<error_row>
    <li>%%error%%</li>
</error_row>