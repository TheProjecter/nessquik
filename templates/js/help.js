/**
* List the available general help categories
*
* There are two types of categories. This function
* will retrieve from the backend, only the categories
* that can be viewed by the general public
*/
function show_help_categories() {
	var url 	= "async/help.php";
	var params	= "action=show_help_categories";

	new Ajax.Updater(
		{success: 'help_categories'},
		url, 
		{
			method: 'post',
			parameters: params
		});
}

/**
* Retrieve the topics for a given category
*
* On the help pages, the categories need to be
* clicked to return the list of topics. This
* function handles talking to the backend to
* get the list of topics for a given category
* ID and formatting the output.
*
* @param integer $category_id Category ID to fetch topics for
*/
function show_help_topics(category_id) {
	var url 	= "async/help.php";
	var params	= "action=show_help_topics&category_id="+category_id;

	Element.hide('welcome_box');

	new Ajax.Updater(
		{success: 'help_topics'},
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});
}
