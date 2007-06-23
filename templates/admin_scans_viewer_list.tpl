{ if $scans }
<table style='width: 100%; text-align: center;' cellspacing='0'>
	<tr>
		<td style='font-weight: bold;'>Username</td>
		<td style='font-weight: bold;'>Date scheduled</td>
		<td style='font-weight: bold;'>Date last finished</td>
		<td style='font-weight: bold;'>Status</td>
	</tr>
	{ section name=scn loop=$scans }
	<tr style='background-color: { cycle values="#eee,#fff" };'>
		<td>
			{$scans[scn].user}
		</td>
		<td>
			{$scans[scn].scheduled}
		</td>
		<td>
			{$scans[scn].finished}
		</td>
		<td>
			{ if $scans[scn].status == "R" }
				<span style='color: green;'>running</span>
			{ elseif $scans[scn].status == "P" }
				<span style='color: #cc0;'>pending</span>
			{ elseif $scans[scn].status == "F" }
				<span style='color: blue;'>finished</span>
			{ else if $scans[scn].status == "N" }
				<span style='color: #366;'>not ready</span>
			{ /if }
		</td>
	</tr>
	{ /section }
</table>
{ else }
<center>no scans were found</center>
{ /if }
<br>
