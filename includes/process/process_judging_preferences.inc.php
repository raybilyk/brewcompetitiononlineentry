<?php 
/*
 * Module:      process_judging_preferences.inc.php
 * Description: This module does all the heavy lifting for adding/editing info in the "judging preferences" table
 */
 
if (((isset($_SESSION['loginUsername'])) && ($_SESSION['userLevel'] <= 1))  || ($section == "setup")) {
	
	if ($action == "add") {
		
	}
	
	if (($action == "edit") || ($section == "setup")) {
	
	// Empty the prefs session variable
	// Will trigger the session to reset the variables in common.db.php upon reload after redirect
	session_name($prefix_session);
	session_start();
	unset($_SESSION['prefs'.$prefix_session]);
	
	if (isset($_POST['jPrefsBottleNum'])) $jPrefsBottleNum = $_POST['jPrefsBottleNum'];
	else $jPrefsBottleNum = "";
	
	if ($_POST['jPrefsQueued'] == "N") $flight_ent = $_POST['jPrefsFlightEntries']; else $flight_ent = $_SESSION['jPrefsFlightEntries'];
	$updateSQL = sprintf("UPDATE $judging_preferences_db_table SET
					 
	jPrefsQueued=%s,
	jPrefsFlightEntries=%s,
	jPrefsMaxBOS=%s,
	jPrefsRounds=%s,
	jPrefsBottleNum=%s,
	jPrefsCapStewards=%s,
	jPrefsCapJudges=%s
	WHERE id=%s",
					   GetSQLValueString($_POST['jPrefsQueued'], "text"),
					   GetSQLValueString($_POST['jPrefsFlightEntries'], "int"),
					   GetSQLValueString($_POST['jPrefsMaxBOS'], "int"),
					   GetSQLValueString($_POST['jPrefsRounds'], "int"),
					   GetSQLValueString($jPrefsBottleNum, "int"),
					   GetSQLValueString($_POST['jPrefsCapStewards'], "int"),
					   GetSQLValueString($_POST['jPrefsCapJudges'], "int"),
					   GetSQLValueString($id, "int"));
					   
	mysqli_real_escape_string($connection,$updateSQL);
	$result = mysqli_query($connection,$updateSQL) or die (mysqli_error($connection));
	
	if ($section == "setup") {
		
		// Lock down the config file
		//if (@chmod("/site/config.php", 0555)) $message = "success"; else $message = "chmod";
		
		$updateSQL = sprintf("UPDATE %s SET setup='1', setup_last_step='8' WHERE id='1'",$prefix."system");			   
		mysqli_real_escape_string($connection,$updateSQL);
		$result = mysqli_query($connection,$updateSQL) or die (mysqli_error($connection));
		
		if (HOSTED) {
			
			if (strpos($_SERVER['SERVER_NAME'],"brewcomp.com") !== false) $server = "brewcomp.com";
			elseif (strpos($_SERVER['SERVER_NAME'],"brewcompetition.com") !== false) $server = "brewcompetition.com";
			else $server = "brewcompetition.com";
			
			// For hosted version: email prost@brewcompetition.com to alert when setup has been completed.
			$to_email = "prost@brewcompetition.com";
			$subject = "BCOEM Setup Completed for ".$_SERVER['SERVER_NAME'];
			$message = "<html>" . "\r\n";
			$message .= "<body>
						<p>BCOEM Setup Completed for http://".$_SERVER['SERVER_NAME']."</p>
						<p>Be sure to change setup_free_access to FALSE</p>
						</body>" . "\r\n";
			$message .= "</html>";
			
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "To: BCOEM Admin <prost@brewcompetition.com>, " . "\r\n";
			$headers .= "From: BCOEM Server <noreply@".$server.">" . "\r\n";
			
			mail($to_email, $subject, $message, $headers);
		}
		
		session_unset();
		session_destroy();
		session_write_close();
		session_regenerate_id(true);
		header(sprintf("Location: %s", $base_url."index.php?msg=16")); 
	}
	
	else {
		$pattern = array('\'', '"');
		$updateGoTo = str_replace($pattern, "", $updateGoTo); 
		header(sprintf("Location: %s", stripslashes($updateGoTo)));
		}
	}
} else echo "<p>Not available.</p>";
?>