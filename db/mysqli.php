<?php
/**
* Database access tools
*
* Abstraction layer for accessing MySQL databases. This
* class uses the "new" MySQL function in PHP. Apparently
* they are better designed or something.
*
* @author Tim Rupp
*/
class MySQLi_DB {
	const _CONNECT_ERROR = "Could not connect to the database";

	/**
	* Connection to the database is stored here
	*
	* @var resource
	*/
	private $link;

	/**
	* Holds all the queries that can ever be run by the system.
	*
	* @var array
	*/
	private $sql_queries;

	/**
	* Specifies whether an error has been encountered
	*
	* @var bool
	*/
	private $error;

	/**
	* Username used to connect to the database server
	*
	* @var string
	*/
	private $username;

	/**
	* Password of username provided
	*
	* @var string
	*/
	private $password;

	/**
	* Server IP address where MySQL database resides
	*
	* @var string
	*/
	private $server;

	/**
	* Database that will be used to select data from
	*
	* @var string
	*/
	private $db;

	/**
	* Port to connect to database on
	*
	* @var integer
	*/
	private $port;

	/**
	* Creates a default database data type to be used
	*
	* This is a default constructor to override the one otherwise
	* created by PHP. This constructor sets up a connection to the
	* database server so that queries can be prepared and executed
	* immediatly
	*
	* @param string $username Username used to connect to the database server
	* @param string $password Password for username used to connect to the database server
	* @param string $db Database that will be used to select data from
	* @param string $server Server hostname or IP address where database resides
	* @param integer $port Port to connect to database on
	* @see connect()
	*/
	public function __construct($username, $password, $db = "", $server = "localhost", $port = 3306) {
		/**
		* Holds the connection to the database
		*/
		$this->link = false;

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
		* If the user specified a non-standard port for the MySQL connection, we need
		* to reformat the server string so that it contains the port to use before we
		* connect to the database
		*/
		if ($port != 3306) {
			$this->port = $port;
			$this->server = $this->server . ":" . $port;
		}

		$this->error = false;

		$this->connect();
	}
	
	/**
	* Creates a connection to a MySQL database
	*
	* Two different types of connections are possible, persistant
	* and non-persistant. The type of connection is determined by
	* the constant variable contained in the config file. During
	* this method, the database that will be used is also selected.
	*
	* @return resource Sets the 'link' class variable to point to the connection resource
	*/
	public function connect() {
		/**
		* A persistant connection, connections are pooled and reused as needed
		* depending on how many people connect to the system.
		*/
		@$this->link = mysqli_connect(	$this->server,
						$this->username,
						$this->password,
						$this->db);

		/**
		* Make sure the database connection was successful
		*/
		if (!$this->link) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception('',self::_CONNECT_ERROR));
			} else {
				die(self::_CONNECT_ERROR);
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
	* @return MySQLi_DB_Statement Object containing database connection and query to execute
	*/
	public function prepare($query) {
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
		return new MySQLi_DB_Statement($this->link, $query);
	}

	/**
	* Loads a SQL file and parses all the lines in
	* the file, executing them using the database
	* layer specified. Note that no values can be
	* supplied to this function for inclusion in
	* the SQL that will be run.
	*
	*		WARNING
	* 	This function is black magic.
	*
	* It was taken from phpMyAdmin 2.10.0.2. The file 
	* which it was taken from is
	*
	*	libraries/import/sql.php
	*
	* I have tweaked variable names and spacing to
	* conform with my coding standards. Other than
	* that, the functionality is the same. I have no
	* idea how this function does what it does, but
	* after testing it with several SQL files, I'm
	* confident it works.
	*
	* Don't send bug reports about this guy!
	*
	* @param string $sql_file The filename you want to read and execute
	*/
	public function load_sql_file($sql_file) {
		//We do not define function when plugin is just queried for information above 
		@$fh = fopen($sql_file, 'r');

		if (!$fh) {
			return false;
		}

		// Defaults for parser
		$buffer 	= '';
		$sql 		= '';
		$start_pos 	= 0;
		$i 		= 0;
		$sql_delimiter 	= ';';
		$finished 	= false;
		$error 		= false;
		$timeout_passed	= false;

		while (!($finished && $i >= $len) && !$error && !$timeout_passed) {
			$data = fgets($fh,4096);

			if (feof($fh)) {
				break;
			}

			// Append new data to buffer
			$buffer .= $data;
			// Do not parse string when we're not at the end and don't have ; inside
			if ((strpos($buffer, $sql_delimiter) === FALSE) && !$finished) {
				continue;
			}

			// Current length of our buffer
			$len = strlen($buffer);

			// Grab some SQL queries out of it
			while ($i < $len) {
				$found_delimiter = false;

				/**
				* Find first interesting character, several strpos seem to be 
				* faster than simple loop in php:
				*
				* while (($i < $len) && (strpos('\'";#-/', $buffer[$i]) === FALSE)) $i++;
				*/
				$oi = $i;

				/**
				* This craziness is doing the following
				*
				* strpos is searching the current buffer for a character
				* (see which character in the second argument). It searches
				* from the end of the last buffer which I'm guessing is $i.
				*
				* If it finds it, it records the position in $pX. If it
				* doesn't find it, the it sets $pX to a huge number. This
				* huge number is used a bit later, scroll down.
				*/
				$p1 = strpos($buffer, '\'', $i);
				if ($p1 === FALSE) {
					$p1 = 2147483647;
				}

				$p2 = strpos($buffer, '"', $i);
				if ($p2 === FALSE) {
					$p2 = 2147483647;
				}

				$p3 = strpos($buffer, $sql_delimiter, $i);
				if ($p3 === FALSE) {
					$p3 = 2147483647;
				} else {
					$found_delimiter = true;
				}

				$p4 = strpos($buffer, '#', $i);
				if ($p4 === FALSE) {
					$p4 = 2147483647;
				}

				$p5 = strpos($buffer, '--', $i);
				if ($p5 === FALSE || $p5 >= ($len - 2) || $buffer[$p5 + 2] > ' ') {
					$p5 = 2147483647;
				}

				$p6 = strpos($buffer, '/*', $i);
				if ($p6 === FALSE) {
					$p6 = 2147483647;
				}

				$p7 = strpos($buffer, '`', $i);
				if ($p7 === FALSE) {
					$p7 = 2147483647;
				}

				/**
				* Here's where the big number is used. It looks like
				* depending on which is the smallest number, that is
				* the position that the code will start working from.
				*
				* I think that this code is being used so that it
				* will know when it's inside a block of data that is,
				* for instance, wrapped in quotes. If it knows its
				* inside quotes, then it wont consider a terminated
				* quote as the end of the quotes
				*
				* example
				*
				*	$i = strpos("this 'is quote\' data'")
				*
				* prevents the \' from being considered the ending
				* quote for the quotes beginning at 'is.
				*/
				$i = min ($p1, $p2, $p3, $p4, $p5, $p6, $p7);

				// and then this makes sure that the previous values
				// wont dirty future values
				unset($p1, $p2, $p3, $p4, $p5, $p6, $p7);

				if ($i == 2147483647) {
					$i = $oi;
					if (!$finished) {
						break;
					}

					// at the end there might be some whitespace...
					if (trim($buffer) == '') {
						$buffer = '';
						$len = 0;
						break;
					}

					// We hit end of query, go there!
					$i = strlen($buffer) - 1;
				}

				// Grab current character
				$ch = $buffer[$i];

				// Quotes
				if (!(strpos('\'"`', $ch) === FALSE)) {
					$quote = $ch;
					$endq = FALSE;
					while (!$endq) {
						// Find next quote
						$pos = strpos($buffer, $quote, $i + 1);

						// No quote? Too short string
						if ($pos === FALSE) {
							// We hit end of string => unclosed quote, but we handle it as end of query
							if ($finished) {
								$endq = TRUE;
								$i = $len - 1;
							}
							break;
						}

						// Was not the quote escaped?
						$j = $pos - 1;
						while ($buffer[$j] == '\\') {
							$j--;
						}

						// Even count means it was not escaped
						// Tim says this is bad programming!
						$endq = (((($pos - 1) - $j) % 2) == 0);

						// Skip the string
						$i = $pos;
					}

					if (!$endq) {
						break;
					}

					$i++;

					// Aren't we at the end?
					if ($finished && $i == $len) {
						$i--;
					} else {
						continue;
					}
				}

				// Not enough data to decide
				if ((($i == ($len - 1) && ($ch == '-' || $ch == '/'))
					|| ($i == ($len - 2) && (($ch == '-' && $buffer[$i + 1] == '-') 
					|| ($ch == '/' && $buffer[$i + 1] == '*')))) && !$finished) {
						break;
				}

				// Comments
				// hahaha, what in the name of...
				if ($ch == '#' || ($i < ($len - 1) && $ch == '-' && $buffer[$i + 1] == '-' && (($i < ($len - 2) && $buffer[$i + 2] <= ' ') || ($i == ($len - 1) && $finished))) || ($i < ($len - 1) && $ch == '/' && $buffer[$i + 1] == '*')) {

					// Copy current string to SQL
					if ($start_pos != $i) {
						$sql .= substr($buffer, $start_pos, $i - $start_pos);
					}

					// Skip the rest
					$j = $i;

					$i = strpos($buffer, $ch == '/' ? '*/' : "\n", $i);

					// didn't we hit end of string?
					if ($i === FALSE) {
						if ($finished) {
							$i = $len - 1;
						} else {
							break;
						}
					}

					// Skip *
					if ($ch == '/') {
						/**
						* Check for MySQL conditional comments and include them as-is
						*
						* Conditional comments look like this
						*
						*	/*!40101 SET SQL_MODE=@OLD_SQL_MODE /;
						*
						* Screw conditionals, this is being commented out. Only maintained
						* for future reference
						*/
						#if ($buffer[$j + 2] == '!') {
						#	$comment = substr($buffer, $j + 3, $i - $j - 3);
						#	if (preg_match('/^[0-9]{5}/', $comment, $version)) {
						#		if ($version[0] <= self::PMA_MYSQL_INT_VERSION) {
						#			$sql .= substr($comment, 5);
						#		}
						#	} else {
						#		$sql .= $comment;
						#	}
						#}
						$i++;
					}

					// Skip last char
					$i++;

					// Next query part will start here
					$start_pos = $i;

					// Aren't we at the end?
					if ($i == $len) {
						$i--;
					} else {
						continue;
					}
				}

				// End of SQL
				if ($found_delimiter || ($finished && ($i == $len - 1))) {
					$tmp_sql = $sql;
					if ($start_pos < $len) {
						$length_to_grab = $i - $start_pos;

						if (!$found_delimiter) {
							$length_to_grab++;
						}

						$tmp_sql .= substr($buffer, $start_pos, $length_to_grab);
						unset($length_to_grab);
					}

					// Do not try to execute empty SQL
					if (!preg_match('/^([\s]*;)*$/', trim($tmp_sql))) {
						$sql = $tmp_sql;

						/**
						* 	Attention freeloaders ! (like me)
						*
						* If you want to use this function in your own code,
						* The following lines should be removed and replaced
						* with whatever means you use the run an actual SQL
						* command in your scripts.
						*/
						$stmt = $this->prepare($sql);
						//echo "\n\n";
						//$stmt->show_sql_executing();
						$stmt->execute();

						// Stop here freeloaders!

						$buffer = substr($buffer, $i + strlen($sql_delimiter));

						// Reset parser:
						$len = strlen($buffer);
						$sql = '';
						$i = 0;
						$start_pos = 0;

						// Any chance we will get a complete query?
						// if ((strpos($buffer, ';') === FALSE) && !$finished) {
						if ((strpos($buffer, $sql_delimiter) === FALSE) && !$finished) {
							break;
						}
					} else {
						$i++;
						$start_pos = $i;
					}
				}
			} // End of parser loop
		} // End of import loop
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
* @author Tim Rupp
*/
class MySQLi_DB_Statement {
	const _EXECUTED_QUERY 		= "The query being executed is.";
	const _QUERY_NOT_EXEC		= "Query was not executed! Run the query before trying to return results!";
	const _INVALID_CONNECTION	= "Invalid connection!";

	/**
	* The number of variables that were passed to the execute function for inclusion
	* in the SQL query that wants to be executed.
	*
	* @var integer
	*/
	private $binds;

	/**
	* Holds the query that is waiting to be executed. It will never contain the
	* query with the data values inserted.
	*
	* @var string
	*/
	private $query;

	/**
	* Holds the result from the query that is executed against the database
	*
	* @var resource
	*/
	private $result;

	/**
	* Holds the connection to the database
	*
	* @var resource
	*/
	private $link;

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
	public function __construct($link, $query) {
		$this->query = $query;
		$this->link = $link;

		if (!$link) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception($this->link, self::_INVALID_CONNECTION));
			} else {
				die(self::_INVALID_CONNECTION);
			}
		}
	}
	
	/**
	* Gets a single row from the result set
	*
	* This will return a single row from the result set
	* indexed by an integer value from 0 to the total
	* number of fields returned. It is identical to the
	* similar PHP function 'mysqli_fetch_row'
	*
	* @return array Single row containing data in fields queried from database
	*/
	public function fetch_row() {
		if(!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception($this->link, self::_QUERY_NOT_EXEC));
			} else {
				die(self::_QUERY_NOT_EXEC);
			}
		}
		return mysqli_fetch_row($this->result);
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
	* 'mysqli_fetch_array'
	*
	* @return array Single row containing data in fields queried from database
	*/
	public function fetch_array() {
		if(!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception($this->link, self::_QUERY_NOT_EXEC));
			} else {
				die(self::_QUERY_NOT_EXEC);
			}
		}
		return mysqli_fetch_array($this->result);
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
	* @return array Single row containing data in fields queried from database
	*/
	public function fetch_assoc() {
		if(!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception($this->link,self::_QUERY_NOT_EXEC));
			} else {
				die(self::_QUERY_NOT_EXEC);
			}
		}
		return mysqli_fetch_assoc($this->result);
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
	public function execute() {
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
				$query = preg_replace("/:$key/", mysqli_real_escape_string($this->link,$val), $query, 1);		
			}
		} else {
			$query 	= $this->query;
		}

		$query = str_replace("__fph__", ':', $query);

		$this->result = mysqli_query($this->link, $query);
	}
	
	/**
	* Returns the number of rows queried in the SQL query
	*
	* This method is identical to the similar PHP function
	* 'mysqli_num_rows'
	*
	* @return integer Number of rows returned by the query
	*/
	public function num_rows() {
		if (!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception($this->link, self::_QUERY_NOT_EXEC));
			} else {
				die(self::_QUERY_NOT_EXEC);
			}
		}

		return mysqli_num_rows($this->result);
	}

	/**
	* Returns the name of the field at the given index
	*
	* This method is identical to the similar PHP function
	* 'mysqli_fetch_field'
	*
	* @return string Field name at the given index
	*/
	public function field($index = 0) {
		if (!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception($this->link, self::_QUERY_NOT_EXEC));
			} else {
				die(self::_QUERY_NOT_EXEC);
			}
		}

		return mysqli_fetch_field($this->result);
	}

	/**
	* Returns a single result from query
	*
	* Returns a single result from the result set at the
	* specified index. This method is identical to the
	* similar PHP function 'mysql_result'
	*
	* @return misc Data from the particular index of the result set
	*/
	public function result($index = 0) {
		if (!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception($this->link, self::_QUERY_NOT_EXEC));
			} else {
				die(self::_QUERY_NOT_EXEC);
			}
		}

		$tmp = @mysqli_fetch_row($this->result);

		return $tmp[$index];
	}

	/**
	* Returns number of affected rows from query
	*
	* Gets the number of affected rows by the last INSERT, 
	* UPDATE, REPLACE or DELETE query.
	*
	* @return integer Number of affected rows from last query
	*/
	public function affected() {
		if (!$this->result) {
			$this->error = true;
			if (_DEBUG) {
				throw(new MySQLi_Exception($this->link,self::_QUERY_NOT_EXEC));
			} else {
				die(self::_QUERY_NOT_EXEC);
			}
		}
	
		return mysqli_affected_rows($this->link);
	}

	/**
	* Displays SQL to be executed - brief
	*
	* This will display the SQL that is about to be
	* executed but it will not replace the placemarker
	* strings with the actual values to be used with
	* the query
	*/
	public function show_sql() {
		echo self::_EXECUTED_QUERY . $this->query;
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
	* @param misc This method has no set parameter that it takes, but it
	*		will accept ANY NUMBER of parameters you give it. The
	*		provided parameters will be inserted into the query
	*		to be executed.
	*/
	public function show_sql_executing() {
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
				$query = preg_replace("/:$key/", mysqli_real_escape_string($this->link,$val), $query, 1);		
			}
		} else {
			$query 	= $this->query;
		}

		$query = str_replace("__fph__", ':', $query);

		echo self::_EXECUTED_QUERY . $query;
	}

	/**
	* Returns the last auto_increment ID of an INSERT
	*
	* @return integer $last_insert_id The ID generated for an auto_increment by previous INSERT query
	*/
	public function last_id() {
		$last_insert_id = mysqli_insert_id($this->link);
		return $last_insert_id;
	}
}

/**
* General Exception handler
*
* Provides tools to access general features of
* errors that are thrown
*
* @author Tim Rupp
*/
class MySQLi_General_Exception extends Exception {
	/**
	* The filename where the error was encountered
	*
	* @var string
	*/
	protected $file;

	/**
	* The line number where the error occured
	*
	* @var integer
	*/
	protected $line;

	/**
	* The message provided for why the error was thrown
	*
	* @var string
	*/
	protected $message;

	/**
	* The error code for the above message
	*
	* @var integer
	*/
	protected $code;

	/**
	* Creates an instance of MySQLi_General_Exception class
	*
	* This is a default constructor to override the one otherwise
	* created by PHP. This constructor need not do anything complex
	* so a basic one is provided. This constructor begins by setting
	* several of the available class variables to information that
	* is provided by PHP and the developer of the extension.
	*/
	public function __construct($message = false, $code = false) {
		$this->file	=	__FILE__;
		$this->line	=	__LINE__;
		$this->message	=	$message;
		$this->code	=	$code;
	}

	/**
	* Returns the file name where the error occured
	*
	* @return string Filename where the error occured
	*/
	protected function get_file() {
		return $this->file;
	}

	/**
	* Returns the line number where the error occured
	*
	* @return integer The line number where the error was thrown
	*/
	protected function get_line() {
		return $this->line;
	}

	/**
	* Returns developer assigned error message
	*
	* @return string Message created by developer to describe the error encountered
	*/
	protected function get_message() {
		return $this->message;
	}

	/**
	* Return error code for specific error message
	*
	* If the developer has created a code to match the
	* error message, then this will return that code number
	*
	* @return integer Developer assigned error code
	*/
	protected function get_code() {
		return $this->code;
	}
}

/**
* MySQLi Exception handler
*
* Provides tools to access general parts of
* MySQLi specific errors that are thrown
*
* @author Tim Rupp
*/
class MySQLi_Exception extends MySQLi_General_Exception {
	/**
	* Maintains a backtrace of all calls up to and including the error
	*
	* @access public
	* @var array
	*/
	private $backtrace;

	/**
	* Creates an instance of MySQLi_Exception class
	*
	* This is a default constructor to override the one otherwise
	* created by PHP. This constructor need not do anything complex
	* so a basic one is provided. This constructor begins by setting
	* several of the available class variables to information that
	* is provided by PHP and the developer of the extension.
	*/
	public function __construct($link = '', $message = false, $code = false) {
		if (!$message) {
			$this->message	=	mysqli_error($link);
		}

		if (!$code) {
			if (is_resource($link)) {
				$this->code	=	mysqli_errno($link);
			}
		}

		$this->backtrace	=	debug_backtrace();
	}
}

?>
