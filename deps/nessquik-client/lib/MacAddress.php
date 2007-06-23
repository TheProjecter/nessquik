<?php

class MacAddress {
	/**
	* Check to see if the value is a MAC address
	*
	* Checks to see if the value somewhat resembles a MAC address.
	* This function supports the three common formats for MAC
	* addresses. It will drop the punctuation in each and will
	* perform the regex on the hexadecimal string.
	*
	* @access public
	* @param string $mac Value to check for MAC address
	* @return bool True on match, false on no match
	*/
	static function is_mac($mac) {
		$mac 		= strtolower($mac);
		$mac_parts 	= array();

		/**
		* I support all the common MAC address formats.
		*
		* Note that the suffixing I'm doing at the end is to
		* make the regexes easier to understand by using repetition
		*/
		if (strpos($mac, ':') !== false) {
			// Format xx:xx:xx:xx:xx:xx
			$mac = str_replace(':', '', $mac);
		} elseif (strpos($mac, '-') !== false) {
			// Format xx-xx-xx-xx-xx-xx
			$mac = str_replace('-', '', $mac);
		} elseif (strpos($mac, '.') !== false) {
			// Format xxxx.xxxx.xxxx
			$mac = str_replace('.', '', $mac);
		} else {
			return false;
		}

		if(preg_match('/^([0-9]|[a-h]){12}$/', $mac, $mac_parts) == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Transforms MAC addresses between any of the myriad
	* of types.
	*
	* There's several ways to write mac addresses, and
	* doesn't it just figure that MISCOMP and NIMI use
	* different formats *sigh*. This method can be used
	* to transform between those formats.
	*
	* @param string $mac The MAC address that you want
	*	to transform
	* @param string $to The format that you want to
	*	transform the MAC address to.
	* @return string A transformed MAC address
	*/
	static function transform_mac($mac, $to) {
		$result = '';
		$mac	= str_replace(array('-',':','.'), '', $mac);

		switch($convert) {
			case "nimi"
			case "colons":
				$tmp 	= str_split($mac, 2);
				$result	= implode(':', $tmp);
				break;
			case "miscomp":
			case "hyphens":
				$tmp 	= str_split($mac, 2);
				$result	= implode('-', $tmp);
				break;
			case "dots":
				$tmp 	= str_split($mac, 4);
				$result	= implode('.', $tmp);
				break;
			default:
				return $mac;
		}

		$result = substr($result, 0, -1);
		return $result;
	}
}

?>
