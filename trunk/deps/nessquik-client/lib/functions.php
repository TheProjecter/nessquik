<?php

/**
* Returns a value from a superglobal
*
* Used to return cleaned variables from the PHP superglobals. This method
* receives an index and also the global that you want to access. Any of
* the PHP superglobals can have their values retrieved this way.
*
* The accepted values for the $scope variable and their associated super
* global are
*
*	G	$_GET
*	P	$_POST
*	C	$_COOKIE
*	R	$_REQUEST
*	S	$_SESSION
*	SE	$_SERVER
*
* @access public
* @param string $varname The index in the Superglobal where your value is stored
* @param string $scope Superglobal to pull data from
* @param string $scrubber How to filter invalid characters out of the data
* @return mixed|NULL The value at the specified index in the specified superglobal or NULL
*/
function import_var($varname, $scope = 'G', $scrubber = 'string') {
        $scope = strtoupper($scope);

        $superglobals = array(
                'G'     => @$_GET,
                'P'     => @$_POST,
                'C'     => @$_COOKIE,
                'R'     => @$_REQUEST,
                'S'     => @$_SESSION,
                'SE'    => @$_SERVER);

                if (count($superglobals[$scope]) > 0) {
                        if (isset($superglobals[$scope][$varname]))
                                return cleanse_input($superglobals[$scope][$varname], $scrubber);
                        else
                                return NULL;
                } else
                        return NULL;
}

/**
* Cleans form input
*
* This is currently just a skeleton function. It is intended to be a gateway
* for sanitizing possible data that may be submitted via forms.
*
* @access public
* @param mixed $input Data which you wish to have cleaned
* @return mixed Return cleaned input
*/
function cleanse_input($input, $scrubber) {
	switch ($scrubber) {
		case "scanner":
		case "special_plugin_name":
		case "cgibin":
			$badchars	= array('!','@','#','$','^','&','*','(',')',
						'~',',','\'','<','.','>','?',
						';',':','\\','|','"','=','%',
						'+','`','{','}','[',']'
			);
			break;
		case "email_subject":
			$badchars	= array('!','@','#','$','^','&','*','(',')',
						'-','~',',','\'','<','.','>','/','?',
						';',':','\\','|','"','=','`'
			);
			break;
		case "email_addy":
			$badchars	= array('!','#','$','^','&','*','(',')',
						'-','~',',','\'','<','>','/','?',
						';',':','\\','|','"','=','%','+',
						'`','[',']','{','}'
			);
			break;
		case "scan_name":
			$badchars	= array('!','@','#','$','^','&','*',
						'~',',','\'','<','>','?',
						';',':','\\','|','"','=','+','`',
						'/','{','}','%'
			);
			break;
		case "search":
			$badchars 	= array('!','@','#','$','^','&','(',')',
						'~',',','\'','<','>','?',
						';',':','\\','|','"','=','+','`',
						'/','{','}','[',']','%'
			);
			break;
		case "list":
			$badchars	 = array('!','@','#','$','^','&','*','(',')',
						'~','\'','<','>','?',';',':',
						'\\','|','"','=','+','`',
						'{','}','%'
			);
			break;
		default:
		case 'string':
		case 'clientdn':
		case 'htmlcontent':
		case 'none':
			$badchars = array();
	}

	if (is_array($input)) {
		foreach($input as $key => $val) {
			$result[$key] = trim(str_replace($badchars, ' ', strip_tags($val)));
		}
	} elseif ($scrubber == 'htmlcontent') {
		$result = trim(str_replace($badchars, ' ',$input));
	} else {
		$result = trim(str_replace($badchars, ' ',strip_tags($input)));
	}

        return $result;
}

/**
* Check to see if value is an IP address
*
* Checks to see if the passed in value somewhat resembles an
* IP address. This could be prone to errors, such as having
* number higher than 255 passed in, but for our purposes is
* adequate at the moment
*
* @access public
* @param string $ip Supposed value to check for IP address format
* @return bool True on match, false on no match
*/
function is_ip($ip) {
	$ip_parts = array();

	if(preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip, $ip_parts) == 1)
		return true;
	else
		return false;
}

/**
* Check to see if the value is a CIDR block
*
* Checks to see if the passed in value somewhat resembles a
* CIDR block.
*
* @access public
* @param string $ip Value to check for CIDR format
* @return bool True on match, false on no match
*/
function is_cidr($ip) {
	$ip_parts = array();

	if(preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\/[0-9]+$/', $ip, $ip_parts) == 1)
		return true;
	else
		return false;
}

/**
* Check to see if the value is an IP range
*
* Checks to see if the value somewhat resembles an IP range
*
* @access public
* @param string $ip Value to check for IP range
* @return bool True on match, false on no match
*/
function is_range($ip) {
	$ip 		= str_replace(' ', '', $ip);
	$ip_parts 	= array();

	if(preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\-(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip, $ip_parts) == 1)
		return true;
	else
		return false;
}

/**
* Check to see if the value is a vhost
*
* Checks to see if the value resembles the VHost format that
* Nessus recognizes and that I have also specified.
*
* @access public
* @param string $vhost Value to check for VHost
* @return bool True on match, false on no match
*/
function is_vhost($vhost) {
	$ip_parts = array();

	if(preg_match('/^\[\S+\]$/', $vhost, $ip_parts) == 1) {
		return true;
	} else {
		return false;
	}
}

/**
* array_filter function to remove empty indices
*
* This function is used by several array_filters in the
* process script to strip out entries from the array that
* have an empty value
*
* @access public
* @param string $var Value in the array to check for emptiness
*/
function strip_empty($var) {
	$var = trim($var);

	if ($var != '') {
		return true;
	}
}

/**
* Send an email with report output
*
* This function will send email to the user who set up the
* scan, and optionally, a list of recipients who should also
* receive the scan output. See further inline comments for
* details on how the email is sent to the multiple recipients.
* It's actually pretty deviant :-)
*
* @param string $to Email of the person who created the scan
* @param array $rcpts Optional list of secondary recipients of the email
* @param string $subj The subject line of the email
* @param string $body The contents of the body of the email. This
*	will likely be the full output of the report
* @param string $format The format of the email that is going
*	to be sent. This is important to specify because different
*	MIME headers in the email will be sent depending on the
*	format specifed. For example, if you send this function
*	a body composed of HTML, but specify the format as 'text',
*	the user will receive a text email exposing all the HTML
*	tags. As such, they'll probably be really confused.
*/
function send_email($to, $rcpts = '', $subj = '', $body = '', $format = 'html') {
	require_once(_ABSPATH.'/lib/phpmailer/class.phpmailer.php');

	/**
	* Normally, only a single recipient gets an email.
	*
	* The user can also specify a list of individuals to
	* receive a CC of the email. This list is the $rcpts
	* variable.
	*
	* If multiple recipients are specified, the user will
	* receive an email from nightwatch containing the scan
	* results. All the recipients though will receive an
	* email from the user. This is kinda like keeping tabs
	* on the user so that they dont spam. Also, it provides
	* a rudimentary means of accountability because any
	* recipients will redirect questions and comments they
	* have, back to the original user and not to nightwatch
	*/
	if ($rcpts == '') {
		$smtp = new PHPMailer();
		$smtp->IsSMTP();
		$smtp->Host	= _SMTP_SERVER;
		$smtp->SMTPAuth = _SMTP_AUTH;
		$smtp->From 	= _SMTP_FROM;
		$smtp->FromName	= _SMTP_FROM_NAME;

		$smtp->AddAddress($to);
		$smtp->AddReplyTo(_SMTP_FROM, _SMTP_FROM_NAME);

		if ($format == 'html') {
			$smtp->IsHTML(true);
		} else {
			$smtp->IsHTML(false);
		}

		$smtp->Subject = $subj;
		$smtp->Body = $body;

		$smtp->Send();
	} else {
		// First, send it to the user who made the scan
		$smtp = new PHPMailer();
		$smtp->IsSMTP();
		$smtp->Host	= _SMTP_SERVER;
		$smtp->SMTPAuth = _SMTP_AUTH;
		$smtp->From 	= _SMTP_FROM;
		$smtp->FromName	= _SMTP_FROM_NAME;

		$smtp->AddAddress($to);
		$smtp->AddReplyTo(_SMTP_FROM, _SMTP_FROM_NAME);

		if ($format == 'html') {
			$smtp->IsHTML(true);
		} else {
			$smtp->IsHTML(false);
		}

		$smtp->Subject	= $subj;
		$smtp->Body 	= $body;

		$smtp->Send();

		// Clear the recipients and start new mail
		$smtp->ClearAllRecipients();
		$smtp->ClearReplyTos();

		/**
		* Now, send the same email to their recipient list
		* spoofing the 'from' field to be the user who made
		* the scan
		*/
		$smtp->From 	= $to;
		$smtp->FromName	= $to;
		$smtp->AddReplyTo($to, $to);

		if ($format == 'html')
			$smtp->IsHTML(true);
		else
			$smtp->IsHTML(false);

		// Send to all the recipients
		foreach ($rcpts as $key => $rcpt) {
			if ($rcpt == '')
				continue;

			$smtp->AddAddress($rcpt);
			$smtp->Send();

			$smtp->ClearAllRecipients();
		}
	}
}

/**
* Processes weekly recurrence rule string
*
* The rule string for weekly recurrence specifes the days
* on which the recurrence takes place. This function will
* split up that string to that it can be used in templating
* or determining if a scan needs to be run
*
* @access public
* @param string $rules_string Weekly recurrence rules string to be processed
* @return array $data Array of processed rules
*/
function days_data($rules_string) {
	$data = array();

	// String is ; delimited
	$tmp = explode(';', $rules_string);

	// For each item in the string...
	foreach($tmp as $key => $val) {
		// Check for empty because a tailing ; will cause this
		if ($val == '')
			continue;

		/**
		* Each block in the ; delimited list is further
		* delimited by :'s
		*/
		$sched = explode(':', $val);

		// Short name is the first value
		$short = $sched[0];

		// Push info into the data array for assigning to template
		$data[] = array(
			'long'	=> ucfirst($sched[0]),
			'key' 	=> $sched[0],
			'val' 	=> $sched[1]
		);
	}

	return $data;
}

/**
* Generate a random string of characters
*
* This function will generate a random string of
* characters that is as long as the length specified
*
* @param integer Length of the string to be generated
* @author Borrowed from http://ebonhost.com/blog/?p=11
* @return string Randomly generated string
*/
function random_string($length = "8") {
	$string 	= ""; 
	$possible	= "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"; 

	if (!is_numeric($length)) {
		$length = 8;
	}
	
	for($i = 0; $i < $length; $i++) {
		// get rand character from possibles
		$char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
	
		$string .= $char;
	
	}

	return $string;
}

/**
* Accept a date and convert it to 24 hour time
*
* This function is used mainly in the settings page or any
* page that includes a clock or calendar. This conversion
* is necessary because MySQL stores datetimes in a 24 hour
* format
*
* @param string $date Date string that needs to be converted
*	to MySQL 24 hour format
* @return string Converted date suitable for storing in MySQL
*/
function date_to_24($date) {
	$time = strtotime($date);
	return strftime("%Y-%m-%d %T", $time);
}

?>
