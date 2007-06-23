<div>
<input type='hidden' id='last_view' value=''>
<input type='hidden' id='dropmenucontainer' value=''>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div id='scanning_overview_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>Scanning Overview</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_admin_scans("notready");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>not ready</td>
						<td style='width: 50%; text-align: right'>
							<span id='nr_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
			<div class='sidechoice padded' onClick='show_admin_scans("pending");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>pending</td>
						<td style='width: 50%; text-align: right'>
							<span id='pending_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
			<div class='sidechoice padded' onClick='show_admin_scans("running");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>running</td>
						<td style='width: 50%; text-align: right'>
							<span id='running_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
			<div class='sidechoice padded' onClick='show_admin_scans("finished");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>finished</td>
						<td style='width: 50%; text-align: right'>
							<span id='finished_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
			<div class='sidechoice padded' onClick='show_admin_scans("all");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>all</td>
						<td style='width: 50%; text-align: right'>
							<span id='all_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div id='scans_list_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Scans
		</span>
		<div class='bullet_list' id='scan_choices'>
			<div class='sidechoice padded' onClick='show_scans_view("user");'>
				list by user
			</div>
			<div class='sidechoice padded' onClick='show_scans_view_history_and_dl("user");'>
				view history and download
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
						to the left are the scan options
					</div>
					<ul>
					<li style='padding-bottom: 10px;'>view scans that have been created and their current status
					<li style='padding-bottom: 10px;'>refine the view of scans to certain users or divisions
					<li style='padding-bottom: 10px;'>download or view scan profiles in several formats
					</ul>
				</div>
			</td>
		</tr>
	</table>
	</div>

	<div id='scans_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: top;' rowspan='2' align='center'>
				<div style='text-align: left;'>
					<div id='nr_scans_container'>
						<div style='color: #4D69A2; font-weight: bold; padding-left: 10px;'>
							Scans not ready to run
						</div>
						<div id='not_ready_scans' style='padding-left: 10px;'></div>
						<br>
					</div>

					<div id='pending_scans_container'>
						<div style='color: #4D69A2; font-weight: bold; padding-left: 10px;'>
							Pending scans
						</div>
						<div id='pending_scans' style='padding-left: 10px;'></div>
						<br>
					</div>

					<div id='running_scans_container'>
						<div style='color: #4D69A2; font-weight: bold; padding-left: 10px;'>
							Running scans
						</div>
						<div id='running_scans' style='padding-left: 10px;'></div>
						<br>
					</div>

					<div id='finished_scans_container'>
						<div style='color: #4D69A2; font-weight: bold; padding-left: 10px;'>
							Finished scans
						</div>
						<div id='finished_scans' style='padding-left: 10px;'></div>
						<br>
					</div>

					<div id='saved_scan_history_container'></div>

					<div id='view_output'>
						<table style='width: 100%;'>
							<tr>
								<td><span style='color: #4D69A2; font-weight: bold; font-size: 13px;'>Scan Report</span></td>
								<td style='text-align: right;' colspan='2'>
									<span onClick='go_back_to_view("scans_list");' class='hyperlink'>back</span>
								</td>
							</tr>
						</table>
						<table style='width: 100%;'>
							<tr>
								<td>
									<div id='view_output_content'></div>
								</td>
							</tr>
						</table>
					</div>
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

<div id="dropmenudiv" onMouseover="clearhidemenu();" onMouseout="dynamichide('dropmenudiv');"></div>

{ literal }
<script type='text/javascript'>
	Element.hide('work_container2');
	Element.hide('scans_box');
	Element.hide('dropmenudiv');
	Element.hide('nr_scans_container');
	Element.hide('pending_scans_container');
	Element.hide('running_scans_container');
	Element.hide('finished_scans_container');
	Element.hide('saved_scan_history_container');
	Element.hide('view_output');

	count_scans(true);

	new PeriodicalExecuter(
		function(pe) {
			count_scans(true);
		}, 10
	);
</script>
{ /literal }
