<?php

/**
* Netmask class
*
* Parse, manipulate and lookup IP network blocks
* Net::Netmask ported from Perl to PHP by Dennis 
* S. Davidoff <null@1system.ru>
*
* @copyright 1998, 2001 David Muir Sharnoff <muir@idiom.com>
* @author David Muir Sharnoff <muir@idiom.com>
* @author Dennis S. Davidoff <null@1system.ru>
* @author Tim Rupp <tarupp@fnal.gov>
* @todo Methods: hostmask, storeNetblock, deleteNetblock
*	Functions: findNetblock, findOuterNetblock, findAllNetblock, 
*		cidrs2contiglists, by_net_netmask_block
*/
class Netmask {
	private $error;
	private $IBASE;
	private $BITS;
	private $quadmask2bits;
	private $imask2bits;
	private $debug;

	static $instance;

	public function __construct() {
		$this->quadmask2bits 	= array();
		$this->imask2bits	= array();
		$this->debug		= false;
	}

	static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new Netmask;
		}
		return self::$instance;
	}
    
	function init($net = '', $mask = '') {
		unset($this->error);
		$bits		= 0;
		$size2bits	= array();
        
		for ($i = 0; $i <= 32; $i++) {
			$imask = $this->imask($i);
			$this->imask2bits["$imask"] = $i;
			$this->quadmask2bits[$this->int2quad($imask)] = $i;
			$hosts = pow(2, (32-$i));
			$size2bits[$hosts] = $i;
		}

		// Matches 127.0.0.1/24
		if (preg_match("/^(\d+\.\d+\.\d+\.\d+)\/(\d+)$/", $net, $matched)) {
			$base = $matched[1];
			$bits = $matched[2];

		// Matches 127.0.0.1/255.255.255.254
		} elseif (preg_match("/^(\d+\.\d+\.\d+\.\d+)[:\/](\d+\.\d+\.\d+\.\d+)$/", $net, $matched)) {
			$base = $matched[1];
			$quadmask = $matched[2];

			if (array_key_exists($quadmask, $this->quadmask2bits)) {
				$bits = $this->quadmask2bits[$quadmask];
			} else {
				$this->error = "illegal netmask: $quadmask";
			}

		// Matches 127.0.0.1/255.255.255.254
		} elseif (preg_match("/^\d+\.\d+\.\d+\.\d+$/", $net) && preg_match("/\d+\.\d+\.\d+\.\d+$/", $mask)) {
			$base = $net;
			if (array_key_exists($mask, $this->quadmask2bits)) {
				$bits = $this->quadmask2bits[$mask];
			} else {
				$this->error = "illegal netmask: $mask";
			}

		// Matches 127.0.0.1/0xAF or 127.0.0.1/24
		} elseif (preg_match("/^\d+\.\d+\.\d+\.\d+$/", $net) && preg_match("/0x([a-z0-9]+)/", $mask, $matched)) {
			$base = $net;
			$imask = hexdec($matched[1]);
			if (array_key_exists("$imask", $this->imask2bits)) {
				$bits = $this->imask2bits["$imask"];
			} else {
				$this->error = "illegal netmask: $imask";
			}

		// Matches 127.0.0.1
		} elseif (preg_match("/^\d+\.\d+\.\d+\.\d+$/", $net) && empty($mask)) {
			$base = $net;
			$bits = 32;

		// Matches 127.0.0
		} elseif (preg_match("/^\d+\.\d+\.\d+$/", $net) && empty($mask)) {
			$base = "$net.0";
			$bits = 24;

		// Matches 127.0
		} elseif (preg_match("/^\d+\.\d+$/", $net) && empty($mask)) {
			$base = "$net.0.0";
			$bits = 16;

		// Matches 127
		} elseif (preg_match("/^\d+$/", $net) && empty($mask)) {
			$base = "$net.0.0.0";
			$bits = 8;

		// Matches 127.0.0/24
		} elseif (preg_match("/^(\d+\.\d+\.\d+)\/(\d+)$/", $net, $matched)) {
			$base = $matched[1] . ".0";
			$bits = $matched[2];

		// Matches 127.0/24
		} elseif (preg_match("/^(\d+\.\d+)\/(\d+)$/", $net, $matched)) {
			$base = $matched[1] . ".0.0";
			$bits = $matched[2];

		// Matches 'default'
		} elseif ($net == 'default') {
			$base = '0.0.0.0';
			$bits = 0;

		// Matches 127.0.0.1 - 127.0.0.2
		} elseif (preg_match("/^(\d+\.\d+\.\d+\.\d+)\s*\-\s*(\d+\.\d+\.\d+\.\d+)$/", $net, $matched)) {
			$ibase = $this->quad2int($matched[1]);
			$end = $this->quad2int($matched[2]);
			if ($ibase == false || $end == false) {
				$this->error = "illegal dotted quad: $net";
			}

			$diff = ($end == false ? 0 : $end) - ($ibase == false ? 0 : $ibase) + 1;

			if (!array_key_exists("$diff", $size2bits) && empty($this->error)) {
				$this->error = "could not find exact fit for $net";
			} else {
				$bits = $size2bits{"$diff"};
			}
		} else {
			$this->error = "could not parse $net";
			if (!empty($mask)) {
				$this->error .= " $mask";
			}
		}
        
		if ($this->debug == true && !empty($this->error)) {
			die($this->error);
		}
        
		if (!isset($ibase)) {
			if (($ibase = $this->quad2int( isset($base) ? $base : 0)) == false && empty($this->error)) {
				$this->error = "could not parse $net";
				if (!empty($mask)) {
					$this->error .= " $mask";
				}
			} else {
				$ibase += 0;
				$ibase = sprintf('%u', ($ibase &= $this->imask($bits)));
			}
		}
        
		$this->IBASE = $ibase + 0;
		$this->BITS  = $bits;

		if ($this->debug == true && !empty($this->error)) {
			die($this->error);
		}

		if (empty($this->error)) {
			return true;
		} else {
			return false;
		}
	}
    
	function imask($i) {
		return pow(2, 32) - pow(2, (32 - $i));
	}

	/**
	* Return the total number of IP addresses in the subnet
	* including the network and broadcast address
	*
	* Returns the total number of IP addresses in the subnet
	* including the 0 and 255 addresses.
	*
	* @return integer The total number of IP addresses in the subnet
	*/
	public function size() {
		return pow(2, (32 - $this->BITS));
	}

	/**
	* Return a count of the number of hosts in each subnet
	*
	* This function is slightly different from 'size' because
	* size will include the net and broadcast addresses in the
	* count. This function does not include those.
	*
	* @return integer The number of hosts in each subnet
	*/
	public function hosts_per_subnet() {
		$size = $this->size();

		return $size - 2;
	}

	/**
	* Returns the netmask as a string
	*
	* The full netmask is returned even if specified in CIDR
	* notation when you init'd the class.
	*
	* example:
	*	255.255.255.0
	*
	* @return string The netmask of the current object
	*/
	public function mask() {
		return $this->int2quad ( $this->imask($this->BITS));
	}

	/**
	* Returns a description of the network block
	*
	* Usually this will just return the string that you used
	* to initialize the class.
	*
	* example:
	*	212.120.109.0/20
	*
	* @return string Description of the network block
	*/
	public function desc() {
		return $this->int2quad($this->IBASE).'/'.$this->BITS;
	}

	function int2quad($n) {
		return implode('.', unpack('C4', pack("N", $n)));
	}

	function nth($index = 1, $bitstep = 0) {
		$size	= $this->size();
		$ibase 	= $this->IBASE;

		if($bitstep == 0) {
			$bitstep = 32;
		}

		$increment = pow(2, (32 - $bitstep));
		$index *= $increment;

		if($index < 0) {
			$index += $size;
		}

		if($index < 0) {
			return false;
		}

		if($index >= $size) {
			return false;
		}

		return $this->int2quad($ibase + $index);
	}

	/**
	* Generate a list of all IP addresses in a subnet
	*
	* This function will walk an entire subnet and generate
	* a list of all the possible IP addresses that can be
	* found in that subnet. This is a _very_ memory intensive
	* function and should be used with extreme caution because
	* I've observed PHP "out of memory" errors (even with 200
	* meg allocated) when enumerating a large subnet.
	*
	* @param integer $bitstep How you want to step from one
	*	IP to the next. If zero, then all IPs in sequential
	*	order will be calculated
	* @return array A list of all the possible IP addresses that
	*	can be found in the subnet
	*/
	function enumerate($bitstep = 0) {
		$size 	= $this->size();
		$ibase	= $this->IBASE;

		if ($bitstep == 0) {
			$bitstep = 32;
		}

		$increment = pow(2, (32-$bitstep));

		for ($i = 0; $i < $size; $i += $increment) {
			$array[] = $this->int2quad($ibase + $i);
		}

		return $array;
	}
    
	function inaddr() {
		$ibase = $this->IBASE;
		$blocks = intval($this->size()/256);
		if ($blocks == 0) {
			return array(
				implode('.', unpack('x/C3', pack("V", $ibase))) . ".in-addr.arpa",
				fmod($ibase, 256), fmod($ibase, 256) + $this->size() - 1
			);
		}
        
		for ($i = 0; $i < $blocks; $i++) {
			$array[] = array(implode('.', unpack('x/C3', pack("V", $ibase + $i*256))) . ".in-addr.arpa", 0, 255);
		}

		return $array;
	}
    
	function quad2int($ip) {
		$bytes = explode('.', $ip);
		if (count($bytes) == 4) {
			for ($i = 0; $i < count($bytes); $i++) {
				if ($bytes[$i] > 255) {
					return false;
				} else {
					eval("\$oct$i = $bytes[$i];");
				}
			}
		} else {
			$this->error = "quad2int(): illegal ipaddr: $ip";

			if ($this->debug == true && !empty($this->error)) {
				die($this->error);
			}

			return false;
		}

		$assoc_array = unpack("Nipaddr", pack('C4', $oct0, $oct1, $oct2, $oct3));
		return sprintf('%u', $assoc_array['ipaddr']);
	}
    
	function imaxblock($ibase, $tbit) {
		while($tbit > 0) {
			$im = $this->imask($tbit-1);

			if ((sprintf('%u', $ibase & $im)) != $ibase) {
				break;
			}

			$tbit--;
		}

		return $tbit;
	}

	function sort_by_ip_address($array) {
		if (is_array($array)) {
			natsort($array);
			return $array;
		} else {
			return false;
		}
	}
    
	function range2cidrlist($startip, $endip) {
		if (($start = $this->quad2int($startip)) == false) {
			$this->error = "range2cidrlist(): illegal \$startip: $startip";

			if ($this->debug == true && !empty($this->error)) {
				die($this->error);
			}

			return false;
		}

		if (($end = $this->quad2int($endip)) == false) {
			$this->error = "range2cidrlist(): illegal \$startip: $startip";

			if ($this->debug == true && !empty($this->error)) {
				die($this->error);
			}

			return false;
		}

		$start += 0;
		$end += 0;

		if ($start > $end) {
			$tmp	= $end;
			$end 	= $start;
			$start 	= $tmp;
			unset($tmp);
		}

		while($end >= $start) {
			$maxsize = $this->imaxblock($start, 32);
			$maxdiff = 32 - intval(log($end - $start + 1)/log(2));

			if ($maxsize < $maxdiff) {
				$maxsize = $maxdiff;
			}

			$array[] = $this->int2quad($start)."/$maxsize";
			$start += pow(2, (32 - $maxsize));
		}

		return $array;
	}
    
	function match($ipaddr) {
		if (($i = $this->quad2int($ipaddr)) == false) {
			$this->error = "match(): illegal ipaddr: $ipaddr";

			if ($this->debug == true && !empty($this->error)) {
				die($this->error);
			}

			return false;
		}

		$i += 0;
		
		$imask = $this->imask($this->BITS);

		if ((sprintf('%u',($i & $imask))) == $this->IBASE) {
			return (($i & ~$imask) || "0 ");
		} else {
			return false;
		}
	}

	/**
	*
	*/
	function split_cidrs($range) {
		// Blow up range for CIDR conversion
		$tmp	= explode('-', $range);
		$start	= trim($tmp[0]);
		$end	= trim($tmp[1]);

		/**
		* This small block handles cases when people might type
		* in "short" ranges
		* This would be met if the specified range looked like
		* 
		*	111.111.111.0-212
		*
		* Notice the "-212" part
		*
		* After this small block of code, the following values
		* will exist
		*
		*	$start 	= 111.111.111.0
		*	$end	= 111.111.111.212
		*/
		if (is_numeric($end)) {
			$tmp 	= explode('.', $start);
			$tmp[3]	= $end;
			$end 	= implode('.', $tmp);
		}

		$cidr_list = $this->range2cidrlist($start, $end);

		return $cidr_list;
	}

	/**
	*
	*/
	function match_cidr($cidr1, $cidr2) {
		$cidr1_range = $this->expand_cidr($cidr1);
		$cidr2_range = $this->expand_cidr($cidr2);

		$in_start	= sprintf("%u", ip2long($cidr1_range[0]));
		$in_end		= sprintf("%u", ip2long($cidr1_range[1]));

		$test_start	= sprintf("%u", ip2long($cidr2_range[0]));
		$test_end	= sprintf("%u", ip2long($cidr2_range[1]));

		if (($test_start >= $in_start) && ($test_end <= $in_end)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Determine a range of addresses for a CIDR block.
	*
	* Function borrowed from
	* http://binarywerks.dk/php_src/expand_CIDR.phps
	*
	* @author mkr@binarywerks.dk
	* @date 2-Sep-2003
	* @param string $cidr CIDR block to expand into IP address ranges
	*/
	public function expand_cidr($cidr) {
		// Separate CIDR structure into network-IP and netmask
		$ip_arr = explode("/",$cidr);

		// Calculate number of hosts in the subnet
		$mask_bits  = $ip_arr[1];
		if($mask_bits > 31 || $mask_bits < 1) {
			return array($ip_arr[0],$ip_arr[0]);
		}

		$host_bits  = 32-$mask_bits;
		$num_hosts  = pow(2,$host_bits)-1;

		// Netmask in decimal for use later: (hack around PHP always using signed ints)
		$netmask    = ip2long("255.255.255.255") - $num_hosts;

		/**
		* Calculate start and end
		* Store IP-addresses internally as longs, to ease compare of two
		* addresses in a sorted structure.
		*/
		$ip_start  = ip2long($ip_arr[0]);

		//if($ip_start != ( $ip_start & $netmask )) {
		//	echo("WARNING: Address $cidr not on network boundary\n");
		//}

		$ip_end    = $ip_start + $num_hosts;

		for($i = 0; $i <= $num_hosts; $i++) {
			if($i == 0) {
       	        		$ip_range[] = long2ip($ip_start+$i);
			} else if ($i == $num_hosts) {
       	        		$ip_range[] = long2ip($ip_start+$i);
			}
		}

		return $ip_range;
	}

	/**
	* Determines if a network in the form of 192.168.17.1/16 or
	* 127.0.0.1/255.255.255.255 or 10.0.0.1 matches a given IP.
	*
	* @access public
	* @param string $network Network to check for IP match in
	* @param string $ip IP address to try to match
	* @return bool True is matched, false if not
	*/
	public function net_match($network, $ip) {
		$ip_arr 	= explode('/', $network);
		$network_long 	= ip2long($ip_arr[0]);

		$x 		= ip2long($ip_arr[1]);
		$mask 		= long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
		$ip_long	= ip2long($ip);

		if (($ip_long & $mask) == ($network_long & $mask)) {
			return true;
		} else {
			return false;
		}
	}
}

?>
