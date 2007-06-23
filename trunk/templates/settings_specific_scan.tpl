<table style='width: 100%;'>
<tr>
	<td style='width: 100%;'>
		<input type='hidden' value='{$ael_count}' id='alt_email_counter' />
		<input type='hidden' value='{$acl_count}' id='alt_cgi_counter' />
		<input type='hidden' value='{$plugin_count}' id='plugin_counter' />
		<input type='hidden' value='{$device_counter}' id='device_counter' />
		<input type='hidden' name='setting_id' id='setting_id' value='{$setting_id}' />
		<input type='hidden' name='profile_id' id='profile_id' value='{$profile_id}' />

		<input type='hidden' name='run_time' id='calendar' value='{$month_input}' />
		<input type='hidden' id='cal_month_val' value='{$month}' />
		<input type='hidden' id='cal_day_val' value='{$day}' />
		<input type='hidden' id='cal_year_val' value='{$year}' />
		<input type='hidden' id='cal_hour_val' value='{$hour}' />
		<input type='hidden' id='cal_minute_val' value='{$minute}' />
		<input type='hidden' id='cal_ampm_val' value='{$ampm}' />

		<input type='hidden' name='recurring_run_time' id='clock' value='{$time_input}' />
		<input type='hidden' id='clock_hour_val' value='{$clock_hour}' />
		<input type='hidden' id='clock_minute_val' value='{$clock_minute}' />
		<input type='hidden' id='clock_ampm_val' value='{$clock_ampm}' />

		<div id='scan_settings'>
			<div style='color: #4D69A2; font-weight: bold; padding-left: 5px;'>
				scanning
			</div>
			<p />
			<table style='width: 100%; padding-left: 5px;'>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("s_name_help");' title='Click for information'>
							scan name
						</span>
						<div id='s_name_help' style='width: 100%;'>
							<p />
							Rename your scans here to better keep track of what is what. By default 
							your scans are named after the first couple of machines being scanned. 
							This can become confusing if you have several scans that scan the same 
							set of machines. Name your scans for your own benefit. You'll be able 
							to better remember what is what.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: center;'>
                                		<table style='width: 100%;'><tr><td style='width: 90%;'>
						<input type='text' style='width: 100%;' name='scan_name' value='{$scan_name}' class='input_txt' maxlength='255'>
						</td><td style='width: 10%;'></td></tr></table>
					</td>
				</tr>
				<tr>
					<td style='width: 70%; vertical-align: top;'>
						<span class='settings_key' onClick='Element.toggle("s_calendar_help");' title='Click for information'>
							scheduled to run on 
						</span>

						<div id='s_calendar_help' style='width: 100%;'>
							<p />
							You can change the date that your scan is schedule to run. Note that if you 
							change this value, your recurrence settings will occur based on the 
							new date. Also, only pending and finished scans will obey this setting.
							Remember to set the scan to <b>pending</b>, or leave it set as <b>finished</b>.
							<ul>
								<li>To increase the values in the fields, use a left click.
								<li>To decrease the the values, use shift+left click
							</ul>
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: center;'>
                                		<table style='width: 100%;'><tr><td style='width: 90%;'>
						<!-- Calendar entries -->
						<div style='border: 1px solid #999; width: 100%; height: 21px;'>
							<span id='cal_month' style='cursor: pointer;'>{$month}</span> 
							<span id='cal_day' style='cursor: pointer;'>{$day}</span>, 
							<span id='cal_year' style='cursor: pointer;'>{$year}</span> at
							<span id='cal_hour' style='cursor: pointer;'>{$hour}</span>:<span id='cal_minute' style='cursor: pointer;'>{$minute}</span>
							<span id='cal_ampm' style='cursor: pointer;'>{$ampm}</span>
						</div>
						</td><td style='width: 10%;'></td></tr></table>
					</td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("s_ping_help");' title='Click for information'>
							ping the host first
						</span>
						<div id='s_ping_help' style='width: 100%;'>
							<p />
							By default, the scanners will not ping the computers you specify 
							before they attempt a scan. Pinging prevents the scanners from 
							trying to scan a machine that is turned off, but this may 
							also stop scans of machines that are really turned on. For 
							example, some firewalls block pings. To the scanners, this would 
							make the computer appear to be offline.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: left;'>
						<table style='width: 100%;'>
							<tr>
						{ if $ping_host_first == "1" }
						<td style='width: 50%;'><input type='radio' name='ping_host_first' value='1' checked='checked'>yes</td>
						<td style='width: 50%;'><input type='radio' name='ping_host_first' value='0'>no</td>
						{ else }
						<td style='width: 50%;'><input type='radio' name='ping_host_first' value='1'>yes</td>
						<td style='width: 50%;'><input type='radio' name='ping_host_first' value='0' checked='checked'>no</td>
						{ /if }
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("s_scanner_help");' title='Click for information'>
							scanner to use
						</span>
						<div id='s_scanner_help' style='width: 100%;'>
							<p />
							You have the choice of using a scanner that is located either inside or
							outside the Fermi network. Depending on which scanner you choose, you 
							can get different views of how your computer is seen from the local network
							compared to the full internet.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: left;'>
						<table style='width: 100%;'>
							<tr>
								<td>
								{ if $scanners }
									<select id='scanner_id' name='scanner_id' class='input_select' style='width: 90%;'>
									{ section name=scn loop=$scanners }
										{ if $scanners[scn].id == $scanner_id }
										<option value='{$scanners[scn].id}' selected='selected'>{$scanners[scn].name|truncate:55}
										{ else }
										<option value='{$scanners[scn].id}'>{$scanners[scn].name|truncate:55}
										{ /if }
									{ /section }
									</select>
								{ else }
									<span style='color: #999;'>There are no registered scanners available</span>
								{ /if }
								</td>
							</tr>
						</table>
					</td>
					<td style='width: 5%; text-align: right; vertical-align: top;'><div style='width: 20px;'>&nbsp;</div></td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("s_port_scan_help");' title='Click for information'>
							port scan range
						</span>
						<div id='s_port_scan_help' style='width: 100%;'>
							<p />
							If you want, you may specify the port range that is scanned 
							by Nessus. The port range must be between 1 and 65535, or, the
							keyword 'default'.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: center;'>
                                		<table style='width: 100%;'><tr><td style='width: 90%;'>
						<input type='text' name='port_range' value='{$port_range}' maxlength='11' class='input_txt' style='width: 100%;'>
						</td><td style='width: 10%;'></td></tr></table>
					</td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("s_cgibin_help");' title='Click for information'>
							alternate cgi-bin directories
						</span>
						<div id='s_cgibin_help' style='width: 100%;'>
							<p />
							If you have a web host that uses cgi-bin directories located 
							in a less-than-standard area (not /cgi-bin), then you can 
							add those alternate cgi-bin directories to this list and 
							Nessus will consider them also when running the scan.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: center;'>
						<div id='alternate_cgis'>
						{section name=acl loop=$alternative_cgibin_list}
                                <div id='cgi{$smarty.section.acl.index}Div'>
                                <table style='width: 100%;'><tr><td style='width: 90%;'>
                                        <input type='text' name='alternate_cgibin[]' id='alternate_cgibin{$smarty.section.acl.index}' class='input_txt' style='width: 100%;' value='{$alternative_cgibin_list[acl]}' maxlength='64'>
                                        </td><td style='width: 10%; text-align: right;'>
                                        <span style='cursor: pointer;'>
                                                <img src='images/delete.png' onClick="remove_cgibin('cgi{$smarty.section.acl.index}Div','{$smarty.section.acl.index}')" alt='Remove this cgi-bin directory' title='Remove this cgi-bin directory'>
                                        </span>
                                        </td></tr></table>
                                </div>
						{ sectionelse }
                                <div id='cgi1Div'>
                                <table style='width: 100%;'><tr><td style='width: 90%;'>
                                        <input type='text' name='alternate_cgibin[]' id='alternate_cgibin1' class='input_txt' style='width: 100%;' value='' maxlength='64'>
                                        </td><td style='width: 10%; text-align: right;'>
                                        <span style='cursor: pointer;'>
                                                <img src='images/delete.png' onClick="remove_cgibin('cgi1Div','1')" alt='Remove this cgi-bin directory' title='Remove this cgi-bin directory'>
                                        </span>
                                        </td></tr></table>
                                </div>
						{/section}
						</div>
					</td>
					<td style='width: 1%; text-align: right; vertical-align: top;'>
                        <div style='cursor: pointer; text-align: center;'>
				<div>
				<table><tr><td>
                                <img src='images/add.png' alt='Add another cgi-bin directory' title='Add another cgi-bin directory' onClick='add_cgibin();'>
				</td></tr></table>
				</div>
                        </div>

					</td>
				</tr>
			</table>
		</div>
		<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
			<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
		</div>
		<div id='schedule_settings'>
			<table style='width: 100%;'>
				<tr>
					<td style='width: 70%;'>
						<div style='color: #4D69A2; font-weight: bold; padding-left: 5px;'>
							scheduling
						</div>
					</td>
					<td style='width: 30%; text-align: center;'>
                                		<table style='width: 100%;'><tr><td style='width: 90%; text-align: left;'>
						<div id='recur_block'>
						{ if $recurrence == '1'}
							<input type='checkbox' name='recurrence' id='recurrence' value='1' checked='checked' onClick='Element.toggle("recurrence_settings");'>enable rescheduling
						{ else }
							<input type='checkbox' name='recurrence' id='recurrence' value='1' onClick='Element.toggle("recurrence_settings");'>enable rescheduling
						{ /if }
						</div>
						</td><td style='width: 10%;'></td></tr></table>
					</td>
				</tr>
			</table>
			<p />
			<div id='recurrence_settings'>
			<table style='width: 100%; padding-left: 5px;'>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key'>
							scheduling rule
						</span>
						<div style='width: 100%;'>
							<p />
							Define how often you want this scan to be rescheduled.
							<p />
						</div>
			<table style='width: 100%;'>
				<tr>
					<td style='vertical-align: top; width: 20%;'>
			{ if $recur_type == "D" }
			<input type='radio' name='recur_type' value='D' checked='checked' onChange='recurrence_switcher("D");'>Daily
			{ else }
			<input type='radio' name='recur_type' value='D' onChange='recurrence_switcher("D");'>Daily
			{ /if }
			<br>
			
			{ if $recur_type == "W" }
			<input type='radio' name='recur_type' value='W' checked='checked' onChange='recurrence_switcher("W");'>Weekly
			{ else }
			<input type='radio' name='recur_type' value='W' onChange='recurrence_switcher("W");'>Weekly
			{ /if }
			<br>

			{ if $recur_type == "M" }
			<input type='radio' name='recur_type' value='M' checked='checked' onChange='recurrence_switcher("M");'>Monthly
			{ else }
			<input type='radio' name='recur_type' value='M' onChange='recurrence_switcher("M");'>Monthly
			{ /if }
			<br>

					</td>
					<td style='vertical-align: top; width: 80%;'>
						<div id='not_future' style='float: left;'>
							Schedule every &nbsp;<input type='text' name='the_interval' id='the_interval' value='{$the_interval}' class='input_txt' size='2' maxlength='2'>&nbsp;
							{ if $recur_type == "D" }
							<span id='recur_type_text'>day(s)</span>
							{ elseif $recur_type == "W" }
							<span id='recur_type_text'>week(s)</span>
							{ elseif $recur_type == "M" }
							<span id='recur_type_text'>month(s)</span>
							{ /if }
							at&nbsp;&nbsp;
						</div>
						<div style='border: 1px solid #999; width: 55px; height: 21px; float: left; text-align: center;'>
							<span id='clock_hour' style='cursor: pointer;'>{$clock_hour}</span>:<span id='clock_minute' style='cursor: pointer;'>{$clock_minute}</span>
							<span id='clock_ampm' style='cursor: pointer;'>{$clock_ampm}</span>
						</div>

						<div id='weekly_recurrence' style='clear: left;'>
							<table style='width: 100%;'>
							<tr>
							{ section name=da loop=$days }
								<td>
								{ if $days[da].val == '1' }
								<input type='checkbox' name='days[{$days[da].key}]' value='1' checked='checked'>{$days[da].long}
								{ else }
								<input type='checkbox' name='days[{$days[da].key}]' value='1'>{$days[da].long}
								{ /if }
								</td>
							{ /section }
							</tr>
							</table>
						</div>

						<div id='monthly_recurrence' style='clear: left;'>
						<table style='width: 100%;'>
							<tr>
								<td style='width: 40%;'>
									{ if $user_nth_day }
									<input type='radio' name='recur_on' value='day' onChange='toggle_recur_boxes("nth_day");' checked='checked'>schedule on the
									{ elseif !$user_nth_day && !$user_nth_day_general}
									<input type='radio' name='recur_on' value='day' onChange='toggle_recur_boxes("nth_day");' checked='checked'>schedule on the
									{ else }
									<input type='radio' name='recur_on' value='day' onChange='toggle_recur_boxes("nth_day");'>schedule on the
									{ /if }
								</td>
								<td style='width: 10%;'>
									<select name='recur_on_day' id='recur_on_day'>
									{ section name=nth loop=$nth_days }
										{ if $user_nth_day == $nth_days[nth].val }
										<option value='{$nth_days[nth].val}' selected='selected'>{$nth_days[nth].name}
										{ else }
										<option value='{$nth_days[nth].val}'>{$nth_days[nth].name}
										{ /if }
									{ /section }
									</select>
								</td>
								<td style='width: 10%;'>day</td>
								<td style='width: 40%;'></td>
							</tr>
							<tr>
								<td style='width: 40%;'>
									{ if $user_nth_day_general }
									<input type='radio' name='recur_on' value='gen' onChange='toggle_recur_boxes("nth_day_general");' checked='checked'>schedule on the
									{ else }
									<input type='radio' name='recur_on' value='gen' onChange='toggle_recur_boxes("nth_day_general");'>schedule on the
									{ /if }
								</td>
								<td style='width: 10%;'>
									<select name='recur_on_day_general' id='recur_on_day_general'>
									{ section name=nthg loop=$nth_days_general }
										{ if $user_nth_day_general == $nth_days_general[nthg].val }
									<option value='{$nth_days_general[nthg].val}' selected='selected'>{$nth_days_general[nthg].name}
									{ else }
									<option value='{$nth_days_general[nthg].val}'>{$nth_days_general[nthg].name}
									{ /if }
									{ /section }
									</select>
								</td>
								<td style='width: 10%;'>
									<select name='day_of_week' id='day_of_week'>
										{ if $user_weekday == "mon" }
										<option value='mon' selected='selected'>Monday
										{ else }
										<option value='mon'>Monday
										{ /if }

										{ if $user_weekday == "tue" }
										<option value='tue' selected='selected'>Tuesday
										{ else }
										<option value='tue'>Tuesday
										{ /if }

										{ if $user_weekday == "wed" }
										<option value='wed' selected='selected'>Wednesday
										{ else }
										<option value='wed'>Wednesday
										{ /if }

										{ if $user_weekday == "thu" }
										<option value='thu' selected='selected'>Thursday
										{ else }
										<option value='thu'>Thursday
										{ /if }

										{ if $user_weekday == "fri" }
										<option value='fri' selected='selected'>Friday
										{ else }
										<option value='fri'>Friday
										{ /if }

										{ if $user_weekday == "sat" }
										<option value='sat' selected='selected'>Saturday
										{ else }
										<option value='sat'>Saturday
										{ /if }

										{ if $user_weekday == "sun" }
										<option value='sun' selected='selected'>Sunday
										{ else }
										<option value='sun'>Sunday
										{ /if }
									</select>
								</td>
								<td style='width: 40%;'></td>
							</tr>
						</table>
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>

<div style='padding-top: 10px; padding-bottom: 15px; text-align: center;'>
	<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
</div>

		<div id='report_settings'>
			<div style='color: #4D69A2; font-weight: bold; padding-left: 5px;'>
				reporting
			</div>
			<p />
			<table style='width: 100%; padding-left: 5px;'>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("s_save_scan_help");' title='Click for information'>
							save the scan report
						</span>
						<div id='s_save_scan_help' style='width: 100%;'>
							<p />
							If you want to save the scan report so that you can 
							access it later, choose this option.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: left;'>
						<table style='width: 100%;'>
							<tr>
						{ if $save_scan_report == "1" }
						<td style='width: 50%;'><input type='radio' name='save_scan_report' value='1' checked='checked'>yes</td>
						<td style='width: 50%;'><input type='radio' name='save_scan_report' value='0'>no</td>
						{ else }
						<td style='width: 50%;'><input type='radio' name='save_scan_report' value='1'>yes</td>
						<td style='width: 50%;'><input type='radio' name='save_scan_report' value='0' checked='checked'>no</td>
						{ /if }
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("s_report_format_help");' title='Click for information'>
							report format
						</span>
						<div id='s_report_format_help' style='width: 100%;'>
							<p />
							Reports can be created in either HTML or Text format.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: left;'>
						<table style='width: 100%;'>
							<tr>
						{ if $report_format == "txt" }
						<td style='width: 50%;'><input type='radio' name='report_format' value='txt' checked='checked'>text</td>
						<td style='width: 50%;'><input type='radio' name='report_format' value='html'>html</td>
						{ else }
						<td style='width: 50%;'><input type='radio' name='report_format' value='txt'>text</td>
						<td style='width: 50%;'><input type='radio' name='report_format' value='html' checked='checked'>html</td>
						{ /if }
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("s_custom_subject_help");' title='Click for information'>
							custom subject line
						</span>
						<div id='s_custom_subject_help' style='width: 100%;'>
							<p />
							In case you want to add a custom subject line to the emails 
							you receive, you can do that here. You can also include several
							macros in the subject line.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: center;'>
						<table style='width: 100%;'><tr><td style='width: 90%;'>
						<input type='text' name='custom_email_subject' value='{$custom_email_subject}' maxlength='128' class='input_txt' style='width: 100%;'>
						</td><td style='width: 10%;'></td></tr></table>
					</td>
				</tr>
				<tr>
		<td style='vertical-align: top; width: 70%;'>
			<span class='settings_key' onClick='Element.toggle("s_send_report_help");' title='Click for information'>
				send the report to these people too
			</span>
			<div id='s_send_report_help' style='width: 100%;'>
				<p />
				If you wish, you can send the report to multiple recipients 
				by adding their email addresses to the list on the right.
				<p />
			</div>
		</td>
		<td style='vertical-align: top; width: 30%; text-align: center;'>
			<div id='alternate_emails'>
				{section name=ael loop=$alternative_email_list}
				<div id='mail{$smarty.section.ael.index}Div'>
				<table style='width: 100%;'><tr><td style='width: 95%;'>
					<input type='text' name='alternate_email_to[]' id='alternate_email_to{$smarty.section.ael.index}' class='input_txt' style='width: 100%;' value='{$alternative_email_list[ael]}' maxlength='128'>
					</td><td style='width: 5%; text-align: right;'>
					<span style='cursor: pointer;'>
						<img src='images/delete.png' onClick="remove_alternate_email('mail{$smarty.section.ael.index}Div','{$smarty.section.ael.index}')" alt='Remove this email address' title='Remove this email address'>
					</span>
					</td></tr></table>
				</div>
				{ sectionelse }
                                <div id='mail1Div'>
                                <table style='width: 100%;'><tr><td style='width: 90%;'>
                                        <input type='text' name='alternate_email_to[]' id='alternate_email_to1' class='input_txt' style='width: 100%;' value='' maxlength='128'>
                                        </td><td style='width: 10%; text-align: right;'>
                                        <span style='cursor: pointer;'>
                                                <img src='images/delete.png' onClick="remove_alternate_email('mail1Div','1')" alt='Remove this email address' title='Remove this email address'>
                                        </span>
                                        </td></tr></table>
                                </div>
				{/section}
			</div>
		</td>
		<td style='width: 5%; text-align: right; vertical-align: top;'>
			<div style='cursor: pointer; text-align: center;'>
				<table><tr><td>
				<img src='images/add.png' alt='Add another email address' title='Add another email address' onClick='add_alternate_email();'>
				</td></tr></table>
			</div>
		</td>
			</table>
		</div>
	</td>
</tr>
</table>

<script type='text/javascript'>
	Element.hide('weekly_recurrence');
	Element.hide('monthly_recurrence');

	{ if $recur_type == 'W' }
		Element.show('weekly_recurrence');
		$('recur_type_text').innerHTML = "weeks(s)";
	{ elseif $recur_type == 'M' }
		Element.show('monthly_recurrence');
		$('recur_type_text').innerHTML = "month(s)";
	{ /if }

	{ if $recurrence == '1' }
		Element.show('recurrence_settings');
	{ else }
		Element.hide('recurrence_settings');
	{ /if }

	{ if $user_nth_day_general }
		$('recur_on_day').disabled = 'disabled';
	{ else }
		$('recur_on_day_general').disabled = 'disabled';
		$('day_of_week').disabled = 'disabled';
	{ /if }

	{literal}

	// Hide all the help texts. They will be displayed
	// when a user clicks on a setting name
	Element.hide('s_calendar_help');
	Element.hide('s_ping_help');
	Element.hide('s_port_scan_help');
	Element.hide('s_save_scan_help');
	Element.hide('s_report_format_help');
	Element.hide('s_custom_subject_help');
	Element.hide('s_send_report_help');
	Element.hide('s_cgibin_help');
	Element.hide('s_name_help');
	Element.hide('s_scanner_help');

	// Observe events so that I can do away with the dhtml calendar.
	// Its too much of a pain to mod, so this is simpler for me
	Event.observe('cal_month','click',cal_month_changer,false);
	Event.observe('cal_day','click',cal_day_changer,false);
	Event.observe('cal_year','click',cal_year_changer,false);
	Event.observe('cal_hour','click',cal_hour_changer,false);
	Event.observe('cal_minute','click',cal_minute_changer,false);
	Event.observe('cal_ampm','click',cal_ampm_changer,false);

	Event.observe('clock_hour','click',clock_hour_changer,false);
	Event.observe('clock_minute','click',clock_minute_changer,false);
	Event.observe('clock_ampm','click',clock_ampm_changer,false);

	{/literal}
</script>
