<?
include_once 'includes/database.php';

// Create database object instance
$db = new Database('localhost', 'root', '', 'exacaster');

//	Create Initial Tables
$result = $db->create_initial_tables();
$success = true;
foreach ($result as $key => $value) {
	if (!$value) {
		switch ($key) {
			case 0:
				printf('Failed to create `coordinates` table.<br />');
				$success = false;

				break;
			
			case 1:
				printf('Failed to create `locations` table.<br />');
				$success = false;

				break;
			case 2:
				printf('Failed to create `infections` table.<br />');
				$success = false;

				break;
		}
	}
}

if ($success) {
	printf('Initial database tables created.<br />');
}

//	Parse Current Data
$data = new stdClass();
$data->locations = array();
$data->coordinates = array();
$data->infections = array();

parse_data($data, "https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Confirmed.csv");

//	Insert Parsed Data Into Our Database
$responce = $db->insert_data($data);

if ($responce['locations']) {
	printf('Locations table filled successfully.<br />');
} else {
	printf('Error filling locations table.<br />');
}

if ($responce['coordinates']) {
	printf('Coordinates table filled successfully.<br />');
} else {
	printf('Error filling coordinates table.<br />');
}

if ($responce['infections']) {
	printf('Infections table filled successfully.<br />');
} else {
	printf('Error filling infections table.<br />');
}

function parse_data(&$data, $resource) {
    $data_source = file_get_contents($resource);
    $source_rows = str_getcsv($data_source, "\n", "'");

    $dates = array();

    foreach($source_rows as $id => $row) {  
        $item_row = str_getcsv($row, ',');

        if ($id === 0) {
            foreach ($item_row as $item_key => $item_info) {
                if ($item_key >= 4) {
                    $dates[] = $item_info;
                }
            }
            continue;
        }
  
        $location = array('id' => $id);
        $coordinate = array('id' => $id);
        $infections = array();
        $dates_iterator = 0;
        foreach ($item_row as $item_key => $item_info) {
            switch ($item_key) {
                case 0:
                    $location['province_state'] = $item_info;
                    break;
                case 1:
                     $location['country_region'] = $item_info;

                    break;
                case 2:
                    $coordinate['lat'] = $item_info;

                    break;
                case 3:
                    $coordinate['long'] = $item_info;

                    break;
                default:
                    $infections[] = array(
                        'location_id' => $id,
                        'date' => $dates[$dates_iterator++],
                        'amount' => $item_info
                    );
            }
        }

        $data->locations[$id] = $location;
        $data->coordinates[$id] = $coordinate;
        $data->infections[$id] = $infections;
    }  
}

?>			
