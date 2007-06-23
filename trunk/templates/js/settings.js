/**
* Display general user settings
*
* This function will call the code necessary to display
* the general settings of the user and will update the
* necessary page elements.
*
* @param string $the_user The username to pull general settings of
*/
function show_user_settings(the_user) {
	$('work_container2').innerHTML = '';

	var url 	= "async/settings.php";
	var params	= "action=x_show_user_settings&username="+the_user;

	Element.hide('welcome_box');
	Element.show('workbox');

	var myAjax = new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}

/**
* Display list of scan settings
*
* This will display the first step in choosing
* a scan to change settings for. It will create
* a list of scans owned by the user and let them
* choose one to view the full scan settings for
*
* @param string $the_user The username  of the person whose scans to get
*/
function show_per_scan_settings(the_user) {
	$('work_container2').innerHTML = '';

	var url 	= "async/settings.php";
	var params	= "action=x_show_scan_settings&username="+the_user;

	Element.hide('welcome_box');
	Element.hide('scan_being_changed');
	Element.hide('scan_choices_container');
	Element.hide('plugins_menu_container');
	Element.hide('configure_menu');
	Element.hide('save_menu_container');

	// Hide boxes that can contain configuration
	Element.hide('devices_box');
	Element.hide('plugins_box');
	Element.show('workbox');

	// Empty out the elements that could possibly contain
	// data left over from previous clicking.
	$('list_of_plugins').innerHTML	= '';
	$('by_computer').innerHTML 	= '';
	$('by_cluster').innerHTML 	= '';
	$('by_whitelist').innerHTML 	= '';
	$('by_saved').innerHTML 	= '';
	$('list').value 		= "click here to enter a list of computers";

	Element.show('configuration_choices');

	var myAjax = new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});

}

/**
* Save user general settings
*
* This will save the general user settings back
* to the user_settings table in the database.
* It will then call a handler function to alert
* the user as to the status of their save request
*
* @see do_save_settings_resp
*/
function do_save_settings() {
	$('action').value 	= "do_save_settings";
	var params 		= $('nessus_reg').serialize(true);
	var url 		= "async/settings.php";

	var myAjax = new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_save_settings_resp
		});
}

/**
* Wrapper to handle general settings save response
*
* This will alert the user and update any page
* elements that need to be updated after the 
* user has clicked the save button on the General
* Settings tab.
*
* @param object $origResp XMLHTTPRequest response object
* @see show_user_settings
* @see do_get_specific_scan_settings
*/
function do_save_settings_resp(origResp) {
	var tmp 	= origResp.responseText.split('::');
	var mesg 	= tmp[0];
	var type 	= tmp[1];
	var identifier	= tmp[2];

	// tmp[3] can be a simple error message
	if (tmp[3]) {
		var more_info = tmp[3];
	}

	if (mesg == "pass") {
		alert("Settings updated successfully");

		/**
		* So I want an alert here but I also need
		* to have that list cleared. Do I really
		* want the alert? I dunno. Also stopping
		* the page refresh will keep that page
		* flicker from happening
		*
		*if (type == "general") {
		*	show_user_settings(identifier);
		*} else if (type == "specific") {
		*/
		if (type == "specific") {
			$('list').value = '';
			change_back('list');
			$('settings_back_button').innerHTML = "done";
		}
		//	do_get_specific_scan_settings(identifier);
		//}
	} else {
		/**
		* A simple error message may accompany the response.
		* This will interpret that message
		*/
		if (more_info) {
			switch(more_info) {
				case "nodays":
					alert("You must select at least one day of the week");
					break;
				case "isrunning":
					alert("Your scan is currently running. The settings cannot be changed at this time");
					break;
			}
		} else {
			alert("Settings were not updated successfully");
		}
	}
}

/**
* Get settings for a specific scan
*
* Aside from the general settings, specific settings
* can also be retrieved and edited so that scans may
* be modified post-scheduling. This function will
* fetch the scan settings, given a specific scan
* profile ID.
*
* @param string $profile_id Profile ID of the scan whose settings are to be fetched
*/
function do_get_specific_scan_settings(profile_id) {
	var profile_id = (profile_id == null) ? $F('select_saved_scan') : profile_id;

	var params 	= "action=x_do_get_specific_scan_settings&profile_id="+profile_id;
	var plu_params 	= "action=x_do_get_specific_scan_plugins&profile_id="+profile_id;
	var dev_params 	= "action=x_do_get_specific_scan_devices&profile_id="+profile_id;
	var url 	= "async/settings.php";

	// This variable contains the string that is shown under the "Changing" header
	// on the specific scan settings page
	var to_display	= $(profile_id).innerHTML.substr(0,40);

	$('scan_being_changed_text').innerHTML = to_display;

	if(profile_id == 'blank') {
		return;
	}

	new Ajax.Updater(
		{success: 'specific_scan_settings'},
		url,
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
	new Ajax.Updater(
		{success: 'selected_plugins'},
		url,
		{
			method: 'post',
			parameters: plu_params,
			evalScripts: true
		});

	new Ajax.Updater(
		{success: 'selected_devices'},
		url,
		{
			method: 'post',
			parameters: dev_params
		});

	Element.hide('settings_chooser');
	Element.show('specific_scan_settings');
	Element.show('scan_being_changed_text');
	Element.show('scan_being_changed');

	// Scan choices sidebar
	Element.show('scan_choices');
	Element.show('scan_choices_container');

	// Plugins menu sidebar
	Element.show('plugins_menu');
	Element.show('plugins_menu_container');

	// Save menu side bar
	Element.show('save_menu');
	Element.show('save_menu_container');

	Element.show('configure_menu');

	Element.hide('configuration_choices');
}

/**
* Toggles the page elements that compose the devices listing
*
* I used a wrapper function instead of the prototype Element.toggle
* function because I needed to hide/show several elements and didnt
* want to stuff them all into a single onClick event. Nevertheless,
* this wrapper function simply hides the plugins and settings
* elements and displays the targets/devices elements.
*/
function toggle_scan_choices() {
	Element.hide('workbox');
	Element.hide('plugins_box');
	Element.show('scan_choices');
	Element.show('devices_box');
}

/**
* Toggles the page elements that compose the plugins listing
*
* Wrapper function to hide the device and settings elements
* and show the plugins elements.
*/
function toggle_plugin_choices() {
	Element.hide('workbox');
	Element.hide('devices_box');
	Element.show('plugins_menu');
	Element.show('plugins_box');
}

/**
* Toggles the page elements for the settings box
*
* Wrapper function to hide the devices and plugins elements
* and display the settings box element
*/
function toggle_setting_choices() {
	Element.hide('devices_box');
	Element.hide('plugins_box');
	Element.show('workbox');
}

/**
* Save a specific scan's settings
*
* The specific scan settings page allows greater
* customization of the settings than is offered
* on the General Settings page. Because of this,
* there are a couple more checks that need to be
* done before the request is sent to the server.
* This function handles the request to save a 
* particular scans' settings
*
* @see do_save_settings_resp
*/
function do_save_specific_settings() {
	$('action').value 	= "do_save_specific_settings";
	var url 		= "async/settings.php";
	var total_plugin_count 	= 0;
	var total_device_count 	= 0;
	var total_recur_days 	= 0;
	var empty_list		= false;

	total_plugin_count 	= get_plugins_count();
	total_device_count 	= get_devices_count();
	total_recur_days	= get_recurrence_days_count();

	empty_list = is_list_empty();

	/**
	* Javascript and DOM are stupid and confusing. Thus, google
	* is one's friend. Code humbly taken from here
	*
	* This script validates that every Radio Button question on
	* the form has been answered.
	*
	* http://javascript.internet.com/forms/radio-question-validator.html
	*/
	el = document.forms[0].elements;
	for(var i = 0; i < el.length; i++) {
		if (el[i].type == "radio") {
			if (el[i].name == "recur_type") {
				var radiogroup = el[el[i].name];
				var itemchecked = false;
				for(var j = 0; j < radiogroup.length; j++) {
					if (radiogroup[j].checked) {
						recur_type = radiogroup[j].value;
					}
				}
			}
		}
	}

	var recurs = $("recurrence").checked;

	/**
	* The second element (index 1) is the weekly item. Make sure the
	* user has selected at least one day
	*/
	if(recurs) {
		if (recur_type == "W") {
			if (total_recur_days < 1) {
				alert("You must select at least one day of the week");
				return;
			}
		}
	}

	if(empty_list && (total_device_count == 0)) {
		alert("You need to enter a computer or list of computers to scan");
		return false;
	}

	// Set the list of computers to empty even if it has the placeholder
	// text. This ensures that no values are sent to the server for this field
	if (empty_list) {
		$('list').value = '';
	}

	// Serialize the form after I've been doing stuff
	var params 		= $('nessus_reg').serialize(true);

	// Send the request to update the entries to the server to save them
	var myAjax = new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_save_settings_resp
		});
}

/**
* Switch text for recurrence fields
*
* When a user clicks the recurrence bullets, each
* one will display a different set of possible
* options to the end user. This function handles
* the showing and hiding that is needed to keep
* users from being confused by too many fields.
*
* @param char $type Type of recurrence to display
*/
function recurrence_switcher(type) {
	Element.hide('weekly_recurrence');
	Element.hide('monthly_recurrence');

	if (type == 'D') {
		$('recur_type_text').innerHTML = "days(s)";
	} else if (type == 'W') {
		$('recur_type_text').innerHTML = "weeks(s)";
		Element.show('weekly_recurrence');
	} else if (type == 'M') {
		$('recur_type_text').innerHTML = "month(s)";
		Element.show('monthly_recurrence');
	}
}

/**
* Toggles the available boxes for month recurrence
*
* The month recurrence allows the user to specify
* two different types of monthly recurrence. To
* keep the user from specifying both, this function
* will disable one of the fields when the other field
* is chosen
*
* @param string $type Type of monthly recurrsion that has been chosen
*/
function toggle_recur_boxes(type) {
	if (type == "nth_day") {
		$('recur_on_day_general').disabled = 'disabled';
		$('day_of_week').disabled = 'disabled';

		$('recur_on_day').disabled = false;
	} else if (type == "nth_day_general") {
		$('recur_on_day').disabled = 'disabled';

		$('recur_on_day_general').disabled = false;
		$('day_of_week').disabled = false;
	}
}

/**
* Adjust the month field for the calendar
*
* The adjustable fields in the calendar form allow the
* month to be either incremented or decremented. This
* function will handle mouse click events and either
* increment or decrement the appropriate field.
*
* @param object $e Mouse Click event captured by javascript
*/
function cal_month_changer(e) {
	var current_month 	= $F('cal_month_val');
	var new_month		= '';
	var new_month_int	= 1;

	if (e.shiftKey) {
		// Shift+left click means decrement
		if (current_month == "January") {
			new_month 	= "December";
		} else if (current_month == "February") {
			new_month 	= "January";
		} else if (current_month == "March") {
			new_month 	= "February";
		} else if (current_month == "April") {
			new_month 	= "March";
		} else if (current_month == "May") {
			new_month 	= "April";
		} else if (current_month == "June") {
			new_month 	= "May";
		} else if (current_month == "July") {
			new_month 	= "June";
		} else if (current_month == "August") {
			new_month 	= "July";
		} else if (current_month == "September") {
			new_month 	= "August";
		} else if (current_month == "October") {
			new_month 	= "September";
		} else if (current_month == "November") {
			new_month 	= "October";
		} else if (current_month == "December") {
			new_month 	= "November";
		}
	} else {
		// Left click means increment
		if (current_month == "January") {
			new_month 	= "February";
		} else if (current_month == "February") {
			new_month 	= "March";
		} else if (current_month == "March") {
			new_month 	= "April";
		} else if (current_month == "April") {
			new_month 	= "May";
		} else if (current_month == "May") {
			new_month 	= "June";
		} else if (current_month == "June") {
			new_month 	= "July";
		} else if (current_month == "July") {
			new_month 	= "August";
		} else if (current_month == "August") {
			new_month 	= "September";
		} else if (current_month == "September") {
			new_month 	= "October";
		} else if (current_month == "October") {
			new_month 	= "November";
		} else if (current_month == "November") {
			new_month 	= "December";
		} else if (current_month == "December") {
			new_month 	= "January";
		}
	}

	$('cal_month_val').value = new_month;
	$('cal_month').innerHTML = new_month;

	update_run_time("calendar");
}

function update_run_time(type) {
	if (type == "calendar") {
		var year	= $F('cal_year_val');
		var month 	= $F('cal_month_val');
		var day		= $F('cal_day_val');
		var hour	= $F('cal_hour_val');
		var min		= $F('cal_minute_val');
		var ampm	= $F('cal_ampm_val');
	} else {
		// The year/month/day values are irrelevant for
		// the clock, so just pull the calendar's values
		var year	= $F('cal_year_val');
		var month 	= $F('cal_month_val');
		var day		= $F('cal_day_val');

		var hour	= $F('clock_hour_val');
		var min		= $F('clock_minute_val');
		var ampm	= $F('clock_ampm_val');
	}

	if (month == "January") {
		month	= "01";
	} else if (month == "February") {
		month	= "02";
	} else if (month == "March") {
		month	= "03";
	} else if (month == "April") {
		month	= "04";
	} else if (month == "May") {
		month	= "05";
	} else if (month == "June") {
		month	= "06";
	} else if (month == "July") {
		month	= "07";
	} else if (month == "August") {
		month	= "08";
	} else if (month == "September") {
		month	= "09";
	} else if (month == "October") {
		month	= "10";
	} else if (month == "November") {
		month	= "11";
	} else if (month == "December") {
		month	= "12";
	}

	if (type == "calendar") {
		$('calendar').value = year+'-'+month+'-'+day+' '+hour+':'+min+' '+ampm;
	} else {
		$('clock').value = year+'-'+month+'-'+day+' '+hour+':'+min+' '+ampm;
	}
}

function cal_ampm_changer(e) {
	var current_ampm 	= $F('cal_ampm_val');

	if (current_ampm == "AM") {
		$('cal_ampm_val').value	= "PM";
		$('cal_ampm').innerHTML	= "PM";
	} else {
		$('cal_ampm_val').value = "AM";
		$('cal_ampm').innerHTML = "AM";
	}

	update_run_time("calendar");
}

function cal_day_changer(e) {
	var the_year 		= $F('cal_year_val');
	var current_month	= $F('cal_month_val');
	var current_day		= parseInt($F('cal_day_val'), 10);
	var new_day		= 1;

	if (current_month == "January") the_month = 1;
	else if (current_month == "February") the_month = 2;
	else if (current_month == "March") the_month = 3;
	else if (current_month == "April") the_month = 4;
	else if (current_month == "May") the_month = 5;
	else if (current_month == "June") the_month = 6;
	else if (current_month == "July") the_month = 7;
	else if (current_month == "August") the_month = 8;
	else if (current_month == "September") the_month = 9;
	else if (current_month == "October") the_month = 10;
	else if (current_month == "November") the_month = 11;
	else if (current_month == "December") the_month = 12;

	var days_in_month = daysInMonth(the_month,the_year);

	if (e.shiftKey) {
		if (current_day > 1) {
			new_day = current_day - 1;
		} else {
			new_day = days_in_month;
		}
	} else {
		if (current_day < days_in_month) {
			new_day = current_day + 1;
		}
	}

	$('cal_day_val').value = new_day;
	$('cal_day').innerHTML = new_day;

	update_run_time("calendar");
}

function cal_year_changer(e) {
	var the_year	= parseInt($F('cal_year_val'), 10);
	var new_year	= 1;

	if (e.shiftKey) {
		new_year = the_year - 1;
	} else {
		new_year = the_year + 1;
	}

	$('cal_year_val').value = new_year;
	$('cal_year').innerHTML = new_year;

	update_run_time("calendar");
}

function cal_hour_changer(e) {
	var the_hour	= parseInt($F('cal_hour_val'), 10);
	var new_hour	= 1;

	if (e.shiftKey) {
		if (the_hour > 1) {
			new_hour = the_hour - 1;
		} else {
			new_hour = 12;
		}
	} else {
		if (the_hour < 12) {
			new_hour = the_hour + 1;
		}
	}

	// For readability's sake, I want to keep a 0 prefixed
	// to the number if it's less than 10. However javascript
	// is doing something funky if I always prefix a 0. That's
	// why I broke it up with an IF statement.
	if (new_hour < 10) {
		$('cal_hour').innerHTML = '0'+new_hour;
	} else {
		$('cal_hour').innerHTML = new_hour;
	}
	
	$('cal_hour_val').value = new_hour;

	update_run_time("calendar");
}

function cal_minute_changer(e) {
	var the_minute	= parseInt($F('cal_minute_val'), 10);
	var new_minute	= 0;

	if (e.shiftKey) {
		if (the_minute > 0) {
			new_minute = the_minute - 1;
		} else {
			new_minute = 59;
		}
	} else {
		if (the_minute < 59) {
			new_minute = the_minute + 1;
		}
	}

	// I'm doing the same thing here that I was doing in the
	// hour function with trying to maintain readability
	if (new_minute < 10) {
		$('cal_minute').innerHTML = '0'+new_minute;
	} else {
		$('cal_minute').innerHTML = new_minute;
	}

	$('cal_minute_val').value = new_minute;

	update_run_time("calendar");
}

function clock_ampm_changer(e) {
	var current_ampm 	= $F('clock_ampm_val');

	if (current_ampm == "AM") {
		$('clock_ampm_val').value	= "PM";
		$('clock_ampm').innerHTML	= "PM";
	} else {
		$('clock_ampm_val').value = "AM";
		$('clock_ampm').innerHTML = "AM";
	}

	update_run_time("clock");
}

function clock_minute_changer(e) {
	var the_minute	= parseInt($F('clock_minute_val'), 10);
	var new_minute	= 0;

	if (e.shiftKey) {
		if (the_minute > 0) {
			new_minute = the_minute - 1;
		} else {
			new_minute = 59;
		}
	} else {
		if (the_minute < 59) {
			new_minute = the_minute + 1;
		}
	}

	// I'm doing the same thing here that I was doing in the
	// hour function with trying to maintain readability
	if (new_minute < 10) {
		$('clock_minute').innerHTML = '0'+new_minute;
	} else {
		$('clock_minute').innerHTML = new_minute;
	}

	$('clock_minute_val').value = new_minute;

	update_run_time("clock");
}

function clock_hour_changer(e) {
	var the_hour	= parseInt($F('clock_hour_val'), 10);
	var new_hour	= 1;

	if (e.shiftKey) {
		if (the_hour > 1) {
			new_hour = the_hour - 1;
		} else {
			new_hour = 12;
		}
	} else {
		if (the_hour < 12) {
			new_hour = the_hour + 1;
		}
	}

	// For readability's sake, I want to keep a 0 prefixed
	// to the number if it's less than 10. However javascript
	// is doing something funky if I always prefix a 0. That's
	// why I broke it up with an IF statement.
	if (new_hour < 10) {
		$('clock_hour').innerHTML = '0'+new_hour;
	} else {
		$('clock_hour').innerHTML = new_hour;
	}

	$('clock_hour_val').value = new_hour;

	update_run_time("clock");
}

/**
* Determine the number of days in a month,year combo
*
* Code taken from
*	http://www.go4expert.com/forums/showthread.php?t=886
*/
function daysInMonth(iMonth, iYear) {
	if(iMonth != 0) {
		iMonth = iMonth - 1;
	}

	return 32 - new Date(iYear, iMonth, 32).getDate();
}
