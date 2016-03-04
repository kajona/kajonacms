<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: postacomment_list, postacomment_form, postacomment_new_button, postacomment_back, postacomment_pages, postacomment_forward -->
<postacomment_list>  
    %%postacomment_new_button%%
    <div id="postacommentFormWrapper_%%postacomment_systemid%%">
        %%postacomment_form%%
    </div>

    <div class="postacommentList">
    %%postacomment_list%%
    </div>

    <nav class="text-xs-center">
        <ul class=" pagination pagination-sm">%%postacomment_back%% %%postacomment_pages%% %%postacomment_forward%%</ul>
    </nav>
</postacomment_list>

<!-- available placeholders: postacomment_post_name, postacomment_post_subject, postacomment_post_message, postacomment_post_date, postacomment_post_systemid, postacomment_post_rating (if module rating installed) -->
<postacomment_post>
	<div class="card">
        <div class="card-header ">
            <div class="row">

            <div class="col-md-4">%%postacomment_post_name%%</div>
            <div class="col-md-4 text-xs-center">%%postacomment_post_date%%</div>
            <div class="col-md-4 text-xs-right">%%postacomment_post_rating%%</div>
            </div>
        </div>
        <div class="card-block">
            <div class="text-muted">%%postacomment_post_subject%%</div>
		    <div class="pacText">%%postacomment_post_message%%</div>
        </div>
	</div>
</postacomment_post>

<!-- available placeholders: comment_systemid -->
<postacomment_new_button>
    <div id="postaCommentButton_%%comment_systemid%%"><a href="#" class="btn btn-primary-outline" onclick="KAJONA.portal.loader.loadFile('/templates/default/js/postacomment.js'); KAJONA.util.fold('postaCommentForm_%%comment_systemid%%', function() {KAJONA.portal.loadCaptcha('%%comment_systemid%%', 180);}); return false;">[lang,postacomment_write_new,postacomment]</a></div>
</postacomment_new_button>

<!-- available placeholders: formaction, comment_name, comment_subject, comment_message, comment_template, comment_systemid, comment_page, error_fields -->
<postacomment_form>
    <div id="postaCommentForm_%%comment_systemid%%" style="display: none;">
        %%validation_errors%%
    	<form name="formComment" accept-charset="UTF-8" id="formComment_%%comment_systemid%%" onsubmit="KAJONA.portal.postacomment.submit('%%comment_systemid%%'); return false;" class="postacommentForm">

    		<fieldset class="form-group">
                <label for="comment_name_%%comment_systemid%%">[lang,form_name_label,postacomment]*:</label>
                <input type="text" name="comment_name" id="comment_name_%%comment_systemid%%" value="%%comment_name%%" class="form-control" />
            </fieldset>
    		<fieldset class="form-group">
                <label for="comment_subject_%%comment_systemid%%">[lang,form_subject_label,postacomment]:</label>
                <input type="text" name="comment_subject" id="comment_subject_%%comment_systemid%%" value="%%comment_subject%%" class="form-control" />
            </fieldset>
    		<fieldset class="form-group">
                <label for="comment_message_%%comment_systemid%%">[lang,form_message_label,postacomment]*:</label><textarea name="comment_message" id="comment_message_%%comment_systemid%%" class="form-control">%%comment_message%%</textarea>
            </fieldset>



            <fieldset class="form-group">
                <label for="kajonaCaptcha_%%comment_systemid%%">[lang,commons_captcha,elements]</label>

                <div class="row">
                    <div class="col-xs-3">
                        <input type="text" name="form_captcha" id="form_captcha_%%comment_systemid%%" class="form-control" autocomplete="off" />
                        <small class="text-muted"><a href="#" onclick="KAJONA.portal.loadCaptcha('%%comment_systemid%%', 180); return false;">[lang,commons_captcha_reload,elements]</a></small>
                    </div>
                    <div class="col-xs-6">
                        <span id="kajonaCaptcha_%%comment_systemid%%"><script type="text/javascript">KAJONA.portal.loadCaptcha('%%comment_systemid%%', 180);</script></span>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-group">
                <button type="submit" class="btn btn-primary">[lang,form_submit_label,postacomment]</button>
            </fieldset>

    		<input type="hidden" name="comment_template" value="%%comment_template%%" />
            <input type="hidden" name="comment_systemid" value="%%comment_systemid%%" />
            <input type="hidden" name="comment_page" value="%%comment_page%%" />


            <!-- custom bootstrap error rendering, update if required -->
            <script type="text/javascript">
                $.each([%%error_fields%%], function(index, value) {
                    $('#'+value).addClass('form-control-danger');
                    $('#'+value).closest('.form-group').addClass('has-danger');
                });
            </script>
    	</form>
    </div>
</postacomment_form>


<!-- available placeholders: error_list -->
<errors>
    <div class="alert alert-danger" role="alert">
        <ul>%%error_list%%</ul>
    </div>
</errors>

<!-- available placeholders: error -->
<validation_error_row>
	<li>%%error%%</li>
</validation_error_row>

<!-- available placeholders: strTitle -->
<categories_category>
    <span class="label label-default">%%strTitle%%</span>
</categories_category>

<!-- available placeholders: categories -->
<categories_wrapper>
    [lang,news_categories,news]%%categories%%
</categories_wrapper>


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
