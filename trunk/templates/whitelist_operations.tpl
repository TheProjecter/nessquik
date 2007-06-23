<div id='whitelist_add'>
	<span style='font-weight: bold;'>Add Entry</span>
	<table style='width: 100%;'>
		<tr>
			<td class='wlactions' style='text-align: center;'>
				<input type='text' name='add_username' id='add_username' class='input_txt' value='user' style='color: #999;' onFocus='first_clear("add_username");' onBlur='change_back("add_username");'>
			</td>
		</tr>
		<tr>
			<td class='wlactions' style='text-align: center;'>
				<input type='text' name='add_item' id='add_item' class='input_txt' value='ip or host or range' style='color: #999;' onFocus='first_clear("add_item");' onBlur='change_back("add_item");'>
			</td>
		</tr>
		<tr>
			<td class='wlactions' style='text-align: center;' colspan='2'>
				<input type='button' value='add' class='input_btn' onClick='do_adduser()'>
			</td>
		</tr>
	</table>
</div>

<div id='whitelist_copy'>
	<span style='font-weight: bold;'>Copy Entry</span>
	<table style='width: 100%;'>
		<tr>
			<td class='wlactions' style='width: 50%;'>
				<input type='text' name='copy_from_user' id='copy_from_user' class='input_txt' value='copy from' style='color: #999;' onFocus='first_clear("copy_from_user");' onBlur='change_back("copy_from_user");'>
			</td>
		</tr>
		<tr>
			<td class='wlactions' style='width: 50%;'>
				<input type='text' name='copy_to_user' id='copy_to_user' class='input_txt' value='copy to' style='color: #999;' onFocus='first_clear("copy_to_user");' onBlur='change_back("copy_to_user");'>
			</td>
		</tr>
		<tr>
			<td style='text-align: center;' colspan='2'>
				<input type='button' value='copy' class='input_btn' onClick='do_copy_user()'>
			</td>
		</tr>
	</table>
</div>

<div id='whitelist_rename'>
	<span style='font-weight: bold;'>Rename Entry</span>
	<table style='width: 100%;'>
		<tr>
			<td class='wlactions' style='width: 50%;'>
				<input type='text' name='mv_from_user' id='mv_from_user' class='input_txt' value='old name' style='color: #999;' onFocus='first_clear("mv_from_user");' onBlur='change_back("mv_from_user");'>
			</td>
		</tr>
		<tr>
			<td class='wlactions' style='width: 50%;'>
				<input type='text' name='mv_to_user' id='mv_to_user' class='input_txt' value='new name' style='color: #999;' onFocus='first_clear("mv_to_user");' onBlur='change_back("mv_to_user");'>
			</td>
		</tr>
		<tr>
			<td style='text-align: center;' colspan='2'>
				<input type='button' value='save' class='input_btn' onClick='do_rename_user()'>
			</td>
		</tr>
	</table>
</div>

{literal}
<script type='text/javascript'>
	Element.hide('whitelist_add');
	Element.hide('whitelist_copy');
	Element.hide('whitelist_rename');
</script>
{/literal}
