{ if $section == "general" }
	{ if $results }
	<table style='width: 100%;'>
	{ section name=res loop=$results }
		<tr>
			<td style='width: 10%; text-align: center; vertical-align: top;'>
				<span style='cursor: pointer;'>
					<img src='images/delete.png' onClick="remove_all_scan_results('{$results[res].id}');" title='Remove all scan results associated with this profile'>
				</span>
			</td>
			<td style='width: 90%; vertical-align: top;'>
				<span class='surflinks' style='padding: 0px;' onClick='get_scan_results_list("{$results[res].id}");'>scan results for {$results[res].name}</span>
			</td>
		</tr>
	{ /section }
	</table>
	{ else }
	<div>
		<div>
			<table style='width: 100%; height: 400px;'>
				<tr>
					<td style='width: 100%; vertical-align: top;' align='center'>
						<div style='width: 100%;'>
							No scan results were found
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	{ /if }
	<p>
{ elseif $section == "general_admin" }
	{ if $results }
	<div id="dropmenudiv2" onMouseover="clearhidemenu();" onMouseout="dynamichide('dropmenudiv2');"></div>
	<input type='hidden' id='current_username' value='{$view_username}'>
	<span style='color: #4D69A2; font-weight: bold;'>Scans for {$view_username}</span>
	<table style='width: 100%;'>
		<tr>
			<td></td>
			<td style='font-weight: bold;'>
				Profile name
			</td>
			<td style='font-weight: bold;'>
				Download
			</td>
		</tr>
	{ section name=res loop=$results }
		<tr>
			<td style='width: 10%; text-align: center; vertical-align: top;'>
				<span style='cursor: pointer;'>
					<img src='images/delete.png' onClick="remove_scan_profile('{$results[res].id}');" title='Remove all scan results associated with this profile'>
				</span>
			</td>
			<td style='width: 60%; vertical-align: top;'>
				{ if $results[res].results }
				<span class='surflinks' style='padding: 0px;' onClick='get_scan_results_list("{$results[res].id}");'>Scan results for {$results[res].name}</span>
				{ else }
				<span>Scan results for {$results[res].name}</span>
				{ /if }
			</td>
			<td style='width: 30%; text-align: center;'>
				<a href='async/admin_scans.php?action=make_nessusrc&profile_id={$results[res].id}' class='surflinks'>nessusrc</a> |
				<a href='async/admin_scans.php?action=make_machine_list&profile_id={$results[res].id}' class='surflinks'>machine list</a>
			</td>
		</tr>
	{ /section }
	</table>
	<br>
	<script type='text/javascript'>
		Element.hide('dropmenudiv2');
	</script>
	{ else }
	<div>
		<div>
			<table style='width: 100%; height: 400px;'>
				<tr>
					<td style='width: 100%; vertical-align: top;' align='center'>
						<div style='width: 100%;'>
							No scan results were found
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<br>
	{ /if }
{ elseif $section == "specific" }
	<input type='hidden' id='profile_id' value='{$profile_id}'>
	<div id="dropmenudiv2" onMouseover="clearhidemenu();" onMouseout="dynamichide('dropmenudiv2');"></div>
	<table style='width: 100%;'>
		<tr>
			<td style='width: 100%;'>
				<span style='color: #4D69A2; font-weight: bold;'>
					Saved scan results for {$setting_name}
				</span>
			</td>
		</tr>
	</table>
	<table style='width: 100%;'>
		<tr>
			<td></td>
			<td style='font-weight: bold;'>Saved on</td>
			<td style='font-weight: bold;'></td>
			<td style='font-weight: bold;'></td>
			<td style='font-weight: bold;'></td>
		</tr>
	{ section name=res loop=$results }
		<tr>
			<td style='width: 5%; text-align: center;'>
				<span style='cursor: pointer;'>
					<img src='images/delete.png' onClick="remove_specific_scan_results('{$results[res].id}');" title='Remove these scan results'>
				</span>
			</td>
			<td style='width: 65%;'>
				<span>{$results[res].saved_on}</span>
			</td>
			<td style='width: 10%;'>
				<div style='text-align: center;'>
					<span class='surflinks' onMouseover="dropdownmenu(this, event, 'dropmenudiv', view, '{$profile_id}:{$results[res].id}');" onMouseout="dynamichide()">view</span>
				</div>
			</td>
			<td style='width: 10%;'>
				<div style='text-align: center;'>
					<span class='surflinks' onMouseover="dropdownmenu(this, event, 'dropmenudiv', save, '{$profile_id}:{$results[res].id}');" onMouseout="dynamichide()">save</span>
				</div>
			</td>
			<td style='width: 10%;'>
				<div style='text-align: center;'>
					<span class='surflinks' onMouseover="dropdownmenu(this, event, 'dropmenudiv', email, '{$profile_id}:{$results[res].id}');" onMouseout="dynamichide()">email</span>
				</div>
			</td>
		</tr>
	{ /section }
	</table>
	<p>
	<script type='text/javascript'>
		Element.hide('dropmenudiv2');
	</script>
{ /if }
