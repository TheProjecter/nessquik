<table style='width: 100%;'>
<tr>
	<td style='width: 100%;'>
		<input type="hidden" value="{$ael_count}" id="alt_email_counter" />
		<input type="hidden" value="{$acl_count}" id="alt_cgi_counter" />
		<div id='interface_settings'>
			<div style='color: #4D69A2; font-weight: bold; padding-left: 5px;'>
				interface
			</div>
			<p />
			<table style='width: 100%; padding-left: 5px;'>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("g_short_plugins_help");' title='Click for information'>
							enable short plugin listings
						</span>
						<div id='g_short_plugins_help' style='width: 100%;'>
							<p />
							This setting controls whether a lot of information is 
							displayed for individual plugins, or just a little.
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: left;'>
						<table style='width: 100%;'>
							<tr>
						{ if $short_plugin_listing == "1" }
						<td style='width: 50%;'><input type='radio' name='short_plugin_listing' value='1' checked='checked'>yes</td>
						<td style='width: 50%;'><input type='radio' name='short_plugin_listing' value='0'>no</td>
						{ else }
						<td style='width: 50%;'><input type='radio' name='short_plugin_listing' value='1'>yes</td>
						<td style='width: 50%;'><input type='radio' name='short_plugin_listing' value='0' checked='checked'>no</td>
						{ /if }
							</tr>
						</table>
					</td>
					<td style='width: 5%; text-align: right; vertical-align: top;'><div style='width: 20px;'>&nbsp;</div></td>
				</tr>
			</table>
		</div>
		<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
			<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
		</div>
		<div id='scan_settings'>
			<div style='color: #4D69A2; font-weight: bold; padding-left: 5px;'>
				scanning
			</div>
			<p />
			<table style='width: 100%; padding-left: 5px;'>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("g_ping_help");' title='Click for information'>
							ping the host first
						</span>
						<div id='g_ping_help' style='width: 100%;'>
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
					<td style='width: 5%; text-align: right; vertical-align: top;'><div style='width: 20px;'>&nbsp;</div></td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("g_scanner_help");' title='Click for information'>
							scanner to use
						</span>
						<div id='g_scanner_help' style='width: 100%;'>
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
									<select name='scanner_id' class='input_select' style='width: 90%;'>
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
						<span class='settings_key' onClick='Element.toggle("g_port_scan_help");' title='Click for information'>
							port scan range
						</span>
						<div id='g_port_scan_help' style='width: 100%;'>
							<p />
							If you want, you may specify the port range that is scanned 
							by Nessus. By default, all ports are scanned. The port range 
							must be between 1 and 65535.
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
						<span class='settings_key' onClick='Element.toggle("g_cgibin_help");' title='Click for information'>
							alternate cgi-bin directories
						</span>
						<div id='g_cgibin_help' style='width: 100%;'>
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
                                        <input type='text' name='alternate_cgibin[]' id='alternate_cgibin{$smarty.section.acl.index}' class='input_txt' style='width: 100%;' value='{$alternative_cgibin_list[acl]}'>
                                        </td><td style='width: 10%; text-align: right;'>
                                        <span style='cursor: pointer;'>
                                                <img src='images/delete.png' onClick="remove_cgibin('cgi{$smarty.section.acl.index}Div','{$smarty.section.acl.index}')" alt='Remove this cgi-bin directory' title='Remove this cgi-bin directory'>
                                        </span>
                                        </td></tr></table>
                                </div>
						{ sectionelse }
                                <div id='cgi1Div'>
                                <table style='width: 100%;'><tr><td style='width: 90%;'>
                                        <input type='text' name='alternate_cgibin[]' id='alternate_cgibin1' class='input_txt' style='width: 100%;' value=''>
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
		<div id='report_settings'>
			<div style='color: #4D69A2; font-weight: bold; padding-left: 5px;'>
				reporting
			</div>
			<p />
			<table style='width: 100%; padding-left: 5px;'>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("g_save_scan_help");' title='Click for information'>
							save the scan report
						</span>
						<div id='g_save_scan_help' style='width: 100%;'>
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
					<td style='width: 5%; text-align: right; vertical-align: top;'><div style='width: 20px;'>&nbsp;</div></td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("g_report_format_help");' title='Click for information'>
							report format
						</span>
						<div id='g_report_format_help' style='width: 100%;'>
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
					<td style='width: 5%; text-align: right; vertical-align: top;'><div style='width: 20px;'>&nbsp;</div></td>
				</tr>
				<tr>
		<td style='vertical-align: top; width: 70%;'>
			<span class='settings_key' onClick='Element.toggle("g_custom_subject_help");' title='Click for information'>
				custom subject line
			</span>
			<div id='g_custom_subject_help' style='width: 100%;'>
				<p />
				In case you want to add a custom subject line to the emails 
				you receive, you can do that here. You can also include several
				macros in the subject line. Note that for scans where a long list of machines 
				was specified, only the first two or so machines will appear on the 
				subject line.
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
			<span class='settings_key' onClick='Element.toggle("g_send_report_help");' title='Click for information'>
				send the report to these people too
			</span>
			<div id='g_send_report_help' style='width: 100%;'>
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
					<input type='text' name='alternate_email_to[]' id='alternate_email_to{$smarty.section.ael.index}' class='input_txt' style='width: 100%;' value='{$alternative_email_list[ael]}'>
					</td><td style='width: 5%; text-align: right;'>
					<span style='cursor: pointer;'>
						<img src='images/delete.png' onClick="remove_alternate_email('mail{$smarty.section.ael.index}Div','{$smarty.section.ael.index}')" alt='Remove this email address' title='Remove this email address'>
					</span>
					</td></tr></table>
				</div>
				{ sectionelse }
                                <div id='mail1Div'>
                                <table style='width: 100%;'><tr><td style='width: 90%;'>
                                        <input type='text' name='alternate_email_to[]' id='alternate_email_to1' class='input_txt' style='width: 100%;' value=''>
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
			<div>
				<table><tr><td>
				<img src='images/add.png' alt='Add another email address' title='Add another email address' onClick='add_alternate_email();'>
				</td></tr></table>
			</div>
			</div>
		</td>
	</tr>
	</td>
</tr>
</table>

<table style='width: 100%; padding: 10px;'>
	<tr>
		<td style='text-align: center;'>
			{ if $scanners }
			<input type='button' name='save_settings' value='Save Settings' class='input_btn' onClick='do_save_settings();'>
			{ else }
			<input type='button' name='save_settings' value='Save Settings' class='input_btn' disabled='disabled'>
			{ /if }
		</td>
	</tr>
</table>

<script type='text/javascript'>
	Element.hide('g_short_plugins_help');
	Element.hide('g_ping_help');
	Element.hide('g_port_scan_help');
	Element.hide('g_save_scan_help');
	Element.hide('g_report_format_help');
	Element.hide('g_custom_subject_help');
	Element.hide('g_send_report_help');
	Element.hide('g_cgibin_help');
	Element.hide('g_scanner_help');
</script>
