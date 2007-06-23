<?php

require_once(_ABSPATH.'/db/nessquikDB.php');

/**
* @author Tim Rupp
*/
class Help {
	/**
	* Contains a single instance of the Help object
	*
	* @var object
	*/
	static $instance;

	/**
	* Return an instance of the Help class
	*
	* Use this for generating or retrieving instance of Help
	* objects so that if the Help class needs to be abstracted
	* in the future, this common interface can still be used
	*
	* @return object New instance of Help class if one doesnt
	*	already exist. Otherwise return the current instance
	*/
	static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new Help;
		}
		return self::$instance;
	}

	/**
	* Get a list of all the help categories
	*
	* There are different types of help categories in nessquik.
	* Admin categories can only be seen from the admin pages,
	* while general categories can be seen by all users. This
	* method will return a list of all the categories, both
	* admin and general
	*
	* @return array Array of all the help categories
	* @see get_help_categories
	*/
	public function get_all_help_categories() {
		$categories = array();

		/**
		* Two types of help categories here
		*
		*	A = Admin categories
		*	G = General user categories
		*/
		$categories = array_merge($categories, $this->get_help_categories('A'));
		$categories = array_merge($categories, $this->get_help_categories('G'));

		return $categories;
	}

	/**
	* Get help categories for a particular type
	*
	* There are two types of help categories: admin and general.
	* Based on the argument that is passed to the method, all the
	* categories from either type will be returned.
	*
	* @param string $admin Type of categories to return. Either
	*	'A' for admin, or 'G' for general user.
	* @return array Array of categories for the specified type.
	*/
	public function get_help_categories($admin = 'G') {
		$categories	= array();
		$db 		= nessquikDB::getInstance();

		$sql = array(
			'select' => "	SELECT 	category_id,
						type,
						category 
					FROM help_categories 
					WHERE type=':1' 
					ORDER BY category ASC;"
		);

		// To prevent an arbitrary type from being specified
		$admin = $this->get_proper_type($admin);

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($admin);

		while($row = $stmt->fetch_assoc()) {
			$categories[] = array(
				'id'	=> $row['category_id'],
				'type'	=> $row['type'],
				'name'	=> $row['category']
			);
		}

		return $categories;
	}

	/**
	* Return a known help category type
	*
	* Just for sanity's sake, this function will check
	* a given category type against the known types. If
	* the type is known, then it will be returned, otherwise,
	* a default type of 'G' for general will be returned.
	*
	* @param string $type The type to check for correctness
	* @return string The original type if correct, a default
	*	type if not correct
	*/
	private function get_proper_type($type) {
		switch ($type) {
			case 'A':
			case 'G':
				break;
			default:
				$type = 'G';
				break;
		}

		return $type;
	}

	/**
	* Get the topics for a category
	*
	* This method will return all the topics for a given
	* category.
	*
	* @param integer $category_id Category ID of the
	*	category to get the help topics for.
	* @return array Array of help topics for the given
	*	category
	*/
	public function get_help_topics($category_id) {
		$topics	= array();
		$count	= 1;
		$db 	= nessquikDB::getInstance();

		$sql 	= array(
			'select' => "	SELECT 	help_id,
						question,
						answer 
					FROM help 
					WHERE category_id=':1' 
					ORDER BY question ASC;"
		);

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($category_id);

		while($row = $stmt->fetch_assoc()) {
			$topics[] = array(
				'help_id' 	=> $row['help_id'],
				'question'	=> $row['question'],
				'answer'	=> $row['answer'],
				'count'		=> $count
			);

			$count++;
		}

		return $topics;
	}

	/**
	* Get the name of a category
	*
	* Given a category's ID number, this method will
	* return the name of the category.
	*
	* @param integer $category_id Category ID of the
	*	category whose name you want to fetch
	* @return string Category name for the given ID.
	*	If the category ID is not a number, an
	*	empty string will be returned.
	*/
	public function get_category_name($category_id) {
		$category_name = '';
		$db = nessquikDB::getInstance();

		if (!is_numeric($category_id)) {
			return '';
		}

		$sql = array(
			'select' => "	SELECT category 
					FROM help_categories 
					WHERE category_id=':1';"
		);

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($category_id);

		$category_name = $stmt->result(0);

		return $category_name;
	}

	/**
	* Get the category ID of a category
	*
	* The category ID is used for the majority of the
	* operations on a category. This method will return
	* the first category ID found for a given category
	* name with the given type. I realize that some
	* people may try to be smart and duplicate category
	* names. Fine, but don't report bugs then.
	*
	* @param string $category_name Name of the category
	*	to get the ID of
	* @param string $type The type of category, either
	*	admin or general, where the category name
	*	is located.
	* @return integer The first match of a category ID
	*	for the category that matches the supplied
	*	arguments.
	*/
	public function get_category_id($category_name, $type = 'G') {
		$category_id = '';
		$db = nessquikDB::getInstance();

		$type = $this->get_proper_type($type);

		$sql = array(
			'select' => "	SELECT category_id 
					FROM help_categories 
					WHERE category=':1' 
					AND type=':2'
					LIMIT 1;"
		);

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($category_name, $type);

		$category_id = $stmt->result(0);

		return $category_id;
	}

	/**
	* Delete a help topic
	*
	* Deletes a help topic from the database given the
	* ID of the topic
	*
	* @param integer $help_id ID of the help topic to remove
	* @return boolean True is the topic was removed, false otherwise
	*/
	public function delete_help_topic($help_id) {
		$db = nessquikDB::getInstance();

		$sql = array(
			'delete' => "DELETE FROM help WHERE help_id=':1'"
		);

		$stmt = $db->prepare($sql['delete']);
		$stmt->execute($help_id);

		if ($stmt->affected() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Delete a category and all associated topics
	*
	* Using this method, you can delete an entire help
	* category and all the help topics associated with
	* the category.
	*
	* @param integer $category_id ID of the category
	*	that is to be removed.
	* @return True if the category and all the help
	*	topics associated with it were removed,
	*	false otherwise.
	*/
	public function delete_category($category_id) {
		$db 	= nessquikDB::getInstance();

		$sql = array(
			'category' => "DELETE FROM help_categories WHERE category_id=':1';",
			'topics' => "DELETE FROM help WHERE category_id=':1';"
		);

		$stmt1 = $db->prepare($sql['category']);
		$stmt2 = $db->prepare($sql['topics']);

		$stmt1->execute($category_id);
		$stmt2->execute($category_id);

		if($stmt1->affected() < 0) {
			return false;
		}

		if($stmt2->affected() < 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	* Get the values for a particular topic
	*
	* Returns all the data associated with a given
	* help topic's ID
	*
	* @param integer $help_id ID of the help topic
	*	to return info for.
	* @return array All the data associated with
	*	the particular help ID.
	*/
	public function get_topic_values($help_id) {
		$db = nessquikDB::getInstance();
		$content = array();

		$sql = array(
			'select' => "SELECT * FROM help WHERE help_id=':1' LIMIT 1;"
		);

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($help_id);

		$content = $stmt->fetch_assoc();

		return $content;
	}
	
	/**
	* Updates a help topic
	*
	* This method will save back to the database, the
	* information that is associated with a topic.
	*
	* @param integer $help_id ID of the help topic that
	*	is being saved
	* @param integer $category_id ID of the category that
	*	the particular topic will/does now reside in
	* @param string $question Question that is posed by
	*	the topic
	* @param string $answer Answer to the help topic question
	* @return boolean True on successful update, false on failure
	*/
	public function edit_help_topic($help_id,$category_id,$question,$answer) {
		$db = nessquikDB::getInstance();

		$sql = array(
			'update' => "UPDATE help SET category_id=':1', question=':2', answer=':3' WHERE help_id=':4'"
		);

		$stmt = $db->prepare($sql['update']);
		$stmt->execute($category_id, $question, $answer, $help_id);

		if($stmt->affected() < 0) {
			return false;
		} else {
			return true;
		}
	}
}

?>
