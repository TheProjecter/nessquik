<?php

/**
* Nessus helper class used (among other things) to
* parse NBE output and convert it to various other
* formats (like HTML and text)
*
* @package nessquik
* @author Tim Rupp
*/
class Nessus {
	/**
	* Full NBE output, delimited by triple colons (':::')
	*
	* @var string
	*/
	public $nbe		= '';

	/**
	* Unique list of hosts that were scanned
	*
	* @var array
	*/
	public $hosts		= array();

	/**
	* List of highest issue criticality for a port. If a lower
	* criticality exists, it will be overwritten by the higher
	* one
	*
	* @var array
	*/
	protected $host_issues	= array();

	/**
	* Multidimensional array indexed like so
	*
	* [target]
	*	[integer]
	*		[severity]
	*		[port]
	*
	* It is used to quickly make the "Analysis of Host" block that
	* can be found in a nessus report. This block lists the following
	*
	* 	target	port	highest_criticality
	*
	* @var array
	*/
	protected $host_analysis	= array();

	/**
	* All issues and suggested fixes for the issues. Indexed like so
	*
	* [target]
	*	[port]
	*		[integer]
	*			[severity]
	*			[content]
	*
	* The array is sorted by severity (most severe being at the top)
	* after the full array has been populated. The content is the full
	* suggested fix for the issue and severity is the integer representaion
	* of the severity as defined by the *_PRESENT constants defined
	* below.
	*
	* @var array 	
	*/
	protected $host_full_issues	= array();

	/**
	* Array that holds a count of the number of holes, warnings, and
	* notes for all the targets scanned
	*
	* @var array
	*/
	public $summary		= array();

	/**
	* Representation (in seconds) of when the scan started
	*
	* @var number
	*/
	public $scan_start		= 0;

	/**
	* Representation (in seconds) of when the scan finished
	*
	* @var number
	*/
	public $scan_end		= 0;

	/**
	*
	*/
	public $nbe_files		= array();

	/**
	* Constant containing value dictating a hole
	*
	* @var integer
	*/
	protected $HOLE_PRESENT 	= 1;

	/**
	* Constant containing value dictating a warning
	*
	* @var integer
	*/
	protected $WARNING_PRESENT	= 2;

	/**
	* Constant containing value dictating a note
	*
	* @var integer
	*/
	protected $NOTE_PRESENT	= 3;

	/**
	* List of anchors that are already used in the HTML document
	*
	* @var array
	*/
	private $used_anchors	= array();

	/**
	* Constructor for Nessus class
	*
	* Accepts an argument containing the NBE output of
	* a Nessus scan. The class can then be used to convert
	* between output formats
	*
	* @param string $nbe NBE output delimited using my own delimiter
	*/
	public function __construct($nbe = '') {
		if (!is_array($nbe)) {
			$nbe = explode(':::', $nbe);
			$this->nbe = $nbe;
			$this->parse_nbe();
		} else if ($nbe != '') {
			$this->nbe = $nbe;
			$this->parse_nbe();
		}
	}

	/**
	* Convert NBE output to HTML output
	*
	* This function will take a block of complete NBE output
	* and transform it into HTML output that mimics the nessus
	* client conversion output. In fact, the below HTML was
	* pulled directly from the html_output.c source code of
	* the GPL version of nessus
	*
	* @param string $output Full NBE output of a nessus scan
	* @param bool $embedded Whether the HTML is going to be embedded in
	*	other HTML or not. If it is, then certain blocks of the
	*	otherwise full HTML code will not be returned.
	* @return string The full HTML conversion from NBE
	*/
	public function output_html($nbe = '', $embedded = false, $strip_css = false) {
		if ($nbe != '') {
			if (!is_array($nbe)) {
				$nbe = explode(':::', $nbe);
			}

			$this->nbe = $nbe;

			$this->parse_nbe();
		}		

		$host_count 	= count($this->hosts);
		$summary	= $this->summary;
		$output		= '';

		$scan_start	= strftime("%A %B, %d %Y at %r", $this->scan_start);
		$scan_end	= strftime("%A %B, %d %Y at %r", $this->scan_end);
		$call_duration	= $this->call_duration($this->scan_start,$this->scan_end);

		if (!$embedded) {
			$output = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'"
			. "'http://www.w3.org/TR/html4/strict.dtd'>"
			. "<html>\n"
			. " <head>\n"
			. " <title>Nessus Scan Report</title>\n"
			. " <meta http-equiv='Content-Type' content='text/html'>\n";
		}

		if (!$strip_css) {
			$output .= " <style type='text/css'>\n"
			. " <!--\n"
			. "	#nessus body {\n"
			. "		background-color: #fff;\n"
			. "		color: #333;\n"
			. "		font-family: tahoma,helvetica,sans-serif;\n"
			. "	}\n"

			. "	#nessus a {\n"
			. "		text-decoration: none;\n"
			. "	}\n"

			. "	#nessus a:visited {\n"
			. "		color: #0000cf;\n"
			. "		text-decoration: none;\n"
			. "	}\n"

			. "	#nessus a:link {\n"
			. "		color: #0000cf;\n"
			. "		text-decoration: none;\n"
			. "	}\n"

			. "	#nessus a:active {\n"
			. "		color: #0000cf;\n"
			. "		text-decoration: underline;\n"
			. "	}\n"

			. "	#nessus a:hover {\n"
			. "		color: #0000cf;\n"
			. "		text-decoration: underline;\n"
			. "	}\n"

			. "	#nessus ol {\n"
			. "		color: #333;\n"
			. "		font-family: tahoma,helvetica,sans-serif;\n"
			. "	}\n"

			. "	#nessus ul {\n"
			. "		color: #333;\n"
			. "		font-family: tahoma,helvetica,sans-serif;\n"
			. "	}\n"

			. "	#nessus p {\n"
			. "		color: #333;\n"
			. "		font-family: tahoma,helvetica,sans-serif;\n"
			. "	}\n"

			. "	#nessus td {\n"
			. "		color: #333;\n"
			. "		font-family: tahoma,helvetica,sans-serif;\n"
			. "	}\n"

			. "	#nessus tr {\n"
			. "		color: #333;\n"
			. "		font-family: tahoma,helvetica,sans-serif;\n"
			. "	}\n"

			. "	#nessus th {\n"
			. "		color: #333;\n"
			. "		font-family: tahoma,helvetica,sans-serif;\n"
			. "	}\n"

			. "	#nessus font.title {\n"
			. "		background-color: #fff;\n"
			. "		color: #363636;\n"
			. "		font-family: tahoma,helvetica,verdana,lucida console,utopia;\n"
			. "		font-size: 10pt;\n"
			. "		font-weight: bold;\n"
			. "	}\n"

			. "	#nessus font.sub {\n"
			. "		background-color: #fff;\n"
			. "		color: #000;\n"
			. "		font-family: tahoma,helvetica,verdana,lucida console,utopia;\n"
			. "		font-size: 10pt;\n"
			. "	}\n"

			. "	#nessus font.layer {\n"
			. "		color: #f00;\n"
			. "		font-family: courrier,sans-serif,arial,helvetica;\n"
			. "		font-size: 8pt;\n"
			. "		text-align: left;\n"
			. "	}\n"

			. "	#nessus td.title {\n"
			. "		background-color: #A2B5CD;\n"
			. "		color: #555;\n"
			. "		font-family: tahoma,helvetica,verdana,lucida console,utopia;\n"
			. "		font-size: 10pt;\n"
			. "		font-weight: bold;\n"
			. "		height: 20px;\n"
			. "		text-align: right;\n"
			. "	}\n"

			. "	#nessus td.sub {\n"
			. "		background-color: #DCDCDC;\n"
			. "		color: #555;\n"
			. "		font-family: tahoma,helvetica,verdana,lucida console,utopia;\n"
			. "		font-size: 10pt;\n"
			. "		font-weight: bold;\n"
			. "		height: 18px;\n"
			. "		text-align: left;\n"
			. "	}\n"

			. "	#nessus td.content {\n"
			. "		background-color: #fff;\n"
			. "		color: #000;\n"
			. "		font-family: tahoma,arial,helvetica,verdana,lucida console,utopia;\n"
			. "		font-size: 8pt;\n"
			. "		text-align: left;\n"
			. "		vertical-align: middle;\n"
			. "	}\n"

			. "	#nessus td.default {\n"
			. "		background-color: #fff;\n"
			. "		color: #000;\n"
			. "		font-family: tahoma,arial,helvetica,verdana,lucida console,utopia;\n"
			. "		font-size: 8pt;\n"
			. "	}\n"

			. "	#nessus td.border {\n"
			. "		background-color: #ccc;\n"
			. "		color: #000;\n"
			. "		font-family: tahoma,helvetica,verdana,lucida console,utopia;\n"
			. "		font-size: 10pt;\n"
			. "		height: 25px;\n"
			. "	}\n"

			. "	#nessus td.border-hilight {\n"
			. "		background-color: #ffc;\n"
			. "		color: #000;\n"
			. "		font-family: verdana,arial,helvetica,lucida console,utopia;\n"
			. "		font-size: 10pt;\n"
			. "		height: 25px;\n"
			. "	}\n"

			. "-->\n</style>\n";
		}

		if (!$embedded) {
			$output .= "</head>\n"
			. "<body>\n";
		}

		$output .= "<div id='nessus'>\n"
		. "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
		. "	<tbody>\n"
		. "		<tr>\n"
		. "			<td>\n"
		. "				<table border='0' cellpadding='2' cellspacing='1' width='100%'>\n"
		. "					<tbody>\n"
		. "						<tr>\n"
		. "							<td class='title'>Nessus Scan Report</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='content'>\n"
		. "							This report gives details on hosts that were tested "
		. "							and issues that were found. Please follow the recommended "
		. "							steps and procedures to eliminate these threats."
		. "							</td>\n"
		. "						</tr>\n"
		. "					</tbody>\n"
		. "				</table>\n"
		. "			</td>\n"
		. "		</tr>\n"
		. "	</tbody>\n"
		. "</table><br>";

		// Begin printing the Scan Details
		$output .= "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
		. "	<tbody>\n"
		. "		<tr>\n"
		. "			<td>\n"
		. "				<table border='0' cellpadding='2' cellspacing='1' width='100%'>\n"
		. "					<tbody>\n"
		. "						<tr>\n"
		. "							<td class='title' colspan='2'>Scan Details</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>\n"
		. "								Hosts which were alive and responding during test\n"
		. "							</td>\n"
		. "							<td class='default' width='30%'>$host_count</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>Number of security holes found</td>\n"
		. "							<td class='default' width='30%'>". $summary['hole'] ."</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>Number of security warnings found</td>\n"
		. "							<td class='default' width='30%'>". $summary['warn'] ."</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>Start Time</td>\n"
		. "							<td class='default' width='30%'>". $scan_start ."</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>Finish Time</td>\n"
		. "							<td class='default' width='30%'>". $scan_end ."</td>\n"
		. "						</tr>\n"
		. "						<tr>\n"
		. "							<td class='default' width='60%'>Elapsed Time</td>\n"
		. "							<td class='default' width='30%'>". $call_duration ."</td>\n"
		. "						</tr>\n"
		. "					</tbody>\n"
		. "				</table>\n"
		. "			</td>\n"
		. "		</tr>\n"
		. "	</tbody>\n"
		. "</table><br><br>\n"

		. "<a name='toc'></a>\n"

		// Begin printing the Host List
		. "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
		. "	<tbody>\n"
		. "		<tr>\n"
		. "			<td>\n"
		. "				<table border='0' cellpadding='2' cellspacing='1' width='100%'>\n"
		. "					<tbody>\n"
		. "						<tr>\n"
		. "							<td class='title' colspan='2'>Host List</td>\n"
		. "						</tr>\n"
		. "						<tr>\n";

		if (count($this->host_issues) > 1) {
			$output .="							<td class='sub' width='60%'>Host(s)</td>\n";
		} else {
			$output .="							<td class='sub' width='60%'>Host</td>\n";
		}

		$output .= "							<td class='sub' width='40%'>Possible Issue</td>\n"
		. "						</tr>\n";


		foreach($this->host_issues as $target => $severity) {
			$href = $this->portname_to_ahref("toc", $target);

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
		. "</table>\n";

		// Begin printing the Analysis of Host
		foreach ($this->host_analysis as $hostname => $ports) {
			$port = '';
			$desc = '';
			$name = '';

			$name = $this->portname_to_ahref("toc", $hostname);

			$output .= "<div>"
			. "<a name='$name'></a>\n"
			. "</div>";

			$output .= "<div>\n"
			. "<font size='-2'><a href='#toc'>[ return to top ]</a></font>\n"
			. "</div>\n"
			. "<br><br>\n"
			. "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
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
			. "</table><br><br>\n"
			. "<table style='background-color: #a1a1a1; width: 100%; border: 0px;' cellpadding='0' cellspacing='0'>\n"
			. "	<tbody>\n"
			. "		<tr>\n"
			. "			<td>\n"
			. "				<table cellpadding='2' cellspacing='1' border='0' width='100%'>\n"
			. "					<tr><td class='title' colspan='3'>Security Issues and Fixes: $hostname</td></tr>\n"
			. "					<tr>\n"
			. "						<td class='sub' width='10%'>Type</td>\n"
			. "						<td class='sub' width='10%'>Port</td>\n"
			. "						<td class='sub' width='80%'>Issue and Fix</td>\n"
			. "					</tr>\n";

			$out = $this->host_full_issues;

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

					$output .= "<td class='default' width='80%'>$report</td></tr>\n";
				}
			}

			$output .= "			</table>\n"
			. "			</td>\n"
			. "		</tr>\n"
			. "	</tbody>\n"
			. "</table>\n";

		}

		$output .= "</div>\n";

		if (!$embedded) {
			$output .= "</body></html>";
		}

		return $output;
	}

	/**
	* Create an ahref jump tag to content
	*
	* Ported from html_output.c in the GPL nessus, this
	* function I believe is supposed to create a jump
	* tag to specific sections of an HTML report
	*
	* @param string $name The anchor that you want to place (can be null).
	*	This equals the 'name' attribute: <a name='$name'></a>
	* @param string $hostname What is actually displayed
	*	This equals the content: <a name=''>$hostname</a>
	*/
	protected function portname_to_ahref($name, $hostname) {
		$new_anchor = '';

		/**
		* Convert '192.168.1.1' to '192_168_1_1' or
		* 'prof.nessus.org' to 'prof_nessus_org'
		*/
		$hostname = str_replace('.', '_', $hostname);

		/**
		* Convert 'telnet (21/tcp)' to '21_tcp'
		*/
		$name = str_replace(' ','_',$name);
		$name = str_replace('(','',$name);
		$name = str_replace(')','',$name);
		$name = str_replace('/','_',$name);

		$new_anchor = $name . '_' . $hostname;

		return $new_anchor;
	}

	/**
	* Set a bit to specify that this anchor name tag has been used
	* and shouldnt be used again
	*
	* HTML 4.01 strict outlaws having multiple <a> tags with the
	* same name attribute. This function modifies an array that is
	* checked during the creation of "Security Issues and Fixes"
	* to make sure that the same name attribute isnt being used twice
	*
	* @param string $name Anchor name to set existence of
	*/
	protected function set_named($name) {
		$this->used_anchors[$name] = true;
	}

	/**
	* Check if anchor name has been used
	*
	* After setting the anchor name bit, it will need to be checked
	* in the future. This will check to see if the bit is set (the
	* name attribute has been used)
	*
	* @param string $name Anchor name to check existence of
	*/
	protected function has_name($name) {
		if (@$this->used_anchors[$name] === true) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Correct multiple empty <p> tags in HTML output
	*
	* Part of my HTML output code converts all newline characters
	* to <p> tags. HTML strict outlaws empty p tags though, so to
	* get the same effect, I need to wrap the text in <p> tags.
	* By exploding the string based on <p> tag location, I can wrap
	* all the content in correct HTML.
	*
	* @param string $content Text to correct that has <p> tags
	*/
	protected function correct_p_tags($content) {
		$tmp 	= explode('<p>', $content);
		$result	= '';

		foreach($tmp as $key => $val) {
			$val = trim($val);

			if ($val == '') {
				continue;
			}

			$result .= '<p>'.$val.'</p>';
		}

		return $result;
	}

	/**
	* Convert NBE output to text output
	*
	* This function will take a block of complete NBE output
	* and transform it into text output that mimics the nessus
	* client conversion output. The below HTML was pulled
	* directly from the text_output.c source code of the
	* GPL version of nessus
	*
	* @param string $output Full NBE output of a nessus scan
	* @param bool $embedded Whether the HTML is going to be embedded in
	*	other HTML or not. If it is, then certain blocks of the
	*	otherwise full HTML code will not be returned.
	* @return string The full text conversion from NBE
	*/
	public function output_text($nbe = '', $embedded = false) {
		if ($nbe != '') {
			if (!is_array($nbe)) {
				$nbe = explode(':::', $nbe);
			}

			$this->nbe = $nbe;

			$this->parse_nbe();
		} else {
			return false;
		}

		$host_count 	= count($this->hosts);
		$summary	= $this->summary;
		$output 	= '';

		if ($embedded) {
			$output .= "<pre>";
		}

		$output .= "Nessus Scan Report\n"
		. "------------------\n\n\n\n"
		. "SUMMARY\n\n"
		. " - Number of hosts which were alive during the test : $host_count\n"
		. " - Number of security holes found 	: ". $summary['hole'] ."\n"
		. " - Number of security warnings found	: ". $summary['warn'] ."\n"
		. " - Number of security notes found 	: ". $summary['note'] ."\n\n"

		. " - Start Time 	: ". strftime("%A %B, %d %Y at %r", $this->scan_start) ."\n"
		. " - Finish Time 	: ". strftime("%A %B, %d %Y at %r", $this->scan_end) ."\n"
		. " - Elapsed Time	: ". $this->call_duration($this->scan_start,$this->scan_end) ."\n"
		
		. "\n\n"
		. "TESTED HOSTS\n\n";

                foreach($this->host_issues as $target => $severity) {
			$output .= " $target";

                        if($severity == $this->HOLE_PRESENT) {
				$output .= " (Security holes found)\n";
                        } else if($severity == $this->WARNING_PRESENT) {
				$output .= " (Security warnings found)\n";
                        } else if($severity == $this->NOTE_PRESENT) {
				$output .= " (Security notes found)\n";
                        } else {
				$output .= " (no noticeable problem found)\n";
			}
                }

		$output .= "\n\n\n"
		. "DETAILS\n\n";

		// Begin printing the Analysis of Host
		foreach ($this->host_analysis as $hostname => $ports) {
			$port = '';
			$desc = '';
			$href = '';
			$name = '';

			$output .= "+ $hostname :\n";

			if(count($ports) > 0) {
				$output .= " . List of open ports :\n";

				foreach($ports as $key2 => $val2) {
					$port 		= $val2['port'];
					$severity	= $val2['severity'];

					if($port) {
						if($severity == 1 || $severity == 2 | $severity == 3 ) {
							$output .= "   o $port";

							if($severity == 1) {
								$output .= " (Security hole found)\n";
							} else if($severity == 2) {
								$output .= " (Security warnings found)\n";
							} else {
             							$output .= " (Security notes found)\n";
							}
						}
              				} else {
						$output .= "   o $port\n";
					}
				}
			} else {
				$output .= " . List of open ports :\n";
				$output .= "   o No Information";
			}

			$out = $this->host_full_issues;

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
					$report 	= str_replace("\n","\n\t",$items['content']);
					$report 	= str_replace('\n',"\n\t",$report);

					$severity	= $items['severity'];

					if ($severity == 3) {
						$output .= "\n . Information found on port $port\n\n";
					} else if ($severity == 2) {
						$output .= "\n . Warning found on port $port\n\n";
					} else if ($severity == 1) {
						$output .= "\n . Vulnerability found on port $port : \n\n";
					}

					$output .= "\t$report\n";
				}
			}
		}

		$output .= "\n\n\n"
		. "------------------------------------------------------\n"
		. "This file was generated by the Nessus Security Scanner\n";

		if (!$embedded)
			return $output;
		else {
			$output .= "</pre>";
			return $output;
		}
	}

	/**
	* Convert nessquik NBE output to Nessus NBE output
	*
	* This function will take a block of complete NBE output
	* as it's stored by nessquik, and convert it back into
	* vanilla NBE output as would be returned by Nessus.
	*
	* @param string $output Full nessquik formatted NBE output
	* @param bool $embedded Whether the NBE is going to be embedded in
	*	other HTML or not.
	* @return string The full Nessus NBE conversion from nessquik NBE
	*/
	public function output_nbe($nbe = '', $embedded = false) {
		if ($nbe != '') {
			$nbe = str_replace("\n", '\n', $nbe);
			$nbe = str_replace(':::', "\n", $nbe);

			if (!$embedded) {
				return $nbe;
			} else {
				$nbe = "<pre>$nbe</pre>";
				return $nbe;
			}
		} else {
			return false;
		}
	}

	/**
	* Parse the Nessus NBE report format
	*
	* This method will parse the NBE report format and break it up
	* into manageable parts that can later be used to tranform the
	* NBE into other formats such as HTML or text
	*/
	public function parse_nbe() {
		$hosts	 	= array();
		$results	= array(
			'hole' => 0,
			'warn' => 0,
			'note' => 0
		);
		$ports		= array();
		$issues		= array();
		$analysis	= array();
		$descriptions	= array();

		// loop through for each line of output
		foreach($this->nbe as $key => $val) {
			$exists = false;

			/**
			* The format of the NBE output that I'm focusing on here
			* are the following two lines
			*
			*	timestamps|||scan_start|Wed Sep 27 08:23:27 2006|
			*	timestamps|||scan_end|Wed Sep 27 08:24:11 2006|
			*
			* I'm only concerned with getting the timestamps out
			*/
			if(strpos($val, 'scan_start') !== false) {
				$this->scan_start	= $this->nbe_scantime_to_timestamp($val);
			} elseif(strpos($val, 'scan_end') !== false) {
				$this->scan_end 	= $this->nbe_scantime_to_timestamp($val);
			}

			/**
			* The format of the NBE output that I'm focusing on here
			* looks like so.
			*
			*	timestamps||131.225.82.83|host_start|Sun Oct  1 00:46:05 2006|
			*
			* I'm only concerned with the 2nd index, the IP address
			*/
			if (strpos($val, 'host_start') !== false) {
				$tmp 		= explode('|', $val);
				$hosts[$tmp[2]]	= 1;
			}

			/**
			* The format of the NBE output that I'm focusing on here
			* looks like so.
			*
			* results|131.225.82|131.225.82.83|general/tcp|12053|Security Note|131.225.82.83 resolves as catbot.dhcp.fnal.gov.\n
			*
			* I'm only concerned with the 5th index. It can be one of the
			* following values
			*
			* - Security Hole
			* - Security Warning
			* - Security Note
			*/
			if (strpos($val, 'results') !== false) {
				$tmp 	= explode('|', $val);

				@$target 	= $tmp[2];
				@$port		= $tmp[3];
				@$result	= $tmp[5];
				@$content	= $tmp[6];

				if ($result == '' && $content == '') {
					continue;
				}

				if ($result == 'Security Hole') {
					// Assign a high severity if a lower severity has already been assigned
					if (@$issues[$target] > $this->HOLE_PRESENT || @$issues[$target] == '') {
						$issues[$target] = $this->HOLE_PRESENT;
					}

					// Increment # of holes by 1
					$results['hole'] += 1;
					$the_index = 1000 + $results['hole'];

					// Populate array of full descriptions
					$descriptions[$target][$port][$the_index] = array(
						'severity' => $this->HOLE_PRESENT,
						'content' => $content
					);

					/**
					* This madness simply checks to see if the current port has already
					* been stuffed into our analysis array and if it has, checks to see
					* if the stored severity is greater than the current severity. If it
					* is greater, then decrement it (make it more severe). In this case
					* set the stored severity to 'hole'
					*/
					if (count($analysis) > 0) {
						if (@is_array($analysis[$target])) {
							foreach(@$analysis[$target] as $key2 => $val2) {
								if ($analysis[$target][$key2]['port'] == $port) {
									if ($analysis[$target][$key2]['severity'] >= $this->HOLE_PRESENT) {
										$analysis[$target][$key2]['severity'] = $this->HOLE_PRESENT;
										$exists = true;
									}
								}
							}
						}
					}
					
					// I dont want duplicates. This 'if' statement takes care of that
					if (!$exists) {
						$analysis[$target][] = array(
							'severity'	=> $this->HOLE_PRESENT,
							'port' 		=> $port
						);
					}
				} else if ($result == 'Security Warning') {
					// Assign a high severity if a lower severity has already been assigned
					if (@$issues[$target] > $this->WARNING_PRESENT || @$issues[$target] == '') {
						$issues[$target] = $this->WARNING_PRESENT;
					}

					// Increment # of warnings by 1
					$results['warn'] += 1;
					$the_index = 2000 + $results['warn'];

					// Populate array of full descriptions
					$descriptions[$target][$port][$the_index] = array(
						'severity' => $this->WARNING_PRESENT,
						'content' => $content
					);

					/**
					* This madness simply checks to see if the current port has already
					* been stuffed into our analysis array and if it has, checks to see
					* if the stored severity is greater than the current severity. If it
					* is greater, then decrement it (make it more severe). In this case
					* set the stored severity to 'warning'
					*/
					if (count($analysis) > 0) {
						if (@is_array($analysis[$target])) {
							foreach(@$analysis[$target] as $key2 => $val2) {
								if ($analysis[$target][$key2]['port'] == $port) {
									if ($analysis[$target][$key2]['severity'] >= $this->WARNING_PRESENT) {
										$analysis[$target][$key2]['severity'] = $this->WARNING_PRESENT;
										$exists = true;
									}
								}
							}
						}
					}
					
					// I dont want duplicates. This 'if' statement takes care of that
					if (!$exists) {
						$analysis[$target][] = array(
							'severity'	=> $this->WARNING_PRESENT,
							'port'		=> $port
						);
					}
				} else if ($result == 'Security Note') {
					if (@$issues[$target] == '') {
						$issues[$target] = $this->NOTE_PRESENT;
					}

					// Increment # of notes by 1
					$results['note'] += 1;
					$the_index = 3000 + $results['note'];

					// Populate array of full descriptions
					$descriptions[$target][$port][$the_index] = array(
						'severity' => $this->NOTE_PRESENT,
						'content' => $content
					);

					/**
					* This madness simply checks to see if the current port has already
					* been stuffed into our analysis array and if it has, checks to see
					* if the stored severity is greater than the current severity. If it
					* is greater, then decrement it (make it more severe). In this case
					* set the stored severity to 'note'
					*/
					if (count($analysis) > 0) {
						if (@is_array($analysis[$target])) {
							foreach($analysis[$target] as $key2 => $val2) {
								if ($analysis[$target][$key2]['port'] == $port) {
									if ($analysis[$target][$key2]['severity'] <= $this->NOTE_PRESENT) {
										$exists = true;
									}
								}
							}
						}
					}

					// I dont want duplicates. This 'if' statement takes care of that
					if (!$exists) {
						$analysis[$target][] = array(
							'severity'	=> $this->NOTE_PRESENT,
							'port'		=> $port
						);
					}
				} else {
					
				}
			}
		}

		$this->hosts 		= $hosts;
		$this->host_issues	= $issues;
		$this->host_full_issues	= $descriptions;
		$this->host_analysis	= $analysis;
		$this->summary 		= $results;
	}

	/**
	* Convert NBE timestamp to UNIX timestamp
	*
	* The format of $time should be like so
	*
	*	timestamps|||scan_start|Wed Sep 27 08:23:27 2006|
	*
	* This function will turn the above time field into
	* a UNIX timestamp suitable for conversion to any
	* time format
	*
	* @param string $time NBE timestamp to be converted
	* @return numeric UNIX timestamp for equivalent time
	*/
	private function nbe_scantime_to_timestamp($time) {
		$tmp 	= explode('|',$time);
		$time 	= strtotime($tmp[4]);

		return $time;
	}

	/**
	* Determines the duration of time between two timestamps
	* and returns that duration as a string. Humbly taken from
	* the PHP documentation comments for datetime.
	*
	* http://us2.php.net/datetime
	* cepercival at thatMailThatsHot dot com
	*
	* @param integer $dateTimeBegin Time in seconds of the start time
	* @param integer $dateTimeEnd Time in seconds of the end time
	* @return string Time (duration) in human readable format between
	*	the two timestamps.
	*/
	protected function call_duration($dateTimeBegin,$dateTimeEnd) {
		$dif = $dateTimeEnd - $dateTimeBegin;

		$days 		= 0;
		$hours	 	= 0;
		$minutes	= 0;
		$seconds 	= 0;

		$days 		= floor($dif /  86400);
		$temp_remainder = $dif - ($days *  86400);

		$hours 		= floor($dif / 3600);
		$temp_remainder = $dif - ($hours * 3600);
      
		$minutes 	= floor($temp_remainder / 60);
		$temp_remainder = $temp_remainder - ($minutes * 60);
      
		$seconds 	= $temp_remainder;
        
		// difference/duration returned as Hours:Mins:Secs e.g. 01:29:32
		$return = '';

		if ($days > 0) {
			$return .= $days . ' day(s),';
		}

		if ($hours > 0) {
			$return .= ' ' . $hours . ' hours,';
		}

		if ($minutes > 0) {
			$return .= ' ' . $minutes . ' minutes,';
		}

		if ($seconds > 0) {
			$return .= ' ' . $seconds . ' seconds,';
		}

		return substr(trim($return),0,-1);
	}

	/**
	* Determines the duration of time between two timestamps
	* and returns that duration in seconds.
	*
	* @param integer $dateTimeBegin Time in seconds of the start time
	* @param integer $dateTimeEnd Time in seconds of the end time
	* @return numeric Time (duration) in seconds, between the two timestamps.
	*/
	public function call_duration_seconds($dateTimeBegin,$dateTimeEnd) {
		return $dateTimeEnd - $dateTimeBegin;
	}

	/**
	* Function taken from Nessus mailing list and ported by
	* me to PHP. It looked like a useful function to have ported
	* for the sake of porting
	*
	* Parse Nessus NSR files and spit out data by host,
	* suitable for comparing with diff or further processing
	* by a database or spreadsheeet.
	*
	* Outputs TAB separate columns as follows:
	*
	* ipaddress hostname risk-factor port plugin-id
	*
	* Output is sorted by host IP address.
	*
	* Based on a script originally posted to the Nessus mailing
	* list by Darren Bounds
	* (http://mail.nessus.org/pipermail/nessus/2004-March/msg00243.html)
	*
	* @param string $filename Filename of the NSR file to parse
	*/
	public function parse_nsr($filename) {
		$fh = fopen($filename,'r');

		while (!feof($fh)) {
			$line 		= fgets($fh,4096);
			$tmp 		= explode('|',$line);

			$ip_address	= $tmp[0];
			$service	= $tmp[1];
			$plugin		= $tmp[2];
			$type		= $tmp[3];
			$info		= $tmp[4];

			# Skip useless lines (e.g. ones with just IP and port but no other data)
			if ($plugin == '') {
				continue;
			}

			# Skip unless plugin is a numerical value and type is "report" or "info"
			if (!is_numeric($plugin) && $type != "REPORT" && $type != "INFO") {
				continue;
			}

			if (strpos($info,'Risk factor :') !== false) {
				$solution 	= '';
				$risk		= '';
				$cve		= '';
				$bid		= '';
				$other		= '';
				$description	= '';

				/**
				* 'Risk factor' doesn't always appear in the right field in NSR files.
				* The following crude regexp seems to catch it everytime:
				*/
				preg_match('/Risk factor :\W+(\w+)/;',$info,$matches);
				$risk = $matches[0];

				# Attempt to parse the rest of the "info" field:
				$info_array = explode(';', $info);

				$info_done = 0;

				foreach ($info_array as $key => $item) {
					if (preg_match('/^Solution/',$item)) {
						$item		= str_replace('Solution:','',$item);
						$solution	= $item;
						$info_done	= 1;
					} elseif (preg_match('/^CVE/',$item)) {
						$cve		= $item;
						$info_done 	= 1;
					} elseif (preg_match('/^BID/',$item)) {
						$bid		= $item;
						$info_done 	= 1;
					} elseif (preg_match('/^Other references/',$item)) {
						$other		= $item;
						$info_done	= 1;
					} elseif ( $info_done == 0 ) {
						$description	= trim($description . " " . $item);
					}
				}

				# Try to resolve hostname:
				$hostname	= $ip_address;
				$hname		= gethostbyaddr($ip_address);

				if ($hname != '') {
					$hostname = $hname;
				}
			}

			# Add to an array so we can sort it later:
			# Use tab separated columns (easy to diff, read into a spreadsheet, etc)
			array_push($vulnlist, array("$ip_address\t$hostname\t$risk\t$service\t$plugin\n"));
		}

		fclose($fh);

		# Sort the array by first column then print:
		sort($vulnlist);
		foreach($sorted as $key => $dataline) {
			print $dataline;
		}
	}

	/**
	* According to the nessus mailing list, this can be accomplished
	* by the normal Nessus client by just cat'ing the nbe files together
	* and running the Nessus client. Therein lies the answer to this
	* function. See Nessus source code for ideas
	*
	* http://lists.virus.org/nessus-0504/msg00073.html
	*/
	public function combine_nbe_output() {

	}
}

?>
