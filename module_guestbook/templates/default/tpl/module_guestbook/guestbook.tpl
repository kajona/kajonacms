<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: link_newentry, link_back, link_pages, link_forward, liste_posts -->
<list>
    <div class="guestbook">
        %%link_newentry%%
        <div class="posts">
            %%liste_posts%%
        </div>

        <nav class="text-xs-center">
            <ul class=" pagination pagination-sm">%%link_back%% %%link_pages%% %%link_forward%%</ul>
        </nav>
    </div>
</list>

<!-- available placehodlers: link_href -->
<insert_link>
    <fieldset class="form-group">
        <a href="%%link_href%%"><button type="submit" class="btn btn-primary">[lang,insert_link,guestbook]</button></a>
    </fieldset>
</insert_link>

<!-- available placeholders: post_name, post_name_plain, post_email, post_page, post_text, post_date -->
<post>
    <div class="card card-block">
        <div class="row">
            <div class="col-xs-3 text-muted">
                [lang,post_name_from,guestbook]
            </div>
            <div class="col-xs-3">
                %%post_name_plain%%
            </div>
            <div class="col-xs-6">
                %%post_date%%
            </div>
        </div>
        <div class="row">

            <div class="col-xs-3 text-muted">
                [lang,post_page_text,guestbook]
            </div>
            <div class="col-xs-9">
                %%post_page%%
            </div>
        </div>
        <div class="row">
            <div class="col-xs-3 text-muted">
                [lang,post_message_text,guestbook]
            </div>
            <div class="col-xs-9">
                %%post_text%%
            </div>


        </div>

    </div>
</post>

<!-- available placeholders: validation_errors, error_fields, gb_post_name, gb_post_email, gb_post_text, gb_post_page, action -->
<entry_form>
    %%validation_errors%%
    <form name="form1" method="post" action="%%action%%" accept-charset="UTF-8" class="guestbookForm">
        <fieldset class="form-group">
		    <label for="gb_post_name">[lang,post_name_text,guestbook]*:</label>
            <input type="text" name="gb_post_name" id="gb_post_name" value="%%gb_post_name%%" class="form-control" />
        </fieldset>
        <fieldset class="form-group">
		    <label for="gb_post_email">[lang,post_mail_text,guestbook]*:</label>
            <input type="text" name="gb_post_email" id="gb_post_email" value="%%gb_post_email%%" class="form-control" />
        </fieldset>
        <fieldset class="form-group">
		    <label for="gb_post_page">[lang,post_page_text,guestbook]:</label>
            <input type="text" name="gb_post_page" id="gb_post_page" value="%%gb_post_page%%" class="form-control" />
        </fieldset>
        <fieldset class="form-group">
		    <label for="gb_post_text">[lang,post_message_text,guestbook]*:</label>
            <textarea name="gb_post_text" id="gb_post_text" class="form-control">%%gb_post_text%%</textarea>
        </fieldset>



        <fieldset class="form-group">
            <label for="gb_post_captcha">[lang,commons_captcha,elements]</label>

            <div class="row">
                <div class="col-xs-3">
                    <input type="text" name="gb_post_captcha" id="gb_post_captcha" class="form-control" autocomplete="off" />
                    <small class="text-muted"><a href="#" onclick="KAJONA.portal.loadCaptcha('gb', 180); return false;">[lang,commons_captcha_reload,elements]</a></small>
                </div>
                <div class="col-xs-6">
                    <span id="kajonaCaptcha_gb"><script type="text/javascript">KAJONA.portal.loadCaptcha('gb', 180);</script></span>
                </div>
            </div>
        </fieldset>

        <fieldset class="form-group">
            <button type="submit" class="btn btn-primary">[lang,post_submit_text,guestbook]</button>
        </fieldset>

        <!-- custom bootstrap error rendering, update if required -->
        <script type="text/javascript">
            $.each([%%error_fields%%], function(index, value) {
                $('#'+value).addClass('form-control-danger');
                $('#'+value).closest('.form-group').addClass('has-danger');
            });
        </script>

    </form>
</entry_form>

<!-- available placeholders: error -->
<error_row>
    <li>%%error%%</li>
</error_row>

<!-- available placeholders: error_list -->
<errors>
    <div class="alert alert-danger" role="alert">
        <ul>%%error_list%%</ul>
    </div>
</errors>



<!-- available placeholders: pageHref -->
<pager_fwd>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[lang,commons_next,system]</a></li>
</pager_fwd>

<!-- available placeholders: pageHref -->
<pager_back>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[lang,commons_back,system]</a></li>
</pager_back>

<!-- available placeholders: pageHref, pageNumber -->
<pager_entry>
    <li class="page-item"><a href="%%pageHref%%" class="page-link">[%%pageNumber%%]</a></li>
</pager_entry>

<!-- available placeholders: pageHref, pageNumber -->
<pager_entry_active>
    <li class="page-item active"><a href="%%pageHref%%" class="page-link">[%%pageNumber%%]</a></li>
</pager_entry_active>

