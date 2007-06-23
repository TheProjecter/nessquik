/**
* Code borrowed from http://www.ogonek.net/mousewheel/demo.html
*
* Investigate using this in a future release for scrolling
* the list of scans on the user scans page and the admin scans
* page.
*/
Object.extend(Event, {
	wheel:function (event){
		var delta = 0;
		if (!event) event = window.event;
		if (event.wheelDelta) {
			delta = event.wheelDelta/120; 
			if (window.opera) delta = -delta;
		} else if (event.detail) { delta = -event.detail/3;	}
		return Math.round(delta); //Safari Round
	}
});
