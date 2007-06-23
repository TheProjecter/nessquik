<?php

/**
* Convert a runtime to a timestamp
*
* This function converts the runtimes that I use on the
* scan settings pages for clocks and calendar times, into
* a timestamp that can be stored in a MySQL database. The
* format of the run time is
*
*	2007-01-02 10:47 am
*
* This function converts that to a timestamp like so
*
*	2007-01-02 10:47:00
*
* Likewise, times that are past noon will be adjusted +12 hours
*
* @param string $run_time Run time that needs to be converted
* @return string $run_time Converted timestamp that can be
*	stored in a MySQL timestamp field
*/
function run_time_to_timestamp($run_time) {
	$tmp = explode(' ',$run_time);
	$run_time = $tmp[0].' ';

	$time_blocks = explode(':', $tmp[1]);

	if (($tmp[2] == 'pm') && ($time_blocks[0] < 12))
		$time_blocks[0] += 12;

	if ($time_blocks[0] < 10)
		$time_blocks[0] = "0".$time_blocks[0];

	if ($time_blocks[1] < 10)
		$time_blocks[1] = "0".$time_blocks[1];

	$run_time .= $time_blocks[0].':'.$time_blocks[1].':00';

	return $run_time;
}

/**
* Check if a port range is valid
*
* Users are allowed to specify the port range that they wish to
* scan during the Nessus scan. This function checks to make
* sure that the port range that they specified is valid.
*
* @param string $port_range Port range to check for validity
* @return string Return port range after any corrections have been made
*/
function check_port_range($port_range) {
	$invalids = array (
		'!','@','#','$','^','&','*','(',')',
		'~',',','\'','<','.','>','?',
		';',':','\\','|','"','=','+','`',
		'/','{','}','[',']','%'
	);

	$port_range = substr($port_range,0,11);
	if (strpos($port_range, '-') !== false) {
		$tmp 		= explode('-', $port_range);

		$first_val 	= trim($tmp[0]);
		$second_val	= trim($tmp[1]);

		/**
		* Make sure that the values entered are numbers and
		* if they're not, then set the scan range to default
		*/
		if (is_numeric($first_val) && is_numeric($second_val))
			$port_range = $first_val.'-'.$second_val;
		else
			$port_range = "default";
	} else {
		/**
		* If not in the correct format, then set the port range
		* to default
		*/
		$port_range = "default";
	}

	// Replace invalid characters in the port range
	$port_range = str_replace($invalids,'',$port_range);

	return $port_range;
}

/**
* Create 'alternate email-to' string
*
* The list of alternate email recipients is created by using
* elements on the settings page. The alternate emails however
* are stored in a single text field in the database. This
* function will convert the array of alternate emails into
* a single string that can be stored in the database.
*
* @param array $alternate_emails Array of alternate email addresses
*	to send the report to.
* @return string $alternative_email_list Emails converted into a
* 	single comma separated string that can be stored in the
*	MySQL text field
*/
function make_alternate_email_to_list($alternate_emails) {
	$alternative_email_list	= '';
	$invalids 		= array('!','#','$','^','&','*','(',')',
					'-','~',',','\'','<','>','/','?',
					';',':','\\','|','"','=','%','+',
					'`','[',']','{','}'
					);

	foreach($alternate_emails as $key => $email) {
		if ($email == '')
			continue;

		$email = str_replace($invalids,'',$email);
		$alternative_email_list .= $email.',';
	}

	/**
	* The alternate email list will have a trailing comma
	* after the previous 'for' loop finishes. This removes
	* that trailing comma
	*/
	$alternative_email_list = substr($alternative_email_list,0,-1);

	return $alternative_email_list;
}

/**
* Create alternate cgi-bin string
*
* Just like the alternate email list, the alternate cgi-bin list
* is stored as a comma separated string in the database. This
* function converts the array that the cgi's are gathered in, into
* a single string that can be stored in the database.
*
* @param array $alternate_cgis Array of alternate cgi-bin directories
* @return string $alternative_cgibin_list Converted string that is
* 	comma separated and contains the list of alternate cgi-bin
*	directories that will be included in the scan
*/
function make_alternate_cgibin_list($alternate_cgis) {
	$alternative_cgibin_list	= '';
	$invalids			= array('!','@','#','$','^','&','*','(',')',
						'~',',','\'','<','.','>','?',
						';',':','\\','|','"','=','%',
						'+','`','{','}','[',']'
						);

	foreach($alternate_cgis as $key => $cgi) {
		if ($cgi == '')
			continue;

		$cgi = str_replace($invalids,'',$cgi);
		$alternative_cgibin_list .= $cgi.':';
	}

	$alternative_cgibin_list = substr($alternative_cgibin_list,0,-1);

	return $alternative_cgibin_list;
}

?>
