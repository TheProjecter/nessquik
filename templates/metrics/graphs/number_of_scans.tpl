<input type='hidden' id='sub_type' name='sub_type' value='{$sub_type}'>
<input type='hidden' id='scan_status' name='scan_status' value='{$scan_status}'>

<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
	<tr>
		<td>
			Show me a graph of the
			&nbsp;&nbsp;&nbsp;
			<select class='input_select' onChange='set_sub_type(this.options[this.selectedIndex].value)'>
				{ section name=sgt loop=$sub_types_arr }
				<option value='{$sub_types_arr[sgt].val}'>{$sub_types_arr[sgt].name}
				{ /section }
			</select>
			&nbsp;&nbsp;&nbsp;
			with  
			&nbsp;&nbsp;&nbsp;
			<select class='input_select' onChange='set_scan_status(this.options[this.selectedIndex].value);'>
				<option value='all'>all
				{ section name=scn loop=$scan_status_arr }
				<option value='{$scan_status_arr[scn].val}'>{$scan_status_arr[scn].name}
				{ /section }
			</select>
			&nbsp;&nbsp;&nbsp;
			status
		</td>
	</tr>
</table>
<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;' align='center'>
	<tr>
		<td>
			<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
				<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
			</div>
			<br>
		</td>
	</tr>
</table>
<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;' align='center'>
	<tr>
		<td align='center'>
			<img id='the_graph' src='async/admin_metrics.php?action=view_metric&metric_id={$metric_id}&sub_type={$sub_type}&scan_status={$scan_status}&begin={$begin}&end={$end}'>
		</td>
	</tr>
</table>
{literal}
<script type='text/javascript'>
	set_scan_status = function(status) {
		$('scan_status').value = status;

		update_img_src();
	}

	set_sub_type = function(sub_type) {
		$('sub_type').value = sub_type;

		update_img_src();
	}
	
	update_img_src = function() {
		var metric_id 	= $F('metric_id');
		var sub_type	= $F('sub_type');
		var scan_status	= $F('scan_status');
		var begin	= $F('calendar_bt');
		var end		= $F('calendar_et');

		var new_url 	= 'async/admin_metrics.php?action=view_metric';
		new_url 	= new_url + '&metric_id='+metric_id;
		new_url		= new_url + '&sub_type='+sub_type;
		new_url		= new_url + '&scan_status='+scan_status;
		new_url		= new_url + '&begin='+begin;
		new_url		= new_url + '&end='+end;

		$('the_graph').src = new_url;
	}
</script>
{/literal}
