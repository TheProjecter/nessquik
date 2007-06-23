function show_scans_view(type) {
	$('work_container2').innerHTML = '';

	var url = "async/admin_scans.php";
	var params = "action=show_scans_view&type="+type;

	hide_scans();

	Element.show('workbox');
	Element.show('work_container2');

	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			asynchronous: false,
			evalScripts: true
		});

	var url = "async/admin_scans.php";
	var params = "action=show_scans_list&type="+type;
	new Ajax.Updater(
		{success: 'scans_container'},
		url, 
		{
			method: 'post',
			parameters: params,
			asynchronous: false
		});
}

function refine_scans_list(type) {
	var refine_scan = $F('refine_scan');

	var url = "async/admin_scans.php";
	var params = "action=show_scans_list&type="+type+"&refine_scan="+refine_scan;
	new Ajax.Updater(
		{success: 'scans_container'},
		url, 
		{
			method: 'post',
			parameters: params
		});
}

/**
* Display all user scans
*
* Wrapper function around individual page updating
* functions. This one function will call all the 
* more specific updating functions so that functions
* that need to update the whole page dont need to
* call every individual function.
*
* @see update_not_ready_scans
* @see update_pending_scans
* @see update_running_scans
* @see update_finished_scans
*/
function show_admin_scans(type) {
	if (type == '') {
		type = "all";
	}

	hide_scans();

	if (type == "notready") {
		$('last_view').value = "notready";
		update_not_ready_scans(true);
		Element.show('nr_scans_container');
	} else if (type == "pending") {
		$('last_view').value = "pending";
		update_pending_scans(true);
		Element.show('pending_scans_container');
	} else if (type == "running") {
		$('last_view').value = "running";
		update_running_scans(true);
		Element.show('running_scans_container');
	} else if (type == "finished") {
		$('last_view').value = "finished";
		update_finished_scans(true);
		Element.show('finished_scans_container');
	} else if (type == "all") {
		$('last_view').value = "all";
		update_not_ready_scans(true);
		update_pending_scans(true);
		update_running_scans(true);
		update_finished_scans(true);

		Element.show('nr_scans_container');
		Element.show('pending_scans_container');
		Element.show('running_scans_container');
		Element.show('finished_scans_container');
	}

	Element.show('scans_box');
}

/**
* Hide all the scan containers
*/
function hide_scans() {
	Element.hide('welcome_box');
	Element.hide('work_container2');

	try {
		Element.hide('dropmenudiv');
	} catch(e) {}

	Element.hide('scans_box');
	Element.hide('view_output');
	Element.hide('saved_scan_history_container');
	Element.hide('nr_scans_container');
	Element.hide('pending_scans_container');
	Element.hide('running_scans_container');
	Element.hide('finished_scans_container');
}

function show_scans_view_history_and_dl(type) {
	$('work_container2').innerHTML = '';

	hide_scans();

	Element.show('work_container2');
	Element.show('workbox');

	var url = "async/admin_scans.php";
	var params = "action=show_scans_view_history_and_dl&type="+type;
	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			asynchronous: false,
			evalScripts: true
		});

	var url = "async/admin_scans.php";
	var params = "action=show_scans_view_history_and_dl_list&type="+type;
	new Ajax.Updater(
		{success: 'scans_container'},
		url, 
		{
			method: 'post',
			parameters: params,
			asynchronous: false
		});
}

function refine_scans_view_history_list(type) {
	var refine_scan = $F('refine_scan');

	var url = "async/admin_scans.php";
	var params = "action=show_scans_view_history_and_dl_list&type="+type+"&refine_scan="+refine_scan;
	new Ajax.Updater(
		{success: 'scans_container'},
		url, 
		{
			method: 'post',
			parameters: params
		});
}

function show_scan_history(username) {
	hide_scans();

	var url = "async/admin_scans.php";
	var params = "action=x_show_scan_history&username="+username;
	new Ajax.Updater(
		'work_container2',
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});

	Element.show('workbox');
	Element.show('work_container2');
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
	var make_sure = confirm("Are you sure you want to delete this scan?");

	if (!make_sure) {
		return;
	}

	var url = "async/scans.php";
	var params = "action=x_remove_scan_profile&profile_id="+profile_id+"&type="+type;

	new Ajax.Request(
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
		update_pending_scans(true);
		count_scans(true);
	} else if (type == "finished") {
		update_finished_scans(true);
		count_scans(true);
	} else if (type == "notready") {
		update_not_ready_scans(true);
		count_scans(true);
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
	var make_sure = confirm("Are you sure you want to cancel this scan?");

	if (!make_sure) {
		return;
	}

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

	new Ajax.Request(
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
			alert("Your scan will be cancelled shortly.");
		} else if (run_status == "saved_running_scan") {
			var the_user	= $F('username');
			alert("Your scan will be cancelled shortly.");
			return;
		} else if (run_status == "saved_pending_scan") {
			var the_user	= $F('username');
			show_per_scan_settings(the_user);
			return;
		}
	}

	if (Element.visible('pending_scans_container') && Element.visible('running_scans_container')) {
		update_not_ready_scans(true);
		update_pending_scans(true);
		update_running_scans(true);
		count_scans(true);
	} else if (Element.visible('running_scans_container')) {
		update_running_scans(true);
		count_scans(true);
	} else if (Element.visible('pending_scans_container')) {
		update_pending_scans(true);
		count_scans(true);
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

	new Ajax.Request(
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
	the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		update_not_ready_scans(true);
		alert("Scan scheduled");
		count_scans(true);
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

	var url = "async/admin_scans.php";
	var params = "action=x_get_scan_results_list&profile_id="+id;
	new Ajax.Updater(
		'saved_scan_history_container',
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});

	Element.hide('workbox');
	Element.show('saved_scan_history_container');
	Element.show('scans_box');
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
	}
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
function remove_scan_profile(profile_id) {
	var make_sure = confirm("Are you sure you want to delete this scan profile?");

	if (!make_sure) {
		return;
	}

	var url = "async/scans.php";
	var params = "action=x_remove_scan_profile&profile_id="+profile_id;;

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: remove_scan_profile_resp
		});
}

function remove_scan_profile_resp(origResp) {
	var tmp = origResp.responseText.split('::');
	var the_mesg = tmp[0];

	if (the_mesg == "pass") {
		var username = $F('current_username');
		show_scan_history(username);
	} else {
		alert("Could not remove this scan profile and it's results");
	}
}
