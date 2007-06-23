nessCal = function(calendar) {
	this.calendar 	= calendar;

	this.year_d	= "cal_year";
	this.month_d	= "cal_month";
	this.day_d	= "cal_day";
	this.hour_d	= "cal_hour";
	this.min_d	= "cal_minute";
	this.ampm_d	= "cal_ampm";

	this.year_v	= "cal_year_val";
	this.month_v	= "cal_month_val";
	this.day_v	= "cal_day_val";
	this.hour_v	= "cal_hour_val";
	this.min_v	= "cal_minute_val";
	this.ampm_v	= "cal_ampm_val";

	this.cal_year_changer	= nessCal.cal_year_changer;
	this.cal_month_changer	= nessCal.cal_month_changer;
	this.cal_minute_changer	= nessCal.cal_minute_changer;
	this.cal_ampm_changer	= nessCal.cal_ampm_changer;
	this.cal_day_changer	= nessCal.cal_day_changer;
	this.cal_hour_changer	= nessCal.cal_hour_changer;
	this.update_calendar	= nessCal.update_calendar;
	this.daysInMonth	= nessCal.daysInMonth;
};

nessCal.cal_month_changer = function(e) {
	var current_month 	= $F(this.month_v);
	var new_month		= '';
	var new_month_int	= 1;

	if (e.shiftKey) {
		// Shift+left click means decrement
		if (current_month == "January") 	new_month 	= "December";
		else if (current_month == "February")	new_month 	= "January";
		else if (current_month == "March")	new_month 	= "February";
		else if (current_month == "April")	new_month 	= "March";
		else if (current_month == "May") 	new_month 	= "April";
		else if (current_month == "June")	new_month 	= "May";
		else if (current_month == "July")	new_month 	= "June";
		else if (current_month == "August")	new_month 	= "July";
		else if (current_month == "September")	new_month 	= "August";
		else if (current_month == "October")	new_month 	= "September";
		else if (current_month == "November")	new_month 	= "October";
		else if (current_month == "December")	new_month 	= "November";
	} else {
		// Left click means increment
		if (current_month == "January")		new_month 	= "February";
		else if (current_month == "February")	new_month 	= "March";
		else if (current_month == "March")	new_month 	= "April";
		else if (current_month == "April")	new_month 	= "May";
		else if (current_month == "May")	new_month 	= "June";
		else if (current_month == "June")	new_month 	= "July";
		else if (current_month == "July")	new_month 	= "August";
		else if (current_month == "August")	new_month 	= "September";
		else if (current_month == "September")	new_month 	= "October";
		else if (current_month == "October")	new_month 	= "November";
		else if (current_month == "November")	new_month 	= "December";
		else if (current_month == "December")	new_month 	= "January";
	}

	$(this.month_v).value 		= new_month;
	$(this.month_d).innerHTML	= new_month;
	this.update_calendar();
};

nessCal.cal_minute_changer = function (e) {
	var the_minute	= parseInt($F(this.min_v), 10);
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

	/**
	* I'm doing the same thing here that I was doing in the
	* hour function with trying to maintain readability
	*/
	if (new_minute < 10) {
		$(this.min_d).innerHTML = '0'+new_minute;
	} else {
		$(this.min_d).innerHTML	= new_minute;
	}

	$(this.min_v).value = new_minute;
	this.update_calendar();
};

nessCal.update_calendar = function() {
	var month 	= $F(this.month_v);
	var year	= $F(this.year_v);
	var day		= $F(this.day_v);
	var hour	= $F(this.hour_v);
	var min		= $F(this.min_v);
	var ampm	= $F(this.ampm_v);

	if (month == "January")		month	= "01";
	else if (month == "February")	month	= "02";
	else if (month == "March")	month	= "03";
	else if (month == "April")	month	= "04";
	else if (month == "May")	month	= "05";
	else if (month == "June")	month	= "06";
	else if (month == "July")	month	= "07";
	else if (month == "August")	month	= "08";
	else if (month == "September")	month	= "09";
	else if (month == "October")	month	= "10";
	else if (month == "November")	month	= "11";
	else if (month == "December")	month	= "12";

	$(this.calendar).value = year+'-'+month+'-'+day+' '+hour+':'+min+' '+ampm;
};

nessCal.cal_ampm_changer = function(e) {
	var current_ampm 	= $F(this.ampm_v);

	if (current_ampm == "AM") {
		$(this.ampm_v).value		= "PM";
		$(this.ampm_d).innerHTML	= "PM";
	} else {
		$(this.ampm_v).value 		= "AM";
		$(this.ampm_d).innerHTML	= "AM";
	}
	this.update_calendar();
};

nessCal.cal_day_changer = function(e) {
	var the_year 		= $F(this.year_v);
	var current_month	= $F(this.month_v);
	var current_day		= parseInt($F(this.day_v), 10);
	var new_day		= 1;

	if (current_month == "January") 	the_month = 1;
	else if (current_month == "February") 	the_month = 2;
	else if (current_month == "March") 	the_month = 3;
	else if (current_month == "April") 	the_month = 4;
	else if (current_month == "May") 	the_month = 5;
	else if (current_month == "June") 	the_month = 6;
	else if (current_month == "July") 	the_month = 7;
	else if (current_month == "August") 	the_month = 8;
	else if (current_month == "September")	the_month = 9;
	else if (current_month == "October") 	the_month = 10;
	else if (current_month == "November") 	the_month = 11;
	else if (current_month == "December") 	the_month = 12;

	var days_in_month = this.daysInMonth(the_month,the_year);

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

	if (new_day < 10) {
		$(this.day_d).innerHTML	= '0'+new_day;
	} else {
		$(this.day_d).innerHTML	= new_day;
	}

	$(this.day_v).value 	= new_day;
	this.update_calendar();
};

nessCal.cal_hour_changer = function(e) {
	var the_hour	= parseInt($F(this.hour_v), 10);
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

	/**
	* For readability's sake, I want to keep a 0 prefixed
	* to the number if it's less than 10. However javascript
	* is doing something funky if I always prefix a 0. That's
	* why I broke it up with an IF statement.
	*/
	if (new_hour < 10) {
		$(this.hour_d).innerHTML 	= '0'+new_hour;
	} else {
		$(this.hour_d).innerHTML	= new_hour;
	}
	
	$(this.hour_v).value = new_hour;
	this.update_calendar();
};

nessCal.cal_year_changer = function(e) {
	var the_year	= parseInt($F(this.year_v), 10);
	var new_year	= 1;

	if (e.shiftKey) {
		new_year = the_year - 1;
	} else {
		new_year = the_year + 1;
	}

	$(this.year_v).value		= new_year;
	$(this.year_d).innerHTML	= new_year;
	this.update_calendar();
};

/**
* Determine the number of days in a month,year combo
*
* Code taken from
*	http://www.go4expert.com/forums/showthread.php?t=886
*/
nessCal.daysInMonth = function(iMonth, iYear) {
	if(iMonth != 0) {
		iMonth = iMonth - 1;
	}

	return 32 - new Date(iYear, iMonth, 32).getDate();
};
