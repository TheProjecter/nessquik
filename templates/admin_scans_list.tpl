{ if $type == "pending" }
	{ if $scans }
	<table style='width: 100%; padding: 10px;'>
		<tr>
			<td style='width: 10%; text-align: center;'>&nbsp;</td>
			<td style='width: 10%; font-weight: bold;'>
				Username
			</td>
			<td style='width: 40%; font-weight: bold;'>
				Scan Name
			</td>
			<td style='width: 40%; font-weight: bold;'>
				When the scan was scheduled
			</td>
		</tr>
	{ section name=ps loop=$scans }
		<tr>
			<td style='text-align: center; vertical-align: top;'>
				<span style='cursor: pointer;'>
					<img src='images/stop.png' onClick="cancel_scan_profile('{$scans[ps].profile_id}','pending');" title='Cancel this pending scan' alt='Cancel this pending scan'>
				</span>
			</td>
			<td style='vertical-align: top;'>
				{$scans[ps].user}
			</td>
			<td style='vertical-align: top;'>
				{$scans[ps].name}
			</td>
			<td style='vertical-align: top;'>
				{$scans[ps].scheduled}
			</td>
		</tr>
	{ /section }
	</table>
	{ else }
		There are no scans scheduled to be run
	{ /if }
{ elseif $type == "notready" }
	{ if $scans }
	<table style='width: 100%; padding: 10px;'>
		<tr>
			<td style='width: 5%;'>&nbsp;</td>
			<td style='width: 5%;'>&nbsp;</td>
			<td style='width: 10%; font-weight: bold;'>
				Username
			</td>
			<td style='width: 40%; font-weight: bold;'>
				Scan Name
			</td>
			<td style='width: 40%; font-weight: bold;'>
				When the scan was scheduled
			</td>
		</tr>
	{ section name=ns loop=$scans }
		<tr>
			<td style='text-align: center; vertical-align: top;'>
				<span style='cursor: pointer;'>
					<img src='images/delete.png' onClick="remove_scan_profile('{$scans[ns].profile_id}','notready');" title='Delete this scan' alt='Delete this scan'>
				</span>
			</td>
			<td style='text-align: center; vertical-align: top;'>
				{ if $scans[ns].scanner }
				<span style='cursor: pointer;'>
					<img src='images/add.png' onClick="reschedule_scan('{$scans[ns].profile_id}');" title='Schedule this scan' alt='Schedule this scan'>
				</span>
				{ /if }
			</td>
			<td style='vertical-align: top;'>
				{$scans[ns].user}
			</td>
			<td style='vertical-align: top;'>
				{$scans[ns].name}
			</td>
			<td style='vertical-align: top;'>
				{$scans[ns].scheduled}
			</td>
		</tr>
	{ /section }
	</table>
	{ else }
		All your scans are either pending, running or finished.
	{ /if }
{ elseif $type == "running" }
	{ if $scans }
	<table style='width: 100%; padding: 10px;'>
		<tr>
			<td style='width: 10%;'>&nbsp;</td>
			<td style='width: 10%; font-weight: bold;'>
				Username
			</td>
			<td style='width: 40%; font-weight: bold;'>
				Scan Name
			</td>
			<td style='width: 25%; font-weight: bold;'>
				When the scan was scheduled
			</td>
			<td style='width: 25%; font-weight: bold;'>
				Scan Progress
			</td>
		</tr>
	{ section name=rs loop=$scans }
		<tr>
			<td style='text-align: center; vertical-align: top;'>
				<span style='cursor: pointer;'>
					<img src='images/stop.png' onClick="cancel_scan_profile('{$scans[rs].profile_id}','running');" title='Cancel this running scan' alt='Cancel this running scan'>
				</span>
			</td>
			<td style='vertical-align: top;'>
				{$scans[rs].user}
			</td>
			<td style='vertical-align: top;'>
				{$scans[rs].name}
			</td>
			<td style='vertical-align: top;'>
				{$scans[rs].scheduled}
			</td>
			<td style='vertical-align: top;'>
				<div style='width: 100%; position: relative; background-color: #000;'>
					<div class='rscan_progress' style='width: {$scans[rs].progress}%;'></div>
					<div class='rscan_difference' style='width: {$scans[rs].difference}%; left: {$scans[rs].progress}%;'></div>
					<div class='rscan_prog_text'>{$scans[rs].progress_text}</div>
				</div>
			</td>
		</tr>
	{ /section }
	</table>
	{ else }
		There are no scans currently running
	{ /if }
{ elseif $type == "finished" }
	{ if $scans }
	<table style='width: 100%; padding: 10px;'>
		<tr>
			<td style='width: 5%;'>&nbsp;</td>
			<td style='width: 5%;'>&nbsp;</td>
			<td style='width: 10%; font-weight: bold;'>
				Username
			</td>
			<td style='width: 40%; font-weight: bold;'>
				Scan Name
			</td>
			<td style='width: 40%; font-weight: bold;'>
				When the scan finished
			</td>
		</tr>
	{ section name=fs loop=$scans }
		<tr>
			<td style='text-align: center; vertical-align: top;'>
				<span style='cursor: pointer;'>
					<img src='images/delete.png' onClick="remove_scan_profile('{$scans[fs].profile_id}','finished');" title='Remove these scan results' alt='Remove these scan results'>
				</span>
			</td>
			<td style='text-align: center; vertical-align: top;'>
				{ if $scans[fs].scanner }
				<span style='cursor: pointer;'>
					<img src='images/add.png' onClick="reschedule_scan('{$scans[fs].profile_id}');" title='Reschedule this scan' alt='Reschedule this scan'>
				</span>
				{ /if }
			</td>
			<td style='vertical-align: top;'>
				{$scans[fs].user}
			</td>
			<td style='vertical-align: top;'>
				{$scans[fs].name}
			</td>
			<td style='vertical-align: top;'>
				{$scans[fs].finished}
			</td>
		</tr>
	{ /section }
	</table>
	{ else }
		There are no finished scans
	{ /if }
{ /if }
