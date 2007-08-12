<portallogin_loginform>
<form name="form1" method="post" action="%%action%%">
  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td>%%username%%</td>
      <td><input type="text" name="portallogin_username" value="" /></td>
    </tr>
    <tr>
      <td>%%password%%</td>
      <td><input type="password" name="portallogin_password" value="" /></td>
    </tr>
    <tr>
      <td><input type="hidden" name="action" value="%%portallogin_action%%" /></td>
      <td><input type="submit" name="Submit" value="%%login%%" /></td>
    </tr>
  </table>
</form>
</portallogin_loginform>

<portallogin_status>
Logged in as: %%username%%<br />
%%logoutlink%%
</portallogin_status>