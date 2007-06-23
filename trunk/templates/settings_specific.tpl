<div id='settings_chooser' style='padding-left: 10px; padding-right: 10px; height: 500px; overflow: auto;'>
	<div style='color: #4D69A2; font-weight: bold;'>
		Available Saved Scans
	</div>
	<table style='width: 100%;'>
		{ section name=ss loop=$scans }
			{ if $scans[ss].status == 'P' }
			<tr>
				<td style='vertical-align: top; width: 10%; text-align: center;'>
					<span style='cursor: pointer;'>
						<img src='images/stop.png' onClick="cancel_scan_profile('{$scans[ss].profile_id}','saved_pending_scan');" title='Cancel this pending scan' alt='Cancel this pending scan'>
					</span>
				</td>
				<td style='vertical-align: top; width: 90%;'>
					<span style='padding: 5px;'>{$scans[ss].name} is pending to be run</span>
				</td>
			</tr>
			{ elseif $scans[ss].status == 'R' }
			<tr>
				<td style='vertical-align: top; width: 10%; text-align: center;'>
					<span style='cursor: pointer;'>
						<img src='images/stop.png' onClick="cancel_scan_profile('{$scans[ss].profile_id}','saved_running_scan');" title='Cancel this running scan' alt='Cancel this running scan'>
					</span>
				</td>
				<td style='vertical-align: top; width: 90%;'>
					<span style='padding: 5px;'>{$scans[ss].name} is currently running</span>
				</td>
			</tr>
			{ else }
			<tr>
				<td style='vertical-align: top; width: 10%; text-align: center;'>
					<span style='cursor: pointer;'>
						<img src='images/delete.png' onClick="remove_scan_profile('{$scans[ss].profile_id}','saved_not_running');" title='Remove this scan profile' alt='Remove this scan profile'>
					</span>
				</td>
				<td style='vertical-align: top; width: 90%;'>
					<span class='surflinks' id='{$scans[ss].profile_id}' onClick='do_get_specific_scan_settings("{$scans[ss].profile_id}");' title='This scan is not scheduled to be run'>{$scans[ss].name}</span>
				</td>
			</tr>
			{ /if }
		{ /section }
	</table>
	</ul>
</div>

<div id='specific_scan_settings'></div>

<script type='text/javascript'>
	Element.hide('specific_scan_settings');
</script>
