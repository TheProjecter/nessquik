/**
* Displays all forms of input
*
* Nessquik supports multiple fields of input that the user
* can enter values into. This function is a wrapper that
* will show and hide the available inputs when they are
* clicked on by the user
*
* @param string $type Type of input field to display
*/
function show_input(type) {
	Element.hide('plugins_box');
	Element.hide('workbox');
	Element.show('devices_box');

	Element.hide('by_list');
	Element.hide('by_cluster');
	Element.hide('by_computer');
	Element.hide('by_whitelist');
	Element.hide('by_saved');
	Element.hide('waiting');

	Element.show('plugins_menu');
	Element.show('configure_menu');

	Element.show('list_of_plugins');
	Element.show('list_of_plugins_text');
	Element.show('selected_plugins');
	Element.show('selected_plugins_text');

	if (type == "computer") {
		var inner_content 		= $('by_computer').innerHTML;

		if (inner_content == '') {
			var url 		= "async/create.php";
			var pars		= "action=x_single_computer";
			$('scan_type').value	= "computer";
			new Ajax.Request(
				url, 
				{
					method: 'post',
					parameters: pars,
					onComplete: show_single_computer_resp
				});
			Element.show('waiting');
		} else {
			Element.hide('waiting');
			Element.show('by_computer');
		}
	} else if (type == "list") {
		Element.show('by_list');
		$('scan_type').value = "list";

		if ($F('list') == "") {
			$('list').value = 'click here to enter a list of computers';
			$('list').style.color = "#999";
			$('list').style.textAlign = "center";
		}

	} else if (type == "cluster") {
		var inner_content		= $('by_cluster').innerHTML;

		if (inner_content == '') {
			var url 		= "async/create.php";
			var pars 		= "action=x_single_cluster";

			$('scan_type').value = "cluster";
			new Ajax.Request(
				url, 
				{
					method: 'post',
					parameters: pars,
					onComplete: show_single_cluster_resp
				});
			Element.show('waiting');
		} else {
			Element.hide('waiting');
			Element.show('by_cluster');
		}
	} else if (type == "whitelist") {
		var inner_content 	= $('by_whitelist').innerHTML;

		if (inner_content == '') {
			var url		= "async/create.php";
			var pars	= "action=x_whitelist";
			$('scan_type').value = "whitelist";
			new Ajax.Request(
				url, 
				{
					method: 'post',
					parameters: pars,
					onComplete: show_whitelist_resp
				});
			Element.show('waiting');
		} else {
			Element.hide('waiting');
			Element.show('by_whitelist');
		}
	} else if (type == "saved") {
		// Hide menus to discourage people from thinking they
		// can configure saved scans from the create page
		Element.hide('plugins_menu');
		Element.hide('configure_menu');

		// Hide the plugins lists to discourage people from trying
		// to add more plugins to a saved scan.
		Element.hide('list_of_plugins');
		Element.hide('list_of_plugins_text');
		Element.hide('selected_plugins');
		Element.hide('selected_plugins_text');

		var inner_content = $('by_saved').innerHTML;

		if (inner_content == '') {
			var url = "async/create.php";
			var pars = "action=x_saved";
			$('scan_type').value = "saved";
			new Ajax.Request(
				url, 
				{
					method: 'post',
					parameters: pars,
					onComplete: show_saved_resp
				});
			Element.show('waiting');
		} else {
			Element.hide('waiting');
			Element.show('by_saved');
		}
	}
}

/**
* Display the saved scans selection
*
* Much like the whitelist, list, and other forms
* of input, the saved scans can also be chosen here.
* This function will ignore any data sent back to
* it that is not the result of 'saved scans' being
* selected. This prevents multiple input elements
* from being displayed simultaneously.
*
* @param object origResp XMLHTTPRequest response
*/
function show_saved_resp(origResp) {
	if ($('scan_type').value != "saved") {
		return;
	} else {
		$('by_saved').innerHTML = origResp.responseText;
		Element.hide('waiting');
		Element.show('by_saved');
	}
}

/**
* Handle displaying the reponse to show the whitelist
*
* This function will ignore any data sent back to
* it that is not the result of showing the user's
* whitelist entries. This prevents multiple input
* elements from being displayed simultaneously.
*
* @param object $origResp XMLHTTPRequest response
*/
function show_whitelist_resp(origResp) {
	if ($('scan_type').value != "whitelist") {
		return;
	} else {
		$('by_whitelist').innerHTML = origResp.responseText;
		Element.hide('waiting');
		Element.show('by_whitelist');
	}
}

/**
* Handle displaying the reponse to show the registered computers
*
* This function will ignore any data sent back to
* it that is not the result of showing the user's
* registered computers entries. This prevents multiple
* input elements from being displayed simultaneously.
*
* @param object $origResp XMLHTTPRequest response
*/
function show_single_computer_resp(origResp) {
	if ($('scan_type').value != "computer") {
		return;
	} else {
		$('by_computer').innerHTML = origResp.responseText;
		Element.hide('waiting');
		Element.show('by_computer');
	}
}

/**
* Handle displaying the reponse to show the users' clusters
*
* This function will ignore any data sent back to
* it that is not the result of showing the user's
* cluster entries. This prevents multiple input
* elements from being displayed simultaneously.
*
* @param object $origResp XMLHTTPRequest response
*/
function show_single_cluster_resp(origResp) {
	if ($('scan_type').value != "cluster") {
		return;
	} else {
		$('by_cluster').innerHTML = origResp.responseText;
		Element.hide('waiting');
		Element.show('by_cluster');
	}
}

/**
* Clears search field the first time it is clicked
*
* As a way of describing what goes in what fields, I put descriptions
* inside the text field. Clicking on the field for the first time should
* cause the field to be cleared, but all further clickings shouldn't
* clear the field.
*/
function first_clear(to_search) {
	if ($F(to_search) == "search") {
		Field.clear(to_search);
	}

	if ($F(to_search) == "      search") {
		Field.clear(to_search);
	}

	/**
	* Clears scan name field the first time it is clicked
	*
	* As a way of describing what goes in the scan name
	* field, I put a description inside the text field.
	* Clicking on the field for the first time should
	* cause the field to be cleared, but all further clickings shouldn't
	* clear the field.
	*/
	if ($F(to_search) == "Choose a setting") {
		Field.clear(to_search);
		return;
	}

	if ($F(to_search) == "click here to enter a list of computers") {
		$(to_search).style.color = "#000";
		$(to_search).style.textAlign = "left";
		Field.clear(to_search);
		return;
	}

	if ($F(to_search) == "user") {
		$(to_search).style.color = "#000";
		Field.clear(to_search);
		return;
	}

	if ($F(to_search) == "ip or host or range") {
		$(to_search).style.color = "#000";
		Field.clear(to_search);
		return;
	}

	// "copy from" whitelist field
	if ($F(to_search) == "copy from") {
		$(to_search).style.color = "#000";
		Field.clear(to_search);
		return;
	}

	// "copy to" whitelist field
	if ($F(to_search) == "copy to") {
		$(to_search).style.color = "#000";
		Field.clear(to_search);
		return;
	}

	// for the search box on the admin scans "view user" page
	if ($F(to_search) == "refine the list of scans by entering a username") {
		$(to_search).style.color = "#000";
		$(to_search).style.textAlign = "left";
		Field.clear(to_search);
		return;
	}

	// for the search box on the admin scans "view group" page
	if ($F(to_search) == "refine the list of scans by entering a group") {
		$(to_search).style.color = "#000";
		$(to_search).style.textAlign = "left";
		Field.clear(to_search);
		return;
	}
}

/**
* Revert a text fields value to a default value
*
* For spiffiness several of my text fields include default
* values that describe the types of data to type into them.
* If a user clicks in the field, that value is removed. If
* they dont type any data in that field after they have
* clicked on it, that original default value is never restored.
* This function takes care of restoring the default value
* if the user clicks off of a text field and leaves the value
* of the field empty
*
* @param string $field_id Div ID of the field to reset
*/
function change_back(field_id) {
	if ($F(field_id) == "") {
		// The plugin search field
		if (field_id == "search_plugin") {
			$(field_id).value = "search";
			return;
		}

		// The username field for adding to the whitelist
		if (field_id == "add_username") {
			$(field_id).value = "user";
			$(field_id).style.color = "#999";
			return;
		}

		// The IP field for adding to the whitelist
		if (field_id == "add_item") {
			$(field_id).value = "ip or host or range";
			$(field_id).style.color = "#999";
			return;
		}

		// "copy from" whitelist field
		if (field_id == "copy_from_user") {
			$(field_id).value = "copy from";
			$(field_id).style.color = "#999";
			return;
		}

		// "copy to" whitelist field
		if (field_id == "copy_to_user") {
			$(field_id).value = "copy to";
			$(field_id).style.color = "#999";
			return;
		}

		if (field_id == "list") {
			$(field_id).value = "click here to enter a list of computers";
			$(field_id).style.color = "#999";
			$(field_id).style.textAlign = "center";
			return;
		}

		if (field_id == "refine_scan") {
			if ($F('refine_scan_type') == "user") {
				$('refine_scan').value = "refine the list of scans by entering a username";
				$('refine_scan').style.color = "#999";
				$('refine_scan').style.textAlign = "center";
				return;
			}

			if ($F('refine_scan_type') == "refine_scan_group") {
				$('refine_scan').value = "refine the list of scans by entering a group";
				$('refine_scan').style.color = "#999";
				$('refine_scan').style.textAlign = "center";
				return;
			}
		}
	}
}

/**
* Display list of plugin severities or families
*
* This function is attached to the hyperlinks that
* display all the severities and families when
* clicked.
*
* @param string $type Type of plugin category to display.
*	This can be either 'family' or 'severity'.
*/
function show_list(type) {
	var url = "async/worker.php";

	Element.hide('workbox');
	Element.hide('devices_box');	
	Element.show('plugins_box');

	if (type == "family") {
		params = "action=x_plugin_specific_search_family";
	} else if (type == "severity") {
		params = "action=x_plugin_specific_search_severity";
	} else if (type == "all") {
		addEvent("all","all","all plugins");
		return;
	} else if (type == "special") {
		params = "action=x_plugin_specific_search_special";
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
* Toggle plugin description
*
* This method is a wrapper to the hide and show functions
* provided to show and hide the plugin description. When
* a list of plugins is available, the user can click the
* short description of the plugin to view the full
* description. Clicking the short description again will
* hide the full description
*
* @param integer $pluginid ID of the plugin to toggle description for
* @see show_full_desc
*/
function toggle_desc(pluginid) {
	var plugin_desc = $('invis_data'+pluginid).innerHTML;

	if (plugin_desc.length == 0) {
		show_full_desc(pluginid);
		Element.show('invis'+pluginid);
		Element.show('invis'+pluginid);
	} else {
		Element.toggle('invis'+pluginid);
	}
}

/**
* Async call to view the plugin description
*
* This function is the wrapper that sends a call to the
* server to retrieve the full plugin details. It will
* return the results of the call to another function
*
* @param integer $pluginid ID of the plugin to get description for
* @see show_full_desc_resp

*/
function show_full_desc(pluginid) {
	var url = "async/worker.php";
	var pars = "action=x_show_full_desc&pluginid="+pluginid;
	new Ajax.Updater(
		'invis_data'+pluginid,
		url, 
		{
			method: 'post',
			parameters: pars
		});

}

/**
* Performs a quick form validation
*
* This script does not replace server side form validation
* that occurs. Instead it's used as a first line of defense
* from people who try to submit the form without filling
* out all the fields. This approach is nice because if there
* is an error, the user will be notified immediately before
* the form is sent.
*
* Sending the form would have processed the page and if there
* was an error, all the user's entries would have been lost.
* This function is here to prevent that from ever needing to
* happen.
*/
function quick_validate() {
	var empty_list		= false;
	var total_plugin_count	= 0;
	var total_device_count	= 0;
	var total_recur_days	= 0;
	var saved_scan_count	= 0;
	var total_minus_saved	= 0;

	total_plugin_count 	= get_plugins_count();
	total_device_count 	= get_devices_count();
	total_recur_days	= get_recurrence_days_count();
	empty_list 		= is_list_empty();
	saved_scan_count	= get_saved_scan_count();
	total_minus_saved	= total_device_count - saved_scan_count;

	// If at least one device is not a saved scan, then I need
	// to check to see if plugins were specified
	if ((total_minus_saved > 0) || !empty_list) {
		if(total_plugin_count < 1) {
			alert("You need to select at least one plugin to use in the scan");
			return;
		}
	}


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

	document.nessus_reg.submit();
}

function get_recurrence_days_count() {
	var form	= $('nessus_reg');
	var checkboxes	= form.getInputs('checkbox');
	var count	= 0;

	checkboxes.each(
		function(box) {
			var name = box.name;
			if (name.match("days")) {
				if(box.checked) {
					count += 1;
				}
			}
	});

	return count;
}

function is_list_empty() {
	if ($F('list') == '') {
		return true;
	} else if ($F('list') == "click here to enter a list of computers") {
		return true;
	}
}

function get_plugins_count() {
	var total_plugin_count = 0;

	// Plugin lists
	var elementPlugList 	= document.getElementsByClassName('plugs');
	var elementFamList 	= document.getElementsByClassName('fams');
	var elementSevList	= document.getElementsByClassName('sevs');
	var elementSpecList	= document.getElementsByClassName('specs');

	// Get the lengths of the possible plugin lists
	var epl	= elementPlugList.length;
	var efl = elementFamList.length;
	var esl = elementSevList.length;
	var esp	= elementSpecList.length;

	total_plugin_count = parseInt(epl + efl + esl + esp);

	return total_plugin_count;
}

function get_devices_count() {
	var total_device_count = 0;

	// Device lists
	var elementCluList 	= document.getElementsByClassName('clusters');
	var elementRegList 	= document.getElementsByClassName('registered');
	var elementWhiList 	= document.getElementsByClassName('white');
	var elementVhoList 	= document.getElementsByClassName('vhost');
	var elementSavList 	= document.getElementsByClassName('saved');

	// Get the lengths of all possible device lists
	var ecl = elementCluList.length;
	var erl = elementRegList.length;
	var ewl = elementWhiList.length;
	var evl = elementVhoList.length;
	var esl	= elementSavList.length;

	total_device_count = parseInt(ecl + erl + ewl + evl + esl);

	return total_device_count;
}

/**
* Get a count of the number of saved scans chosen to run
*/
function get_saved_scan_count() {
	var elementSavList 	= document.getElementsByClassName('saved');
	var esl			= elementSavList.length;

	return esl;
}

/**
* Adds a plugin item to scan list
*
* This is general code to add plugins, families, severites
* and the 'all' plugins items to the scan list on the right
* side of the page.
*
* Add and remove event javascript taken from the site shown below
* http://www.dustindiaz.com/add-and-remove-html-elements-dynamically-with-javascript/
* Modified for use with this application
*
* @access public
* @param string $type Input type to add to the form
* @param string $item Value to assign to the input element
* @param string $disp String to display for the input element
* @see removeEvent
*/
function addEvent(type, item, disp) {
	var ni = $('selected_plugins');
	var ness = $('nessus_reg');

	/**
	* plugin_counter holds the current total number of plugins
	* that are being displayed on the page. This number is crucial
	* to giving me the ability to selectively remove any plugin from
	* the list of selected plugins
	*/
	var num 			= $("plugin_counter").value;
	var newnum			= (parseInt(num) + 1);
	$("plugin_counter").value 	= newnum;

	var divIdName = "file"+newnum+"Div";
	var inputIdName = "item"+newnum;

	var elementPlugList 	= document.getElementsByClassName('plugs');
	var elementFamList 	= document.getElementsByClassName('fams');
	var elementSevList	= document.getElementsByClassName('sevs');
	var elementSpecsList	= document.getElementsByClassName('specs');

	var allInputs 		= Form.getInputs('nessus_reg', 'hidden');
	var possible_inputs 	= new Array();

	allInputs.each(
		function(the_input) {
			var to_search 	= the_input.name;
			if (to_search.match('item')) {
				possible_inputs.push(the_input.value);
			}
		}
	);

	var inner_stuff_all 	= possible_inputs.indexOf("a:all");

	/**
	* If the user has chosen a single plugin, then if they try to
	* choose the same plugin again, this block of code will catch
	* that and alert them that specifying the same plugin twice
	* is not allowed.
	*/
	if(elementPlugList.length > 0) {
		for (x = 0; x < elementPlugList.length; x++) {
			var inner_stuff		= possible_inputs.indexOf("p:"+item);
			if(inner_stuff >= 0) {
				alert('You have already added this plugin to the list');
				return;
			} else if (inner_stuff_all >= 0) {
				alert('You have already added this plugin to the list');
				return;
			}
		}
	}

	// Check for duplicate families in the selected plugins list
	if (elementFamList.length > 0) {
		for (x = 0; x < elementFamList.length; x++) {
			var inner_stuff		= possible_inputs.indexOf("f:"+item);
			if(inner_stuff >= 0) {
				alert('You have already added this family to the list');
				return;
			} else if (inner_stuff_all >= 0) {
				alert('You have already added this plugin to the list');
				return;
			}
		}
	}

	// Check for duplicate severities in the selected plugins list
	if (elementSevList.length > 0) {
		for (x = 0; x < elementSevList.length; x++) {
			var inner_stuff		= possible_inputs.indexOf("s:"+item);
			if(inner_stuff >= 0) {
				alert('You have already added this severity to the list');
				return;
			} else if (inner_stuff_all >= 0) {
				alert('You have already added this plugin to the list');
				return;
			}
		}
	}

	// Check for duplicate plugin profiles in the selected plugins list
	if (elementSpecsList.length > 0) {
		for (x = 0; x < elementSpecsList.length; x++) {
			var inner_stuff		= possible_inputs.indexOf("sp:"+item);
			if(inner_stuff >= 0) {
				alert('You have already added this special plugin to the list');
				return;
			} else if (inner_stuff_all >= 0) {
				alert('You have already added this plugin to the list');
				return;
			}
		}
	}

	var newdiv = document.createElement('div');
	newdiv.setAttribute("id",divIdName);

	// Begin making new table entry
	if (type == "plugin") {
		var new_content = "<input type='hidden' name='item["+newnum+"]' id='"+inputIdName+"' value='p:"+item+"'>";
	} else if (type == "family") {
		var new_content = "<input type='hidden' name='item["+newnum+"]' id='"+inputIdName+"' value='f:"+item+"'>";
	} else if (type == "severity") {
		var new_content = "<input type='hidden' name='item["+newnum+"]' id='"+inputIdName+"' value='s:"+item+"'>";
	} else if (type == "all") {
		var new_content = "<input type='hidden' name='item["+newnum+"]' id='"+inputIdName+"' value='a:"+item+"'>";
	} else if (type == "special") {
		var new_content = "<input type='hidden' name='item["+newnum+"]' id='"+inputIdName+"' value='sp:"+item+"'>";
	}

	// then the bulk of the plugin entry needs to be added to the new_content
	new_content = new_content + "<table width='100%'><tr><td style='width: 10%; text-align: center;'>";
	new_content = new_content + "<span style='cursor: pointer;'>";
	new_content = new_content + "<img src='images/delete.png' onClick=\"removeEvent('"+divIdName+"')\">";
	new_content = new_content + "</span>";
	new_content = new_content + "</td><td width='90%' style='text-align: left;'>";

	// depending on the type of device, different HTML needs to be displayed.
	if (type == "plugin") {
		new_content = new_content + "<div class='plugs'>"+disp.escapeHTML()+"</div>";
	} else if (type == "family") {
		new_content = new_content + "<div class='fams'>"+disp.escapeHTML()+"</div>";
	} else if (type == "severity") {
		new_content = new_content + "<div class='sevs'>"+disp.escapeHTML()+"</div>";
	} else if (type == "all") {
		new_content = new_content + "<div class='plugs'>All Plugins Available: "+disp.escapeHTML()+"</div>";
	} else if (type == "special") {
		new_content = new_content + "<div class='specs'>Special Plugin: "+disp.escapeHTML()+"</div>";
	}

	// close up open HTML tags
	new_content = new_content + "</td></tr></table>";

	newdiv.innerHTML = new_content;

	ni.appendChild(newdiv);
}

/**
* Removes a plugin item from scan list
*
* This is general code to remove plugins, families, severites
* and the 'all' plugins items from the scan list on the right
* side of the page.
*
* Add and remove event javascript taken from the site shown below
* http://www.dustindiaz.com/add-and-remove-html-elements-dynamically-with-javascript/
* Modified for use with this application
*
* @access public
* @param string $divNum div containing the plugin item to remove
* @param string inputName Input form field to remove from the DOM
* 	so that it is not sent to the processing script
* @see addEvent
*/
function removeEvent(divNum) {
	plu_count = $F('plugin_counter');
	plu_count = plu_count - 1;

	$('plugin_counter').value = plu_count;

	var d 		= $('selected_plugins');
	var olddiv 	= $(divNum);
	d.removeChild(olddiv);
}

/**
* Add an email address to scan settings
*
* This function will create a new field where the user can
* specify alternate email addresses to send the results of
* a scan to.
*
* @access public
* @see remove_alternate_email
*/
function add_alternate_email() {
	var ni 				= $('alternate_emails');
	var num 			= $("alt_email_counter").value;
	var newnum			= (parseInt(num) + 1);
	$("alt_email_counter").value 	= newnum;
	var divIdName 			= "mail"+newnum+"Div";

	var newdiv = document.createElement('div');
	newdiv.setAttribute("id",divIdName);

	// Begin making new table entry
	var new_content = "<table width='100%'><tr><td style='width: 90%;'>";
	new_content = new_content + "<input type='text' name='alternate_email_to[]' id='alternate_email_to"+newnum+"' class='input_txt' style='width: 100%;'>";
	new_content = new_content + "</td><td style='width: 10%; text-align: right;'>";
	new_content = new_content + "<span style='cursor: pointer;'>";
	new_content = new_content + "<img src='images/delete.png' onClick=\"remove_alternate_email('"+divIdName+"','"+newnum+"')\">";
	new_content = new_content + "</span>";
	new_content = new_content + "</td></tr></table>";

	newdiv.innerHTML = new_content;

	ni.appendChild(newdiv);
}

/**
* Remove an email address from scan settings
*
* If the user determines that they dont want to include
* an individual in the list of recipients, they can use
* this function to remove the field from the form so that
* it's data is not sent to save save or processing scripts.
*
* @access public
* @param string divNum ID of the div to remove from the document
*	so that it's value is not sent along in the save form
* @see add_alternate_email
*/
function remove_alternate_email(divNum,aindex) {
	alt_count = $F('alt_email_counter');

	tmp_alt_count = alt_count - 1;

	if (tmp_alt_count < 1) {
		try {
			$("alternate_email_to"+aindex).value = '';
			return;
		} catch(e) {
			return;
		}
	}

	$('alt_email_counter').value = tmp_alt_count;

	var d = $('alternate_emails');
	var olddiv = $(divNum);
	d.removeChild(olddiv);
}

/**
* Add a new cgi-bin directory
*
* This function takes care of adding new cgi-bin directories
* to the page so that multiple directories can be sent to
* nessus when the scan is performed.
*
* @access public
* @see remove_cgibin
*/
function add_cgibin() {
	var ni 				= $('alternate_cgis');
	var num 			= $F("alt_cgi_counter");
	var newnum			= (parseInt(num) + 1);
	$("alt_cgi_counter").value 	= newnum;
	var divIdName 			= "cgi"+newnum+"Div";

	var newdiv = document.createElement('div');
	newdiv.setAttribute("id",divIdName);

	// Begin making new table entry
	var new_content = "<table width='100%'><tr><td style='width: 90%;'>";
	new_content = new_content + "<input type='text' name='alternate_cgibin[]' id='alternate_cgibin"+newnum+"' class='input_txt' style='width: 100%;'>";
	new_content = new_content + "</td><td style='width: 10%; text-align: right;'>";
	new_content = new_content + "<span style='cursor: pointer;'>";
	new_content = new_content + "<img src='images/delete.png' onClick=\"remove_cgibin('"+divIdName+"','"+newnum+"')\">";
	new_content = new_content + "</span>";
	new_content = new_content + "</td></tr></table>";

	newdiv.innerHTML = new_content;

	ni.appendChild(newdiv);
}

/**
* Remove cgi-bin directory
*
* Alternate cgi-bins can be specified so that nessus scans
* them. It's easy to add new ones by clicking the + button
* next to the setting. With this function, it's equally easy
* to remove the existing or added cgi-bin directories
*
* @access public
* @param string $divNum ID of the div where the cgi-bin exists
*	that needs to be removed. div's are in the following
*	format.
*		"cgi"+number+"Div"
*	Where number is an auto-incremented number based on
*	the current number of cgi-bin divs that are on the page
* @see add_cgibin
*/
function remove_cgibin(divNum,aindex) {
	alt_count = $F('alt_cgi_counter');
	tmp_alt_count = alt_count - 1;

	if (tmp_alt_count < 1) {
		try {
			$("alternate_cgibin"+aindex).value = '';
			return;
		} catch(e) {
			return;
		}
	}

	$('alt_cgi_counter').value = tmp_alt_count;

	var d = $('alternate_cgis');
	var olddiv = $(divNum);
	d.removeChild(olddiv);
}

/**
* Add a new device to selected devices list
*
* Any type of device can be added to the selected devices list.
* This includes clusters, registered machines, vhosts, etc. This
* function handles modifying the DOM so that the devices can be
* submitted back to the server to be processed.
*
* @param string $type The Type of device being added to the list
* @param string $item The value of the device that will be stored in the database
* @param string $disp The value to display in the "selected devices" list
* @see remove_device
*/
function add_device(type,item,disp) {
	var ni = $('selected_devices');
	var ness = $('nessus_reg');

	var num 			= $("device_counter").value;
	var newnum			= (parseInt(num) + 1);
	$("device_counter").value 	= newnum;

	var divIdName = "device"+newnum+"Div";
	var inputIdName = "dev"+newnum;

	var elementCluList 	= document.getElementsByClassName('clusters');
	var elementRegList 	= document.getElementsByClassName('registered');
	var elementWhiList 	= document.getElementsByClassName('white');
	var elementVhoList 	= document.getElementsByClassName('vhost');
	var elementSavList	= document.getElementsByClassName('saved');

	var allInputs = Form.getInputs('nessus_reg', 'hidden');
	var possible_inputs = new Array();

	allInputs.each(
		function(the_input) {
			var to_search 	= the_input.name;
			if (to_search.match('dev')) {
				possible_inputs.push(the_input.value);
			}
		}
	);

	if (elementCluList.length > 0) {
		for (x = 0; x < elementCluList.length; x++) {
			var inner_stuff = possible_inputs.indexOf(":clu:"+item);

			// indexOf will return -1 if not found
			if(inner_stuff >= 0) {
				alert('You have already added this cluster to the list');
				return;
			}
		}
	}

	if (elementRegList.length > 0) {
		for (x = 0; x < elementRegList.length; x++) {
			var inner_stuff = possible_inputs.indexOf(":reg:"+item);
			if (inner_stuff >= 0) {
				alert('You have already added this registered device to the list');
				return;
			}
		}
	}

	if (elementWhiList.length > 0) {
		for (x = 0; x < elementWhiList.length; x++) {
			var inner_stuff = possible_inputs.indexOf(":whi:"+item);
			if (inner_stuff >= 0) {
				alert('You have already added this whitelisted device to the list');
				return;
			}
		}
	}

	if (elementVhoList.length > 0) {
		for (x = 0; x < elementVhoList.length; x++) {
			var inner_stuff = possible_inputs.indexOf(":vho:"+item);
			if (inner_stuff >= 0) {
				alert('You have already added this virtual host to the list');
				return;
			}
		}
	}

	if (elementSavList.length > 0) {
		for (x = 0; x < elementSavList.length; x++) {
			var inner_stuff = possible_inputs.indexOf(":sav:"+item);
			if (inner_stuff >= 0) {
				alert('You have already added this saved scan to the list');
				return;
			}
		}
	}

	// Create new div to put selected device content in
	var newdiv = document.createElement('div');
	newdiv.setAttribute("id",divIdName);

	/**
	* Create the new input form field which will hold
	* the value for the device that is submitted to the
	* process page
	*/
	if (type == "cluster") {
		var new_content = "<input type='hidden' name='dev["+newnum+"]' id='"+inputIdName+"' value=':clu:"+item+"'>";
	} else if (type == "registered") {
		var new_content = "<input type='hidden' name='dev["+newnum+"]' id='"+inputIdName+"' value=':reg:"+item+"'>";
	} else if (type == "whitelist") {
		var new_content = "<input type='hidden' name='dev["+newnum+"]' id='"+inputIdName+"' value=':whi:"+item+"'>";
	} else if (type == "vhost") {
		var new_content = "<input type='hidden' name='dev["+newnum+"]' id='"+inputIdName+"' value=':vho:"+item+"'>";
	} else if (type == "saved") {
		var new_content = "<input type='hidden' name='dev["+newnum+"]' id='"+inputIdName+"' value=':sav:"+item+"'>";
	}

	// Begin making new table entry. All this data will go inside the new div
	new_content = new_content + "<table width='100%'><tr><td style='width: 10%; text-align: center;'>";
	new_content = new_content + "<span style='cursor: pointer;'>";
	new_content = new_content + "<img src='images/delete.png' onClick=\"remove_device('"+divIdName+"')\">";
	new_content = new_content + "</span>";
	new_content = new_content + "</td><td width='90%' style='text-align: left;'>";

	/**
	* Depending on what type of device it is, display
	* back to the user slightly different information
	* so that they know what they have selected
	*/
	if (type == "cluster") {
		new_content = new_content + "<div class='clusters'>Cluster: "+disp.escapeHTML()+"</div>";
	} else if (type == "registered") {
		new_content = new_content + "<div class='registered'>Registered: "+disp.escapeHTML()+"</div>";
	} else if (type == "whitelist") {
		new_content = new_content + "<div class='white'>Whitelist: "+disp.escapeHTML()+"</div>";
	} else if (type == "vhost") {
		new_content = new_content + "<div class='vhost'>Virtual Host: "+disp.escapeHTML()+"</div>";
	} else if (type == "saved") {
		new_content = new_content + "<div class='saved'>Saved Scan: "+disp.escapeHTML()+"</div>";
	}

	new_content = new_content + "</td></tr></table>";
	newdiv.innerHTML = new_content;

	ni.appendChild(newdiv);
}

/**
* Remove a device from the selected devices list
*
* Any number of devices can be combined to shove into the targets
* file. In the case someone wants to remove a particular device,
* this function will handle removing the device from the selected
* devices list
*
* @param string $divNum DIV of the device to remove from the DOM
* @see add_device
*/
function remove_device(divNum) {
	dev_count = $F('device_counter');
	dev_count = dev_count - 1;

	$('device_counter').value = dev_count;

	var d = $('selected_devices');
	var olddiv = $(divNum);

	d.removeChild(olddiv);
}

/**
* Removes bad characters
*
* As a first step in input cleaning, this function is used
* to strip characters that are not allowed in strings. This
* function is not a replacement for the server side cleaning
* instead it's just another layer
*
* @param string $the_string String to be cleaned
* @returns string Cleaned string
*/
function badchars(the_string) {
	var invalids = "!@#$%^&*()-~,'<.>/?;:\|\"";
	for(i = 0; i < invalids.length; i++) {
		the_string = the_string.replace(invalids.charAt(i), '');
	}

	return the_string;
}

/**
* Search for individual plugin
*
* Wrapper to search for an individual plugin and update page 
* elements with the results
*
* @access public
*/
function search_for_plugin() {
	var search_this = $('search_plugin').value;
	var current_page = $F('page');

	if (current_page == "settings") {
		Element.hide('workbox');
		Element.hide('devices_box');
		Element.show('plugins_box');
	} else if (current_page == "admin_settings") {
		Element.hide('groups_box');
		Element.show('plugins_box');
	} else if (current_page == "edit_plugin_profile") {
		Element.hide('groups_box');
		Element.show('plugins_box');
	} else {
		Element.hide('devices_box');
		Element.show('plugins_box');
	}

	url = "async/worker.php";
	params = "action=x_plugin_search&search_for="+search_this;

	new Ajax.Updater(
		'list_of_plugins',
		url,
		{
			method: 'post', 
			parameters: params
		});
}
