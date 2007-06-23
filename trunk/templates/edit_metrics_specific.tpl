<table width='95%'>
	<input type='hidden' name='metric_id' value='{$metric_id}'>
	<tr>
		<td>filename</td>
		<td colspan='2'>
			<input type='text' class='input_txt' style='width: 95%;' value='{$filename}' disabled='disabled'>
		</td>
	</tr>
	<tr>
		<td>description</td>
		<td colspan='2'>
			<input type='text' style='width: 95%;' id='description' name='description' class='input_txt' value='{$description}' maxlength='255'>
		</td>
	</tr>
	<tr>
		<td>metric type</td>
		{ if $type == "graph" }
		<td style='text-align: center;'>
			<input type='radio' name='metric_type' value='graph' checked='checked'>graph
		</td>
		<td style='text-align: center;'>
			<input type='radio' name='metric_type' value='report'>report
		</td>
		{ else }
		<td style='text-align: center;'>
			<input type='radio' name='metric_type' value='graph'>graph
		</td>
		<td style='text-align: center;'>
			<input type='radio' name='metric_type' value='report' checked='checked'>report
		</td>
		{ /if }
	</tr>
</table>
<br>
<table style='width: 100%;'>
	<tr>
		<td style='text-align: center;'>
			<input type='button' class='input_btn' value='Save' onClick='do_edit_metrics_specific()'>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type='button' class='input_btn' value='Cancel' onClick='show_edit_metrics()'>
		</td>
	</tr>
</table>
<br>
