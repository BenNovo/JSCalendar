<?php
	require 'database.php';
	header("Content-Type: application/json");
    session_start();
    $event = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $token = $_POST['token'];
    if(!hash_equals($token, $_SESSION['token']))
    {  
        $sendArray = array("error" => "invalid token");
        echo json_encode($sendArray);
        exit;
    }
    if(preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}/', $date) && preg_match('/^[0-9]\:[0-9]{2}\ \-\ [0-9]\:[0-9]{2}/', $time))
    {           
                $stmt = $mysqli->prepare("insert into events (event, date, time, user) values (?, ?, ?, ?)");
				if(!$stmt) {
					$sendArray = array("error" => "Query failed");
					echo json_encode($sendArray);
					exit;
				}
				$stmt->bind_param('ssss', $event, $date, $time, $_SESSION['username']);
				$stmt->execute();
				$stmt->close();
                $sendArray = array("error" => "success");
                echo json_encode($sendArray);
				exit;
    }
    else
    {
        $sendArray = array("error" => "follow the required format for date: ##/##/#### and time: #:## - #:##");
        echo json_encode($sendArray);
		exit;
    }
?>