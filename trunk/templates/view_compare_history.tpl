{ if $section == "main" }
	{ if $results }
	<table style='width: 100%; text-align: left;'>
	{ section name=res loop=$results }
		<tr>
			<td style='width: 10%; text-align: center;'>
				<span style='cursor: pointer;'>
					<img src='images/info.png' title='Compare the results associated with this scan profile'>
				</span>
			</td>
			<td style='width: 90%;'>
				<span class='surflinks' style='padding-left: 5px;' onClick='get_scan_results_list_for_compare("{$results[res].id}", "{$results[res].name}");'>
					scan results for {$results[res].name}
				</span>
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
{ elseif $section == "specific" }
	<table style='width: 100%;'>
		<tr>
			<td style='font-weight: bold;'></td>
			<td style='font-weight: bold;'>Saved on</td>
			<td style='font-weight: bold;'></td>
			<td style='font-weight: bold;'></td>
		</tr>
	{ section name=res loop=$results }
		<tr>
			<td style='width: 10%; text-align: center;'>
				<input type='radio' name='compare_value_{$compare_step}' value='{$results[res].id}'>
			</td>
			<td style='width: 90%;'>
				<span>{$results[res].saved_on}</span>
			</td>
		</tr>
	{ /section }
	</table>
{ /if }
