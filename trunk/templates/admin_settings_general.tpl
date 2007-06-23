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
						<span class='settings_key' onClick='Element.toggle("g_reports_viewable_help");' title='Click for information'>
							report formats that can be viewed
						</span>
						<div id='g_reports_viewable_help' style='width: 100%;'>
							<p />
							You can specify the types of formats that users are
							allowed to view on the scans page
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: left;'>
						<table style='width: 100%;'>
							<tr>
								<td>
								<td style='width: 33%;'>
									<input type='checkbox'>txt
								</td>
								<td style='width: 33%;'>
									<input type='checkbox'>html
								</td>
								<td style='width: 33%;'>
									<input type='checkbox'>nbe
								</td>
							</tr>
						</table>
					</td>
					<td style='width: 5%; text-align: right; vertical-align: top;'><div style='width: 20px;'>&nbsp;</div></td>
				</tr>
				<tr>
					<td style='vertical-align: top; width: 70%;'>
						<span class='settings_key' onClick='Element.toggle("g_reports_savable_help");' title='Click for information'>
							report formats that can be saved
						</span>
						<div id='g_reports_savable_help' style='width: 100%;'>
							<p />
							You can specify the types of formats that users are
							allowed to choose from in their scan settings
							<p />
						</div>
					</td>
					<td style='vertical-align: top; width: 30%; text-align: left;'>
						<table style='width: 100%;'>
							<tr>
								<td>
								<td style='width: 33%;'>
									<input type='checkbox'>txt
								</td>
								<td style='width: 33%;'>
									<input type='checkbox'>html
								</td>
								<td style='width: 33%;'>
									<input type='checkbox'>nbe
								</td>
							</tr>
						</table>
					</td>
					<td style='width: 5%; text-align: right; vertical-align: top;'><div style='width: 20px;'>&nbsp;</div></td>
				</tr>
			</table>
		</div>
	</td>
</tr>
</table>
<script type='text/javascript'>
	Element.hide('g_reports_viewable_help');
	Element.hide('g_reports_savable_help');
</script>
