{ if $not_ready }
<div style='color: #4D69A2; font-weight: bold;'>
	Metrics to be installed
</div>
<table style='width: 100%;' cellspacing='0'>
	<tr>
		<td style='font-weight: bold; width: 90%;'>Displayed Name</td>
		<td style='width: 10%;'></td>
	</tr>
	{ section name=nrd loop=$not_ready }
	<tr style='background-color: { cycle values="#eee,#fff" };'>
		<td>
			{$not_ready[nrd].filename}
		</td>
		<td style='text-align: center;'>
			<span class='hyperlink' onClick='show_edit_metrics_specific("{$not_ready[nrd].id}")'>install</span>
		</td>
	</tr>
	{ /section }
</table>
<br>
{ /if }
{ if $ready}
<div style='color: #4D69A2; font-weight: bold;'>
	Installed metrics
</div>
<table style='width: 100%;' cellspacing='0'>
	<tr>
		<td style='font-weight: bold; width: 50%;'>Description</td>
		<td style='font-weight: bold; width: 10%;'>Type</td>
		<td style='width: 10%;'></td>
	</tr>
	{ section name=rdy loop=$ready }
	<tr style='background-color: { cycle values="#eee,#fff" };'>
		<td>
			{$ready[rdy].desc}
		</td>
		<td>
			{$ready[rdy].type}
		</td>
		<td style='text-align: center;'>
			<span class='hyperlink' onClick='show_edit_metrics_specific("{$ready[rdy].id}")'>edit</span>
		</td>
	</tr>
	{ /section }
</table>
{ /if }
