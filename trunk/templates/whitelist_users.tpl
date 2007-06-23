{ if $users }
<table width='100%' cellspacing='0'>
{ section name=usr loop=$users }
	<tr>
		<td style='width: 10%; text-align: center;'>
			<img src='images/delete.png' class='hyperlink' onClick='do_delete_whitelist_user("{$users[usr].username}")' alt='Delete user from whitelist' title='Delete user from whitelist'>
		</td>
		<td>
			<div>
				<span class='surflinks' style='padding-left: 0px;' onClick='user_entries("{$users[usr].username}")'>{$users[usr].username}</span>
			</div>
		</td>
	</tr>
{ /section }
</table>
{ else }
<center>No whitelist entries exist</center>
<br>
{ /if }
