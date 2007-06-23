function show_add_help_content() {
	var url		= "async/admin_help.php";
	var params 	= "action=x_show_add_help_content";

	Element.hide('welcome_box');
	Element.show('workbox');

	Form.reset('nessus_reg');

	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}

function do_add_help_content() {
	$('action').value = "do_add_help_content";

	var url		= "async/admin_help.php";
	var params	= Form.serialize('nessus_reg');

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_add_help_content_resp
		});
}

function do_add_help_content_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		alert("Content added successfully");
		Form.reset('nessus_reg');
	} else {
		alert("Content could not be added");
	}
}

function do_add_help_category() {
	$('action').value = "do_add_help_category";

	var url		= "async/admin_help.php";
	var params	= Form.serialize('nessus_reg');

	new Ajax.Request(
		url,
		{
			method: 'post',
			parameters: params,
			onComplete: do_add_help_category_resp
		});
}

function do_add_help_category_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		alert("Category added successfully");
		Form.reset('nessus_reg');
		$('content_type_category').checked = "checked";
		show_help_categories();
	} else {
		alert("Category could not be added");
	}
}

function show_change_help_content(type) {
	var url 	= "async/admin_help.php";
	var params	= "action=show_change_help_content&type="+type;

	Element.hide('welcome_box');
	Element.show('workbox');
	Element.show('work_container2');

	$('current_content_type').value = type;

	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params
		});

}

function show_help_categories() {
	var url 	= "async/admin_help.php";
	var params	= "action=show_help_categories";

	new Ajax.Updater(
		{success: 'help_categories'},
		url, 
		{
			method: 'post',
			parameters: params
		});
}

function show_help_topics(category_id) {
	var url 	= "async/admin_help.php";
	var params	= "action=show_help_topics&category_id="+category_id;

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

function do_delete_help_topic(help_id) {
	var make_sure = confirm("Are you sure you want to delete this help topic?");

	if (!make_sure) {
		return;
	}

	var url = "async/admin_help.php";
	var params = "action=do_delete_help_topic&help_id="+help_id;

	new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: do_delete_help_topic_resp
		});
}

function do_delete_help_topic_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		var type = $F('current_content_type');
		show_change_help_content(type);
	} else {
		alert("Couldn't remove the help topic");
	}
}

function do_delete_help_category(category_id) {
	var make_sure = confirm("Are you sure you want to delete this category?");

	if (!make_sure) {
		return;
	}

	var url = "async/admin_help.php";
	var params = "action=do_delete_help_category&category_id="+category_id;

	new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: do_delete_help_category_resp
		});

}

function do_delete_help_category_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		var type = $F('current_content_type');
		show_help_categories();
		show_change_help_content(type);
	} else {
		alert("Couldn't remove the help topic");
	}
}

function edit_specific_help_topic(help_id) {
	var url 	= "async/admin_help.php";
	var params	= "action=edit_specific_help_topic&help_id="+help_id;

	new Ajax.Updater(
		{success: 'work_container2'},
		url, 
		{
			method: 'post',
			parameters: params
		});
}

function do_edit_specific_help_topic() {
	$('action').value = 'do_edit_specific_help_topic';

	var url = "async/admin_help.php";
	var params = Form.serialize('nessus_reg');

	new Ajax.Request(
		url, 
		{
			method: 'post',
			parameters: params,
			onComplete: do_edit_specific_help_topic_resp
		});
}

function do_edit_specific_help_topic_resp(origResp) {
	var the_mesg = origResp.responseText;

	if (the_mesg == "pass") {
		var type = $F('current_content_type');

		alert("Topic updated");

		show_change_help_content(type);
	} else {
		alert("Could not save the changes to this topic");
	}

}
