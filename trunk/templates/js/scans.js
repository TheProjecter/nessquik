/**
* Hide all the scan containers
*/
function hide_scans() {
	Element.hide('welcome_box');
	Element.hide('scans_box');
	Element.hide('nr_scans_container');
	Element.hide('pending_scans_container');
	Element.hide('running_scans_container');
	Element.hide('finished_scans_container');
	Element.hide('saved_scan_history_container');
	Element.hide('view_output');
	Element.hide('compare_main_container');
	Element.hide('compare_results');
	Element.hide('compare_results_content');
	Element.hide('compare_results_container');
	Element.hide('compare_container_left');
	Element.hide('compare_container_right');
	Element.hide('compare_buttons');
	Element.hide('compare_list_container');
	Element.hide('compare_list');
	Element.hide('compare_specific');
}

/**
* Remove a scan profile
*
* Removes a scan profile and all the associated data with the
* profile including any saved reports.
*
* @param integer $profile_id Profile ID of the scan to remove
* @see remove_scan_profile_resp
*/
function remove_scan_profile(profile_id, type) {
	var make_sure = confirm("Are you sure you want to delete this scan profile?");

	if (!make_sure) {
		return;
	}

	var url = "async/scans.php";
	var params = "action=x_remove_scan_profile&profile_id="+profile_id+"&type="+type;

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: remove_scan_profile_resp
		});
}

/**
* Followup to removing a scan profile
*
* After making the AJAX call to remove the profile, the output
* will be sent back to the browser and caught by this function.
*
* @param object $origResp The XMLHTTPRequest response object
* @see remove_scan_profile
*/
function remove_scan_profile_resp(origResp) {
	var tmp = origResp.responseText.split("::");

	var the_mesg	= tmp[0];
	var type 	= tmp[1];
	var run_status	= tmp[2];

	if (the_mesg != "pass") {
		alert("Unable to remove the scan");
	}

	if (type == "pending") {
		update_pending_scans();
		count_scans();
	} else if (type == "finished") {
		update_finished_scans();
		count_scans();
	} else if (type == "notready") {
		update_not_ready_scans();
		count_scans();
	} else if (type == "saved_not_running") {
		var the_user	= $F('username');
		show_per_scan_settings(the_user);
	}
}

/**
* Cancel a pending scan
*
* Scans can be cancelled if they are pending. By canceling
* a scan, you move it to the "not ready" pile where it will
* sit and wait for you to either delete it or schedule it.
* This method will cancel a pending scan.
*
* @param string $profile_id Profile ID of the scan to cancel
* @see cancel_scan_profile_resp
*/
function cancel_scan_profile(profile_id, run_status) {
	var url = "async/scans.php";

	if (run_status == "running") {
		var params = "action=x_cancel_scan_profile&profile_id="+profile_id+"&status="+run_status;
	} else if (run_status == "pending") {
		var params = "action=x_cancel_scan_profile&profile_id="+profile_id+"&status="+run_status;
	} else if (run_status == "saved_running_scan") {
		var params = "action=x_cancel_scan_profile&profile_id="+profile_id+"&status="+run_status;
	} else if (run_status == "saved_pending_scan") {
		var params = "action=x_cancel_scan_profile&profile_id="+profile_id+"&status="+run_status;
	}

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: cancel_scan_profile_resp
		});
}

/**
* Cancel pending scan response
*
* Wrapper to catch the response to the cancel pending scan
* request. This function handles updating any parts of the
* page or notifying the user that the scan was successfully
* cancelled.
*
* @param object $origResp The XMLHTTPRequest response object
* @see cancel_scan_profile
*/
function cancel_scan_profile_resp(origResp) {
	var tmp 	= origResp.responseText.split("::");
	var the_mesg 	= tmp[0];
	var run_status	= tmp[1];

	if (the_mesg != "pass") {
		alert("Unable to cancel the scan");
	} else {
		if (run_status == "running") {
			//alert("Your scan will be cancelled shortly.");
		} else if (run_status == "saved_running_scan") {
			var the_user	= $F('username');
			//alert("Your scan will be cancelled shortly.");
			return;
		} else if (run_status == "saved_pending_scan") {
			var the_user	= $F('username');
			show_per_scan_settings(the_user);
			return;
		}
	}

	if (Element.visible('pending_scans_container') && Element.visible('running_scans_container')) {
		update_not_ready_scans();
		update_pending_scans();
		update_running_scans();
		count_scans();
	} else if (Element.visible('running_scans_container')) {
		update_running_scans();
		count_scans();
	} else if (Element.visible('pending_scans_container')) {
		update_pending_scans();
		count_scans();
	}
}

/**
* Reschedule a finished or "not ready" scan
*
* If a scan has finished and the user wants to schedule it
* again without going back to the home page, they can click
* the reschedule button and this function will reschedule
* the scan. Likewise, scans that are "not ready" can be
* scheduled at any time.
*
* @param string $profile_id Profile ID of the profile to schedule
*/
function reschedule_scan(profile_id) {
	var url = "async/scans.php";
	var params = "action=x_reschedule_scan_profile&profile_id="+profile_id;

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: reschedule_scan_profile_resp
		});
}

/**
* Followup to rescheduling a scan
*
* After making the AJAX call to reschedule the scan, the
* output will be sent back and caught by this function.
* Here I'll update any page components that need to be
* updated with new info and will message the user that 
* the rescheduling was either a success or a failure.
*
* @param object $origResp The XMLHTTPRequest response object
* @see reschedule_scan
*/
function reschedule_scan_profile_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		update_not_ready_scans();
		//alert("Scan scheduled");
		count_scans();
	} else if (the_mesg == "no_scanner") {
		var to_user = 	"The scan was not scheduled because the "
				+ "scanner associated with this profile "
				+ "doesn't appear to exist anymore. To "
				+ "correct this, choose a new scanner "
				+ "in the scan settings for this profile.";

		alert(to_user);
	} else {
		alert("The scan was not scheduled");
	}
}

/**
* View saved report output
*
* nessquik allows the user to save the report to the nessquik
* database so that they can go back and perform various changes
* or operations on it in the future, such as changing it's format
* and re-sending it via email. This function will display the
* saved report in an embedded popup when the user clicks the
* 'view report' link
*
* @access public
* @param string $profile_id Profile ID of the scan whose report to fetch
* @param string $format Format of the report to display
*/
function view_report(profile_id, results_id, format) {
	hide_scans();

	var url 	= "async/scans.php";
	var params	= "action=view_report&profile_id="+profile_id+"&results_id="+results_id+"&format="+format;

	$('view_output_content').innerHTML = '';

	var ajax_req = new Ajax.Updater(
		'view_output_content',
		url,
		{
			method: 'post', 
			parameters: params
		});

	Element.show('view_output');
	Element.show('scans_box');
}

/**
* Re-email the scan report
*
* If a user has saved the scan results, this function
* will send a request to the server to re-email the
* scan results _only_ to the user making the request.
* If alternate email addresses were specified, they
* will not receive the re-sent email
*
* @param string $profile_id Profile ID of the scan whose results to send
* @param integer $results_id Results ID of the specific scan whose results to send
* @param string $format Format of the report to send
*/
function email_report(profile_id, results_id, format) {
	var url		= "async/scans.php";
	var params	= "action=email_report&profile_id="+profile_id+"&results_id="+results_id+"&format="+format;

	var ajax_req	= new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: email_report_resp
		});
}

/**
* Response to re-sending the email
*
* This function handles the response from the server
* concerning the request to re-send email to the user
* about their report.
*
* @param object origResp Original XMLHTTPRequest response
*/
function email_report_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		alert("Email sent");
	} else {
		alert("Email was not sent");
	}
}

/**
* Show the scan history page
*
* This function will update an area of the page pertaining
* to the scan history and will display all the users scans
* based on the profile ID so that the user can view all the
* previous scans for a specific profile_id
*/
function show_scan_history() {
	hide_scans();

	var url = "async/scans.php";
	var params = "action=x_show_scan_history";
	var myAjax = new Ajax.Updater(
		'saved_scan_history_container',
		url, 
		{
			method: 'post',
			parameters: params
		});

	Element.show('saved_scan_history_container');
	Element.show('scans_box');
}

/**
* Display page to compare scan results
*/
function show_compare_scans() {
	hide_scans();

	$('compare_results_content').innerHTML = '';
	$('compare_container_right').innerHTML = '';

	var url = "async/scans.php";
	var params = "action=x_show_compare&compare_step=main";

	var myAjax = new Ajax.Updater(
		'compare_list',
		url, 
		{
			method: 'post',
			parameters: params
		});

	Element.show('compare_list');
	Element.show('compare_list_container');
	Element.show('compare_main_container');
}

/**
* Go back a step in viewing certain page info
*
* A 'back' link is provided on several pages (such as when viewing
* a scans results) that are more than one click away from a pages
* main content. This function will "go back" a step to return the
* users screen to what it contained before they clicked a particular
* link
*
* @param string $section The currently viewed section
*/
function go_back_to_view(section) {
	var last_view = $('last_view').value;
	var profile_id = '';

	if (last_view == "scan_history") {
		hide_scans();
		profile_id = $F('profile_id');
		get_scan_results_list(profile_id);
	} else {
		show_user_scans(last_view);
	}
}

/**
* Update historical view of available results from profile
*
* For viewing the scan history, when a user clicks on a scan
* profile name, this function updates the area of the page
* that allows them to view all the results for the scan profile
*
* @param integer $id Profile ID to return results for
*/
function get_scan_results_list(id) {
	$('last_view').value = "scan_history";

	var url = "async/scans.php";
	var params = "action=x_get_scan_results_list&profile_id="+id;
	var myAjax = new Ajax.Updater(
		'saved_scan_history_container',
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});

	Element.show('saved_scan_history_container');
	Element.show('scans_box');
}

/**
* 
*/
function get_scan_results_list_for_compare(id, profile_name) {
	hide_scans();

	var url 		= "async/scans.php";
	var params1 		= "action=x_get_scan_results_list_for_compare&profile_id="+id+"&compare_step=left";
	var params2 		= "action=x_get_scan_results_list_for_compare&profile_id="+id+"&compare_step=right";

	new Ajax.Updater(
		'compare_container_left',
		url, 
		{
			method: 'post',
			parameters: params1
		});

	new Ajax.Updater(
		'compare_container_right',
		url, 
		{
			method: 'post',
			parameters: params2
		});

	Element.show('compare_container_left');
	Element.show('compare_container_right');
	Element.show('compare_specific');
	Element.show('compare_main_container');
	Element.show('compare_buttons');

	$('profile_id').value 		= id;
	$('profile_name').innerHTML	= profile_name;
}

/**
* Remove a specific set of results from a scan profile's history
*
* Each time a scan is run, it's results, if saved, are appended
* to a database table. These individual results can be deleted
* by the user if they feel there's no reason to keep them anymore.
* This function will remove a specific result entry from the database
*/
function remove_specific_scan_results(results_id) {
	var make_sure = confirm("Are you sure you want to delete these results?");

	if (!make_sure) {
		return;
	}

	var url = "async/scans.php";
	var params = "action=x_remove_specific_scan_results&results_id="+results_id;

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: remove_specific_scan_results_resp
		});

}

/**
* Catch response to removing a specific set of results
*
* After the results are removed, this function follows up with
* it's caller to prompt the user with messages telling them whether
* or not the scan results were successfully deleted
*
* @param object $origResp XMLHTTPRequest response object
* @see remove_specific_scan_results
*/
function remove_specific_scan_results_resp(origResp) {
	var tmp = origResp.responseText.split("::");

	var the_mesg 	= tmp[0];
	var profile_id	= tmp[1];
	var remaining	= tmp[2];

	if (the_mesg != "pass") {
		alert("Could not delete the selected scan results");
		return;
	} else {
		if (remaining < 1) {
			show_scan_history();
		} else {
			get_scan_results_list(profile_id);
		}
	}
}

function do_compare_scans() {
	var step_1 	= false;
	var step_2	= false;

	var from_val	= 0;
	var to_val	= 0;
	var profile_id	= $F('profile_id');

	var form 	= $('nessus_reg');
	var radios_1 	= form.getInputs('radio', 'compare_value_left');
	var radios_2 	= form.getInputs('radio', 'compare_value_right');

	radios_1.each(
		function(radio) {
			if (radio.checked) {
				step_1 		= true;
				from_val	= radio.value;
			}
		}
	);

	radios_2.each(
		function(radio) {
			if (radio.checked) {
				step_2		= true;
				to_val		= radio.value;
			}
		}
	);

	if (step_1 && step_1) {
		var url		= "async/scans.php";
		var params 	= "action=do_compare_scans&profile_id="+profile_id+"&from="+from_val+"&to="+to_val;

		var myAjax = new Ajax.Updater(
			'compare_results_content',
			url, 
			{
				method: 'post',
				parameters: params
			});

		Element.hide('compare_main_container');
		Element.show('compare_results');
		Element.show('compare_results_content');
		Element.show('compare_results_container');
	} else {
		alert("Select a pair of scan results to compare");
	}
}
