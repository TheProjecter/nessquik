<input type='hidden' id='page' value='admin_settings'>
<input type='hidden' value='0' id='plugin_counter' />
<input type='hidden' value='0' id='group_counter' />

<div style='height: 100%; margin: 10px 0px 10px 0px; border: 1px solid #fff; vertical-align: top;'>
	<table style='width: 100%; padding-left: 20px;'>
		<tr>
			<td style='width: 50%;'>
				Plugin Profile Name
			</td>
			<td style='width: 50%;'>
				<span styl='padding-left: 17px;'></span>
				<input type='text' id='special_plugin_name' name='special_plugin_name' class='input_txt' style='width: 95%;' maxlength='128'>
			</td>
		</tr>
		<tr>
			<td style='width: 50%;'>
				<span onClick='show_plugins();' style='cursor: pointer;'>Plugins to include in this profile</span>
			</td>
			<td style='width: 50%;'>
				<input id='search_plugin' class='search_plugin input_txt' type='text' value='search' onClick='first_clear("search_plugin");' onKeyUp='search_for_plugin();' onBlue='change_back("search_plugin");' style='width: 95%; background: #fff;'>
			</td>
		</tr>
		<tr>
			<td style='width: 50%;'></td>
			<td style='width: 50%;'>
				or choose: 
				<span onClick='show_list_plugin_profile("family","add");' class='surflinks' style='padding: 0px;'>by family</span> |
				<span onClick='show_list_plugin_profile("severity","add");' class='surflinks' style='padding: 0px;'>by severity</span>
			</td>
		</tr>
		<tr>
			<td style='width: 50%;'>
				<span onClick='show_groups();' style='cursor: pointer;'>Groups that are allowed to use this profile</span>
			</td>
			<td style='width: 50%;'>
				<input type="text" id="autocomplete" name="autocomplete_parameter" onKeyUp='search_for_group();' class='input_txt' style='width: 95%;'/>
			</td>
		</tr>
		<tr>
			<td style='width: 50%;'></td>
			<td style='width: 50%;'>
				or just choose
				<span onClick='add_group("all","All Groups"); Element.show("groups_box"); Element.hide("plugins_box");' class='surflinks' style='padding: 0px;'>all groups</span>
			</td>
		</tr>
	</table>
	<br>

	<center>
	<table style='width: 95%; margin-bottom: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-top-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-top-middle'>&nbsp;</td>
			<td class='gray-top-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>

	<div id='groups_box' style='width: 95%;'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 50%; vertical-align: top; border-right: 1px solid #ccc; height: 350px;'>
				<div id='list_of_groups_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>available groups</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='list_of_groups' style='height: 350px;'></div>
			</td>
			<td style='width: 50%; vertical-align: top; height: 350px;'>
				<div id='selected_groups_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>selected groups</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='selected_groups' style='height: 350px;'></div>
			</td>
		</tr>
	</table>
	</div>

	<div id='plugins_box' style='width: 95%;'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 50%; vertical-align: top; border-right: 1px solid #ccc; height: 350px;'>
				<div id='list_of_plugins_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>available plugins</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='list_of_plugins' style='height: 350px;'></div>
			</td>
			<td style='width: 50%; vertical-align: top; height: 350px;'>
				<div id='selected_plugins_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>selected plugins</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='selected_plugins' style='height: 350px;'></div>
			</td>
		</tr>
	</table>
	</div>
	<table style='width: 95%; margin-top: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-bottom-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-bottom-middle'>&nbsp;</td>
			<td class='gray-bottom-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
	</center>
	<br>
	<div style='width: 100%; text-align: center;'>
		<input type='button' value='Create Plugin Profile' class='input_btn' onClick='do_add_special_plugin_profile();'>
	</div>
</div>
{ literal }
<script type='text/javascript'>
	Element.hide('groups_box');
</script>
{ /literal }
