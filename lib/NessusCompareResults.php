<?php

require_once(_ABSPATH.'/lib/Nessus.php');

/**
* Class used to compare the Nessus objects provided
* by nessquik, and generate reports based on their
* differences
*
* @author Tim Rupp
*/
class NessusCompareResults extends Nessus {
	/**
	* nessus object that will be the base for comparison
	*
	* @var object
	*/
	public $from;

	/**
	* nessus object that will be compared to the base object
	*
	* @var object
	*/
	public $to;

	/**
	* List of hosts that are new the to the 'compare to' results
	*
	* @var array
	*/
	public $new_hosts;

	/**
	* List of hosts that no longer exist in the 'compre to' results
	*
	* @var array
	*/
	public $old_hosts;

	/**
	* List of hosts whose severities have changed from the last scan
	*
	* @var array
	*/
	private $changed_hosts;

	var $closed_holes;
	var $closed_warnings;
	var $closed_notes;

	var $new_holes;
	var $new_warnings;
	var $new_notes;

	var $unchanged_holes;
	var $unchanged_warnings;
	var $unchanged_notes;

	var $run_time_difference;

	var $profile_name;
	var $scan_end_from;
	var $scan_end_to;

	/**
	* Strings in NBE output that will change each
	* time a Nessus scan is run. This means that it
	* will _always_ show up in a comparison. Because
	* of that, I just ignore NBE lines that contain
	* these strings.
	*
	* @var array
	*/
	private $irrelevant_nbe = array (
		'timestamps|',
		'Information about this scan',
		'here is the traceroute from',

		// If NTP is listening, the values in this NBR line likely
		// differ each time the scanner reads the NTP banner
		'rootdispersion=',

		// More NTP stuff, the value in seconds is listed in the NBE output
		'The difference between the local and remote clocks'
	);

	/**
	* Constructor. Create comparison object.
	*
	* The constructor preps the comparison object right
	* away so that it can be used immediately after
	* instantiation. It requires that at least two(2)
	* Nessus objects be sent to it. These objects can
	* be made by using the Nessus class supplied with
	* nessquik
	*
	* @param object $from Nessus object containing scan
	*	that will be compared from. In other words,
	*	the original scan
	* @param object $to Nessus object containing scan
	*	that you are comparing the first scan to.
	* @param string $profile_name The name of the scan
	*	profile.
	*/
	public function __construct(Nessus $from, Nessus $to, $profile_name = 'default') {
		$this->from 	= $from;
		$this->to	= $to;

		$from_time_diff	= $this->from->scan_end - $this->from->scan_start;
		$to_time_diff 	= $this->to->scan_end - $this->to->scan_start;

		$this->run_time_difference 	= $from_time_diff - $to_time_diff;
		
		$this->new_hosts		= $this->find_new_hosts($this->from->hosts, $this->to->hosts);
		$this->old_hosts		= $this->find_old_hosts($this->from->hosts, $this->to->hosts);

		$this->scan_end_from		= strftime("%A %B, %d %Y at %r", $this->from->scan_end);
		$this->scan_end_to		= strftime("%A %B, %d %Y at %r", $this->to->scan_end);

		$this->profile_name		= $profile_name;
	}

	/**
	* Compare the scan results
	*/
	public function compare_results() {
		$identical = false;

		$this->filter_irrelevant_nbe();
		$this->filter_unchanged_nbe();

		$identical = $this->check_for_identity($this->from->nbe, $this->to->nbe);

		if ($identical) {
			return;
		}

		// Changed hosts mean that the host existed in the 'from'
		// NBE, but the specific NBE line is either new or different
		$this->find_changed_hosts($this->to->nbe, $this->from->hosts);
		
		$this->from->parse_nbe();
		$this->to->parse_nbe();

		#$this->gather_fixed("holes");
		#$this->gather_unchanged("holes");
	}

	/**
	* Find hosts that are new to the scan results
	*
	* In the event that new hosts are appearing in the
	* scan results, this method will pick out those new
	* hosts and report on them.
	*
	* New hosts could show up for a variety of reasons
	*/
	private function find_new_hosts($from, $to) {
		// The 'hosts' class variable is keyed by host address
		$from_hosts 	= array_keys($from);
		$to_hosts	= array_keys($to);

		return array_diff($to_hosts, $from_hosts);
	}

	/**
	* Find hosts whose NBE has changed
	*/
	private function find_changed_hosts($to_nbe, $from_hosts) {
		$from_hosts 	= array_keys($from_hosts);
		$results	= array();

		foreach($to_nbe as $key => $val) {
			$tmp 	= explode('|', $val);

			@$target 	= $tmp[2];

			if ($target == '') {
				continue;
			} else if (in_array($target, $from_hosts)) {
				continue;
			} else {
				$results[] = $target;
			}
		}

		return $results;		
	}

	/**
	* The opposite of new hosts
	*
	* This function should return a list of machines that have
	* "fallen off" the scan list. This could happen for several
	* reasons.
	*
	*	1. You changed the devices in the scan profile
	*	2. The machine was not online during the scan time
	*	3. The machine blocked attempts to ping it, or they timed out
	*
	* In any case, I think it's helpful to know that a machine is
	* no longer on the scan profile.
	*
	* @param array $from Array indexed by the host IP address
	* @param array $to Array indexed by the host IP address
	* @return array Array of hosts that do not exist in the new results
	* @see find_new_hosts
	*/
	private function find_old_hosts($from, $to) {
		$result = $this->find_new_hosts($to, $from);

		return $result;
	}

	/**
	* Check to see if the NBE reports are identical
	*
	* After appropriate filtering has been done, this
	* method will check to see if the reports are identical
	* by comparing the number of remaining NBE lines that
	* exist in the report
	*
	* @param array $from Array of parsed NBE lines
	* @param array $to Array of parsed NBE lines
	* @return bool True if identical, false otherwise
	*/
	private function check_for_identity($from, $to) {
		$from_count 	= count($from);
		$to_count	= count($to);

		if ($from_count == 0 && $to_count == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Remove irrelevant NBE from compared reports
	*
	* Some NBE lines can change each time the report is
	* generated, such as the NTP discovery NBE results.
	* In my opinion this just creates noise. Therefore
	* I've specified a list of strings which I consider
	* to be "noise" and this method will remove all the
	* NBE lines that contain those strings. This will
	* hopefully cut down on the number of bogus "changed"
	* issues that could possibly be reported in the
	* comparison report
	*
	* @see irrelevant_nbe
	* @see irrelevant
	*/
	private function filter_irrelevant_nbe() {
		foreach($this->irrelevant_nbe as $key => $val) {
			/**
			* Find any irrelevant NBE and set the key
			* value equal to 'empty' so that it will be
			* cleaned from the NBE array before further
			* filters are run against the NBE
			*/
			$this->from->nbe 	= array_filter($this->from->nbe, array($this, 'irrelevant'));
			$this->to->nbe		= array_filter($this->to->nbe, array($this, 'irrelevant'));
		}

		$this->from->nbe	= array_filter($this->from->nbe, array($this, "strip_empty"));
		$this->to->nbe 		= array_filter($this->to->nbe, array($this, "strip_empty"));
	}

	/**
	* Filtering function for removing irrelevant NBE
	*
	* This function needs a lot of explaining. PHP's array_filter function
	* will include the value in the result array, of the value passed to it
	* if the return value of this function is true.
	*
	* I do a strpos in this function to see if the irrelevant string exists
	* in the current array value. If it DOES exist (!== false) then I set
	* the $found variable to false so that the found value is NOT included
	* in my filtered array. The logic is back-asswards I know, but that's
	* how it works.
	*
	* @param string $var String to search for irrelevant terms
	* @return bool True if the value is NOT found, false if the value IS found
	*/
	private function irrelevant($var) {
		$found = true;

		foreach ($this->irrelevant_nbe as $key => $val) {
			$pos = strpos($var, $val);

			if ($pos !== false) {
				$found = false;
				break;
			}
		}

		return $found;
	}

	/**
	* Remove unchanged NBE
	*
	* As far as I'm concerned, NBE that hasnt changed is
	* next to useless. If you're comparing scan results,
	* then it would stand to reason that you'd want to see
	* what has changed. This method will remove all the
	* NBE lines from the two scan results that are identical.
	* This will likely drastically reduce the total size
	* of the NBE and will make for shorter comparisons
	*
	* @see process_nbe_unchanged
	* @see strip_empty
	*/
	private function filter_unchanged_nbe() {
		foreach($this->from->nbe as $key => $val) {
			if(in_array($val, $this->to->nbe)) {
				$to_key = '';

				$this->process_nbe_unchanged($val);

				// Set the 'from' nbe to be empty
				$this->from->nbe[$key] = '';

				/**
				* The 'from' nbe key may not be the same
				* key as the 'to' nbe key. That's why I do
				* a search for the 'to' nbe key that matched
				* the 'from' nbe key
				*/
				$to_key = array_search($val, $this->to->nbe);
				$this->to->nbe[$to_key] = '';
			}
		}

		$this->from->nbe	= array_filter($this->from->nbe, array($this, "strip_empty"));
		$this->to->nbe 		= array_filter($this->to->nbe, array($this, "strip_empty"));
	}

	private function filter_fixed_holes() {

	}

	private function filter_unchanged_holes() {

	}

	/**
	* array_filter function to remove empty indices
	*
	* This function is used by several array_filters in the
	* process script to strip out entries from the array that
	* have an empty value
	*
	* @param string $var Value in the array to check for emptiness
	*/
	private function strip_empty($var) {
		if ($var != '') {
			return true;
		}
	}

	public function output_html($embedded = false) {

		$identical = $this->check_for_identity($this->from->nbe, $this->to->nbe);

		if ($identical) {
			echo "<div style='font-family: \"lucida grande\", tahoma, "
			. "verdana, arial, sans-serif; font-size: 11px; width: 100%; "
			. "text-align: center;'>Scan results for the selected reports are identical</div>";
			return;
		}

		/**
		* Earlier I subtracted the 'to' scan time from the 'from' scan time.
		* therefore if the value of run_time_difference is positive, that means
		* that the 'to' scan time was shorter. If the value is negative, that
		* means the 'to' scan time took longer.
		*/
		if ($this->run_time_difference < 0) {
			$call_duration		= $this->call_duration($this->run_time_difference,0);
			$call_duration_txt 	= sprintf("The original scan was shorter by %s", $call_duration);
		} else {
			$call_duration		= $this->call_duration(0,$this->run_time_difference);
			$call_duration_txt	= sprintf("The original scan took %s longer to complete", $call_duration);
		}

		if (!$embedded) {
			$output = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'"
			. "'http://www.w3.org/TR/html4/strict.dtd'>"
			. "<html>\n"
			. " <head>\n"
			. " <title>Nessus Scan Comparison Report</title>\n"
			. " <meta http-equiv='Content-Type' content='text/html'>\n";
		}
		
		$output .= " <style type='text/css'>\n"
		. " <!--\n"
		. "  #nessus BODY {\n\tBACKGROUND-COLOR: #ffffff\n }\n"
		. "  #nessus A {\tTEXT-DECORATION: none }\n"
		. "  #nessus A:visited {\tCOLOR: #0000cf; TEXT-DECORATION: none }\n"
		. "  #nessus A:link {\tCOLOR: #0000cf; TEXT-DECORATION: none }\n"
		. "  #nessus A:active {\tCOLOR: #0000cf; TEXT-DECORATION: underline }\n"
		. "  #nessus A:hover {\tCOLOR: #0000cf; TEXT-DECORATION: underline }\n"
		. "  #nessus OL {\tCOLOR: #333333; FONT-FAMILY: tahoma,helvetica,sans-serif }\n"
		. "  #nessus UL {\tCOLOR: #333333; FONT-FAMILY: tahoma,helvetica,sans-serif }\n"
		. "  #nessus P {\tCOLOR: #333333; FONT-FAMILY: tahoma,helvetica,sans-serif }\n"
		. "  #nessus BODY {\tCOLOR: #333333; FONT-FAMILY: tahoma,helvetica,sans-serif }\n"
		. "  #nessus TD {\tCOLOR: #333333; FONT-FAMILY: tahoma,helvetica,sans-serif }\n"
		. "  #nessus TR {\tCOLOR: #333333; FONT-FAMILY: tahoma,helvetica,sans-serif }\n"
		. "  #nessus TH {\tCOLOR: #333333; FONT-FAMILY: tahoma,helvetica,sans-serif }\n"
		. "  #nessus FONT.title {\tBACKGROUND-COLOR: white; COLOR: #363636; FONT-FAMILY: "
		. "	tahoma,helvetica,verdana,lucida console,utopia; FONT-SIZE: 10pt; FONT-WEIGHT: bold }\n"
		. "  #nessus FONT.sub {\tBACKGROUND-COLOR: white; COLOR: #000000; FONT-FAMILY: "
		. "	tahoma,helvetica,verdana,lucida console,utopia; FONT-SIZE: 10pt }\n"
		. "  #nessus FONT.layer {\tCOLOR: #ff0000; FONT-FAMILY: courrier,sans-serif,arial,helvetica; FONT-SIZE: 8pt; TEXT-ALIGN: left }\n"
		. "  #nessus TD.title {\tBACKGROUND-COLOR: #A2B5CD; COLOR: #555555; FONT-FAMILY: "
		. "	tahoma,helvetica,verdana,lucida console,utopia; FONT-SIZE: 10pt; FONT-WEIGHT: bold; HEIGHT: 20px; TEXT-ALIGN: right }\n"
		. "  #nessus TD.sub {\tBACKGROUND-COLOR: #DCDCDC; COLOR: #555555; FONT-FAMILY: "
		. "	tahoma,helvetica,verdana,lucida console,utopia; FONT-SIZE: 10pt; FONT-WEIGHT: bold; HEIGHT: 18px; TEXT-ALIGN: left }\n"
		. "  #nessus TD.content {\tBACKGROUND-COLOR: white; COLOR: #000000; FONT-FAMILY: "
		. "	tahoma,arial,helvetica,verdana,lucida console,utopia; FONT-SIZE: 8pt; TEXT-ALIGN: left; VERTICAL-ALIGN: middle }\n"
		. "  #nessus TD.default {\tBACKGROUND-COLOR: WHITE; COLOR: #000000; FONT-FAMILY: "
		. "	tahoma,arial,helvetica,verdana,lucida console,utopia; FONT-SIZE: 8pt; }\n"
		. "  #nessus TD.border {\tBACKGROUND-COLOR: #cccccc; COLOR: black; FONT-FAMILY: "
		. "	tahoma,helvetica,verdana,lucida console,utopia; FONT-SIZE: 10pt; HEIGHT: 25px }\n"
		. "  #nessus TD.border-HILIGHT {\tBACKGROUND-COLOR: #ffffcc; COLOR: black; FONT-FAMILY: "
		. "	verdana,arial,helvetica,lucida console,utopia; FONT-SIZE: 10pt; HEIGHT: 25px }\n"
		. "-->\n</style>\n";

		if (!$embedded) {
			$output .= "</head>\n"
			. "<body>\n";
		}

		$output .= "<div id='nessus'>\n"
		. "<a name='toc'></a>\n"
		. "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
		. "	<tbody>\n"
		. "		<tr>\n"
		. "			<td>\n"
		. "				<table border='0' cellpadding='2' cellspacing='1' width='100%'>\n"
		. "					<tbody>\n"
		. "						<tr>\n"
		. "							<td class='title'>Nessus Scan Comparison Report</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='content'>\n"
		. "							This report compares the changes "
		. "							between the selected scan results."
		. "							</td>\n"
		. "						</tr>\n"
		. "					</tbody>\n"
		. "				</table>\n"
		. "			</td>\n"
		. "		</tr>\n"
		. "	</tbody>\n"
		. "</table><br>"
		. "<a name='compdetails'></a>\n";

		// Begin printing the Scan Details
		$output .= "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
		. "	<tbody>\n"
		. "		<tr>\n"
		. "			<td>\n"
		. "				<table border='0' cellpadding='2' cellspacing='1' width='100%'>\n"
		. "					<tbody>\n"
		. "						<tr>\n"
		. "							<td class='title' colspan='2'>Comparison Details</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>\n"
		. "								Results to compare from\n"
		. "							</td>\n"
		. "							<td class='default' width='40%'>\n"
		. 								$this->scan_end_from
		. "							</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>\n"
		. "								Results to compare to\n"
		. "							</td>\n"
		. "							<td class='default' width='40%'>\n"
		. 								$this->scan_end_to
		. "							</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>\n"
		. "								Profile containing the results\n"
		. "							</td>\n"
		. "							<td class='default' width='40%'>\n"
		. 								$this->profile_name
		. "							</td>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>\n"
		. "								New hosts found in the scan\n"
		. "							</td>\n"
		. "							<td class='default' width='40%'>";

		$count = count($this->new_hosts);
		if ($count == 1) {
			$output .= "<a href='#toc_new'>$count host</a>";
		} else if ($count > 1) { 
			$output .= "<a href='#toc_new'>$count hosts</a>";
		} else {
			$output .= "no new hosts";
		}

		$output .= "							</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>\n"
		. "								Hosts no longer found in the scan\n"
		. "							</td>\n"
		. "							<td class='default' width='40%'>\n";

		$count = count($this->old_hosts);
		if ($count == 1) {
			$output .= "<a href='#toc_old'>$count host</a>";
		} else if ($count > 1) {
			$output .= "<a href='#toc_old'>$count hosts</a>";
		} else {
			$output .= "no hosts are missing";
		}

		$output .= "							</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>\n"
		. "								Hosts with changed issues\n"
		. "							</td>\n"
		. "							<td class='default' width='40%'>\n";

		$count = count($this->changed_hosts);
		if ($count == 1) {
			$output .= "$count new host";
		} else if ($count > 1) {
			$output .= "$count new hosts";
		} else {
			$output .= "no issues have changed";
		}

		$output .= "						<tr>\n"
		. "							<td class='default' width='60%'>Difference in scan time</td>\n"
		. "							<td class='default' width='40%'>". $call_duration_txt ."</td>\n"
		. "						</tr>\n"
		. "					</tbody>\n"
		. "				</table>\n"
		. "			</td>\n"
		. "		</tr>\n"
		. "	</tbody>\n"
		. "</table><br><br>\n"

		. "<a name='toc_new'></a>\n"

		// Begin printing the Host List
		. "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
		. "	<tbody>\n"
		. "		<tr>\n"
		. "			<td>\n"
		. "				<table border='0' cellpadding='2' cellspacing='1' width='100%'>\n"
		. "					<tbody>\n"
		. "						<tr>\n"
		. "							<td class='title' colspan='2'>List of New Hosts</td>\n"
		. "						</tr>\n"
		. "						<tr>\n";

		if (count($this->to->host_issues) > 1) {
			$output .="							<td class='sub' width='60%'>Host(s)</td>\n";
		} else {
			$output .="							<td class='sub' width='60%'>Host</td>\n";
		}

		$output .= "							<td class='sub' width='40%'>Possible Issue</td>\n"
		. "						</tr>\n";


		foreach($this->to->host_issues as $target => $severity) {
			$href		= $this->portname_to_ahref("toc", $target);

			$output .= "<tr><td class='default' width='60%'><a href='#$href'>$target</a></td>\n";

			if($severity == $this->HOLE_PRESENT) {
				$output .= "<td class='default' width='40%'><font color='red'>Security hole(s) found</font></td>\n";
			} else if($severity == $this->WARNING_PRESENT) {
				$output .= "<td class='default' width='40%'>Security warning(s) found</td>\n";
			} else if($severity == $this->NOTE_PRESENT) {
				$output .= "<td class='default' width='40%'>Security note(s) found</td>\n";
			} else {
				$output .= "<td class='default' width='40%'>No noticeable information found</td>\n";
			}

			$output .= "</tr>";
		}

		$output .= "				</tbody>\n"
		. "				</table>\n"
		. "			</td>\n"
		. "		</tr>\n"
		. "	</tbody>\n"
		. "</table><br><br>\n";

		if (count($this->old_hosts) > 0) {
			$output .= "<a name='toc_old'></a>\n"
			// Begin printing the Host List
			. "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
			. "	<tbody>\n"
			. "		<tr>\n"
			. "			<td>\n"
			. "				<table border='0' cellpadding='2' cellspacing='1' width='100%'>\n"
			. "					<tbody>\n"
			. "						<tr>\n"
			. "							<td class='title'>List of Hosts Not Found</td>\n"
			. "						</tr>\n";

			foreach($this->old_hosts as $key => $val) {
				$output .= "<tr>\n"
				. "<td class='default'>$val</td>\n"
				. "</tr>\n";
			}

			$output .= "				</tbody>\n"
			. "				</table>\n"
			. "			</td>\n"
			. "		</tr>\n"
			. "	</tbody>\n"
			. "</table>\n"
			. "<div style='text-align: left;'>\n"
			. "<font size='-2'><a href='#toc'>[ return to top ]</a></font>\n"
			. "</div><br><br>\n";
		}


		// Begin printing the Analysis of Host
		foreach ($this->to->host_analysis as $hostname => $ports) {
			$port = '';
			$desc = '';
			$name = '';

			$name = $this->portname_to_ahref("toc", $hostname);

			$output .= "<div>"
			. "<a name='$name'></a>\n"
			. "</div>";

			$output .= "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
			. "	<tbody>\n"
			. "		<tr>\n"
			. "			<td>\n"
			. "				<table cellpadding='2' cellspacing='1' border='0' width='100%'>\n"
			. "					<tbody>\n"
			. "						<tr><td class='title' colspan='3'>Analysis of Host</td></tr>\n"
			. "						<tr>\n"
			. "							<td class='sub' width='20%'>Address of Host</td>\n"
			. "							<td class='sub' width='30%'>Port/Service</td>\n"
			. "							<td class='sub' width='30%'>Issue regarding Port</td>\n"
			. "						</tr>\n";


			if(count($ports) > 0) {
				foreach($ports as $key2 => $val2) {
					$port 		= $val2['port'];
					$severity	= $val2['severity'];
					$name		= $this->portname_to_ahref($port,$hostname);

					if($port) {
						if($severity == 1 || $severity == 2 | $severity == 3 ) {
							$output .= "<tr>\n"
							. "<td class='default' width='20%'>$hostname</td>\n"
							. "<td class='default' width='30%'><a href='#$name'>$port</a></td>\n";

							if($severity == 1) {
								$output .= "<td class='default' width='30%'>"
								. "<font color='red'>Security hole found</font>"
								. "</td>\n";
							} else if($severity == 2) {
								$output .= "<td class='default' width='30%'>Security warning(s) found</td>\n";
							} else {
								$output .= "<td class='default' width='30%'>Security notes found</td>\n";
							}

							$output .= "</tr>";
						}
              				} else {
						$output .= "<tr><td class='default' width='20%'>$hostname</td>\n"
						. "<td class='default' width='30%'>$name</td>\n"
						. "<td class='default' width='30%'>No Information</td></tr>\n";
					}
				}
			} else {
				$output .= "<tr><td class='default' width='20%'>$hostname</td>\n";
				$output .= "<td class='default' width='30%'>$name</td><td class='default' width='30%'>No Information</td></tr>\n";
			}

			// Begin printing 
			$output .= "				</tbody>\n"
			. "				</table>\n"
			. "			</td>\n"
			. "		</tr>\n"
			. "	</tbody>\n"
			. "</table>\n"
			. "<div style='text-align: left;'>\n"
			. "<font size='-2'><a href='#toc'>[ return to top ]</a></font>\n"
			. "</div><br><br>\n";

			if (count($this->changed_hosts) > 0) {
				$output .= "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
				. "	<tbody>\n"
				. "		<tr>\n"
				. "			<td>\n"
				. "				<table cellpadding='2' cellspacing='1' border='0' width='100%'>\n"
				. "					<tr><td class='title' colspan='6'>Security Issues and Fixes: $hostname</td></tr>\n"
				. "					<tr>
										<td class='title' colspan='3' style='background-color: #fff;'>Old Results</td>
										<td class='title' colspan='3' style='background-color: #fff;'>New Results</td>
									</tr>\n"
				. "					<tr>\n"
				. "						<td class='sub' width='10%'>Type</td>\n"
				. "						<td class='sub' width='10%'>Port</td>\n"
				. "						<td class='sub' width='30%'>Issue and Fix</td>\n"
				. "						<td class='sub' width='10%'>Type</td>\n"
				. "						<td class='sub' width='10%'>Port</td>\n"
				. "						<td class='sub' width='30%'>Issue and Fix</td>\n"
				. "					</tr>\n";

				$in	= $this->from->host_full_issues;
				$out 	= $this->to->host_full_issues;


				// These loops sort the contents of the full issues by severity ascending
				foreach ($out as $key => $val) {
					foreach ($val as $key2 => $val2) {
						ksort($out[$key][$key2]);
					}
				}

				foreach ($in as $key => $val) {
					foreach ($val as $key2 => $val2) {
						ksort($in[$key][$key2]);
					}
				}

				foreach($out[$hostname] as $port => $val) {
					$report = '';
					$info = '';
					$note = '';

					foreach($val as $key => $items) {
						$report 	= str_replace("\n",'<p>',$items['content']);
						$report 	= str_replace('\n','<p>',$report);

						$report		= $this->correct_p_tags($report);

						$severity	= $items['severity'];

						if ($severity == 3) {
							$output .= "<tr><td valign='top' class='default' width='10%'>Informational</td>\n";
						} else if ($severity == 2) {
							$output .= "<tr><td valign='top' class='default' width='10%'>Warning</td>\n";
						} else if ($severity == 1) {
							$output .= "<tr><td valign='top' class='default' width='10%'><font color='red'>Vulnerability</font></td>\n";
						}
		
						$name = $this->portname_to_ahref($port, $hostname);

						if ($this->has_name($name) === false) {
							$output .= "<td valign='top' class='default' width='10%'><a name='$name'></a>$port</td>\n";
							$this->set_named($name);
						} else {
							$output .= "<td valign='top' class='default' width='10%'>$port</td>\n";
						}

						$output .= "<td class='default' width='30%'>$report</td></tr>\n";
					}
				}

				$output .= "			</table>\n"
				. "			</td>\n"
				. "		</tr>\n"
				. "	</tbody>\n"
				. "</table>\n";
			}

			if (count($this->new_hosts) > 0) {
				$output .= "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
				. "	<tbody>\n"
				. "		<tr>\n"
				. "			<td>\n"
				. "				<table cellpadding='2' cellspacing='1' border='0' width='100%'>\n"
				. "					<tr><td class='title' colspan='3'>New Security Issues and Fixes: $hostname</td></tr>\n"
				. "					<tr>\n"
				. "						<td class='sub' width='10%'>Type</td>\n"
				. "						<td class='sub' width='10%'>Port</td>\n"
				. "						<td class='sub' width='80%'>Issue and Fix</td>\n"
				. "					</tr>\n";

				$out 	= $this->to->host_full_issues;


				// These loops sort the contents of the full issues by severity ascending
				foreach ($out as $key => $val) {
					foreach ($val as $key2 => $val2) {
						ksort($out[$key][$key2]);
					}
				}

				foreach($out[$hostname] as $port => $val) {
					$report = '';
					$info = '';
					$note = '';

					foreach($val as $key => $items) {
						$report 	= str_replace("\n",'<p>',$items['content']);
						$report 	= str_replace('\n','<p>',$report);

						$report		= $this->correct_p_tags($report);

						$severity	= $items['severity'];

						if ($severity == 3) {
							$output .= "<tr><td valign='top' class='default' width='10%'>Informational</td>\n";
						} else if ($severity == 2) {
							$output .= "<tr><td valign='top' class='default' width='10%'>Warning</td>\n";
						} else if ($severity == 1) {
							$output .= "<tr><td valign='top' class='default' width='10%'><font color='red'>Vulnerability</font></td>\n";
						}
		
						$name = $this->portname_to_ahref($port, $hostname);

						if ($this->has_name($name) === false) {
							$output .= "<td valign='top' class='default' width='10%'><a name='$name'></a>$port</td>\n";
							$this->set_named($name);
						} else {
							$output .= "<td valign='top' class='default' width='10%'>$port</td>\n";
						}

						$output .= "<td class='default' width='30%'>$report</td></tr>\n";
					}
				}

				$output .= "			</table>\n"
				. "			</td>\n"
				. "		</tr>\n"
				. "	</tbody>\n"
				. "</table>\n";
			}
		}

		$output .= "</div><br>\n";

		if (!$embedded) {
			$output .= "</body></html>";
		}

		return $output;
	}

	/**
	* Check if NBE line indicates a hole
	*
	* NBE output that contains results will indicate in
	* the actual NBE line what type of result it is. This
	* method will verify if the NBE line is specifying
	* a security hole
	*
	* @param string $nbe NBE line to check for security warning
	*/
	function is_hole($nbe) {
		$pos = strpos($nbe, "Security Hole");

		if ($pos !== false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Check if NBE line indicates a warning
	*
	* This method will verify if the NBE line is specifying
	* a security warning
	*
	* @param string $nbe NBE line to check for security warning
	*/
	function is_warning($nbe) {
		$pos = strpos($nbe, "Security Warning");

		if ($pos !== false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Check if NBE line indicates a note
	*
	* This method will verify if the NBE line is specifying
	* a security note
	*
	* @param string $nbe NBE line to check for security warning
	*/
	function is_note($nbe) {
		$pos = strpos($nbe, "Security Note");

		if ($pos !== false) {
			return true;
		} else {
			return false;
		}
	}

	function process_nbe_unchanged($nbe) {
		if ($this->is_hole($nbe)) {
			$this->unchanged_holes[] 	= $nbe;
		} else if ($this->is_warning($nbe)) {
			$this->unchanged_warnings[]	= $nbe;
		} else if ($this->is_note($nbe)) {
			$this->unchanged_notes[] 	= $nbe;
		}
	}

	function process_nbe_addition($val) {

	}
}

?>
