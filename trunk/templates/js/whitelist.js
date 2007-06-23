/**
* Add new entry to the whitelist
*
* Adds a new individual entry to the whitelist. This also
* acts as adding a new user to the whitelist if no entries
* for that user exist yet because the "users" is just a 
* distinct listing of usernames from the whitelist table
*
* @access public
* @see do_adduser_resp
*/
function do_adduser() {
	iuser = $F('add_username');
	iitem = $F('add_item');

	if(iuser == "" || iuser == "user") {
		alert("Username cannot be empty");
		return;
	}

	if (iitem == "" || iitem == "ip or host or range") {
		alert("Whitelist entry cannot be empty");
		return;
	}

	url	= "async/whitelist.php";
	pars 	= "action=x_do_adduser&username="+iuser+"&wlitem="+iitem;
	var myAjax = new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: pars,
			onComplete: do_adduser_resp
		});
}

/**
* Notify admin of status of whitelist addition
*
* After the request to add an entry to the whitelist has
* been made, this function will catch the response sent
* back and notify the admin of either a success or failure.
* It will also update any div elements that need to be
* updated to reflect the new whitelist.
*
* @access public
* @param object $origResp XMLHTTPRequest response
* @see req_refresh_users
*/
function do_adduser_resp(origResp) {
	var tmp 		= origResp.responseText.split("::");
	var resp 		= tmp[0];
	var the_user 		= tmp[1];
	var viewing_user	= $F('viewing_current_user');

	if (resp == "pass") {
		alert("Entry added to whitelist");
		req_refresh_users();

		if (the_user == viewing_user) {
			user_entries(viewing_user);
		}

		$('add_username').value		= 'user';
		$('add_item').value 		= 'ip or host or range';
		$('add_username').style.color	= "#999";
		$('add_item').style.color 	= "#999";
	} else if(resp == "dupe") {
		alert("Entry already exists in list");
	}  else {
		alert("Entry was not added to whitelist");
	}
}

/**
* Delete an entire user from whitelist
*
* Deletes all a users entries from the whitelist so
* that selective deletion does not need to be done for
* each entry the user has
*
* @access public
* @param string $username Username to remove from whitelist
* @see do_delete_whitelist_user_resp
*/
function do_delete_whitelist_user(username) {
	question = confirm("Are you sure you want to remove "+username+"? All their entries will be removed as well.");

	if(question) {
		$('viewing_current_user').value = '';

		url 	= "async/whitelist.php";
		pars 	= "action=x_do_delete_whitelist_user&username="+username;
		var myAjax = new Ajax.Request(
			url,
			{
				method: 'post',
				parameters: pars,
				onComplete: do_delete_whitelist_user_resp
			});
	} else {
		return;
	}
}

/**
* Notify admin of whitelist user deletion status
*
* After a user has been deleted from the whitelist, this
* function will catch the response and inform the admin
* of the status of the deletion. It will also update
* information in any necessary divs so that the interface
* is up to date
*
* @access public
* @param object $origResp XMLHTTPRequest response
* @see do_delete_whitelist_user
* @see req_refresh_users
*/
function do_delete_whitelist_user_resp(origResp) {
	var resp = origResp.responseText;

	if (resp == "pass") {
		alert("Successfully removed the user and all their associated entries");
		req_refresh_users();
		Element.hide('whitelist_edit');
	} else {
		alert("Did not successfully remove the user from the list");
	}
}

/**
* Delete an individual whitelist entry
*
* Users can have a lot of different whitelist entries, so this
* function provides a way of selectively deleting entries from
* an individual users whitelist
*
* @access public
* @param integer $wlid Whitelist ID to delete
* @see do_delete_whitelist_entry_resp
*/
function do_delete_whitelist_entry(wlid) {
	question = confirm("Are you sure you want to remove this entry?");

	if(question) {
		$('viewing_current_user').value = '';
		var url		= "async/whitelist.php";
		var pars 	= "action=x_do_delete_whitelist_entry&wlid="+wlid;
		var myAjax = new Ajax.Request(
			url,
			{
				method: 'post',
				parameters: pars,
				onComplete: do_delete_whitelist_entry_resp
			});
	} else {
		return;
	}
}

/**
* Refresh user list
*
* This function will refresh the entire whitelist user
* list to update the new additions if there are any
* or removals if any users no longer exist in the list
*
* @access public
* @see refresh_user_list
*/
function req_refresh_users() {
       params  = "action=x_refresh_users";
       url     = 'async/whitelist.php';

       var myAjax = new Ajax.Request(
               url,
               {
                       method: 'post',
                       parameters: params,
                       onComplete: req_refresh_users_resp
               }
       );
}

/**
* Update userlist div
*
* After the call to retrieve the users in the whitelist
* is complete, this function acts as a catch function
* to update the div that displays all the whitelist users
*
* @access public
* @param object $origResp XMLHTTPRequest response
* @see req_refresh_users
*/
function req_refresh_users_resp(origResp) {
	$('userlist').innerHTML = origResp.responseText;
}

/**
* Refresh user entries
*
* Acts as a return function for the function which
* deletes a single whitelist entry for the user. This
* function handles updating any parts of the page that
* need to be updated to reflect the changes that were
* made after removing a specific whitelist entry
*
* @access public
* @param object $origResp XMLHTTPRequest returned object
* @see do_delete_whitelist_entry_resp
* @see req_refresh_users
* @see user_entries
*/
function do_delete_whitelist_entry_resp(origResp) {
	var mdata = origResp.responseText.split('::');

	var username	= mdata[0];
	var resp 	= mdata[1];
	var remaining	= mdata[2];

	if (resp == "pass") {
		alert("Successfully deleted the entry from the whitelist");
		req_refresh_users();

		if (remaining > 0) {
			user_entries(username);
		} else {
			$('whitelist').style.color 	= "#999";
			$('whitelist').style.textAlign	= "center";
			$('whitelist').innerHTML 	= "click a username to display their whitelist";
		}
	} else {
		alert("Could not delete the entry from the whitelist");
	}
}

/**
* Display user's whitelist entries
*
* After clicking on a username in the whitelist, this function
* will display all the entries associated with this user.
*
* @access public
* @param string $the_user Username to pull entries for
*/
function user_entries(the_user) {
	var url = "async/whitelist.php";
	var params = "action=x_show_entry_info&username="+the_user;

	$('viewing_current_user').value 	= the_user;
	$('whitelist').innerHTML 		= '';
	$('wlentry_td').style.verticalAlign	= "top";
	$('whitelist').style.textAlign	 	= "left";
	$('whitelist').style.color 		= "#000";
	Element.show('whitelist');

	var myAjax = new Ajax.Updater(
		{success: 'whitelist'},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}

/**
* Rename a user in the whitelist
*
* In case you botch the name while adding someone to the
* whitelist and you've already added all their entries,
* this function will rename the user so that their whitelist
* name is correct according to admin input
*
* @access public
* @see do_rename_user_resp
*/
function do_rename_user() {
	var from_user 	= $F('mv_from_user');
	var to_user 	= $F('mv_to_user');

	if (from_user == '' || from_user == "old name") {
		alert("You must specify a user to rename");
		return;
	}

	if (to_user == '' || to_user == "new name") {
		alert("You must specify a new name for this user");
		return;
	}

	params  = "action=x_rename_user&from="+from_user+"&to="+to_user;
	url     = 'async/whitelist.php';

	var myAjax = new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
                       onComplete: do_rename_user_resp
               }
       );
}

/**
* Handle rename user response
*
* After renaming a user, the rename function will call
* this callback function to handle updating any divs that
* may need to be updated to reflect changes to the whitelist
*
* @access public
* @param object $origResp XMLHTTPRequest response
* @see do_rename_user
* @see req_refresh_users
*/
function do_rename_user_resp(origResp) {
	var resp = origResp.responseText;

	if (resp == "pass") {
		alert("Successfully renamed the user in the whitelist");
		req_refresh_users();
		$('mv_from_user').value	= '';
		$('mv_to_user').value 	= '';
		Element.toggle('whitelist_rename');
	} else if (resp == "exists") {
		alert("The username you are renaming to, already exists.");
	} else {
		alert("Could not rename the user");
	}
}

/**
* Copy entries from one user to another
*
* Sometimes it may be necessary to copy the whitelist entries
* of one user to that of another user. This is useful if you
* need to more or less clone an entry. This function handles
* sending the request to copy to the server.
*
* @access public
* @see do_copy_user_resp
*/
function do_copy_user() {
	var from_user	= $F('copy_from_user');
	var to_user 	= $F('copy_to_user');

	if (from_user == '' || from_user == "copy from") {
		alert("You must specify a user to copy from");
		return;
	}

	if (to_user == '' || to_user == "copy to") {
		alert("You must specify a user to copy to");
		return;
	}

       params  = "action=x_copy_user&from="+from_user+"&to="+to_user;
       url     = 'async/whitelist.php';

       var myAjax = new Ajax.Request(
               url,
               {
                       method: 'post',
                       parameters: params,
                       onComplete: do_copy_user_resp
               }
       );
}

/**
* Handles a copy user response
*
* After a request to copy a user has been sent, the sending
* function will return a response that will be caught by this
* function. Here is where any alerting of the admin will occur
* as well as refreshing any elements on the page that need
* to be refreshed.
*
* @access public
* @param object $origResp XMLHTTPRequest response
* @see do_copy_user_resp
* @see req_refresh_users
*/
function do_copy_user_resp(origResp) {
	var resp = origResp.responseText;

	if (resp == "pass") {
		alert("Successfully copied the first user's entries to the second user's whitelist");
		req_refresh_users();
		$('copy_from_user').value	= '';
		$('copy_to_user').value 	= '';
		Element.toggle('whitelist_copy');
	} else if (resp == "none") {
		alert("Nothing was copied because all the entries already exist.");
	}
}

/**
* Switcher for the whitelist actions
*
* Handles showing and hiding the document elements
* that allow modifying the whitelist
*
* @param string $action Div element containing action you want to display
*/
function show_whitelist_action(action) {
	Element.hide('whitelist_add');
	Element.hide('whitelist_copy');
	Element.hide('whitelist_rename');

	Element.show(action);
}
