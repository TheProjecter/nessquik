/***********************************************
* AnyLink Drop Down Menu- © Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/

//Contents for menu 1
var view = new Array();
view[0] = "<div class='viewdiv' onClick='view_report(\"#{pid}\", \"#{rid}\", \"html\");'>as html</div>";
view[1] = "<div class='viewdiv' onClick='view_report(\"#{pid}\", \"#{rid}\", \"txt\");'>as text</div>";
view[2] = "<div class='viewdiv' onClick='view_report(\"#{pid}\", \"#{rid}\", \"nbe\");'>as nbe</div>";

//Contents for menu 2, and so on
var save = new Array();
save[0] = "<a href='async/scans.php?action=make_report&profile_id=#{pid}&results_id=#{rid}&format=html'><div class='viewdiv'>as html</div></a>";
save[1] = "<a href='async/scans.php?action=make_report&profile_id=#{pid}&results_id=#{rid}&format=txt'><div class='viewdiv'>as text</div></a>";
save[2] = "<a href='async/scans.php?action=make_report&profile_id=#{pid}&results_id=#{rid}&format=nbe'><div class='viewdiv'>as nbe</div></a>";

var email = new Array();
email[0] = "<div class='viewdiv' onClick='email_report(\"#{pid}\", \"#{rid}\", \"html\");'>as html</div>";
email[1] = "<div class='viewdiv' onClick='email_report(\"#{pid}\", \"#{rid}\", \"txt\");'>as text</div>";
email[2] = "<div class='viewdiv' onClick='email_report(\"#{pid}\", \"#{rid}\", \"nbe\");'>as nbe</div>";

var disappeardelay=200;  //menu disappear speed onMouseout (in miliseconds)
var delayhide = 0;

function dropdownmenu(obj, e, container, type, values) {
	clearhidemenu()

	var tmp 	= values.split(':');
	var profile_id 	= tmp[0];
	var results_id 	= tmp[1];
	var divcontents	= '';

	if (window.event) {
		event.cancelBubble = true;
	} else if (e.stopPropagation) {
		e.stopPropagation();
	}

	$(container).innerHTML = '';

	type.each(
		function(format) {
			var the_template 	= new Template(format);
			var the_values		= {pid: profile_id, rid: results_id};

			divcontents		= divcontents + the_template.evaluate(the_values);
		}
	);
	
	$(container).innerHTML = divcontents;

	positions	= Position.cumulativeOffset(obj);
	x_axis		= positions[0];
	y_axis		= positions[1];

	$(container).style.left	= (x_axis - 20) + "px";
	$(container).style.top	= (y_axis + 20) + "px";

	Element.show(container);

	return false;
}

function dynamichide(container) {
	$('dropmenucontainer').value = container;
	delayhide = setTimeout("hidemenu()", disappeardelay);
}

function hidemenu(e) {
	/**
	* There's a bug somewhere in here where the value of container
	* isnt being set. Until I figure out what it is, I'm just trapping
	* the error and moving on.
	*
	* It's a weird error because it only shows up when you slightly
	* hover over the view,save or email links. If you "confidently"
	* hover over them (dont hover around the edges) the problem goes
	* away. Weird. I think it may have to do with the speed at which
	* the browser set a form element's value. If that's longer than
	* the $disappeardelay time I've defined up top, then I guess you
	* may see this error. If that's really the problem, then screw it,
	* the trap here will stay.
	*/
	try {
		var container = $F('dropmenucontainer');
		Element.hide(container);
	} catch(e) {}
}

function clearhidemenu() {
	if (typeof delayhide!="undefined") {
		clearTimeout(delayhide);
	}
}
