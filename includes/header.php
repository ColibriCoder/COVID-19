<?
	include_once 'config.php';
	include_once 'database.php';

	$db = new Database($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASSWORD, $_DATABASE_TITLE);
	$locations = $db->get_locations();

?>			
<!DOCTYPE html>
<html>
	<head>
		<title>Exacaster</title>
        <style>
            table, th, td {
              border: 1px solid black;
            }
        </style>
	</head> 
	<body>