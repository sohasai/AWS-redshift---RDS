<?php
	// RDS Connectivity //
	$mysql_host = "instacart.ck12z0ni2znt.us-east-2.rds.amazonaws.com";

	$mysql_user = "admin";
	$mysql_pwd = "cs527group7";
	$mysql_db = "instacart";
	
	// $redshift_endpoint //
	$redshift_endpoint = "host=redshift-cluster-1.clypxjjaqru2.us-east-2.redshift.amazonaws.com port=5439 dbname=instacart user=cs527user password=MyRedshift123";
	// 
		
	if(isset($_POST['doaction']) && $_POST['doaction'] == 'csvexport') {		
		$sqlQuery = $_REQUEST['textArea'];
		$choice = $_REQUEST['radio'];
		
		
		if ($choice == 1) {
			// MySQLi connection to the database through the database credentials
			$link = mysqli_connect($mysql_host, $mysql_user, $mysql_pwd, $mysql_db, 3306);

			// Error handling for a failed connection to the database
			if(!$link){
				echo "Connection Failed. Reason :".mysqli_connect_error();
			}

			// Incase of successful connection to the MySQL Database
			else{

				// Query the database and store the results and calculates the time taken for the query to be executed.
				$time_start = microtime(true);
				$results = mysqli_query($link, $sqlQuery);
				$time_end = microtime(true);

				// Error handling incase the query doesn't yield results
				if (!$results) {
					die('Could not query:' . mysqli_error());
				}
				
				
				$arrColumns = array();
				$arrRows = array();
				// Loop to print the headers of the query result
				while ($fieldinfo=mysqli_fetch_field($results)){
					$arrColumns[] = $fieldinfo->name;
				}

				// Fetch the number of fields in the result to be used as loop variable in future
				$count = mysqli_num_fields($results);

				// Fetch each individual row as an array to be printed to the frontend
				while($row = mysqli_fetch_array($results)) {					
					$tmp = array();
					for ($x = 0; $x < $count; $x++) {
						$tmp[] = $row[$x];
					}
					$arrRows[] = $tmp;
				}
				
				header('Content-type: text/csv');
				header('Content-Disposition: attachment; filename="query-data.csv"');
				 
				// do not cache the file
				header('Pragma: no-cache');
				header('Expires: 0');
				$file = fopen('php://output', 'w');
				fputcsv($file, $arrColumns);
				foreach ($arrRows as $row) {
					fputcsv($file, $row);
				}
				fclose($file);
				exit();
			}
		} else if ($choice == 2) {
			$connection = pg_connect($redshift_endpoint);

			// Pinging the DB to ensure connection status
			if(!pg_ping($connection)){
				die("Connection to DB Broken");
			}
			
			// Query the database and store the results and calculates the time taken for the query to be executed.
			$time_start = microtime(true);
			$results = pg_query($connection, $sqlQuery);
			
			if (!$results) {
				die('Could not query:' . pg_last_error($connection));
			}
			
			// Fetch the number of fields in the result to be used as loop variable in future
			$count = pg_num_fields($results);

			$arrColumns = array();
			$arrRows = array();
			
			// Loop to print the headers of the query result
			for ($x = 0; $x < $count; $x++) {
				$arrColumns[] = pg_field_name($results, $x);
			}

			// Fetch each individual row as an array to be printed to the frontend
			while ($row = pg_fetch_array($results)) {
				$tmp = array();
				// Loops through the row and prints each field under the respective field
				for ($x = 0; $x < $count; $x++) {
					$tmp[] = $row[$x];
				}
				$arrRows[] = $tmp;
			}
			
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="Redshift-data.csv"');
			 
			// do not cache the file
			header('Pragma: no-cache');
			header('Expires: 0');
			$file = fopen('php://output', 'w');
			fputcsv($file, $arrColumns);
			foreach ($arrRows as $row) {
				fputcsv($file, $row);
			}
			fclose($file);
			exit();
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Db Sys For Data Science Project</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="images/icons/database.png"/>
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
	
	<script>
		function csvexport() {
			document.frm.submit();
		}
	</script>
</head>
<body>
	<div class="bg-contact2" style="background-image: url('images/bg-26.png');">
		<div class="container-contact2">
			<div class="wrap-contact2" style="text-align: center;border:1px solid #000;">
				<form name="frm" method="post">
					<input type="hidden" name="doaction" value="csvexport">
					<input type="hidden" name="textArea" value="<?=$_REQUEST['textArea']?>">
					<input type="hidden" name="radio" value="<?=$_REQUEST['radio']?>">
				</form>	
				<div align='right'>
					<a href="index.html">Go Home</a> || <a href="javascript:;" onClick="javascript:history.back();">Go Back</a> || <a href="javascript:;" onClick="javascript:csvexport();">Download CSV</a>
				</div>
				<div style="float: left;">
					<?php 
					// Accepts the query and choice of database from the user and stores them in variables
					$sqlQuery = $_REQUEST['textArea'];
					$choice = $_REQUEST['radio'];
					
					// Conditional statements to check for users choice and execute the relevant database connections. For MySQL.
					if ($choice == "1") {


						// MySQLi connection to the database through the database credentials
						$link = mysqli_connect($mysql_host, $mysql_user, $mysql_pwd, $mysql_db, 3306);


						// Error handling for a failed connection to the database
						if(!$link){
							echo "Connection Failed. Reason :".mysqli_connect_error();
						}

						// Incase of successful connection to the MySQL Database
						else{

							// Query the database and store the results and calculates the time taken for the query to be executed.
							$time_start = microtime(true);
							$results = mysqli_query($link, $sqlQuery);
							$time_end = microtime(true);
							//prints the time taken to execute query
							$time = $time_end - $time_start;

							// Error handling incase the query doesn't yield results
							if (!$results) {
								echo ('<br><br><b>Could not query: </b>'). "<BR><BR>";
								echo "<b>Error No: </b>". mysqli_errno($link) . "<BR>";
								echo "<b>Error Details: </b>". mysqli_error($link) . "<BR>";
								exit;
							}

							// HTML tags for the result table
							echo "<div style='text-align: center'>";
							echo "<div style='padding-bottom: 5px; font-weight:bold;font-size:16px;'>Query Output</div>";
							//echo "<div>Execution time: <b>".$time." seconds</b></div>";

							echo "<table border='1' align='center' cellspacing='5' cellpadding='5'>";
							echo "<tr>";

							// Loop to print the headers of the query result
							while ($fieldinfo=mysqli_fetch_field($results)){
								printf("<th style='text-align:center'>%s</th>",$fieldinfo->name);
							}
							echo "</tr>";

							// Fetch the number of fields in the result to be used as loop variable in future
							$count = mysqli_num_fields($results);
							echo "<div>Execution Time: <b>".$time." seconds</b></div><br>";
							// Fetch each individual row as an array to be printed to the frontend
							while($row = mysqli_fetch_array($results)) {

								echo "<tr>";
								// Loops through the row and prints each field under the respective field
								for ($x = 0; $x < $count; $x++) {
									echo "<td>".$row[$x]."</td>";
								} 
								echo "</tr>";
							}
							echo "</table>";
							echo "</div>";
							
							echo "<br>";
							

						}
					}

					// Code block to run query on AWS Redshift
					else{

						// PostgreSQL command to connect to AWS Redshift Cluster
						// $connection = pg_connect("host=redshift-cluster-1.ctlxufsutwdi.us-east-1.redshift.amazonaws.com port=5439 dbname=instacart user=ttomar password=RutgersSavita21");
						
						$connection = pg_connect($redshift_endpoint);

						// Pinging the DB to ensure connection status
						if(!pg_ping($connection)){
							die("Connection to DB Broken");
						}

						
						// Query the database and store the results and calculates the time taken for the query to be executed.
						$time_start = microtime(true);
						$results = pg_query($connection, $sqlQuery);
						$time_end = microtime(true);
						// Error handling incase the query doesn't yield results
						if (!$results) {
							die('Could not query:' . pg_last_error($connection));
						}
						// HTML tags for the result table
						echo "<div style='text-align: center'>";
						echo "<table border='1' align='center' cellspacing='5' cellpadding='5'>";
						echo "<tr>";

						// Fetch the number of fields in the result to be used as loop variable in future
						$count = pg_num_fields($results);

						// Loop to print the headers of the query result
						for ($x = 0; $x < $count; $x++) {
							echo "<th style='text-align: center'>".pg_field_name($results, $x)."</th>";
						}
						echo "</tr>";
						//prints the time taken to execute query
						$time = $time_end - $time_start;
						echo "<div style='padding-bottom: 5px; font-weight:bold;font-size:16px;'>Query Output</div>";
						echo "<div>Execution Time: <b>".$time." seconds</b></div><br>";
						// Fetch each individual row as an array to be printed to the frontend
						while ($row = pg_fetch_array($results)) {
							echo "<tr>";
							// Loops through the row and prints each field under the respective field
							for ($x = 0; $x < $count; $x++) {
								echo "<td>".$row[$x]."</td>";
							}
							echo "</tr>";
						}
						echo "</table>";
						echo "</div>";
						
					}

					?>
				</div>
			</div>
		</div>
	</div>

	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="vendor/select2/select2.min.js"></script>
	<script src="js/main.js"></script>

</body>
</html>
