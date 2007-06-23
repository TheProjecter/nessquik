{ if $profiles }
<table width='100%' cellspacing='0'>
	<tr>
		<td style='width: 10%;'></td>
		<td style='width: 90%; font-weight: bold;'>
			profile name
		</td>
	</tr>
{ section name=pro loop=$profiles }
	<tr>
		<td style='width: 10%; text-align: center;'>
			<img src='images/delete.png' class='hyperlink' onClick='do_delete_plugin_profile("{$profiles[pro].id}")' alt='Delete special plugin profile {$profiles[pro].name}' title='Delete special plugin profile {$profiles[pro].name}'>
		</td>
		<td>
			<div>
				<span class='surflinks' style='padding: 0px;' onClick='show_edit_special_plugin_profile("{$profiles[pro].id}")'>{$profiles[pro].name}</span>
			</div>
		</td>
	</tr>
{ /section }
</table>
{ else }
<center>No special plugin profiles were found</center>
<br>
{ /if }
