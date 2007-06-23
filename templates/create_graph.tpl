<div id='settings_chooser' style='padding-left: 10px; padding-right: 10px;'>
	<input type='hidden' name='begin_time_range' id='calendar_bt' value='{$month_input}' />
	<input type='hidden' name='end_time_range' id='calendar_et' value='{$month_input}' />

	<input type='hidden' id='cal_month_val_bt' value='{$month}' />
	<input type='hidden' id='cal_day_val_bt' value='{$day}' />
	<input type='hidden' id='cal_year_val_bt' value='{$year}' />
	<input type='hidden' id='cal_hour_val_bt' value='{$hour}' />
	<input type='hidden' id='cal_minute_val_bt' value='{$minute}' />
	<input type='hidden' id='cal_ampm_val_bt' value='{$ampm}' />

	<input type='hidden' id='cal_month_val_et' value='{$month}' />
	<input type='hidden' id='cal_day_val_et' value='{$day}' />
	<input type='hidden' id='cal_year_val_et' value='{$year}' />
	<input type='hidden' id='cal_hour_val_et' value='{$hour}' />
	<input type='hidden' id='cal_minute_val_et' value='{$minute}' />
	<input type='hidden' id='cal_ampm_val_et' value='{$ampm}' />

	<div style='color: #4D69A2; font-weight: bold;'>
		Graph settings
	</div>
	<table style='width: 100%;'>
		<tr>
			<td align='center'>
		<table style='width: 80%;' cellspacing='5'>
			<tr>
				<td style='width: 30%; text-align: left;'>
					<span style='font-weight: bold;'>title</span>
				</td>
				<td style='width: 70%;' colspan='3'>
					<input type='text' style='width: 100%;' id='graph_title' name='graph_title' class='input_txt' maxlength='255'>
				</td>
			</tr>
			<tr>
				<td style='width: 30%; text-align: left;'>
					<span style='font-weight: bold;'>size</span>
				</td>
				<td style='width: 30%;'>
					<input type='text' id='graph_size_x' name='graph_size_x' class='input_txt' maxlength='4' style='width: 100%; text-align: center;' value='600'>
				</td>
				<td style='width: 10%; text-align: center;'>
					by
				</td>
				<td style='width: 30%;'>
					<input type='text' id='graph_size_y' name='graph_size_y' class='input_txt' maxlength='4' style='width: 100%; text-align: center;' value='400'>
				</td>
			</tr>
			<tr>
				<td style='width: 30%;'>
					<span style='font-weight: bold;'>type</span>
				</td>
				<td style='width: 30%; text-align: center;'>
					<input type='radio' name='graph_type' value='bar' checked='checked'>bar
				</td>
				<td style='width: 10%; text-align: center;'>
					<input type='radio' name='graph_type' value='line'>line
				</td>
				<td style='width: 30%; text-align: center;'>
					<input type='radio' name='graph_type' value='pie'>pie
				</td>
			</tr>
			<tr>
				<td style='width: 30%;'>
					<span style='font-weight: bold;'>time range</span>
				</td>
				<td style='width: 30%;'>
					<!-- Calendar entries -->
					<div style='border: 1px solid #999; width: 100%; height: 21px; text-align: center;'>
						<span id='cal_month_bt' style='cursor: pointer;'>{$month}</span> 
						<span id='cal_day_bt' style='cursor: pointer;'>{$day}</span>, 
						<span id='cal_year_bt' style='cursor: pointer;'>{$start_year}</span> at
						<span id='cal_hour_bt' style='cursor: pointer;'>{$hour}</span>:<span id='cal_minute_bt' style='cursor: pointer;'>{$minute}</span>
						<span id='cal_ampm_bt' style='cursor: pointer;'>{$ampm}</span>
					</div>
				</td>
				<td style='width: 10%; text-align: center;'>
					to
				</td>
				<td style='width: 30%;'>
					<div style='border: 1px solid #999; width: 100%; height: 21px; text-align: center;'>
						<span id='cal_month_et' style='cursor: pointer;'>{$month}</span> 
						<span id='cal_day_et' style='cursor: pointer;'>{$day}</span>, 
						<span id='cal_year_et' style='cursor: pointer;'>{$end_year}</span> at
						<span id='cal_hour_et' style='cursor: pointer;'>{$hour}</span>:<span id='cal_minute_et' style='cursor: pointer;'>{$minute}</span>
						<span id='cal_ampm_et' style='cursor: pointer;'>{$ampm}</span>
					</div>
				</td>
			</tr>
		</table>
			</td>
		</tr>
	</table>
	<br>
	<div style='color: #4D69A2; font-weight: bold;'>
		What to graph
	</div>
	<table style='width: 100%;'>
		<tr>
			<td align='center'>
				<table style='width: 80%;'>
					<tr>
						<td style='width: 30%;'>
							<select id='what_to_graph' name='what_to_graph' class='input_select' style='width: 100%;' onChange='show_graph_config(this.options[this.selectedIndex].value)';>
								<option value=''>:: select an item to graph ::
								{ section name=gph loop=$graphs }
								<option value='{$graphs[gph].name}'>{$graphs[gph].desc}
								{ /section }
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<div style='color: #4D69A2; font-weight: bold;'>
		Extra graph settings
	</div>
	<div id='extra_settings'>
		<table style='width: 100%;'>
			<tr>
				<td align='center'>
					to configure extra graph settings, choose a graphing method above
				</td>
			</tr>
		</table>
	</div>
	<br>
	<div style="text-align: center;">
		<input type='button' value='View Graph' class='input_btn' id='view_graph_btn' disabled='true' onClick='do_create_graph();'>
	</div>
	<br>
</div>

{literal}
<script type='text/javascript'>
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
