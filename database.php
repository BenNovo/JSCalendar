<?php
	$mysqli = new mysqli('localhost','Ben','Guitar44','CalendarDatabase');
	if($mysqli->connect_errno) {
		printf("Connection error: %s", $mysqli->connect_error);
		exit;
	}
?>