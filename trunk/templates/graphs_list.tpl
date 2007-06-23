{ if $graphs }
<table style='width: 100%;'>
	<tr>
		<td style='width: 10%;'></td>
		<td style='font-weight: bold; text-align: center'>
			Graph Title
		</td>
		<td style='font-weight: bold; text-align: center'>
			Creation Date
		</td>
		<td style='font-weight: bold; text-align: center'>
			Date last viewed
		</td>
		<td style='font-weight: bold; text-align: center'>
			View
		</td>
	</tr>
{ section name=gph loop=$graphs }
	<tr style='background-color: { cycle values="#eee,#fff" };'>
		<td style='width: 10%;'>
			<div style='cursor: pointer; width: 100%; text-align: center;'>
				<img src='images/delete.png' onClick='do_delete_graph("{$graphs[gph].id}");' alt='Delete this graph' title='Delete this graph'>
			</div>
		</td>
		<td style='text-align: left; width: 30%;'>
			{$graphs[gph].title}
		</td>
		<td style='text-align: left; width: 25%;'>
			{$graphs[gph].created}
		</td>
		<td style='text-align: left; width: 25%;'>
			{$graphs[gph].last_view}
		</td>
		<td style='width: 10%; text-align: center;'>
			{ if $graphs[gph].type == "bar" }
			<span class='surflinks' style='padding: 0px;' onClick='show_graph("{$graphs[gph].id}", "0");'>
				<img src='images/bar_graph_small.png' height='16' width='33' alt='bar' title='Bar Graph'>
			</span>
			{ elseif $graphs[gph].type == "line" }
			<img src='images/line_graph_small.png' height='16' width='36' alt='line' title='Line Graph'>
			{ elseif $graphs[gph].type == "pie" }
			<img src='images/pie_chart_small.png' height='16' width='33' alt='pie' title='Pie Chart'>
			{ /if }
		</td>
	</tr>
{ /section }
</table>
<br>
{ else }
<center>No graphs currently exist</center>
<br>
{ /if }
