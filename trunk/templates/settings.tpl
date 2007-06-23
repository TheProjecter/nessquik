<div>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div id='configuration_choices' style='width: 90%; margin-top: 10px; overflow: hidden;'>
		<span style='font-weight: bold;'>Configuration Choices</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_user_settings("{$username}");'>
				general
			</div>
			<div class='sidechoice padded' onClick='show_per_scan_settings("{$username}");'>
				per-scan
			</div>
		</div>
	</div>
	<div id='scan_being_changed' style='font-weight: bold;'>
		<div>Changing</div>
		<div id='scan_being_changed_text' style='color: #4D69A2; font-weight: bold; text-align: center;'></div>
	</div>
	<div id='scan_choices_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold; cursor: pointer;' onClick='toggle_scan_choices();'>
			Change Devices
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
	<div id='plugins_menu_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold; cursor: pointer;' onClick='toggle_plugin_choices();'>
			Change Plugins
		</span>
		<div class='bullet_list' id='plugins_menu'>
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
			Change Settings
		</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='toggle_setting_choices();'>
				settings
			</div>
		</div>
	</div>
	<div id='save_menu_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Finish Up
		</span>
		<div class='bullet_list' id='save_menu'>
			{ if $scanners_count > 0 }
			<div class='sidechoice padded' onClick='do_save_specific_settings();'>
			{ else }
			<div class='sidechoice padded' style='color: #999;'>
			{ /if }
				save
			</div>
			<div id='settings_back_button' class='sidechoice padded' onClick='show_per_scan_settings("{$username}");'>
				cancel
			</div>
		</div>
	</div>
</td>
<td style='width: 80%; vertical-align: top;'>
<div style='height: 100%; vertical-align: top;'>
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

	<div id='welcome_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: middle; height: 400px;' align='center'>
				<div id='scans_welcome' style='padding-left: 10px; text-align: left; width: 60%;'>
					<div style='color: #4D69A2; font-weight: bold; text-align: center;'>
						to the left are categories of settings
					</div>
					<ul>
					<li style='padding-bottom: 10px;'>general settings affect all your scans and the look of the interface
					<li style='padding-bottom: 10px;'>per-scan settings allow you to change individual scans you have created
					<li style='padding-bottom: 10px;'>you can only change settings for individual scans that are not running
					<li>help is available for all settings by clicking on the setting name
					</ul>
				</div>
			</td>
		</tr>
	</table>
	</div>


	<div id='devices_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 50%; vertical-align: top; border-right: 1px solid #ccc; height: 500px;'>
				<div id='available_devices_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>available devices</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='waiting' style='width: 100%; text-align: center;'>
					<img src='images/spinner.gif' alt='spinner'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Please wait while we fetch your info
				</div>
				<div id='by_computer' class='settings_by_computer'></div>
				<div id='by_list' class='settings_by_list'>
					&nbsp;
					<textarea name='list' id='list' rows='5' cols='5' style='color: #999; text-align: center; height: 500px;' onFocus='first_clear("list");' onBlur='change_back("list");'>click here to enter a list of computers</textarea>
				</div>
				<div id='by_cluster' class='settings_by_cluster'></div>
				<div id='by_whitelist' class='settings_by_whitelist'></div>
				<div id='by_saved' class='settings_by_saved'></div>
			</td>
			<td style='width: 50%; vertical-align: top;'>
				<div id='selected_devices_text' style='width: 100%; text-align: right;'>
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
			<td style='width: 50%; vertical-align: top; border-right: 1px solid #ccc; height: 500px;'>
				<div id='list_of_plugins_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>available plugins</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='list_of_plugins' style='height: 470px;'></div>
			</td>
			<td style='width: 50%; vertical-align: top'>
				<div id='selected_plugins_text' style='width: 100%; text-align: right;'>
					<span style='color: #999;'>selected plugins</span>
					<hr class='small_hr_underline'>
				</div>
				<div id='selected_plugins' style='height: 470px;'></div>
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
	Element.hide('workbox');
	Element.hide('scan_being_changed');

	// for the scan choices sidebar
	Element.hide('scan_choices');
	Element.hide('scan_choices_container');

	// for the plugins menu sidebar
	Element.hide('plugins_menu');
	Element.hide('plugins_menu_container');

	// for the save menu side bar
	Element.hide('save_menu');
	Element.hide('save_menu_container');

	Element.hide('configure_menu');

	Element.hide('devices_box');
	Element.hide('plugins_box');

	// For the devices section
	Element.hide('by_computer');
	Element.hide('by_cluster');
	Element.hide('by_whitelist');
	Element.hide('by_saved');
	Element.hide('waiting');

	new Form.Element.DelayedObserver(
		"search_plugin", 
		0.5,
		search_for_plugin
	);
</script>
{/literal}
