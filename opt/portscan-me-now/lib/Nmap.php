<?php

/**
* A port of the Nmap::Parser Perl module
* for use in nessquik.
*
* I more or less used the Perl module as a basis
* for this module. I'll admit that I dont understand
* 99% of what's happening in that Perl module, so
* some of these functions may only be identical
* in name. This goes for several of the classes.
*
* @author Tim Rupp
*/
class Nmap {
	private $xml;
	private $SESSION;
	private $HOSTS;

	/**
	*
	*/
	public function __construct() {
		$this->xml 	= '';
		$this->HOSTS 	= array();
		$this->SESSION	= array();
	}

	/**
	*
	*/
	public function parse($xml) {
		if (file_exists($xml)) {
			$this->xml = new SimpleXMLElement($xml, NULL, true);
		} else {
			$this->xml = new SimpleXMLElement($xml);
		}

		$_nmps = new NmapSession();
		$_nmps->parse_attributes($this->xml->attributes());
		$_nmps->parse_runstats($this->xml->runstats);
		$_nmps->parse_scaninfo($this->xml->scaninfo);
		$this->SESSION = $_nmps;

		foreach ($this->xml->xpath('host') as $host) {
			$address = $host->address['addr'];
			$this->HOSTS["$address"] = new NmapHost($host);
		}
	}

	/**
	*
	*/
	public function parsefile($xml_file) {
		$this->parse($xml_file);

		return true;
	}

	/**
	*
	*/
	public function purge() {
		$this->xml = null;
	}

	/**
	*
	*/
	public function get_session() {
		return $this->SESSION;
	}

	/**
	*
	*/
	public function get_host($ip) {
		if ($ip == '') {
			return false;
		} else {
			return $this->HOSTS[$ip];
		}
	}

	/**
	*
	*/
	public function all_hosts($status = '') {
		$hosts = array();

#	return (values %{$self->{HOSTS}}) if($status eq '');
	
#	my @hosts = grep {$_->{status} eq $status} (values %{$self->{HOSTS}});
#	return @hosts;
	}
}

/**
*
* @author Tim Rupp
* @author Anthony G Persaud <apersaud@gmail.com>
* 	For original Perl source and comments
*/
class NmapSession extends Nmap {
	private $attributes;
	private $runstats;
	private $scaninfo;

	/**
	*
	*/
	public function __construct() {
		$this->attributes = array();
		$this->runstats = array();
		$this->scaninfo = array();
	}

	/**
	*
	*/
	public function parse_attributes($attributes)  {
		$this->attributes = array(
			'scanner'		=> (string)$attributes['scanner'],
			'args'			=> (string)$attributes['args'],
			'start'			=> (string)$attributes['start'],
			'startstr'		=> (string)$attributes['startstr'],
			'version'		=> (string)$attributes['version'],
			'xmloutputversion'	=> (string)$attributes['xmloutputversion'],
		);
	}

	/**
	*
	*/
	public function parse_runstats($runstats) {
		$this->runstats = array(
			'finish_time'		=> (string)$runstats->finished['time'],
			'finish_timestr'	=> (string)$runstats->finished['timestr'],
			'hosts_up'		=> (string)$runstats->hosts['up'],
			'hosts_down'		=> (string)$runstats->hosts['down'],
			'hosts_total'		=> (string)$runstats->hosts['total'],
			'comment'		=> (string)$runstats->comment
		);
	}

	/**
	*
	*/
	public function parse_scaninfo($scaninfo) {
		$services = $this->parse_services((string)$scaninfo['services']);
		$this->scaninfo = array(
			'type'		=> (string)$scaninfo['type'],
			'protocol'	=> (string)$scaninfo['protocol'],
			'numservices'	=> (string)$scaninfo['numservices'],
			'services'	=> $services
		);
	}

	/**
	*
	*/
	private function parse_services($services) {
		$ports	= array();
		$tmp 	= explode(',', $services);

		foreach ($tmp as $key => $port) {
			$ports[] = $port;
		}

		return $ports;
	}

	/**
	*
	*/
	public function attributes() {
		return $this->attributes;
	}

	/**
	*
	*/
	public function scanner() {
		return $this->attributes['scanner'];
	}

	/**
	* Returns a string which contains the nmap
	* executed command line used to run the scan.
	*/
	public function scan_args() {
		return $this->attributes['args'];
	}

	/**
	* Returns the numeric form of the time the nmap scan started.
	*/
	public function start_time() {
		return $this->attributes['start'];
	}

	/**
	* Returns the human readable format of the start time.
	*/
	public function start_str() {
		return $this->attributes['startstr'];
	}

	/**
	* Returns the version of nmap used for the scan.
	*/
	public function nmap_version() {
		return $this->attributes['version'];
	}

	/**
	* Returns the version of nmap xml file.
	*/
	public function xml_version() {
		return $this->attributes['xmloutputversion'];
	}

	/**
	* Returns the numeric time that the nmap scan finished.
	*/
	public function finish_time() {
		return $this->runstats['finish_time'];
	}

	/**
	* Returns the human readable format of the finish time.
	*/
	public function time_str() {
		return $this->runstats['finish_timestr'];
	}

	/**
	* If numservices is called without argument, it returns 
	* the total number of services that were scanned for all
	* types. If $type is given, it returns the number of 
	* services for that given scan type. See scan_types()
	* for more info.
	*/
	public function num_services($type = '') {
		if ($type == '') {
			return $this->scaninfo['numservices'];
		} else {
			// Not implemented yet
			return false;
		}
	}

	/**
	* Returns the protocol type of the given scan type 
	* (provided by $type). See scan_types() for more info.
	*/
	public function scan_type_proto($type) {

	}

	/**
	* Returns the list of scan types that were performed.
	* It can be any of the following: 
	* (syn|ack|bounce|connect|null|xmas|window|maimon|fin|udp|ipproto).
	*/
	public function scan_types() {

	}
}

/**
* @author Tim Rupp
* @author Anthony G Persaud <apersaud@gmail.com>
* 	For original Perl source and comments
*/
class NmapHost {
	private $status;
	private $addr;
	private $addrtype;
	private $hostnames;
	private $extraports_state;
	private $extraports_count;
	private $tcp_ports;
	private $udp_ports;
	private $SERVICES;
	private $OS;

	public function __construct($xml) {
		$this->status		= '';
		$this->addr		= '';
		$this->addrtype		= '';
		$this->extraports_state	= '';
		$this->extraports_count	= '';
		$this->hostnames	= array();
		$this->tcp_ports	= array();
		$this->udp_ports	= array();
		$this->OS		= array();

		$this->status 		= (string)$xml->status['state'];
		$this->addr		= (string)$xml->address['addr'];
		$this->addrtype		= (string)$xml->address['addrtype'];

		$hostnames		= (array)$xml->hostnames;

		foreach($hostnames as $key => $val) {
			$this->hostnames[] = (string)$val->hostname['name'];
		}

		$ports			= $xml->ports;

		@$this->extraports_state	= (string)$ports->extraports['state'];
		@$this->extraports_count	= (string)$ports->extraports['count'];

		foreach($xml->xpath("ports") as $val) {
			$protocol 	= (string)$val['protocol'];
			$portid		= (string)$val['portid'];

			if ($protocol == "tcp") {
				$this->tcp_ports[$portid] = (string)$val->state['state'];
			} else {
				$this->udp_ports[$portid] = (string)$val->state['state'];
			}
		}

		ksort($this->tcp_ports);
		ksort($this->udp_ports);

		if (count($xml->xpath("os")) > 0) {
			$this->OS = new NmapHostOS($xml->os);
		}
	}

	/**
	* Returns the state of the host. It is usually one of these
	* (up|down|unknown|skipped).
	*/
	public function status() {
		return $this->status;
	}

	/**
	* Returns the main IP address of the host. This is usually 
	* the IPv4 address. If there is no IPv4 address, the IPv6 
	* is returned (hopefully there is one).
	*/
	public function addr() {
		return $this->addr;
	}

	/**
	* Returns the address type of the address given by addr().
	*/
	public function addrtype() {
		return $this->addrtype;
	}

	/**
	* Returns a list of all hostnames found for the given host.
	*/
	public function all_hostnames() {
		return $this->hostnames;
	}

	/**
	* Returns the number of extraports found.
	*/
	public function extraports_count() {
		return $this->extraports_count;
	}

	/**
	* Returns the state of all the extraports found.
	*/
	public function extraports_state() {
		return $this->extraports_state;
	}

	/**
	* As a basic call, hostname() returns the first 
	* hostname obtained for the given host. If there 
	* exists more than one hostname, you can provide 
	* a number, which is used as the location in the 
	* array. The index starts at 0;
	*
	* In the case that there are only 2 hostnames
	* hostname() eq hostname(0);
	* hostname(1); #second hostname found
	* hostname(400) eq hostname(1) #nothing at 400; return the name at the last index
	*
	* @param integer $index
	*/
	public function hostname($index = 0) {
		return $this->hostnames[0];
	}

	/**
	* Explicitly return the IPv4 address.
	*/
	public function ipv4_addr() {

	}

	/**
	* Explicitly return the IPv6 address.
	*/
	public function ipv6_addr() {
		return false;
	}

	/**
	* Explicitly return the MAC address.
	*/
	public function mac_addr() {

	}

	/**
	* Return the vendor information of the MAC.
	*/
	public function mac_vendor() {

	}

	/**
	* Returns an NmapHostOS object that can 
	* be used to obtain all the Operating System signature 
	* (fingerprint) information. See NmapHostOS for more details.
     	*
	*	$os = $host->os_sig;
	*	$os->name;
	*	$os->osfamily;
	*/
	public function os_sig() {

	}

	/**
	* Returns the class information of the tcp sequence.
	*/
	public function tcpsequence_class() {

	}

	/**
	* Returns the index information of the tcp sequence.
	*/
	public function tcpsequence_index() {

	}

	/**
	* Returns the values information of the tcp sequence.
	*/
	public function tcpsequence_values() {

	}

	/**
	* Returns the class information of the ipid sequence.
	*/
	public function ipidsequence_class() {

	}

	/**
	* Returns the values information of the ipid sequence.
	*/
	public function ipidsequence_values() {

	}

	/**
	* Returns the class information of the tcpts sequence.
	*/
	public function tcptssequence_class() {

	}

	/**
	* Returns the values information of the tcpts sequence.
	*/
	public function tcptssequence_values() {

	}

	/**
	* Returns the human readable format of the timestamp
	* of when the host had last rebooted.
	*/
	public function uptime_lastboot() {

	}

	/**
	* Returns the number of seconds that have passed since
	* the host's last boot from when the scan was performed.
	*/
	public function uptime_seconds() {

	}

	/**
	* Returns the sorted list of TCP ports that were scanned
	* on this host. Optionally a string argument can be given
	* to these functions to filter the list.
	*
	* Note that if a port state is set to 'open|filtered' 
	* (or any combination), it will be counted as an 'open'
	* port as well as a 'filtered' one.
	*
	* example:
	* 
	* 	returns all only 'open' ports (even 'open|filtered')
	* 
	* 	$host->tcp_ports('open') 
	*/
	public function tcp_ports($filter = null) {
		if (is_null($filter)) {
			return array_keys($this->tcp_ports);
		} else {
			return array_keys($this->tcp_ports,$filter);
		}
	}

	/**
	* Returns the sorted list of UDP ports that were scanned
	* on this host. Optionally a string argument can be given
	* to these functions to filter the list.
	*
	* Note that if a port state is set to 'open|filtered' 
	* (or any combination), it will be counted as an 'open'
	* port as well as a 'filtered' one.
	*
	* example:
	* 
	*	matches exactly ports with 'open|filtered'
	* 
	* 	$host->udp_ports('open|filtered');
	*/
	public function udp_ports($filter = '') {
		if (is_null($filter)) {
			return array_keys($this->udp_ports);
		} else {
			return array_keys($this->udp_ports, $filter);
		}
	}

	/**
	* Returns the total of TCP ports scanned.
	*/
	public function tcp_port_count() {
		return count($this->tcp_ports);
	}

	/**
	* Returns the total of UDP ports scanned.
	*/
	public function udp_port_count() {
		return count($this->udp_ports);
	}

	/**
	* Returns the state of the given port,
	* provided by the port number in $portid.
	*/
	public function tcp_port_state($portid) {
		if(!is_numeric($portid)) {
			return false;
		} else {
			return $this->tcp_ports[$portid];
		}
	}

	/**
	* Returns the state of the given port,
	* provided by the port number in $portid.
	*/
	public function udp_port_state($portid) {
		if(!is_numeric($portid)) {
			return false;
		} else {
			return $this->udp_ports[$portid];
		}
	}

	/**
	* Returns the list of open TCP ports.
	* Note that if a port state is for example,
	* 'open|filtered', it will appear on this list as well.
	*/
	public function tcp_open_ports() {
		$ports = array();

		$open		= array_keys($this->tcp_ports, "open");
		$open_filtered	= array_keys($this->tcp_ports, "open|filtered");

		$ports = array_merge($open, $open_filtered);

		return array_unique($ports);
	}

	/**
	* Returns the list of open UDP ports.
	* Note that if a port state is for example,
	* 'open|filtered', it will appear on this list as well.
	*/
	public function udp_open_ports() {
		$ports = array();

		$open		= array_keys($this->udp_ports, "open");
		$open_filtered	= array_keys($this->udp_ports, "open|filtered");

		$ports = array_merge($open, $open_filtered);

		return array_unique($ports);

	}

	/**
	* Returns the list of filtered TCP ports.
	* Note that if a port state is for example,
	* 'open|filtered', it will appear on this list as well.
	*/
	public function tcp_filtered_ports() {
		$ports = array();

		$filtered	= array_keys($this->tcp_ports, "filtered");
		$open_filtered	= array_keys($this->tcp_ports, "open|filtered");

		$ports = array_merge($filtered, $open_filtered);

		return array_unique($ports);
	}

	/**
	* Returns the list of filtered UDP ports.
	* Note that if a port state is for example,
	* 'open|filtered', it will appear on this list as well.
	*/
	public function udp_filtered_ports() {
		$ports = array();

		$filtered	= array_keys($this->udp_ports, "filtered");
		$open_filtered	= array_keys($this->udp_ports, "open|filtered");

		$ports = array_merge($filtered, $open_filtered);

		return array_unique($ports);
	}

	/**
	* Returns the list of closed TCP ports.
	* Note that if a port state is for example,
	* 'closed|filtered', it will appear on this list as well.
	*/
	public function tcp_closed_ports() {
		$ports = array();

		$closed			= array_keys($this->tcp_ports, "closed");
		$closed_filtered	= array_keys($this->tcp_ports, "closed|filtered");

		$ports = array_merge($closed, $closed_filtered);

		return array_unique($ports);
	}
	
	/**
	* Returns the list of closed UDP ports.
	* Note that if a port state is for example,
	* 'closed|filtered', it will appear on this list as well.
	*/
	public function udp_closed_ports() {
		$ports = array();

		$closed			= array_keys($this->udp_ports, "closed");
		$closed_filtered	= array_keys($this->udp_ports, "closed|filtered");

		$ports = array_merge($closed, $closed_filtered);

		return array_unique($ports);
	}

	/**
	* Returns the NmapHostService object of a
	* given service running on port, provided
	* by $portid. See NmapHostService for more info.
	*/
	public function tcp_service($portid) {

	}

	/**
	* Returns the NmapHostService object of a
	* given service running on port, provided
	* by $portid. See NmapHostService for more info.
	*/
	public function udp_service($portid) {

	}
}

/**
* @author Tim Rupp
* @author Anthony G Persaud <apersaud@gmail.com>
* 	For original Perl source and comments
*/
class NmapHostService extends NmapHost {
	public function __construct() {

	}

	/**
	* Returns the confidence level in service detection.
	*/
	public function confidence() {

	}

	/**
	* Returns any additional information nmap knows about the service.
	*/
	public function extrainfo() {

	}

	/**
	* Returns the detection method.
	*/
	public function method() {

	}

	/**
	* Returns the service name.
	*/
	public function name() {

	}

	/**
	* Returns the process owner of the given service. (If available)
	*/
	public function owner() {

	}

	/**
	* Returns the port number where the service is running on.
	*/
	public function port() {

	}

	/**
	* Returns the product information of the service.
	*/
	public function product() {

	}

	/**
	* Returns the protocol type of the service.
	*/
	public function proto() {

	}

	/**
	* Returns the RPC number.
	*/
	public function rpcnum() {

	}

	/**
	* Returns the tunnel value. (If available)
	*/
	public function tunnel() {

	}

	/**
	* Returns the version of the given product of the running service.
	*/
	public function version() {

	}

}

/**
* @author Tim Rupp
* @author Anthony G Persaud <apersaud@gmail.com>
* 	For original Perl source and comments
*/
class NmapHostOS extends NmapHost {
//	private 



/**
    [os] => SimpleXMLElement Object
        (
            [portused] => Array
                (
                    [0] => SimpleXMLElement Object
                        (
                            [@attributes] => Array
                                (
                                    [state] => open
                                    [proto] => tcp
                                    [portid] => 21
                                )

                        )

                    [1] => SimpleXMLElement Object
                        (
                            [@attributes] => Array
                                (
                                    [state] => closed
                                    [proto] => tcp
                                    [portid] => 1
                                )

                        )

                )

            [osclass] => Array
                (
                    [0] => SimpleXMLElement Object
                        (
                            [@attributes] => Array
                                (
                                    [type] => general purpose
                                    [vendor] => Microsoft
                                    [osfamily] => Windows
                                    [osgen] => 95/98/ME
                                    [accuracy] => 90
                                )

                        )

                    [1] => SimpleXMLElement Object
                        (
                            [@attributes] => Array
                                (
                                    [type] => general purpose


**/












	/**
	* Returns the list of all the guessed OS names for the given host.
	*/
	public function all_names() {

	}

	/**
	* A basic call to class_accuracy() returns
	* the osclass accuracy of the first record.
	* If $index is given, it returns the osclass
	* accuracy for the given record. The index starts at 0.
	*/
	public function class_accuracy($index = 0) {

	}

	/**
	* Returns the total number of OS class records obtained from the nmap scan.
	*/
	public function class_count() {

	}

	/**
	* A basic call to name() returns the OS name of
	* the first record which is the name with the
	* highest accuracy. If $index is given, it returns
	* the name for the given record. The index starts at 0.
	*/
	public function name($index = 0) {

	}

	/**
	* A basic call to name_accuracy() returns the OS
	* name accuracy of the first record. If $index is
	* given, it returns the name for the given record.
	* The index starts at 0.
	*/
	public function name_accuracy($index = 0) {

	}

	/**
	* Returns the total number of OS names (records) for the given host.
	*/
	public function name_count() {

	}

	/**
	* A basic call to osfamily() returns the OS family
	* information of the first record. If $index is given,
	* it returns the OS family information for the given
	* record. The index starts at 0.
	*/
	public function osfamily($index = 0) {

	}

	/**
	* A basic call to osgen() returns the OS generation
	* information of the first record. If $index is given,
	* it returns the OS generation information for the
	* given record. The index starts at 0.
	*/
	public function osgen($index = 0) {

	}

	/**
	* Returns the closed port number used to help identify
	* the OS signatures. This might not be available for all hosts.
	*/
	public function portused_closed() {

	}

	/**
	* Returns the open port number used to help identify the
	* OS signatures. This might not be available for all hosts.
	*/
	public function portused_open() {

	}

	/**
	* A basic call to type() returns the OS type information
	* of the first record. If $index is given, it returns the
	* OS type information for the given record. The index 
	* starts at 0.
	*/
	public function type($index = 0) {

	}

	/**
	* A basic call to vendor() returns the OS vendor information
	* of the first record. If $index is given, it returns the OS
	* vendor information for the given record. The index starts at 0.
	*/
	public function vendor($index = 0) {

	}
}

/**
* @author Tim Rupp
*/
class NmapCompare {
	private $old_host;
	private $new_host;

	public function __construct(NmapHost $old_host, NmapHost $new_host) {
		$this->old_host = $old_host;
		$this->new_host = $new_host;
	}
}

?>
