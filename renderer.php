<?
include_once 'includes/config.php';
include_once 'includes/database.php';

$from = $_GET['from'];
$to = $_GET['to'];
$location_id = $_GET['location_id'];

$db = new Database($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASSWORD, $_DATABASE_TITLE);

$locations = $db->get_locations(true, $location_id);
$coordinates = $db->get_coordinates($location_id);
$infections = $db->get_infections($from, $to, $location_id);

$rows = '';
$formated_data = array();

foreach ($locations as $location) {
	$formated_data[$location['id']] = array(
		'country_region' => htmlspecialchars($location['country_region']), 
		'province_state' => htmlspecialchars($location['province_state'])
	);
}

foreach ($coordinates as $coordinate) {
	$formated_data[$coordinate['location_id']]['latitude'] = $coordinate['latitude'];
	$formated_data[$coordinate['location_id']]['longitude'] = $coordinate['longitude'];	
}

foreach ($infections as $key => $infection) {
	$formated_data[$infection['location_id']]['infections'][$infection['date']] = $infection['amount'];
}

$html = '';
$rows = '';
foreach ($formated_data as $key => $data_row) {
	$country_region = '-';
	$province_state = '-';
	$latitude = '-';
	$longitude = '-';
	if (!empty($data_row['country_region'])) {
		$country_region = $data_row['country_region'];
	}
	if (!empty($data_row['province_state'])) {
		$province_state = $data_row['province_state'];
	}
	if (!empty($data_row['latitude'])) {
		$latitude = $data_row['latitude'];
	}
	if (!empty($data_row['longitude'])) {
		$longitude = $data_row['longitude'];
	}
	if (!empty($data_row['amount'])) {
		$amount = $data_row['amount'];
	}

	$infections_dates_html = '';
	$infections_amount_html = '';

	if (!empty($data_row['infections'] )) {
		foreach ($data_row['infections'] as $date => $amount) {
			$infections_dates_html .= '<th>Infections (' . $date . ')</th>';
			$infections_amount_html .= '<td>' . $amount . '</td>';
	
		}
	}

	$rows .= '<tr>
		<td>' . $country_region . '</td>
		<td>' . $province_state . '</td>
		<td>' . $latitude . '</td>
		<td>' . $longitude . '</td>
		' . $infections_amount_html . '
	</tr>';
}

function generate_table($rows = '', $dates = '') {
	return '
 		<table style="width:100%;">
          <tr>
            <th>Country/Region</th>
            <th>Province/State</th>
            <th>Latitude</th>
            <th>Longitude</th>
            ' . $dates . '
          </tr>
          ' . $rows . '
        </table>
	';
}

echo json_encode(array('data' => generate_table($rows, $infections_dates_html)));
