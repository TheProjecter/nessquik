<?php

require_once(_ABSPATH.'/lib/functions.php');

/**
* @author Tim Rupp
*/
class MetricsInstaller {
	/**
	* Unique metric ID used to identify metric
	*
	* @var integer
	*/
	private $metric_id;

	/**
	* Name of the metric. Must not contain spaces
	*
	* @var string
	*/
	private $metric_name;
	
	/**
	* Path to the metrics folder
	*
	* @var string
	*/
	private $metric_path;

	/**
	* Contains all known currently installed metrics (from database)
	*
	* @var array
	*/
	private $installed_metrics;

	/**
	* Contains list of all currently available metrics (from metrics folder)
	*
	* @var array
	*/
	private $metric_list;

	/**
	* Specify whether or not there are new metrics to configure
	*
	* @var boolean
	*/
	public $new_metrics;

	/**
	* The type of a particular metric. This can be either 'graphs' or 'reports'
	*
	* @var string
	*/
	private $type;

	/**
	* Performs all necessary metric operations at instantiation
	*
	* The metric upon intiation, will automatically discover all new metrics
	* and remove all old metrics. There should be no need to manually call any
	* of the methods provided.
	*
	* @param string $metric_path Path to the metrics directory
	*/
	public function __construct($metric_path, $type = "graphs") {
		if ($metric_path == '') {
			exit;
		}

		if ($type == '') {
			$this->type = "graphs";
		} else {
			$this->type = $type;
		}

		$this->metric_path		= $metric_path;
		$this->installed_metrics	= array();
		$this->metric_list		= array();
		$this->new_metrics		= false;
	}

	/**
	* Discovers all metrics known and unknown
	*
	* Performs process of discovering all metrics and adding and removing
	* new and dead metrics.
	*/
	public function discover_metrics() {
		$this->get_installed_metrics();
		$this->get_all_known_metrics();

		/**
		* By this point we should have a complete list of all currently installed metrics
		* and a list of all known metrics ( directories in the metrics directory )
		*
		* We now need to run a comparison.
		* - Any metrics in the "all known" list that are not in the "current list" need to be added
		* - Any metrics in the "current list" that are not in the "all known list" need to be removed
		*/
		$this->add_new_metrics();
		$this->remove_dead_metrics();
	}
	
	/**
	* Get all installed metrics
	* 
	* Creates an array of all the currently installed metrics by fetching
	* the list of currently installed metrics from the database
	*/
	private function get_installed_metrics() {
		$db = nessquikDB::getInstance();
		$sql = array (
			'installed' => "SELECT name FROM metrics WHERE type=':1'",
		);

		$stmt = $db->prepare($sql['installed']);
		$stmt->execute($this->type);

		while ($row = $stmt->fetch_array()) {
			$this->installed_metrics[] = $row["name"];
		}

		sort($this->installed_metrics);
	}

	/**
	* Retrieves list of all known metrics
	*
	* By all known metrics I mean all metrics that exist in the metrics
	* directory. Since this is the only place where metrics can be put, there
	* will always potentially be more metrics here than in the database. The
	* database is updated via checking which metrics exist in the metrics
	* directory
	*/
        private function get_all_known_metrics() {
                /**
                * Variables $x and $y are only temp counting variables. As such, don't
                * spend time trying to understand what they are used for.
                */
                $handle = opendir($this->metric_path);
                while ($x = readdir($handle)) {
			$fullpath = $this->metric_path . "/$x";
                        if($x != "." && $x != ".." && $x != "index.php" && substr($x, 0, 1) != '.') {
                                $this->metric_list[] = basename($x, ".php");
                        }
                }
                closedir($handle);
		sort($this->metric_list);
        }

	/**
	* Adds new metrics
	*
	* Adds any new metrics found in the filesystem to the database. The
	* only place that will be search for metrics will be the location
	* stored in the class variable $metric_path
	*/
	private function add_new_metrics() {
		$db = nessquikDB::getInstance();

		/**
		* The metric_list will always be >= the current_list.
		* This is why we loop for each metric_list item
		* as opposed to looping for each installed_metrics item
		*/
		$diff = array_diff($this->metric_list, $this->installed_metrics);

		$sql = array (
			'select' => "SELECT metric_id FROM metrics WHERE name=':1' AND type=':2'",
		);
		
		// Prepare all the SQL we'll be using
		$stmt1 = $db->prepare($sql['select']);

		// For each metric we find, we'll need to do stuff
		foreach ($diff as $key => $metric_class) {
			$metric		= '';

			// Run the SQL to check to see if the metric name already exists
			$stmt1->execute($metric_class, $this->type);

			// Get the result if any
			$result = @$stmt1->result(0);
			
			// If the metric_id already exists, we can assume the metric already exists!
			if (is_integer($result)) {
				continue;
			} else {
				require_once($this->metric_path.'/'.$metric_class.'.php');
				$this->new_metrics = true;

				$metric_class = new ReflectionClass($metric_class);
				$metric = $metric_class->newInstance();

				$metric->_install();
			}
		}
	}

	/**
	* Removes dead metrics
	*
	* A dead metric is basically a metric that has been removed by the user
	* by having its folder deleted from the metrics folder. This method takes
	* care of clearing the database of these dead metrics.
	*/
	private function remove_dead_metrics() {
		$db = nessquikDB::getInstance();

                /**
		* For this walk, we only care about the installed metrics.
                */
		$diff = array_diff($this->installed_metrics, $this->metric_list);

		$sql = array (
			'delete' => "DELETE FROM metrics WHERE `name`=':1' AND type=':2'"
		);

		$stmt = $db->prepare($sql['delete']);

		foreach ($diff as $key => $metric_class) {
			require_once($this->metric_path.'/'.$metric_class.'.php');
			$this->new_metrics = true;

			$metric_class = new ReflectionClass($metric_class);
			$metric = $metric_class->newInstance();

			$metric->_remove();

			$stmt->execute($metric_class, $this->type);
                }
	}
}

?>
