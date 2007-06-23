<div>
	<input type='hidden' id='current_viewed_plugin_profile' value=''>
	<input type='hidden' id='current_viewed_group' value=''>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div id='scan_choices_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Scanners
		</span>
		<div class='bullet_list' id='scan_choices'>
			<div class='sidechoice padded' onClick='show_add_scanner();'>
				add
			</div>
			<div class='sidechoice padded' onClick='show_scanners();'>
				change
			</div>
		</div>
	</div>
	<div id='configuration_choices' style='width: 90%; margin-top: 10px; overflow: hidden;'>
		<span style='font-weight: bold;'>
			Special Plugin Profiles
		</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_add_special_plugin_profile();'>
				add
			</div>
			<div class='sidechoice padded' onClick='show_plugin_profiles();'>
				change
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
			<td style='width: 100%; vertical-align: top;' align='center'>
				<div id='scans_welcome' style='padding-left: 10px; text-align: left; width: 60%; height: 320px;'>
					<table style='width: 100%; height: 100%; vertical-align: middle;'>
						<tr>
							<td>
					<div style='color: #4D69A2; font-weight: bold; text-align: center;'>
						to the left are categories of settings
					</div>
					<ul>
						<li style='padding-bottom: 10px;'>special profiles can be created to bundle sets of plugins into groups
						<li style='padding-bottom: 10px;'>add new scanners so that users can schedule scans from different locations
						<li style='padding-bottom: 10px;'>general settings affect the interface of nessquik as others will see it
					</ul>
							</td>
						</tr>
					</table>
				</div>
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

<script type='text/javascript'>
	Element.hide('workbox');
</script>
