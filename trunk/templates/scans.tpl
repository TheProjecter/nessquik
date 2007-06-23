<div>
<input type='hidden' id='last_view' value=''>
<input type='hidden' id='dropmenucontainer' value=''>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>Scanning Overview</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_user_scans("notready");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>not ready</td>
						<td style='width: 50%; text-align: right'>
							<span id='nr_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
			<div class='sidechoice padded' onClick='show_user_scans("pending");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>pending</td>
						<td style='width: 50%; text-align: right'>
							<span id='pending_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
			<div class='sidechoice padded' onClick='show_user_scans("running");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>running</td>
						<td style='width: 50%; text-align: right'>
							<span id='running_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
			<div class='sidechoice padded' onClick='show_user_scans("finished");'>
				<table style='width: 100%;'>
					<tr>
						<td style='width: 50%;'>finished</td>
						<td style='width: 50%; text-align: right'>
							<span id='finished_count' style='color: #ccc; padding-right: 5px;'>0</span>
						</td>
					</tr>
				</table>
			</div>
			<div class='sidechoice padded' onClick='show_user_scans("all");'>
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
	<div id='plugins_menu' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>Past Scans</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_scan_history();'>
				view scan history
			</div>
			<div class='sidechoice padded' onClick='show_compare_scans();'>
				compare results
			</div>
		</div>
	</div>
</td>
<td style='width: 80%; vertical-align: top;'>
<div style='height: 100%; margin: 10px 0px 0px 0px; border: 1px solid #fff; vertical-align: top;'>
	<table style='width: 100%; margin-bottom: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-top-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-top-middle'>&nbsp;</td>
			<td class='gray-top-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
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

	<div id='welcome_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: middle; height: 400px;' align='center'>
				<div id='scans_welcome' style='padding-left: 10px; text-align: left; width: 60%;'>
					<div style='color: #4D69A2; font-weight: bold; text-align: center;'>
						to the left is a list of all your scans
					</div>
					<ul>
						<li style='padding-bottom: 10px;'>click on a scan type to view all the details for those particular scans
						<li style='padding-bottom: 10px;'>if a scan is running, a progress meter will be available so that you can view the status of it
						<li style='padding-bottom: 10px;'>if a scan is pending or finished, you have the option to remove it
						<li>you can also cancel any running scan by clicking the stop icon next to it
					</ul>
				</div>
			</td>
		</tr>
	</table>
	</div>

	<div id='compare_main_container'>
		<div id='compare_list_container'>
			<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc; height: 400px;'>
				<tr>
					<td style='width: 100%; vertical-align: top;' align='center'>
						<div id='compare_list' style='width: 100%;'></div>
					</td>
				</tr>
			</table>
		</div>
		<div id='compare_specific'>
		<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
			<tr>
				<td style='width: 100%; text-align: center; border-bottom: 1px solid #ccc;' colspan='2'>
					<input type='hidden' name='profile_id' id='profile_id' value=''>
					<div id='profile_name' style='font-weight: bold; text-align: center; color: #4D69A2;'></div>
					<br>
				</td>
			</tr>
			<tr>
				<td style='width: 50%; vertical-align: top;'>
					<div id='compare_header_step1' style='font-weight: bold; text-align: center;'>
						I want to compare these scan results<br><br>
					</div>
					<div id='compare_container_left' style='width: 100%; height: 400px; overflow: auto;'></div>
				</td>
				<td style='width: 50%; border-left: 1px solid #ccc; vertical-align: top;'>
					<div id='compare_header_step2' style='font-weight: bold; text-align: center;'>
						To these scan results<br><br>
					</div>
					<div id='compare_container_right' style='width: 100%; height: 400px; overflow: auto;'></div>
				</td>
			</tr>
		</table>
		<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
			<tr>
				<td style='width: 100%; text-align: center; border-top: 1px solid #ccc;'>
					<div id='compare_buttons' style='width: 100%; text-align: center;'>
						<br>
						<input type='button' id='do_compare_btn' class='input_btn' onClick='do_compare_scans();' value='Compare Scan Results'>
						<br><br>
					</div>
				</td>
			</tr>
		</table>
		</div>
	</div>

	<div id='compare_results_container'>
		<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
			<tr>
				<td style='width: 100%; vertical-align: top;' align='center'>
					<div id='compare_results'>
						<table style='width: 100%;'>
							<tr>
								<td><span style='color: #4D69A2; font-weight: bold; font-size: 13px;'>Scan Comparison Report</span></td>
								<td style='text-align: right;' colspan='2'>
									<span onClick='show_compare_scans();' class='hyperlink'>back</span>
								</td>
							</tr>
						</table>
						<table style='width: 100%;'>
							<tr>
								<td>
									<div id='compare_results_content' style='width: 100%;'></div>
								</td>
							</tr>
						</table>
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

{literal}
<script type='text/javascript'>
	count_scans(false);

	new PeriodicalExecuter(
		function(pe) {
			count_scans(false);
		}, 10
	);

	Element.hide('dropmenudiv');
	Element.hide('scans_box');
	Element.hide('nr_scans_container');
	Element.hide('pending_scans_container');
	Element.hide('running_scans_container');
	Element.hide('finished_scans_container');
	Element.hide('saved_scan_history_container');
	Element.hide('view_output');
	Element.hide('compare_main_container');
	Element.hide('compare_results_container');
	Element.hide('compare_specific');
</script>
{/literal}
