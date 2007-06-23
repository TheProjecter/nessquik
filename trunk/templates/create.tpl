<input type='hidden' value='{$tmp_profile_id}' id='tmp_profile_id' />
<input type='hidden' value='0' id='device_counter' />
<input type='hidden' value='0' id='plugin_counter' />

<div>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div id='scan_choices_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold; cursor: pointer;' onClick='toggle_scan_choices();'>
			Scan Choices
		</span>
		<div class='bullet_list' id='scan_choices'>
			{ if $HAS_REGISTERED_COMPS }
			<div class='sidechoice padded' onClick='show_input("computer");'>
				registered computers
			</div>
			{ /if }
			{ if $HAS_WHITELIST }
			<div class='sidechoice padded' onClick='show_input("whitelist");'>
				whitelist entries
			</div>
			{ /if }
			{ if $HAS_SAVED_SCANS }
			<div class='sidechoice padded' onClick='show_input("saved");'>
				saved scans
			</div>
			{ /if }
			{ if $vhosts }
			<div class='sidechoice padded' onClick='show_input("vhosts");'>
				virtual hosts
			</div>
			{ /if }
			<div class='sidechoice padded' onClick='show_input("list");'>
				a list of computers
			</div>
			{ if $HAS_CLUSTERS }
			<div class='sidechoice padded' onClick='show_input("cluster");'>
				a cluster of computers
			</div>
			{ /if }
		</div>
	</div>
	<div id='plugins_menu' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold; cursor: pointer;' onClick='toggle_plugin_choices();'>
			Plugins
		</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_list("family");'>
				by family
			</div>
			<div class='sidechoice padded' onClick='show_list("severity");'>
				by severity
			</div>
			{ if $HAS_SPECIAL_PLUGINS }
			<div class='sidechoice padded' onClick='show_list("special");'>
				special plugins
			</div>
			{ /if }
			<div class='sidechoice padded' onClick='show_list("all");'>
				all plugins
			</div>
			<br>
			<input id='search_plugin' class='search_plugin search_plugin_padding input_txt' type='text' value='search' onclick='first_clear("search_plugin");' onBlur='change_back("search_plugin");' style='width: 90%;'>
		</div>
	</div>
	<div id='configure_menu' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold; cursor: pointer;' onClick='toggle_setting_choices();'>
			Configure
		</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='toggle_setting_choices();'>
				scan settings
			</div>
		</div>
	</div>
	<div style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>Finish</span>
		<div class='bullet_list'>
			{ if $scanners_count > 0 }
			<div class='sidechoice padded' onClick='quick_validate();'>
			{ else }
			<div class='sidechoice padded' style='color: #999;'>
			{ /if }
				schedule scan
			</div>
		</div>
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
	<div id='workbox'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: top;'>
				<div id='work_container2'></div>
			</td>
		</tr>
	</table>
	</div>

	<div id='devices_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 50%; vertical-align: top; border-right: 1px solid #ccc; height: 470px;'>
				<div id='available_devices_text' style='width: 100%; text-align: right; margin-bottom: 10px;'>
					<span style='color: #999;'>available devices</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='waiting' style='width: 100%; text-align: center;'>
					<img src='images/spinner.gif' alt='spinner'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Please wait while we fetch your info
				</div>
				<div id='by_computer' class='by_computer'></div>
				<div id='by_list' class='by_list' style='vertical-align: top;'>
					<textarea name='list' id='list' rows='5' cols='5' style='height: 430px;' onFocus='first_clear("list");' onBlur='change_back("list");'>click here to enter a list of computers</textarea>
				</div>
				<div id='by_cluster' class='by_cluster'></div>
				<div id='by_whitelist' class='by_whitelist'></div>
				<div id='by_saved' class='by_saved'></div>
			</td>
			<td style='width: 50%; vertical-align: top;'>
				<div id='selected_devices_text' style='width: 100%; text-align: right; margin-bottom: 10px;'>
					<span style='color: #999;'>selected devices</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='selected_devices'></div>
			</td>
		</tr>
	</table>
	</div>

	<div id='plugins_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 50%; vertical-align: top; border-right: 1px solid #ccc; height: 470px;'>
				<div id='list_of_plugins_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>available plugins</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='list_of_plugins'></div>
			</td>
			<td style='width: 50%; vertical-align: top;'>
				<div id='selected_plugins_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>selected plugins</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='selected_plugins'></div>
			</td>
		</tr>
	</table>
	</div>
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

{literal}
<script type='text/javascript'>
	var tmp_profile_id = $F('tmp_profile_id');
	var params = "action=x_do_get_specific_scan_settings&profile_id="+tmp_profile_id;
	var url = "async/settings.php";

	Element.hide('by_computer');
	Element.hide('by_cluster');
	Element.hide('by_whitelist');
	Element.hide('by_saved');
	Element.hide('waiting');

	Element.hide('workbox');
	Element.hide('plugins_box');

        new Ajax.Updater(
                {success: 'work_container2'},
                url,
                {
                        method: 'post',
                        parameters: params,
                        evalScripts: true
                });


	new Form.Element.DelayedObserver(
		"search_plugin", 
		0.5,
		search_for_plugin
	);
</script>
{/literal}
