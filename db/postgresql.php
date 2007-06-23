<?php
/**
* Database access tools
*
* Abstraction layer for accessing PostgreSQL databases
*
* @package PgSQL_DB
* @access public
*/
class PgSQL_DB {
	/**
	* Connection to the database is stored here
	*
	* @access public
	* @var resource
	*/
	var $link;

	/**
	* Holds all the queries that can ever be run by the system.
	*
	* @access public
	* @var array
	*/
	var $sql_queries;

	/**
	* Specifies whether an error has been encountered
	*
	* @access public
	* @var bool
	*/
	var $error;

	/**
	* Username used to connect to the database server
	*
	* @access protected
	* @var string
	*/
	var $username;

	/**
	* Password of username provided
	*
	* @access protected
	* @var string
	*/
	var $password;

	/**
	* Server where PostgreSQL database resides
	*
	* @access protected
	* @var string
	*/
	var $server;

	/**
	* Database that will be used to select data from
	*
	* @access protected
	* @var string
	*/
	var $db;

	/**
	* Port to connect to database on
	*
	* @access protected
	* @var integer
	*/
	var $port;

	/**
	* Creates a default database data type to be used
	*
	* This is a default constructor to override the one otherwise
	* created by PHP. This constructor sets up a connection to the
	* database server so that queries can be prepared and executed
	* immediatly
	*
	* @access public
	* @param string $username Username used to connect to the database server
	* @param string $password Password for username used to connect to the database server
	* @param string $db Database that will be used to select data from
	* @param string $server Server hostname or IP address where database resides
	* @param integer $port Port to connect to database on
	* @see connect()
	*/
	function PgSQL_DB ($username, $password, $db = "template1", $server = "localhost", $port = 5432) {
		/**
		* Holds the connection to the database
		*/
		$this->link = "";

		/**
		* Username used to connect to the database
		*/
		$this->username = $username;

		/**
		* Password for the username used to connect to the database
		*/
		$this->password = $password;

		/**
		* The name of the database where the system tables are stored
		*/
		$this->db = $db;

		/**
		* The server where the above database resides
		*/
		$this->server = $server;

		/**
		* The port that the server is listening on
		*/
		$this->port = $port;

		/**
		* No errors so far
		*/
		$this->error = false;

		/**
		* Performs the connection to the database
		*/
		$this->connect();
	}
	
	/**
	* Creates a connection to a PostgreSQL database
	*
	* Two different types of connections are possible, persistant
	* and non-persistant. The type of connection is determined by
	* the constant variable contained in the config file. During
	* this method, the database that will be used is also selected.
	*
	* @access public
	* @return resource Sets the 'link' class variable to point to the connection resource
	*/
	function connect() {
		$connection_string 	= "host=".$this->server." "
					. "port=".$this->port." "
					. "dbname=".$this->db." ";

		if ($this->username != '')
			$connection_string .= "user=".$this->username." ";

		if ($this->password != '')
			$connection_string .= "password=".$this->password." ";

		$connection_string = trim($connection_string);

		/**
		* Open a connection using the type specified in the config file
		*/
		if (_CONNECT_TYPE == "persist") {
			/**
			* A persistant connection, connections are pooled and reused as needed
			* depending on how many people connect to the system.
			*/
			@$this->link = pg_pconnect($connection_string);
		} else {
			/**
			* A normal connection. This results in each request by the user requiring a 
			* new connection to the database.
			*/
			@$this->link = pg_connect($connection_string); 
		}
		/**
		* Make sure the database connection was successful
		*/
		if (!is_resource($this->link)) {
			$this->error = true;
			if (_DEBUG) {
				throw(new Exception(_CONNECT_ERROR));
			} else {
				die("Could not connect to the database");
			}
		}
	}

	/**
	* Readies a query for execution
	*
	* All queries must be prepared before they are executed.
	* This makes code easier to read because all possible
	* queries can be prepared immediatly at the beginning of
	* the script and then executed whenever the user wishes.
	*
	* @access public
	* @return PgSQL_DB_Statement Object containing database connection and query to execute
	*/
	function prepare($query) {
		/**
		* If the database connection has died, re-establish it
		*/
		if (!$this->link) {
			$this->connect();
		}

		/**
		* Return an object that is specific to the query you want to run.
		* this object will let you run all the data request functions you need.
		*
		* Because each statement is its own object, you can also create and 
		* execute all your queries at the start of your scripts. This can
		* result in a performance boost at times.
		*/
		return new PgSQL_DB_Statement($this->link, $query);
	}

	/**
	* Closes connection to database
	*
	* This will destroy the database object and close
	* the connection to the MySQL database server.
	* This method is only called during garbage cleanup
	* by the PHP interpreter.
	*
	* @access public
	*/
	function __destruct() {
		if ($this->link) {
			pg_close($this->link);
		}
	}
}

/**
* Database query class
*
* Provides access to individual query results so that
* many queries can be prepared and executed simultaneously
* and all their results will be seperate so that they
* can be operated on individually.
*
* @package PgSQL_DB
* @access public
*/
class PgSQL_DB_Statement {
	/**
	* The number of variables that were passed to the execute function for inclusion
	* in the SQL query that wants to be executed.
	*
	* @access public
	* @var integer
	*/
	var $binds;

	/**
	* Holds the query that is waiting to be executed. It will never contain the
	* query with the data values inserted.
	*
	* @access public
	* @var string
	*/
	var $query;

	/**
	* Holds the result from the query that is executed against the database
	*
	* @access protected
	* @var resource
	*/
	var $result;

	/**
	* Holds the connection to the database
	*
	* @access protected
	* @var resource
	*/
	var $link;

	/**
	* Creates a default database data type to be used
	*
	* This is a default constructor to override the one otherwise
	* created by PHP. This constructor stores the database connection
	* and query in variables and checks to see if the connection
	* to the database is still valid
	*
	* @param resource $link Connection to the database
	* @param string $query Query to be executed
	*/
	function PgSQL_DB_Statement($link, $query) {
		$this->query = $query;
		$this->link = $link;

		if (!is_resource($link)) {
			$this->error = true;
			if (_DEBUG) {
				throw(new Exception(_INVALID_CONNECTION));
			} else {
				die("Invalid connection!");
			}
		}
	}
	
	/**
	* Gets a single row from the result set
	*
	* This will return a single row from the result set
	* indexed by an integer value from 0 to the total
	* number of fields returned. It is identical to the
	* similar PHP function 'mysql_fetch_row'
	*
	* @access public
	* @return array Single row containing data in fields queried from database
	*/
	function fetch_row() {
		if(!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new Exception(_QUERY_NOT_EXEC));
			} else {
				die("Query was not executed! Run the query before trying to return results!");
			}
		}
		return pg_fetch_row($this->result);
	}

	/**
	* Gets a single row from the result set
	*
	* This method is different from fetch_row because in
	* addition to returning an integer indexed array of
	* results, it will also include in the array, data 
	* that is indexed by field name. This is slightly
	* more 'headache free' from a developer poitn of view
	* because in the future if you change the number of 
	* fields that are queried for, you will not also need
	* to change the actual usage of the results later in
	* the script because this array contains an index
	* based on field name as well as integer based.
	* This method is identical to the similar PHP function
	* 'mysql_fetch_array'
	*
	* @access public
	* @return array Single row containing data in fields queried from database
	*/
	function fetch_array() {
		if(!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new Exception(_QUERY_NOT_EXEC));
			} else {
				die("Query was not executed! Run the query before trying to return results!");
			}
		}
		return pg_fetch_array($this->result);
	}
	

	/**
	* Gets a single row from the result set
	*
	* Fetches a single row from the result set and
	* returns it as an associative array with the keys
	* being the field name from where the data was pulled.
	* Note that this method is exactly the same as
	* the fetch_array method, but will only return an
	* array indexed by field name.
	*
	* @access public
	* @return array Single row containing data in fields queried from database
	*/
	function fetch_assoc() {
		if(!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new Exception(_QUERY_NOT_EXEC));
			} else {
				die("Query was not executed! Run the query before trying to return results!");
			}
		}
		return pg_fetch_assoc($this->result);
	}

	/**
	* Executes a query
	*
	* Binds and executes a given query. The results of the
	* query are stored in the class variable 'result' where
	* they can be accessed later by helper methods to retrieve
	* the data returned from the query
	*
	* @access public
	* @param misc This method has no set parameter that it takes, but it
	*		will accept ANY NUMBER of parameters you give it. The
	*		provided parameters will be inserted into the query
	*		to be executed.
	* @return resource Pointer to object containing results of the query
	*/
	function execute() {
		$binds = func_get_args();

		/**
		* This if is only to remove warnings that may crop up about the $binds
		* var not having at least 1 value to run the foreach loop with.
		* This is perfectly possible because some SQL code may not need values
		* passed into it.
		*/
		if (count($binds) > 0) {
			foreach ($binds as $key => $val) {
				$count = 0;
				// Code allowing an array of values to be passed
				if(is_array($val)) {
					foreach ($val as $key2 => $val2) {
						$this->binds[$count + 1] = $val2;
						$count += 1;
					}
				} else {
					// Otherwise multiple args were passed
					$this->binds[$key + 1] = $val;
				}
			}

			$query 	= $this->query;

			foreach ($this->binds as $key => $val) {
				$val = str_replace(':', "__fph__", $val);
				
				/**
				* We need to use preg_replace instead of str_replace because str_replace
				* will replace ALL occurances of the string. This can lead to bugs in the
				* SQL code if more than 10 variables are passed to the execute method.
				* With preg_replace, we can limit the number of matches, aka the 1 in the
				* function call.
				*/
				$query = preg_replace("/:$key/", pg_escape_string($val), $query, 1);		
			}
		} else {
			$query 	= $this->query;
		}

		$query = str_replace("__fph__", ':', $query);

		$this->result = pg_query($this->link, $query);
	}
	
	/**
	* Returns the number of rows queried in the SQL query
	*
	* This method is identical to the similar PHP function
	* 'mysql_num_rows'
	*
	* @access public
	* @return integer Number of rows returned by the query
	*/
	function num_rows() {
		if (!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new PostgreSQL_Exception(_QUERY_NOT_EXEC));
			} else {
				die("Query was not executed! Run the query before trying to return results!");
			}
		}

		return pg_num_rows($this->result);
	}

	/**
	* Returns the name of the field at the given index
	*
	* This method is identical to the similar PHP function
	* 'mysql_fetch_field'
	*
	* @access public
	* @return string Field name at the given index
	*/
	function field($index = 0) {
		if (!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new PostgreSQL_Exception(_QUERY_NOT_EXEC));
			} else {
				die("Query was not executed! Run the query before trying to return results!");
			}
		}

		return pg_field_name($this->result, $index);
	}

	/**
	* Returns a single result from query
	*
	* Returns a single result from the result set at the
	* specified index. This method is identical to the
	* similar PHP function 'mysql_result'
	*
	* @access public
	* @return misc Data from the particular index of the result set
	*/
	function result($index = 0, $row = 1) {
		if (!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new PostgreSQL_Exception(_QUERY_NOT_EXEC));
			} else {
				die("Query was not executed! Run the query before trying to return results!");
			}
		}

		return @pg_fetch_result($this->result, $row, $index);
	}

	/**
	* Displays SQL to be executed - brief
	*
	* This will display the SQL that is about to be
	* executed but it will not replace the placemarker
	* strings with the actual values to be used with
	* the query
	*
	* @access public
	*/
	function show_sql() {
		echo _EXECUTED_QUERY . $this->query;
	}

	/**
	* Displays SQL to be executed - full
	*
	* If variables are provided (exactly like the execute() method),
	* this method will display the actual SQL that is about to be
	* used to query the database. This is an extremely helpful
	* method that can be used when a query dies and you do not
	* know why it died.
	*
	* @access public
	* @param misc This method has no set parameter that it takes, but it
	*		will accept ANY NUMBER of parameters you give it. The
	*		provided parameters will be inserted into the query
	*		to be executed.
	*/
	function show_sql_executing() {
		$binds = func_get_args();

		/**
		* This 'if' is only to remove warnings that may crop up about the $binds
		* var not having at least 1 value to run the foreach loop with.
		* This is perfectly possible because some SQL code may not need values
		* passed into it.
		*/
		if (count($binds) > 0) {
			foreach ($binds as $key => $val) {
				$count = 0;
				// Code allowing an array of values to be passed
				if(is_array($val)) {
					foreach ($val as $key2 => $val2) {
						$this->binds[$count + 1] = $val2;
						$count += 1;
					}
				} else {
					// Otherwise multiple args were passed
					$this->binds[$key + 1] = $val;
				}
			}

			$query 	= $this->query;

			foreach ($this->binds as $key => $val) {
				$val = str_replace(':', "__fph__", $val);
				
				/**
				* We need to use preg_replace instead of str_replace because str_replace
				* will replace ALL occurances of the string. This can lead to bugs in the
				* SQL code if more than 10 variables are passed to the execute method.
				* With preg_replace, we can limit the number of matches, aka the 1 in the
				* function call.
				*/
				$query = preg_replace("/:$key/", pg_escape_string($val), $query, 1);
			}
		} else {
			$query 	= $this->query;
		}
		
		$query = str_replace("__fph__", ':', $query);

		echo _EXECUTED_QUERY . $query;
	}
}

/**
* General Exception handler
*
* Provides tools to access general features of
* errors that are thrown
*
* @package Error_Handler
* @access public
*/
class PgSQL_General_Exception extends Exception {
	/**
	* The filename where the error was encountered
	*
	* @access protected
	* @var string
	*/
	var $file;

	/**
	* The line number where the error occured
	*
	* @access protected
	* @var integer
	*/
	var $line;

	/**
	* The message provided for why the error was thrown
	*
	* @access protected
	* @var string
	*/
	var $message;

	/**
	* Creates an instance of PgSQL_General_Exception class
	*
	* This is a default constructor to override the one otherwise
	* created by PHP. This constructor need not do anything complex
	* so a basic one is provided. This constructor begins by setting
	* several of the available class variables to information that
	* is provided by PHP and the developer of the extension.
	*
	* @access public
	*/
	function PgSQL_General_Exception($message = false, $code = false) {
		$this->file	=	__FILE__;
		$this->line	=	__LINE__;
		$this->message	=	$message;
	}

	/**
	* Returns the file name where the error occured
	*
	* @access public
	* @return string Filename where the error occured
	*/
	function get_file() {
		return $this->file;
	}

	/**
	* Returns the line number where the error occured
	*
	* @access public
	* @return integer The line number where the error was thrown
	*/
	function get_line() {
		return $this->line;
	}

	/**
	* Returns developer assigned error message
	*
	* @access public
	* @return string Message created by developer to describe the error encountered
	*/
	function get_message() {
		return $this->message;
	}
}

/**
* MySQL Exception handler
*
* Provides tools to access general parts of
* MySQL specific errors that are thrown
*
* @package Error_Handler
* @access public
* @author Tim Rupp <tarupp01@indianatech.net>
* @copyright GPL
*/
class PostgreSQL_Exception extends PgSQL_General_Exception {
	/**
	* Maintains a backtrace of all calls up to and including the error
	*
	* @access public
	* @var array
	*/
	var $backtrace;

	/**
	* Creates an instance of PostgreSQL_Exception class
	*
	* This is a default constructor to override the one otherwise
	* created by PHP. This constructor need not do anything complex
	* so a basic one is provided. This constructor begins by setting
	* several of the available class variables to information that
	* is provided by PHP and the developer of the extension.
	*
	* @access public
	*/
	function PostgreSQL_Exception($message = false, $code = false) {
		if (!$message) {
			$this->message	=	pg_last_error();
		}

		$this->backtrace	=	debug_backtrace();
	}
}

?>
