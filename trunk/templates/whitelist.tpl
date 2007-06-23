<div>
<input type='hidden' id='viewing_current_user' value=''>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div style='float: left; width: 90%; clear: left; margin-top: 10px;'>
		<span style='font-weight: bold;'>Modify List</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_whitelist_action("whitelist_add");'>
				add entry
			</div>
			<div class='sidechoice padded' onClick='show_whitelist_action("whitelist_copy");'>
				copy entry
			</div>
			<div class='sidechoice padded' onClick='show_whitelist_action("whitelist_rename");'>
				rename entry
			</div>
		</div>
		{ include file='whitelist_operations.tpl' }
	</div>
</td>
<td style='width: 80%; vertical-align: top;'>
<div style='height: 100%; margin: 10px 0px 10px 0px; border: 1px solid #fff; vertical-align: top;'>
	<table style='width: 100%; margin-bottom: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-top-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-top-middle'>&nbsp;</td>
			<td class='gray-top-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 50%; border-right: 1px solid #ccc; vertical-align: top;'>
				<div id='userlist' style='height: 400px; overflow: auto'>
					{ include file='whitelist_users.tpl' }
				</div>
			</td>
			<td id='wlentry_td' style='width: 50%; vertical-align: top; text-align: center;'>
				<div id='whitelist' style='color: #999; text-align: center; height: 400px; overflow: auto;'>
					click a username to display their whitelist
				</div>
			</td>
		</tr>
	</table>
	<table style='width: 100%; margin-top: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-bottom-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-bottom-middle'>&nbsp;</td>
			<td class='gray-bottom-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
</div>
</td>
</tr>
</table>
</div>
