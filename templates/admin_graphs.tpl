<div>
	<input type='hidden' name='run_time' id='calendar' value='{$month_input}' />
	<input type='hidden' id='cal_month_val' value='{$month}' />
	<input type='hidden' id='cal_day_val' value='{$day}' />
	<input type='hidden' id='cal_year_val' value='{$year}' />
	<input type='hidden' id='cal_hour_val' value='{$hour}' />
	<input type='hidden' id='cal_minute_val' value='{$minute}' />
	<input type='hidden' id='cal_ampm_val' value='{$ampm}' />

<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div id='total_holes_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Plot Graphs
		</span>
		<div class='bullet_list' id='scan_choices'>
			<div class='sidechoice padded' onClick='show_metrics("total_holes_all");'>
				total holes
			</div>
			<div class='sidechoice padded' onClick='show_metrics("total_holes_group");'>
				total warnings
			</div>
			<div class='sidechoice padded' onClick='show_metrics("total_holes_user");'>
				more...
			</div>
		</div>
	</div>
	<div id='total_warnings_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Plot
		</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_metrics("family");'>
				by group
			</div>
			<div class='sidechoice padded' onClick='show_metrics("severity");'>
				by user
			</div>
		</div>
	</div>
	<div id='total_scans_scheduled_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			For the Time Period
		</span>
		<div class='bullet_list'>
                                		<table style='width: 100%;'><tr><td style='width: 90%; text-align: center;'>
						<!-- Calendar entries -->
						<div style='border: 1px solid #999; width: 100%; height: 21px;'>
							<span id='cal_month' style='cursor: pointer;'>{$month}</span> 
							<span id='cal_day' style='cursor: pointer;'>{$day}</span>, 
							<span id='cal_year' style='cursor: pointer;'>{$year}</span> at
							<span id='cal_hour' style='cursor: pointer;'>{$hour}</span>:<span id='cal_minute' style='cursor: pointer;'>{$minute}</span>
							<span id='cal_ampm' style='cursor: pointer;'>{$ampm}</span>
						</div>
						</td><td style='width: 10%;'></td></tr></table>


			<div style='width: 100%; text-align: center;'>
				to
			</div>
                                		<table style='width: 100%;'><tr><td style='width: 90%; text-align: center;'>
						<!-- Calendar entries -->
						<div style='border: 1px solid #999; width: 100%; height: 21px;'>
							<span id='cal_month' style='cursor: pointer;'>{$month}</span> 
							<span id='cal_day' style='cursor: pointer;'>{$day}</span>, 
							<span id='cal_year' style='cursor: pointer;'>{$year}</span> at
							<span id='cal_hour' style='cursor: pointer;'>{$hour}</span>:<span id='cal_minute' style='cursor: pointer;'>{$minute}</span>
							<span id='cal_ampm' style='cursor: pointer;'>{$ampm}</span>
						</div>
						</td><td style='width: 10%;'></td></tr></table>
		</div>
	</div>
	<div id='ready_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Finish
		</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_metrics("family");'>
				view plot
			</div>
			<div class='sidechoice padded' onClick='show_metrics("family");'>
				save plot
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


	<div id='welcome_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: middle; height: 400px;' align='center'>
				<div id='metrics_welcome' style='padding-left: 10px; text-align: left; width: 60%;'>
					<div style='color: #4D69A2; font-weight: bold; text-align: center;'>
						to the left are the metric options
					</div>
					<ul>
					<li style='padding-bottom: 10px;'>items to plot will appear as items on your graph
					<li style='padding-bottom: 10px;'>plot will narrow the scope of the items to graph
					<li style='padding-bottom: 10px;'>time period will further narrow the scope
					<li style='padding-bottom: 10px;'>view or save your graph when you finish configuring it
					</ul>
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
