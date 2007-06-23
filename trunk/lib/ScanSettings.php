<?php

/**
* @author Tim Rupp
*/
class ScanSettings {
	public function update() {

	}

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
	public function run_time_to_timestamp($run_time) {
		$tmp 		= explode(' ',$run_time);
		$run_time 	= $tmp[0].' ';
		$time_blocks	= explode(':', $tmp[1]);

		if (($tmp[2] == 'pm') && ($time_blocks[0] < 12)) {
			$time_blocks[0] += 12;
		}

		if ($time_blocks[0] < 10) {
			$time_blocks[0] = "0".$time_blocks[0];
		}

		if ($time_blocks[1] < 10) {
			$time_blocks[1] = "0".$time_blocks[1];
		}

		$run_time .= $time_blocks[0].':'.$time_blocks[1].':00';

		return $run_time;
	}
}

?>
