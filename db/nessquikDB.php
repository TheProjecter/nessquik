<?php

/**
* The general database abstraction layer
* All database layers can extend this class
* to gain access to the general database
* methods provided in the class. Most
* important of those methods is the factory
* method
*
* @author Tim Rupp
*/
class genDB {
	/**
	* Database factory method to create a database
	* object for any supported database
	*
	* @param string $username Username to connect to the database with
	* @param string $password Password for the user who you are connecting with
	* @param string $database The name of the database that you are connecting to
	* @param string $server The database server to connect to
	* @param integer $port The port that the database server is running on
	* @param string $database_type The type of database that is being connected to.
	*	See the switch statement contained in this method for the supported
	*	databases.
	* @return object Database object for the database layer requested
	*/
	public function db_factory($username, $password, $database, $server, $port, $database_type = '') {
		if ($database_type == '') {
			$database_type	= self::determine_db_type();
		} else {
			$database_type	= $database_type;
		}

		if (!$database_type) {
			die("MySQL database functionality does not exist!");
		}

		switch($database_type) {
			case "mysql":
				if (!file_exists(_ABSPATH.'/db/mysql.php')) {
					die ("MySQL database authentication layer does not exist");
				}

				require_once(_ABSPATH.'/db/mysql.php');
				return new MySQL_DB($username, $password, $database, $server, $port);
				break;
			case "mysqli":
				if (!file_exists(_ABSPATH.'/db/mysqli.php')) {
					die ("MySQLi database authentication layer does not exist");
				}
	
				require_once(_ABSPATH.'/db/mysqli.php');
				return new MySQLi_DB($username, $password, $database, $server, $port);
				break;
			case "pgsql":
				if (!file_exists(_ABSPATH.'/db/postgresql.php')) {
					die ("PostgreSQL database authentication layer does not exist");
				}

				require_once(_ABSPATH.'/db/postgresql.php');
				return new PgSQL_DB($username, $password, $database, $server, $port);
				break;
			default:
				die("Database type could not be determined");
				break;
		}
	}

	/**
	* Determine a default database type if database is not specified
	*
	* nessquik uses MySQL for it's database. There are two MySQL
	* layers in PHP. As of PHP 5, mysqli is the new default layer.
	* If no database type is specified, then I need to check which
	* database layer to use. I'm told that I want to use mysqli
	* before I use mysql, so this method will first check to see
	* if the mysqli library exists. If it doesnt, it will check
	* to see if the mysql library exists. If that doesn't, then
	* we have a problem and nessquik will exit.
	*
	* @return string
	*/
	private function determine_db_type() {
		/**
		* Check to see if I should use mysql or mysqli php code
		* Some Ubuntu users reported that the php-mysqli package
		* is installed instead of php-mysql
		*/
		if (function_exists('mysqli_connect')) {
			return "mysqli";
		} else if (function_exists('mysql_connect')) {
			return "mysql";
		} else {
			return false;
		}
	}
}

/**
* Singleton class for returning a general database
* connection. This object is used for the majority
* of nessquik database work.
*
* @author Tim Rupp
* @see genDB
*/
class nessquikDB extends genDB {
	private static $instance;

	public static function getInstance($type = '') {
		if (empty(self::$instance)) {
			self::$instance = parent::db_factory(_DBUSER, _DBPASS, _DBUSE, _DBSERVER, _DBPORT, $type);
		}
		return self::$instance;
	}
}

/**
* Singleton class for returning a general database
* connection that does not connect to a specific
* database. This object is used for database work
* that needs to be done without the restriction
* of being connected to a specific database on the
* server.
*
* @author Tim Rupp
* @see genDB
*/
class maintDB extends genDB {
	private static $instance;

	public static function getInstance($type = '') {
		if (empty(self::$instance)) {
			self::$instance = parent::db_factory(_DBUSER, _DBPASS, '', _DBSERVER, _DBPORT, $type);
		}
		return self::$instance;
	}
}

/**
* Singleton class for returning a database connection
* to a separate scan results database. For Fermi's case,
* a separate database is used for policy reasons. In
* the general release of nessquik, a separate database
* is not used and instead the regular old nessquikDB
* singleton is returned.
*
* @author Tim Rupp
* @see genDB
*/
class resultsDB extends genDB {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			switch(_RELEASE) {
				case "fermi":
					self::$instance = parent::db_factory(_SAVED_DBUSER, _SAVED_DBPASS, _SAVED_DBUSE, _SAVED_DBSERVER, _SAVED_DBPORT);
					break;
				case "general":
				default:
					self::$instance = nessquikDB::getInstance();
					break;
			}
		}
		return self::$instance;
	}
}

/**
* Singleton class for returning a database connection
* to the exemption database. This code is only relevant
* to FNAL.
*
* @author Tim Rupp
* @see genDB
*/
class exemptDB extends genDB {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = parent::db_factory(_EXEMPT_DBUSER, _EXEMPT_DBPASS, _EXEMPT_DBUSE, _EXEMPT_DBSERVER, _EXEMPT_DBPORT);
		}
		return self::$instance;
	}
}

/**
* Singleton class for returning a database connection
* to the NIMI inventory database. This code is only
* relevant to FNAL.
*
* @author Tim Rupp
* @see genDB
*/
class nimiDB extends genDB {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = parent::db_factory(_NIMI_DBUSER, _NIMI_DBPASS, _NIMI_DBUSE, _NIMI_DBSERVER, _NIMI_DBPORT, "pgsql");
		}
		return self::$instance;
	}
}

/**
* Singleton class for returning a database connection
* to the MISCOMP systems database. This code is only
* relevant to FNAL.
*
* @author Tim Rupp
* @see genDB
*/
class miscompDB extends genDB {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			require_once(_ABSPATH."/lib/adodb/adodb.inc.php");
			$db = NewADOConnection("oci8");
			$db->connectSID = true;
			$db->Connect(_MIS_DBSERVER, _MIS_DBUSER, _MIS_DBPASS, _MIS_DBUSE);

			self::$instance = $db;
		}
		return self::$instance;
	}
}

/**
* Singleton class for connecting to the historic
* saved scan results database.
*
* @author Tim Rupp
* @see genDB
*/
class historyDB extends genDB {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = parent::db_factory(_HISTORY_DBUSER, _HISTORY_DBPASS, _HISTORY_DBUSE, _HISTORY_DBSERVER, _HISTORY_DBPORT);
		}
		return self::$instance;
	}
}

?>
