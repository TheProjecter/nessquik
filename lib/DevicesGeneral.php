<?php

/**
* @author Tim Rupp
*/
class DevicesGeneral {
	/**
	* Array of the known types of devices
	*
	* There are 6 known types. Those types are
	*
	*	registered
	*	cluster
	*	whitelist
	*	vhost
	*	saved
	*	general
	*
	* @var array
	*/
	private $types;

	/**
	* Constructor for new Devices objects
	*
	* Simple constructor that just sets some class variables
	*/
	public function __construct() {
		$this->types = array(
			'registered' 	=> ":reg:",
			'cluster'	=> ":clu:",
			'whitelist'	=> ":whi:",
			'vhost'		=> ":vho:",
			'saved'		=> ":sav:",
			'general'	=> ":gen:"
		);
	}

	/**
	* Return a count of the number of devices for a given type
	*
	* Will count the number of devices in the machine list
	* by the type specified and return the amount found.
	* Types should be specified by their long name. The
	* values that will be searched for will be by short name
	*
	* @param array $devices List of machines to check for type
	* @param string $type Type to count in the machine list
	* @return integer Number of machines that match the given type
	*/
	public function count_devices_by_type($devices, $type = 'registered') {
		$counter = 0;

		foreach($devices as $key => $val) {
			if (strpos($val, $this->types[$type]) !== false) {
				$counter++;
			}
		}

		return $counter;
	}

	/**
	* Determine the device type of a device
	*
	* This is a very simple process. Really all I'm doing
	* is reading the prefix off a device. Since it's used
	* a lot though, I added it as a method
	*
	* @param string $device Full device name and type
	* @return string The devices full type on success,
	*	an empty string on failure.
	*/
	public function determine_device_type($device) {
		$type = '';

		foreach ($this->types as $key => $val) {
			if (strpos($device, $val) !== false) {
				return $key;
			}
		}

		return $type;
	}

	/**
	* Remove device type from device
	*
	* Simply strip off the device type if it exists
	* on the given device.
	*
	* @param string $device Device to remove type from
	* @return string Device without the type prefix
	*/
	public function strip_device_type($device) {
		$types_short	= array_values($this->types);
		$result		= str_replace($types_short,'',$device);
		return trim($result);
	}

	/**
	* Return list of devices for a given type
	*
	* Useful if you have an array with many different device
	* types in it and you only want the devices for a specific
	* type. This method breaks apart the array and returns the
	* devices that only match the type given. You can optionally
	* have the device prefix removed too so that you get an
	* array that has only the devices list; no type prefix
	*
	* ex. device with prefix
	*
	*	:reg:123.45.67.89
	*
	* ex. device without prefix
	*
	*	123.45.67.89
	*
	* @param array $devices List of devices to narrow down
	* @param string $type Type that you want to filter by
	* @param bool $remove_prefix Specify whether you want to remove the prefix
	* @return array Array of filtered devices
	*/
	public function get_devices_by_type($devices,$type = 'registered',$remove_prefix = true) {
		$results = array();

		foreach($devices as $key => $val) {
			if ($type == 'all') {
				foreach($this->types as $key2 => $val2) {
					if (strpos($val, $this->types[$key2]) !== false) {
						if ($remove_prefix) {
							$tmp = str_replace($val2,'',$val);
						} else {
							$tmp = $val;
						}

						$results[] = $tmp;
					}
				}
			} else if (strpos($val, $this->types[$type]) !== false) {
				if ($remove_prefix) {
					$tmp = str_replace($this->types[$type],'',$val);
				} else {
					$tmp = $val;
				}

				$results[] = $tmp;
			}
		}

		return $results;
	}
}

?>
