
<!-- Template API: voting_systemid, voting_title, voting_content-->
<voting_wrapper>
    <div class="voting">
        <h3 data-kajona-editable="%%voting_systemid%%#strTitle#plain">%%voting_title%%</h3>
        %%voting_content%%
    </div>
</voting_wrapper>


<!-- Template API: voting_systemid, voting_action, voting_answers-->
<voting_voting>
    <form method="post" action="%%voting_action%%" id="voting_%%voting_systemid%%" class="votingForm">
        <div class="radio">
            %%voting_answers%%
        </div>

        <input type="hidden" name="systemid" value="%%voting_systemid%%" />
        <fieldset class="form-group">
            <button type="submit" class="btn btn-primary">[lang,voting_submit,votings]</button>
        </fieldset>
    </form>

</voting_voting>


<!-- Template API: voting_systemid, answer_systemid, answer_text-->
<voting_voting_option>
    <div>
        <label for="option_%%answer_systemid%%">
            <input type="radio" name="voting_%%voting_systemid%%" value="%%answer_systemid%%" id="option_%%answer_systemid%%"/>
            <span data-kajona-editable="%%answer_systemid%%#strText#plain">%%answer_text%%</span>
        </label>
    </div>
</voting_voting_option>


<!-- Template API: voting_systemid, voting_action, voting_answers-->
<voting_result>
    %%voting_answers%%
    <div class="alert alert-info" role="alert">
        [lang,voting_hits,votings] %%voting_hits%%
    </div>
</voting_result>


<!-- Template API: answer_systemid, answer_hits, answer_text-->
<voting_result_answer>
    <div class="row">
        <div class="col-md-6">
            <span data-kajona-editable="%%answer_systemid%%#strText#plain">%%answer_text%%</span>
        </div>
        <div class="col-md-4">
            <progress class="progress" value="%%answer_percent%%" max="100">
                <div class="progress">
                    <span class="progress-bar" style="width: %%answer_percent%%%;">%%answer_percent%%%</span>
                </div>
            </progress>
        </div>
        <div class="col-md-2">
            %%answer_hits%% / %%answer_percent%% %
        </div>
    </div>
</voting_result_answer>