<?php
	require 'database.php';
	header("Content-Type: application/json");
	session_start();
	$token = $_POST['token'];
    if(!hash_equals($token, $_SESSION['token']))
    {  
        $sendArray = array("error" => "invalid token");
        echo json_encode($sendArray);
        exit;
    }
	$stmt = $mysqli->prepare("select event, date, time from events where user =?");
				if(!$stmt) {
					$sendArray = array("error" => "Query failed");
					echo json_encode($sendArray);
					exit;
				}
				$stmt->bind_param('s', $_SESSION['username']);
				$stmt->execute();
				$stmt->bind_result($event, $date, $time);
				$isEmpty = true;
				while($stmt->fetch())
				{
					$dataArray[$date] = $event . " " . $time;
					$isEmpty = false;
				}
				if($isEmpty)
				{
					$dataArray = array("error" => "error");
				}
				$stmt->close();
				echo json_encode($dataArray);
				exit;
 ?>
				