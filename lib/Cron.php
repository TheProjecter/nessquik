<?php

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/db/nessquikDB.php');
require_once(_ABSPATH.'/lib/pear/Date.php');
require_once(_ABSPATH.'/lib/pear/Date/Calc.php');

/**
* @author Tim Rupp
*/
class Cron {
	/**
	* PEAR Date object containing today's date
	*
	* @var object
	*/
	private $today;

	/**
	* A file handle to the debug file if debugging
	* has been turned on
	*
	* @var resource
	*/
	private $dfh;

	/**
	* Constructor
	*
	* Primarily used for setting class variables.
	*/
	public function __construct() {
		$this->today 	= new Date();

		if (_DEBUG) {
			$timestamp	= strftime("%y-%m-%d_%H-%M-%S", time());
			$this->dfh	= fopen(_ABSPATH.'/logs/cron-'.$timestamp.'-'.mt_rand(0,10000).'.log', 'w');
		}
	}

	/**
	* Main cron loop.
	*
	* This main loop will fetch all the schedules that
	* need to be processed, and update each one as needed
	*/
	public function run() {
		$db = nessquikDB::getInstance();

		$sql = array(
			'select' => "	SELECT 	pl.profile_id,
						pl.date_scheduled,
						rec.recurrence_id,
						rec.recur_type,
						rec.the_interval,
						rec.specific_time,
						rec.rules_string 
					FROM recurrence AS rec 
					LEFT JOIN profile_list AS pl 
					ON rec.profile_id=pl.profile_id 
					WHERE pl.status='F' 
					AND date_scheduled <= ':1'"
		);

		$date	= date('Y-m-d H:i:s');

		$stmt 	= $db->prepare($sql['select']);
		$stmt->execute($date);

		while($row = $stmt->fetch_assoc()) {
			$recur_type = $row['recur_type'];

			switch($recur_type) {
				case "D":
					$this->handle_day_recurrence($row);
					break;
				case "W":
					$this->handle_week_recurrence($row);
					break;
				case "M":
					$this->handle_month_recurrence($row);
					break;
			}
		}

		if (_DEBUG) {
			fclose($this->dfh);
		}
	}

	/**
	* Specifically handle day recurrence
	*
	* This method will take care of parsing the day
	* recurrence data and correctly rescheduling a
	* scan if the recurrence time has been met
	*
	* @param array $parameters Array of the parameters
	*	from the database entry for this recurrence
	*	setting
	*/
	private function handle_day_recurrence($parameters) {
		$the_interval 	= $parameters['the_interval'];
		$date_scheduled	= $parameters['date_scheduled'];
		$profile_id 	= $parameters['profile_id'];

		$last           = new Date($date_scheduled);
		$last_ts	= $last->getTime();
		$specific_time	= $this->specific_time($parameters['specific_time']);

		$time		= strtotime("+$the_interval day", $last_ts);
		$future_date 	= new Date($time);
		$future_date	= $this->set_new_time($future_date, $specific_time);

		if ($this->today->after($future_date)) {
			// Update the date scheduled field
			$this->reschedule_scan($profile_id, $this->today->getDate());

			// Reschedule the scan
			$this->update_scan_status($profile_id, 'F', 'P');
		}
	}

	/**
	* Specifically handle week recurrence
	*
	* This method will take care of parsing the week
	* recurrence data and correctly rescheduling a
	* scan if the recurrence time has been met
	*
	* @param array $parameters Array of the parameters
	*	from the database entry for this recurrence
	*	setting
	*/
	private function handle_week_recurrence($parameters) {
		$the_interval 	= $parameters['the_interval'];
		$date_scheduled	= $parameters['date_scheduled'];
		$profile_id 	= $parameters['profile_id'];
		$rules_string	= $parameters['rules_string'];
		$specific_time	= $this->specific_time($parameters['specific_time']);

		$last           = new Date($date_scheduled);
		$last_ts	= $last->getTime();
		$tmp 		= explode(';',$rules_string);

		// Doing this because of how strtotime's offset works
		$the_interval = $the_interval - 1;
		if ($the_interval == 1) {
			$the_interval = 0;
		}

		// Foreach of the days (sun,mon...)
		foreach($tmp as $key => $val) {
			$split = explode(':',$val);

			@$day 		= trim($split[0]);
			@$checked_day	= trim($split[1]);

			if ($checked_day == '') {
				continue;
			}

			if ($checked_day == 1) {
				$day 		= $this->get_weekday_fullname($day);
				$time 		= strtotime("+$the_interval week $day", $last_ts);

				$future_date	= new Date($time);
				$future_date	= $this->set_new_time($future_date, $specific_time);
				$dates[]	= $future_date;

				sort($dates);
			}
		}

		foreach ($dates as $key => $date) {
			if ($this->today->after($date)) {
				// Update the date scheduled field
				$this->reschedule_scan($profile_id, $this->today->getDate());

				// Reschedule the scan
				$this->update_scan_status($profile_id, 'F', 'P');

				break;
			}
		}
	}

	/**
	* Specifically handle month recurrence
	*
	* This method will take care of parsing the month
	* recurrence data and correctly rescheduling a
	* scan if the recurrence time has been met
	*
	* @param array $parameters Array of the parameters
	*	from the database entry for this recurrence
	*	setting
	*/
	private function handle_month_recurrence($parameters) {
		$the_interval	= $parameters['the_interval'];
		$profile_id	= $parameters['profile_id'];
		$date_scheduled	= $parameters['date_scheduled'];
		$rules_string	= $parameters['rules_string'];
		$specific_time	= $this->specific_time($parameters['specific_time']);

		// The monthly rules string is colon delimited with a max of 3 items
		$tmp = explode(':',$rules_string);

		// The type of monthly recursion will be 'day' or 'gen'
		$type 	= $tmp[0];

		// This is either the day of the month, or a relative day of the week
		$day 	= $tmp[1];

		/**
		* Take the date the scan was scheduled, and only return the
		* year and month because my calculations are based off of the 0th
		* day of the month at midnight. Using 0th day because of how 
		* strtotime determines it's offset
		*/
		$month_time = strtotime($date_scheduled);
		$month_time = strftime("%Y-%m-00 00:00:00", $month_time);

		/**
		* Get X months in the future from the last date scheduled
		* Because the remaining date calculations will be based
		* off of that future time.
		*/
		$time = strtotime("+$the_interval month", strtotime($month_time));

		/**
		* Turn the future date into PEAR object so I can use
		* the PEAR object's methods.
		*/
		$future_date = new Date($time);

		$future_month 	= $future_date->getMonth();
		$future_year	= $future_date->getYear();

		switch($type) {
			case "gen":
				// Get the weekday that was specified
				$weekday = $this->get_weekday_fullname($tmp[2]);

				// Get the number of days in the month
				$days_in_month = Date_Calc::daysInMonth($future_month, $future_year);

				/**
				* Turn the above into an array where the day of the
				* month is the value. The last value will be the last
				* day of the month
				*/
				for($day = 1; $day <= $days_in_month; $day++) {
					$days[] = $day;
				}
				$days_in_month = $days;

				switch($day) {
					case "1st":
						$day = $this->get_relative_day($days_in_month, $future_month, $future_year, $weekday);
						$future_date->setDay($day);
						break;
					case "2nd":
						$day = $this->get_relative_day($days_in_month, $future_month, $future_year, $weekday, 2);
						$future_date->setDay($day);
						break;
					case "3rd":
						$day = $this->get_relative_day($days_in_month, $future_month, $future_year, $weekday, 3);
						$future_date->setDay($day);
						break;
					case "4th":
						$day = $this->get_relative_day($days_in_month, $future_month, $future_year, $weekday, 4);
						$future_date->setDay($day);
						break;
					case "last":
						$days_in_month = array_reverse($days_in_month);

						/**
						* I reversed the array above, so in essence we're
						* starting from the end of the month, therefore the
						* "last" day of the month will, in this case, be
						* the first match
						*/
						$day = $this->get_relative_day($days_in_month, $future_month, $future_year, $weekday);
						$future_date->setDay($day);
						break;
					case "2_last":
						$days_in_month = array_reverse($days_in_month);

						/**
						* I reversed the array above, so in essence we're
						* starting from the end of the month, therefore the
						* "2nd to last" day of the month will, in this case, be
						* the second match
						*/
						$day = $this->get_relative_day($days_in_month, $future_month, $future_year, $weekday, 2);
						$future_date->setDay($day);
						break;
				}
				break;
			default:
				$future_date->setDay($day);
				break;
		}

		$future_date = $this->set_new_time($future_date, $specific_time);

		// Compare the dates
		if ($this->today->after($future_date)) {
			// Update the date scheduled field
			$this->reschedule_scan($profile_id, $this->today->getDate());

			// Reschedule the scan
			$this->update_scan_status($profile_id, 'F', 'P');
		}
	}

	/**
	* Set the time of the future Date object
	*
	* Users are allowed to specify a precise time to reschedule
	* scans, so after adjusting the future month, day and year
	* for comparison, I also need to set the time. This function
	* accepts an HH:MM:SS format string and will set the time
	* for the object it is given
	*
	* @param object $future_date A PEAR Date object that is to
	*	have it's time set.
	* @param string $specific_time The time you want to set the
	*	object's time to.
	* @return object Returns an adjusted PEAR Date object
	*/
	private function set_new_time($future_date, $specific_time) {
		$tmp 	= explode(':', $specific_time);
		$hour 	= $tmp[0];
		$minute	= $tmp[1];
		$second = $tmp[2];

		$future_date->setHour($hour);
		$future_date->setMinute($minute);
		$future_date->setSecond($second);

		return $future_date;
	}

	/**
	* Get the new day for a relative time period
	*
	* This method is super important for the monthly
	* recurrence because it supports what I call the
	* "relative" days of the month. A relative day
	* is "1st monday" or "2nd to last friday". This
	* is different from the (easier to compute) absolute
	* days like "the 4th" or "the 23rd". This method
	* handles the relative days compution in a pretty
	* simple way.
	*
	* An array of all the days in the month is sent
	* to the method. A for loop runs through each one
	* of these days and turns the particular day (1st,
	* 2nd, 3rd, etc) into it's weekday equivalent (Monday,
	* Tuesday, Wednesday, etc).
	*
	* For a "first" day, the first match of the given
	* weekday (Monday, Tuesday, etc) is returned.
	*
	* For a "second" day, a simple counter is used. When
	* the particular weekday is stumbled upon, the counter
	* is incremented. When the counter reaches the appropriate
	* relative day, that day of the month (1st, 2nd, 3rd) is
	* returned, and the PEAR Date class is set to use that
	* new day.
	*
	* By adjusting the "future" object, I can use the built
	* "after" or "before" methods to compare two PEAR Date
	* objects
	*
	* @param array $days_in_month The days of a given month
	*	where the values of the array are 1,2,3...31
	*	up to however many days are in the month
	* @param integer $future_month The month of the "future"
	*	date (date being checked to see if today's date
	*	has passed it)
	* @param integer $future_year The year of the "future"
	*	date.
	* @param string $weekday The fullname of the weekday being
	*	checked for. "Monday", "Tuesday", etc.
	* @param integer The number of weekdays that must be found
	*	before the day of the month will be returned.
	* @return integer Day of the month for the relative day
	*/
	private function get_relative_day($days_in_month, $future_month, $future_year, $weekday, $count_to = 0) {
		$needed_count = 0;

		foreach($days_in_month as $key => $day) {
			$check_day = Date_Calc::getWeekdayFullname($day, $future_month, $future_year);

			if ($check_day == $weekday) {
				if ($count_to > 0) {
					$needed_count++;
					if ($needed_count == $count_to) {
						break;
					}
				} else {
					break;
				}
			}
		}

		return $day;
	}

	/**
	* Get the recurrence time
	*
	* The cron script allows the user to finely control
	* the date and time that the scan will be re-scheduled.
	* The time is stored in the database as a datetime
	* so this method will convert that to a time usable
	* by methods of the class.
	*
	* @param datetime $specific_time Specific time pulled
	*	from the database. It's stored as a MySQL
	*	DATETIME
	* @return time A transformed time in HH:MM:SS format
	*/
	private function specific_time($specific_time) {
		/**
		* Convert the specific time to only hours:minutes:seconds format
		* in 24 hour format
		*/
		$specific_time	= strtotime($specific_time);
		$specific_time	= strftime("%T", $specific_time);

		return $specific_time;
	}

	/**
	* Update a scan's status after re-scheduling
	*
	* A scan's status will be incorrect even after it
	* has been rescheduled. It must be set back to
	* pending or else the scan-maker will never
	* pick it back up
	*
	* @param string $profile_id ID of the profile to update
	* @param string $old_status Status that is being updated
	*	from. I think I'm checking this to make sure that
	*	I'm updating from a correct status. For example,
	*	if the "old" status is "R" for running, then I
	*	wouldn't want to set it back to pending or something
	* @param string $new_status New status to set the profile
	*	to. Typically this will be 'P' for pending.
	* @return boolean True if successfully updated, false otherwise
	*/
	private function update_scan_status($profile_id, $old_status, $new_status) {
		$db = nessquikDB::getInstance();

		$sql = array(
			'update' => "	UPDATE profile_list 
					SET status=':3', cancel='N'
					WHERE profile_id=':1' 
					AND status=':2'"
		);

		$stmt = $db->prepare($sql['update']);
		$stmt->execute($profile_id, $old_status, $new_status);

		if ($stmt->affected() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Re-schedule a scan
	*
	* This method will handle rescheduling the scan and updating
	* it's date_scheduled field so that the scan maker can check
	* the new field against the current time when it comes around
	*
	* @param string $profile_id ID of the profile to reschedule
	* @param datetime $date_scheduled The new date and time to
	*	reschedule the scan. The format should be that of
	*	the MySQL DATETIME field format
	* @return boolean True if successfully rescheduled. False otherwise
	*/
	private function reschedule_scan($profile_id, $date_scheduled) {
		$db = nessquikDB::getInstance();
		$sql = array(
			'update' => "	UPDATE profile_list 
					SET date_scheduled=':2' 
					WHERE profile_id=':1'"
		);

		$stmt = $db->prepare($sql['update']);
		$stmt->execute($profile_id, $date_scheduled);

		if ($stmt->affected() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Return fullname for an abbreviated day
	*
	* Given an abbreviated day of the week, this method
	* will return the full name of the week. This is
	* needed especially by the relative date finder
	* since it uses full weekday names for comparison.
	*
	* @param string $abbreviation Abbreviated weekday that
	*	is to be converted into a full weekday name
	* @return string Full weekday name
	*/
	private function get_weekday_fullname($abbreviation) {
		$day 		= '';
		$abbreviation	= strtolower($abbreviation);

		switch($abbreviation) {
			case "sun":
				return "Sunday";
				break;
			case "mon":
				return "Monday";
				break;
			case "tue":
				return "Tuesday";
				break;
			case "wed":
				return "Wednesday";
				break;
			case "thu":
				return "Thursday";
				break;
			case "fri":
				return "Friday";
				break;
			case "sat":
				return "Saturday";
				break;
		}
	}
}

?>
