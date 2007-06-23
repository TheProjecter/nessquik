function show_add_special_plugin_profile() {
	$('work_container2').innerHTML = '';

	var url = "async/admin_settings.php";
	var params = "action=x_show_add_special_profile";

	Element.hide('welcome_box');
	Element.show('workbox');

	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}

function do_add_special_plugin_profile() {
	$('action').value 	= "do_add_special_plugin_profile";
	var params 		= $('nessus_reg').serialize(true);
	var url 		= "async/admin_settings.php";

	total_plugins = get_plugins_count();

	if ($F('special_plugin_name') == '') {
		alert("You need to enter a name for this plugin profile");
		return;
	}

	if(total_plugins < 1) {
		alert("You need to select at least one plugin to include in this profile");
		return;
	}

	var elementGroupsList	= document.getElementsByClassName('groups');

	// At least one group must be associated with a scanner
	if(elementGroupsList.length < 1) {
		alert("You must select at least one group allowed to use this plugin profile");
		return;
	}

	/**
	* Send the request to update the entries to the server to save them
	* The form has already been serialized in the params variable, so 
	* there's no need to make some cryptic url.
	*/
	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_add_special_plugin_profile_resp
		});
}

function do_add_special_plugin_profile_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		alert("Plugin profile created successfully");
	} else if (the_mesg == "dupe") {
		alert("Plugin profile already exists");
	}
}

function show_plugin_profiles() {
	$('work_container2').innerHTML 	= '';
	var params 			= "action=x_show_plugin_profiles";
	var success_container		= "work_container2";

	var url = "async/admin_settings.php";

	Element.hide('welcome_box');
	Element.show('workbox');

	new Ajax.Updater(
		{success: success_container},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}

function show_add_scanner() {
	$('work_container2').innerHTML = '';

	var url = "async/admin_settings.php";
	var params = "action=x_show_add_scanner";

	Element.hide('welcome_box');
	Element.show('workbox');

	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}

/**
* Add a scanner to the database
*
* When a new scanner is ready to be added, this function
* will handle sending the request to the webserver.
*
* @see do_add_scanner_resp
*/
function do_add_scanner() {
	$('action').value 	= "do_add_scanner";
	var params 		= $('nessus_reg').serialize(true);
	var url 		= "async/admin_settings.php";
	var scanner_name 	= $F('scanner_name');

	// Scanners must also be named
	if (scanner_name == '') {
		alert("You must provide a name for the scanner");
		return;
	}

	var elementGroupsList	= document.getElementsByClassName('groups');

	// At least one group must be associated with a scanner
	if(elementGroupsList.length < 1) {
		alert("You must select at least one group allowed to use this scanner");
		return;
	}

	/**
	* Send the request to update the entries to the server to save them
	* The form has already been serialized in the params variable, so 
	* there's no need to make some cryptic url.
	*/
	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_add_scanner_resp
		});
}

/**
* Handle add scanner response
*
* After a request to add a scanner has been sent, this function
* handles the response that is sent back by the server. There
* are several possible messages that can be received, and each
* will results in a different alert for the admin
*
* @param object $origResp XMLHTTPRequest response object
* @see do_add_scanner
*/
function do_add_scanner_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		alert("Scanner created successfully");
	} else if (the_mesg == "dupe") {
		alert("Scanner already exists");
	} else if (the_mesg == "fail_no_name") {
		alert("You must provide a name for this scanner");
	} else if (the_mesg == "fail_no_groups") {
		alert("You must select at least one group to associate this scanner with");
	}
}

function show_scanners() {
	$('work_container2').innerHTML 	= '';
	var params 			= "action=x_show_scanners";
	var success_container		= "work_container2";

	var url = "async/admin_settings.php";

	Element.hide('welcome_box');
	Element.show('workbox');

	new Ajax.Updater(
		{success: success_container},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}

function show_scanners_for_group(group_id) {
	url = "async/admin_settings.php";
	params = "action=x_show_scanners_for_group&group_id="+group_id;

	new Ajax.Updater(
		'scanners',
		url,
		{
			method: 'post', 
			parameters: params
		});

	$('current_viewed_group').value = group_id;
}

function show_list_plugin_profile(type, page) {
	var url = "async/worker.php";
	var params = '';

	if (page == "add") {
		Element.hide('groups_box');
	}

	Element.show('plugins_box');

	if (type == "family") {
		params = "action=x_plugin_specific_search_family";
	} else if (type == "severity") {
		params = "action=x_plugin_specific_search_severity";
	}

	new Ajax.Updater(
		'list_of_plugins',
		url,
		{
			method: 'post', 
			parameters: params
		});
}

/**
* Display the general admin settings
*
* This function handles updating the 'general' section of the
* admin settings page so that the admin can change the values
*/
function show_admin_settings_general() {
	$('work_container2').innerHTML = '';

	var url 	= "async/admin_settings.php";
	var params	= "action=x_show_admin_settings_general";

	Element.hide('welcome_box');
	Element.show('workbox');

	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}

/**
* Search for a division/group
*
* When creating either a new scanner or plugin profile, it is
* necessary to specify the groups that these items will be
* associated with. This function will search for groups based
* on the words that are typed into the search box on either
* of the previously mentioned pages. It will also update the
* necessary fields so that the groups can be seen by the admin
*/
function search_for_group() {
	var search_this = $F('search_group');

	url = "async/admin_settings.php";
	params = "action=x_group_search&search_for="+search_this;

	new Ajax.Updater(
		'list_of_groups',
		url,
		{
			method: 'post', 
			parameters: params
		});

	/**
	* This function is also used by the add_scanner page
	* so this hiding needs to be trapped in the event that
	* this function is being called from that page because
	* that page has no element called plugins_box
	*/
	try {
		Element.hide('plugins_box');
	} catch (e) {}
	Element.show('groups_box');
}

/**
* Add group to the selected groups list
*
* To add a group to the list of selected groups for (as an example)
* on the special plugin profile creation page, this function is
* needed. This function will take a group from the available list
* and place them in the selected list so that when the form is
* submitted, the list of groups for that operation is sent along
* to the server with the rest of the form.
*
* @param string $item Value to include in the form field for this group
* @param string $disp What to display in the selected groups
*	box after you have selected a group to put in that list
* @see remove_group
*/
function add_group(item, disp) {
	var ni 				= $('selected_groups');
	var num 			= $F("group_counter");
	var newnum			= (parseInt(num) + 1);
	$("group_counter").value 	= newnum;
	var divIdName 			= "group"+newnum+"Div";

	var elementGroupsList	= document.getElementsByClassName('groups');

	/**
	* If the users try to choose the same group twice, this block 
	* of code will catch that and alert them that specifying the
	* same plugin twice is not allowed.
	*/
	if(elementGroupsList.length > 0) {
		for (x = 0; x < elementGroupsList.length; x++) {
			var inner_stuff = elementGroupsList[x].innerHTML
			if(inner_stuff.match(disp.escapeHTML())) {
				alert('You have already added this group to the list');
				return;
			} else if (inner_stuff.match("All Groups")) {
				alert('You have already added this plugin to the list');
				return;
			}
		}
	}

	var newdiv = document.createElement('div');
	newdiv.setAttribute("id",divIdName);

	// Begin making new table entry
	var new_content = "<input type='hidden' name='groups["+newnum+"]' value='"+item+"'>";
	new_content = new_content + "<table width='100%'><tr><td style='width: 10%; text-align: center;'>";
	new_content = new_content + "<span style='cursor: pointer;'>";
	new_content = new_content + "<img src='images/delete.png' onClick=\"remove_group('"+divIdName+"')\">";
	new_content = new_content + "</span>";
	new_content = new_content + "</td><td width='90%' style='text-align: left;'>";
	new_content = new_content + "<div class='groups'>"+disp.escapeHTML()+"</div>";
	new_content = new_content + "</td></tr></table>";

	newdiv.innerHTML = new_content;

	ni.appendChild(newdiv);
}

/**
* Remove a group from the selected list
*
* After adding a group to the list of selected groups the
* admin can remove a selected group using this function.
* This function actually removes the entire div that the
* group is contained in from the DOM. Therefore I dont
* need to have any magicalness in the function to specifically
* remove the input field from the form.
*
* @param string $divNum DIV that contains the group information.
*	This div will be removed by this function
* @see add_group
*/
function remove_group(divNum) {
	grp_count = $F('group_counter');
	grp_count = grp_count - 1;

	$('group_counter').value = grp_count;

	var d 		= $('selected_groups');
	var olddiv 	= $(divNum);
	d.removeChild(olddiv);
}

/**
* Display the plugins list
*
* On the 'add special plugin profile' page, the admin has the
* ability to click the "Plugins to include..." text and have
* the current list of 'searched for plugins' displayed. This
* function will hide the groups box and display the plugins
* box without altering the contents of the plugins box
*
* @see show_groups
*/
function show_plugins() {
	Element.hide('groups_box');
	Element.show('plugins_box');
}

/**
* Display the groups list
*
* On the 'add special plugin profile' page, the admin can also
* click on the "Groups that are allowed..." text to view the
* current list of groups that they have searched for. This function
* hides the plugin list and displays the group list without
* altering the contents of either box.
*
* @see show_plugins
*/
function show_groups() {
	Element.hide('plugins_box');
	Element.show('groups_box');
}

/**
* Delete a plugin profile
*
* Obviously if I'm allowed to create special plugin profiles
* then I should equally be allowed to delete them if I dont
* want them any more. This function handles the deletion of
* special plugin profiles by sending a request to the server.
*
* @param integer $profile_id Profile ID of the special
*	plugin profile that is to be deleted
* @see do_delete_specific_plugin_profile_resp
*/
function do_delete_plugin_profile(profile_id) {
	var make_sure = confirm("Are you sure you want to delete this plugin profile?");

	if (!make_sure) {
		return;
	}

	var url = "async/admin_settings.php";
	var params = "action=do_delete_plugin_profile&profile_id="+profile_id;

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_delete_plugin_profile_resp
		});
}

/**
* Response to deleting a plugin profile
*
* After a request to the server to delete a specific plugin
* profile has been made, this function will handle the
* response sent back by the server. If the request failed
* for any reason, the individual will be alerted that the
* operation failed. Otherwise, depending on the number of
* specific plugin profiles that are left, a portion of the
* webpage will be updated to reflect the new list of plugin
* profiles
*
* @param object $origResp XMLHTTPRequest response
* @see do_delete_plugin_profile
*/
function do_delete_plugin_profile_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		show_plugin_profiles();
	} else {
		alert("Could not remove the plugin profile from this group");
	}
}

/**
* Delete a group's scanners
*
* This function will delete a group's scanners. This is useful
* because since a group can have many scanners, I may want to
* delete all the scanners associated with a group without having
* to individually delete each scanner.
*
* @param integer $group_id Group ID of the group whose scanners
*	are going to be deleted
* @see do_delete_group_scanners_resp
*/
function do_delete_group_scanners(group_id) {
	var url = "async/admin_settings.php";
	var params = "action=do_delete_group_scanners&group_id="+group_id;

	var make_sure = confirm("Are you sure you want to delete all the scanners for this group?");

	if (!make_sure) {
		return;
	}

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_delete_group_scanners_resp
		});
}

/**
* Response to deleting a group's scanners
*
* After confirming that we want to delete a group's scanners,
* this function will act as the callback function. It will
* handle the response from the server and alert the user if
* the group's scanners were not able to be removed.
*
* Upon passing, the group lists on the pages will be refreshed
*
* @param object $origResp Original XMLHTTPRequest response object 
* @see do_delete_group_scanners
*/
function do_delete_group_scanners_resp(origResp) {
	var mesg_tmp	= origResp.responseText.split("::");
	var the_mesg 	= mesg_tmp[0];
	var the_group	= mesg_tmp[1];
	var cvpp	= $F('current_viewed_group');

	if (the_mesg == "pass") {
		show_scanners();
	} else {
		alert("Could not remove the scanners for this group");
	}
}

function show_edit_scanner(scanner_id) {
	$('work_container2').innerHTML = '';

	var url = "async/admin_settings.php";
	var params = "action=x_show_edit_scanner&scanner_id="+scanner_id;

	var grp_url	= "async/admin_settings.php";
	var grp_params	= "action=x_do_get_specific_groups&page=scanner&scanner_id="+scanner_id;

	Element.hide('welcome_box');
	Element.show('workbox');

	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			asynchronous: false,
			evalScripts: true
		});

	new Ajax.Updater(
		{success: 'selected_groups'},
		grp_url, 
		{
			method: 'post',
			parameters: grp_params,
			asynchronous: false,
			evalScripts: true
		});

}

function show_edit_special_plugin_profile(profile_id) {
	$('work_container2').innerHTML = '';

	var pprofile_url 	= "async/admin_settings.php";
	var pprofile_params	= "action=x_show_edit_special_plugin_profile&profile_id="+profile_id;

	var plu_url 	= "async/settings.php";
	var plu_params 	= "action=x_do_get_specific_scan_plugins&profile_id="+profile_id+"&plugin_profile=true";

	var grp_url	= "async/admin_settings.php";
	var grp_params	= "action=x_do_get_specific_groups&page=plugin_profile&profile_id="+profile_id;

	Element.hide('welcome_box');
	Element.show('workbox');

	new Ajax.Updater(
		{success: 'work_container2'},
		pprofile_url, 
		{
			method: 'post',
			parameters: pprofile_params,
			asynchronous: false,
			evalScripts: true
		});

	new Ajax.Updater(
		{success: 'selected_plugins'},
		plu_url,
		{
			method: 'post',
			parameters: plu_params,
			asynchronous: false,
			evalScripts: true
		});

	new Ajax.Updater(
		{success: 'selected_groups'},
		pprofile_url, 
		{
			method: 'post',
			parameters: grp_params,
			asynchronous: false,
			evalScripts: true
		});
}

function do_edit_special_plugin_profile() {
	var url 		= "async/admin_settings.php";
	$('action').value 	= "do_edit_special_plugin_profile";
	var params 		= $('nessus_reg').serialize(true);

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_edit_special_plugin_profile_resp
		});
}

function do_edit_special_plugin_profile_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		alert("Plugin profile sucessfully updated");
		show_plugin_profiles();
	} else {
		alert("Plugin profile was not updated successfully");
	}
}

/**
* Delete a specific scanner from a group
*
* When changing the scanners for a given group, the admin
* will need the ability to remove existing scanners if that
* scanner is retired, or in some other way not used any more
* To keep the available scanners from becomming too polluted,
* this function can be used to delete a specific scanner so
* that it is no longer available for selection from the
* scans configuration page
*
* @param integer $scanner_id Scanner ID to delete from the database
* @see do_delete_specific_scanner_resp
*/
function do_delete_specific_scanner(scanner_id) {
	var url = "async/admin_settings.php";
	var params = "action=do_delete_specific_scanner&scanner_id="+scanner_id;

	var make_sure = confirm("Are you sure you want to delete this scanner from this group?");

	if (!make_sure) {
		return;
	}

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_delete_specific_scanner_resp
		});
}

/**
* Handle response from deleting a specific scanner
*
* After removing a specific scanner from it's associated group,
* this function will handle the response from the server. It will
* also update any necessary page elements and notify the admin
* as to whether the deletion was successful or not
*
* @param object origResp XMLHTTPRequest response
* @see do_delete_specific_scanner
*/
function do_delete_specific_scanner_resp(origResp) {
	var tmp = origResp.responseText.split('::');

	/**
	* The message returned by the server. Can be one of the following
	*
	* 	pass
	*	fail
	*/
	var the_mesg 	= tmp[0];

	// The number of scanners still associated with the deleted scanners group
	var remaining	= tmp[1];

	// The group ID of the group that the deleted scanner was a member of
	var group_id 	= tmp[2];

	if (the_mesg == "pass") {
		if (remaining > 0) {
			/**
			* If there are scanners remaining for this group, then
			* I want to update the page element that lists the scanners
			* for the group who just had their scanner deleted
			*/
			show_scanners_for_group(group_id);
		} else {
			/**
			* Otherwise, I want to refresh the list of groups because
			* we can't get here unless the group no longer has any
			* scanners associated with it. If that happens, then it
			* makes sense to update the list of groups because there
			* is now one less group
			*/
			show_scanners();
			$('scanners').innerHTML = "click a group to display their scanners";
		}
	} else {
		alert("Could not remove the scanner from this group");
	}
}

/**
* Delete a scanner from nessquik's database
*
* Scanners can be removed from the nessquik database if
* they're no longer used. This function handles calling
* the backend scripts that will remove the scanner from
* the database
*
* @param int $scanner_id ID of the scanner to remove
* @see do_delete_scanner_resp
*/
function do_delete_scanner(scanner_id) {
	var make_sure = confirm("Are you sure you want to delete this scanner?");

	if (!make_sure) {
		return;
	}

	var url		= "async/admin_settings.php";
	var params 	= "action=do_delete_scanner&scanner_id="+scanner_id;

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_delete_scanner_resp
		});
}

/**
* Response handler for removing a scanner
*
* After the function to remove the scanner has been called,
* this function will handle the response that is returned
* by the server.
*
* @param object $origResp XMLHTTPRequest response object
* @see do_delete_scanner
*/
function do_delete_scanner_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		show_scanners();
	} else {
		alert("Unable to remove the scanner");
	}
}

/**
* Edit a scanner's properties
*
* This function will wrap the form containing the modified
* scanner details and will send the data to the server for
* processing.
*
* @see do_edit_scanner_resp
*/
function do_edit_scanner() {
	var url 		= "async/admin_settings.php";
	$('action').value 	= "do_edit_scanner";
	var params 		= $('nessus_reg').serialize(true);
	var scanner_name 	= $F('scanner_name');

	// Scanners must also be named
	if (scanner_name == '') {
		alert("You must provide a name for the scanner");
		return;
	}

	var elementGroupsList	= document.getElementsByClassName('groups');

	// At least one group must be associated with a scanner
	if(elementGroupsList.length < 1) {
		alert("You must select at least one group allowed to use this scanner");
		return;
	}

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_edit_scanner_resp
		});
}

/**
* Handle editing scanner resp
*
* After a request to edit a scanner has been processed,
* this method will handle catching the response that
* is generated
*
* @param object XMLHTTPRequest response object
* @see do_edit_scanner
*/
function do_edit_scanner_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		alert("Settings updated successfully");
		show_scanners();
	} else {
		alert("Unable to update the scanner settings");
	}
}

function regenerate_client_key(scanner_id) {
	var url = "async/admin_settings.php";
	var params = "action=regenerate_client_key&scanner_id="+scanner_id;

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: regenerate_client_key_resp
		});
}

function regenerate_client_key_resp(origResp) {
	var tmp_msg 	= origResp.responseText.split("::");
	var the_mesg	= tmp_msg[0];
	var new_key	= tmp_msg[1];

	if (the_mesg == "pass") {
		$('client_key').value = new_key;
		$('client_key_display').innerHTML = new_key;
	} else {
		alert("Did not regenerate the client key successfully");
	}
}
