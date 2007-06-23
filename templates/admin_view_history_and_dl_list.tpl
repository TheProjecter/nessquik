{ if $scans }
<table style='width: 100%; text-align: center;' cellspacing='0'>
	<tr>
		<td style='font-weight: bold;'>Username</td>
		<td style='font-weight: bold;'>Scans not ready to run</td>
		<td style='font-weight: bold;'>Scans pending</td>
		<td style='font-weight: bold;'>Scans running</td>
		<td style='font-weight: bold;'>Scans finished</td>
		<td></td>
	</tr>
	{ section name=scn loop=$scans }
	<tr style='background-color: { cycle values="#eee,#fff" };'>
		<td>
			<span class='surflinks' style='padding: 0px;' onClick='show_scan_history("{$scans[scn].user}");'>{$scans[scn].user}</span>
		</td>
		<td style='color: #6D84B4;'>
			{$scans[scn].not_ready}
		</td>
		<td style='color: #6D84B4;'>
			{$scans[scn].pending}
		</td>
		<td style='color: #6D84B4;'>
			{$scans[scn].running}
		</td>
		<td style='color: #6D84B4;'>
			{$scans[scn].finished}
		</td>
	</tr>
	{ /section }
</table>
<br>
{ else }
<center>no scans were found</center><br>
{ /if }
<br>
