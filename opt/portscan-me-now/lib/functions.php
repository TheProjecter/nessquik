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
* Sanitize possible data that may be submitted via superglobals.
*
* @access public
* @param mixed $input Data which you wish to have cleaned
* @return mixed Return cleaned input
*/
function cleanse_input($input, $scrubber) {
	switch ($scrubber) {
		case "ip":
			$tmp = explode('.', $input);
			if (is_numeric($tmp[0]) && is_numeric($tmp[1]) && is_numeric($tmp[2]) && is_numeric($tmp[3])) {
				$badchars = array();
			} else {
				return false;
			}
			break;
		default:
		case 'string':
			$badchars = array();
			break;
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

function get_port($port) {
	if (strpos($port, '-')) {
		$tmp = explode('-', $port);

		if (count($tmp) == 2) {
			if (is_numeric($tmp[0]) && is_numeric($tmp[1])) {
				return $tmp[0].'-'.$tmp[1];
			} else {
				return "1-65535";
			}
		} else {
			return "1-65535";
		}
	} else {
		return "1-65535";
	}
}

function get_verbose($verbose) {
	if ($verbose == 'v') {
		return "-v";
	} else if ($verbose == 'vv') {
		return "-vv";
	} else {
	        return '';
	}
}

?>
