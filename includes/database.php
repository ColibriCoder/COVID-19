<?
class Database {
	var $connection;
	var $dbhost;
	var $dbuser;
	var $dbpass;
	var $db;

	public function __construct($dbhost, $dbuser, $dbpass, $db) {
		$connection = new Connection();

		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->db = $db;
		$this->connection = $connection->open($dbhost, $dbuser, $dbpass, $db);
	}

	public function create_initial_tables() {
		$results = array();

		$results[] = Operator::Query($this->connection, 
			"CREATE TABLE `coordinates` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`location_id` int(11) NOT NULL,
			`latitude` float NOT NULL,
			`longitude` float NOT NULL,
			PRIMARY KEY ( id )
		)");

		$results[] = Operator::Query($this->connection, 
			"CREATE TABLE `locations` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`province_state` varchar(255) NOT NULL,
			`country_region` varchar(255) NOT NULL,
			PRIMARY KEY ( id )
		)");

		$results[] = Operator::Query($this->connection, 
			"CREATE TABLE `infections` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`location_id` int(11) NOT NULL,
			`date` date NOT NULL,
			`amount` int(11) NOT NULL,
			PRIMARY KEY ( id )
		)");

		return $results;
	}

	public function insert_data($data) {

		$responce = array('locations' => false, 'coordinates' => false);

		//	Fill Up Locations Table
		$sql = '';
		foreach ($data->locations as $key => $location) {
	        $province_state = '-';
	        $country_region = '-';
	        if (!empty($location['province_state'])) {
	            $province_state = htmlspecialchars($location['province_state']);
	        }
	        if (!empty($location['country_region'])) {
	            $country_region = htmlspecialchars($location['country_region']);
	        }

	      
	        $sql .= 'INSERT INTO locations (province_state, country_region) VALUES ("' . $province_state . '", "' . $country_region . '"); ';
	    }

	    if (Operator::Query($this->connection, $sql, true)) {
	    	$responce['locations'] = true;
	    }

		$this->reconnect();

		//	Fill Up Coordinates Table
		$sql = '';
		foreach ($data->coordinates as $key => $coordinate) {
	        $sql .= 'INSERT INTO coordinates (location_id, latitude, longitude) VALUES ("' . $coordinate['id'] . '", "' . $coordinate['lat'] . '", "' . $coordinate['long'] . '"); ';
	    }

		if (Operator::Query($this->connection, $sql, true)) {
	    	$responce['coordinates'] = true;
	    }

		//	Fill Up Coordinates Table
		$responce['infections'] = true;
	    foreach ($data->infections as $infections_group) {
			$this->reconnect();
			
	        $sql = ''; 
	        # code...
	        foreach ($infections_group as $infection) {
	            # code...

	            $date = date_create($infection['date']);

	            $sql .= "INSERT INTO infections (location_id, date, amount) VALUES ('" . $infection['location_id'] . "', '" . date_format($date,"Y-m-d") . "', '" . $infection['amount'] . "'); ";
	        }

	        if (!Operator::Query($this->connection, $sql, true)) {
		    	$responce['infections'] = false;
		    }
	    }

	    return $responce;
	}

	public function get_locations($raw = false, $location_id = false) {
		$locations = array();
		$data = array();

		$sql = "SELECT * from locations";
	    if ($location_id) {
	        $sql .= ' WHERE id = ' . $location_id;
	    }

	    $result = Operator::Query($this->connection, $sql);

	    if (!empty($result)) {
		    foreach ($result as $row) {
				$dropdown_row = $row['country_region'];
	            if (!empty($row['province_state'])) {
	                $dropdown_row .= ' (' . $row['province_state'] . ')';
	            }
	            $locations[$row['id']] = $dropdown_row;
		    }
	    }

	    if ($raw) {
	        return $result;
	    } else {
	        return $locations;
	    }
	}

	public function get_coordinates($location_id = false) {
		$sql = "SELECT * from coordinates";
	    if ($location_id) {
	        $sql .= ' WHERE location_id = ' . $location_id;
	    }

	    return Operator::Query($this->connection, $sql);
	}

	public function get_infections($date_from, $date_to, $location_id = false) {
	    $sql = "SELECT * FROM infections WHERE ( date BETWEEN '" . $date_from . "' AND '" . $date_to . "' )";
	    if ($location_id) {
	        $sql .= ' AND location_id = ' . $location_id;
	    }

	    return Operator::Query($this->connection, $sql);
	}

	private function reconnect() {
		$this->connection->close();
		$connection = new Connection();
		$this->connection = $connection->open($this->dbhost, $this->dbuser, $this->dbpass, $this->db);
	}

	function __destruct() {
		$this->connection->close();
    }
}

class Operator {
    public static function Query(&$connection, $sql, $multi_query = false) {
    	$result = array();
    	if ($multi_query) {
    		$query = $connection->multi_query($sql);
    	} else {
    		$query = $connection->query($sql);
    	}
    	

    	if (is_bool($query)) {
    		return $query;
    	}

		if ($query->num_rows > 0) {
			while ($row = $query->fetch_assoc()) {
				$result[$row['id']] = $row;
			}
		}

		return $result;
    }
}

class Connection {
	var $connection;

	public function open($dbhost, $dbuser, $dbpass, $db) {
	 	$this->connection = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
	 	return $this->connection;
	}

	public function close() {
		$this->connection->close();
	}
}