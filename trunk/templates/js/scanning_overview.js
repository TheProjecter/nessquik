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
function show_user_scans(type) {
	if (type == '') {
		type = "all";
	}

	hide_scans();

	if (type == "notready") {
		$('last_view').value = "notready";
		update_not_ready_scans();
		Element.show('nr_scans_container');
	} else if (type == "pending") {
		$('last_view').value = "pending";
		update_pending_scans();
		Element.show('pending_scans_container');
	} else if (type == "running") {
		$('last_view').value = "running";
		update_running_scans();
		Element.show('running_scans_container');
	} else if (type == "finished") {
		$('last_view').value = "finished";
		update_finished_scans();
		Element.show('finished_scans_container');
	} else if (type == "all") {
		$('last_view').value = "all";
		update_not_ready_scans();
		update_pending_scans();
		update_running_scans();
		update_finished_scans();

		Element.show('nr_scans_container');
		Element.show('pending_scans_container');
		Element.show('running_scans_container');
		Element.show('finished_scans_container');
	}

	Element.show('scans_box');
}

/**
* Update "not ready" section of scans page
*
* This function will update the content of the scans
* considered not ready on the scans page
*/
function update_not_ready_scans(admin) {
	if (admin) {
		var url = "async/admin_scans.php";
	} else {
		var url = "async/scans.php";
	}

	var params = "action=x_show_user_scans&type=notready";
	new Ajax.Updater(
		'not_ready_scans',
		url, 
		{
			method: 'post',
			parameters: params
		});
}

/**
* Update "pending" section of scans page
*
* This function will update the content of the scans
* considered pending on the scans page
*/
function update_pending_scans(admin) {
	if (admin) {
		var url = "async/admin_scans.php";
	} else {
		var url = "async/scans.php";
	}
	var params = "action=x_show_user_scans&type=pending";
	new Ajax.Updater(
		'pending_scans',
		url, 
		{
			method: 'post',
			parameters: params
		});
}

/**
* Update "running" section of scans page
*
* This function will update the content of the scans
* considered running on the scans page
*/
function update_running_scans(admin) {
	if (admin) {
		var url = "async/admin_scans.php";
	} else {
		var url = "async/scans.php";
	}
	var params = "action=x_show_user_scans&type=running";
	new Ajax.Updater(
		'running_scans',
		url, 
		{
			method: 'post',
			parameters: params
		});
}

/**
* Update "finished" section of scans page
*
* This function will update the content of the scans
* considered finished on the scans page
*/
function update_finished_scans(admin) {
	if (admin) {
		var url = "async/admin_scans.php";
	} else {
		var url = "async/scans.php";
	}
	var params = "action=x_show_user_scans&type=finished";
	new Ajax.Updater(
		'finished_scans',
		url, 
		{
			method: 'post',
			parameters: params
		});
}

/**
* Count the number of scans pending, running, etc
*
* This function is a lighter weight implementation to
* check to see if a page needs to be updated. This
* function checks to see if the scan counts have changed
* and if they have, then update specific parts of the page
*/
function count_scans(admin) {
	if (admin) {
		var url	= "async/admin_scans.php";
	} else {
		var url	= "async/scans.php";
	}
	var params	= "action=x_count_scans";

	new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: count_scans_resp
		});
}

/**
* Handler for counting the scan numbers
*
* After the count_scans runs, this function will handle the
* response that is returned. Depending on the scans counts
* that have changed, specific areas of the page need to be
* updated
*
* @param object $origResp Original XMLHTTPRequest response
*/
function count_scans_resp(origResp) {
	/**
	* The format of the results returned looks like so
	*
	*	"pass::$not_running,$pending,$running,$finished,$all"
	*/
	var the_mesg = origResp.responseText.split("::");

	if (the_mesg[0] == "pass") {
		stats = the_mesg[1].split(";");

		var prev_nr_count	= $('nr_count').innerHTML;
		var prev_pending_count	= $('pending_count').innerHTML;
		var prev_running_count	= $('running_count').innerHTML;
		var prev_finished_count	= $('finished_count').innerHTML;
		var prev_all_count	= $('all_count').innerHTML;

		var curr_nr_count	= stats[0];
		var curr_pending_count	= stats[1];
		var curr_running_count	= stats[2];
		var curr_finished_count	= stats[3];
		var curr_all_count	= stats[4];

		var admin_status	= stats[5];
		var something_changed	= false;

		if (admin_status == "admin") {
			var admin	= true;
		} else {
			var admin	= false;
		}

		// Update appropriate boxes. I'm trying to cut down on the
		// number of calls made to the backend
		if (Element.visible('pending_scans_container') && Element.visible('running_scans_container')) {
			// use the stats variable to check in this case
			// because there is no 'all_container' div. Instead
			// literally all the containers are shown
			if (curr_nr_count != prev_nr_count) {
				update_not_ready_scans(admin);
			}

			if (curr_pending_count != prev_pending_count) {
				update_pending_scans(admin);
			}

			if (curr_running_count != prev_running_count) {
				update_running_scans(admin);
			} else if (stats[2] > 0) {
				update_running_scans(admin);
			}

			if (curr_finished_count != prev_finished_count) {
				update_finished_scans(admin);
			}
		} else if (Element.visible('nr_scans_container')) {
			// If the 'not ready' scan count changed
			if (curr_nr_count != prev_nr_count) {
				update_not_ready_scans(admin);
			}
		} else if (Element.visible('pending_scans_container')) {
			// If the 'pending' scan count changed
			if (curr_pending_count != prev_pending_count) {
				update_pending_scans(admin);
			}
		} else if (Element.visible('running_scans_container')) {
			/**
			* Need to do two checks here. First, if the 'running' scan
			* count changed, then that means a running scan finished,
			* so update the running scans
			*
			* The second check is to see if any scans are still running.
			* if they are, then we also need to update the running scans
			* because otherwise the progress meter will never move
			*/
			if (curr_running_count != prev_running_count) {
				update_running_scans(admin);
			} else if (curr_running_count > 0) {
				update_running_scans(admin);
			}
		} else if (Element.visible('finished_scans_container')) {
			// If the 'finished' scan count changed
			if (curr_finished_count != prev_finished_count) {
				update_finished_scans(admin);
			}
		} else {
			if (admin) {
				if (curr_nr_count != prev_nr_count) {
					something_changed = true;
				} else if (curr_pending_count != prev_pending_count) {
					something_changed = true;
				} else if (curr_running_count != prev_running_count) {
					something_changed = true;
				} else if (curr_finished_count != prev_finished_count) {
					something_changed = true;
				}

				try {
					if (Element.visible('view_history_user')) {
						if (something_changed) {
							show_scans_view_history_and_dl("user");
						}
					} else if (Element.visible('view_history_group')) {
						alert("view_history_group_update");
					}
				} catch(e) {}
			}
		}

		$('nr_count').innerHTML 	= stats[0];
		$('pending_count').innerHTML	= stats[1];
		$('running_count').innerHTML	= stats[2];
		$('finished_count').innerHTML	= stats[3];
		$('all_count').innerHTML	= stats[4];
	}
}
