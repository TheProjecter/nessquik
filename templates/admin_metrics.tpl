<div>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div>
		<input type='hidden' name='metric_id' id='metric_id' value='' />
		<input type='hidden' name='begin_time_range' id='calendar_bt' value='{$month_input}' />
		<input type='hidden' name='end_time_range' id='calendar_et' value='{$month_input}' />

		<input type='hidden' id='cal_month_val_bt' value='{$month}' />
		<input type='hidden' id='cal_day_val_bt' value='{$day}' />
		<input type='hidden' id='cal_year_val_bt' value='{$start_year}' />
		<input type='hidden' id='cal_hour_val_bt' value='{$hour}' />
		<input type='hidden' id='cal_minute_val_bt' value='{$minute}' />
		<input type='hidden' id='cal_ampm_val_bt' value='{$ampm}' />

		<input type='hidden' id='cal_month_val_et' value='{$month}' />
		<input type='hidden' id='cal_day_val_et' value='{$day}' />
		<input type='hidden' id='cal_year_val_et' value='{$end_year}' />
		<input type='hidden' id='cal_hour_val_et' value='{$hour}' />
		<input type='hidden' id='cal_minute_val_et' value='{$minute}' />
		<input type='hidden' id='cal_ampm_val_et' value='{$ampm}' />
	</div>

	<div style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Graphs
		</span>
		<div id='graph_categories' class='bullet_list'></div>
	</div>
	<div style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Reports
		</span>
		<div id='report_categories' class='bullet_list'></div>
	</div>
	<div style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Time Range
		</span>
		<div class='bullet_list'>
			<div class='padded'>
				<span style='font-weight: bold;'>From</span>
				<div style='border: 1px solid #999; width: 100%; height: 21px; text-align: center;'>
					<span id='cal_month_bt' style='cursor: pointer;'>{$month}</span> 
					<span id='cal_day_bt' style='cursor: pointer;'>{$day}</span>, 
					<span id='cal_year_bt' style='cursor: pointer;'>{$start_year}</span> at
					<span id='cal_hour_bt' style='cursor: pointer;'>{$hour}</span>:<span id='cal_minute_bt' style='cursor: pointer;'>{$minute}</span>
					<span id='cal_ampm_bt' style='cursor: pointer;'>{$ampm}</span>
				</div>
			</div>
		</div>
		<div class='bullet_list'>
			<div class='padded'>
				<span style='font-weight: bold;'>To</span>
				<div style='border: 1px solid #999; width: 100%; height: 21px; text-align: center;'>
					<span id='cal_month_et' style='cursor: pointer;'>{$month}</span> 
					<span id='cal_day_et' style='cursor: pointer;'>{$day}</span>, 
					<span id='cal_year_et' style='cursor: pointer;'>{$end_year}</span> at
					<span id='cal_hour_et' style='cursor: pointer;'>{$hour}</span>:<span id='cal_minute_et' style='cursor: pointer;'>{$minute}</span>
					<span id='cal_ampm_et' style='cursor: pointer;'>{$ampm}</span>
				</div>
			</div>
		</div>
	</div>
</td>
<td style='width: 80%; vertical-align: top;'>
<div style='height: 100%; margin: 10px 0px 10px 0px; border: 1px solid #fff; vertical-align: top;'>
	<table style='width: 100%; margin-bottom: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-top-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-top-middle'>&nbsp;</td>
			<td class='gray-top-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
	<div id='workbox'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: top;'>
				<div id='work_container2'></div>
			</td>
		</tr>
	</table>
	</div>

	<div id='graph_box'></div>

	<div id='welcome_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: middle; height: 400px;' align='center'>
				<div id='metrics_welcome' style='padding-left: 10px; text-align: left; width: 60%;'>
					<div style='color: #4D69A2; font-weight: bold; text-align: center;'>
						to the left are the available metrics
					</div>
					<ul>
					<li style='padding-bottom: 10px;'>view the graphs by choosing a link to the left
					<li style='padding-bottom: 10px;'>graphs and reports can be narrowed in scope after choosing them
					<li style='padding-bottom: 10px;'>use the time range to limit the amount of data in a metric
					</ul>
					{ if $new_metrics }
					<div style='color: red; font-weight: bold; text-align: center;'>
						There are new metrics available. They have been added to nessquik.
					</div>
					{ /if }
				</div>
			</td>
		</tr>
	</table>
	</div>


	<table style='width: 100%; margin-top: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-bottom-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-bottom-middle'>&nbsp;</td>
			<td class='gray-bottom-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
</div>
</td>
</tr>
</table>
</div>

{literal}
<script type='text/javascript'>
	show_graph_categories();
	show_report_categories();

	Element.hide('work_container2');
	Element.hide('graph_box');

	Event.observe('cal_month_bt',	'click', function(event) { begin_cal.cal_month_changer(event); },false);
	Event.observe('cal_day_bt',	'click', function(event) { begin_cal.cal_day_changer(event); },false);
	Event.observe('cal_year_bt',	'click', function(event) { begin_cal.cal_year_changer(event); },false);
	Event.observe('cal_hour_bt',	'click', function(event) { begin_cal.cal_hour_changer(event); },false);
	Event.observe('cal_minute_bt',	'click', function(event) { begin_cal.cal_minute_changer(event); },false);
	Event.observe('cal_ampm_bt',	'click', function(event) { begin_cal.cal_ampm_changer(event); },false);

	Event.observe('cal_month_et',	'click', function(event) { end_cal.cal_month_changer(event); },false);
	Event.observe('cal_day_et',	'click', function(event) { end_cal.cal_day_changer(event); },false);
	Event.observe('cal_year_et',	'click', function(event) { end_cal.cal_year_changer(event); },false);
	Event.observe('cal_hour_et',	'click', function(event) { end_cal.cal_hour_changer(event); },false);
	Event.observe('cal_minute_et',	'click', function(event) { end_cal.cal_minute_changer(event); },false);
	Event.observe('cal_ampm_et',	'click', function(event) { end_cal.cal_ampm_changer(event); },false);
</script>
{/literal}
