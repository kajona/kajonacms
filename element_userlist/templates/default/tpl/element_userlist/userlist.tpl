<userlist_wrapper>
<div id="userlistWrapper">
    <table cellpadding="4" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <th style="border: 1px solid #cccccc;">[lang,userlistName,elements]</th>
            <th style="border: 1px solid #cccccc;">[lang,userlistForename,elements]</th>
            <th style="border: 1px solid #cccccc;">[lang,userlistEmail,elements]</th>
            <th style="border: 1px solid #cccccc;">[lang,userlistStreet,elements]</th>
            <th style="border: 1px solid #cccccc;">[lang,userlistPostal,elements]</th>
            <th style="border: 1px solid #cccccc;">[lang,userlistCity,elements]</th>
            <th style="border: 1px solid #cccccc;">[lang,userlistPhone,elements]</th>
            <th style="border: 1px solid #cccccc;">[lang,userlistMobile,elements]</th>
            <th style="border: 1px solid #cccccc;">[lang,userlistBirthday,elements]</th>
        </tr>
        %%userlist_rows%%
    </table>
    <a href="%%csvHref%%">[lang,userlistLinkCsv,elements]</a>
</div>
</userlist_wrapper>


<userlist_row>
    <tr>
        <td style="border: 1px solid #cccccc;">%%userName%%</td>
        <td style="border: 1px solid #cccccc;">%%userForename%%</td>
        <td style="border: 1px solid #cccccc;">%%userEmail%%</td>
        <td style="border: 1px solid #cccccc;">%%userStreet%%</td>
        <td style="border: 1px solid #cccccc;">%%userPostal%%</td>
        <td style="border: 1px solid #cccccc;">%%userCity%%</td>
        <td style="border: 1px solid #cccccc;">%%userPhone%%</td>
        <td style="border: 1px solid #cccccc;">%%userMobile%%</td>
        <td style="border: 1px solid #cccccc;">%%userBirthday%%</td>
    </tr>
</userlist_row>