

<voting_wrapper>
    <div>
        <h3>%%voting_title%%</h3>
        %%voting_content%%
    </div>
</voting_wrapper>


<voting_voting>
    <form method="post" action="%%voting_action%%" id="voting_%%voting_systemid%%">
        <table width="80%">
            %%voting_answers%%
        </table>

        <input type="hidden" name="systemid" value="%%voting_systemid%%" />
        <input type="submit" value="[lang,voting_submit,votings]" class="button" />
        <br />
    </form>

</voting_voting>


<voting_voting_option>
    <tr>
        <td width="25">
            <input type="radio" name="voting_%%voting_systemid%%" value="%%answer_systemid%%" id="option_%%answer_systemid%%"/>
        </td>
        <td align="left">
            <label for="option_%%answer_systemid%%">%%answer_text%%</label>
        </td>
    </tr>
</voting_voting_option>

<voting_result>
    <table width="80%">
        %%voting_answers%%
        <tr>
            <td colspan="2">[lang,voting_hits,votings] %%voting_hits%%</td>
        </tr>
    </table>
</voting_result>


<voting_result_answer>
    <tr>
        <td colspan="2">%%answer_text%%</td>
    </tr>
    <tr>
        <td width="80%"><img src="_webpath_/templates/default/pics/kajona/icon_progressbar.gif" height="15" width="%%answer_percent%%%" alt="%%answer_percent%%%" /></td>
        <td>%%answer_hits%% / %%answer_percent%% %</td>
    </tr>
</voting_result_answer>