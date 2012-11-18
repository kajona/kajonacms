
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
        <div>
            %%voting_answers%%
        </div>

        <input type="hidden" name="systemid" value="%%voting_systemid%%" />
        <input type="submit" value="[lang,voting_submit,votings]" class="button" />
        <br />
    </form>

</voting_voting>


<!-- Template API: voting_systemid, answer_systemid, answer_text-->
<voting_voting_option>
    <div>
        <input type="radio" name="voting_%%voting_systemid%%" value="%%answer_systemid%%" id="option_%%answer_systemid%%"/>
        <label for="option_%%answer_systemid%%" data-kajona-editable="%%answer_systemid%%#strText#plain">%%answer_text%%</label>
    </div>
</voting_voting_option>


<!-- Template API: voting_systemid, voting_action, voting_answers-->
<voting_result>
    <table width="80%" class="votingResult">
        %%voting_answers%%
        <tr>
            <td colspan="2">[lang,voting_hits,votings] %%voting_hits%%</td>
        </tr>
    </table>
</voting_result>


<!-- Template API: answer_systemid, answer_hits, answer_text-->
<voting_result_answer>
    <tr>
        <td colspan="2" data-kajona-editable="%%answer_systemid%%#strText#plain">%%answer_text%%</td>
    </tr>
    <tr>
        <td width="80%"><img src="_webpath_/templates/default/pics/default/icon_progressbar.gif" height="15" width="%%answer_percent%%%" alt="%%answer_percent%%%" /></td>
        <td>%%answer_hits%% / %%answer_percent%% %</td>
    </tr>
</voting_result_answer>