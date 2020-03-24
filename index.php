<?  include_once 'includes/header.php'; ?>
	<form id="filter">
	    <label for="countries">Country/Region:</label>
	    <select id="countries">
		<option value="0">-- SELECT --</option>
		<? 
		    foreach ($locations as $key => $location) {
			echo '<option value="' . $key . '">' . $location .'</option>';
		    }
		?>
	    </select>

	    <br /><br />
	    <label for="date_from">From:</label>
	    <input type="date" id="date-from" name="date_from">

	    <label for="date_to">To:</label>
	    <input type="date" id="date-to" name="date_to">
	    <br /><br />
	    <input id="filter-submit" type="submit" name="" value="Filter" />
	    <br /><br />
	</form>

	<div id="infections-table"></div>  
<?  include_once 'includes/footer.php'; ?>
 
