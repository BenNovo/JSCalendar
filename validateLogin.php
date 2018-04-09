<?php
	require 'database.php';
	header("Content-Type: application/json");
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	$action = $_POST['action'];
	$error = false;
	$sendArray = null;
	//Make sure that all variables are set and valid
	
	if(isset($username, $password, $action)) {
		if(preg_match('/^[\w_\.\-]+$/', $username) && preg_match('/^[\w_\.\-]+$/', $password) && preg_match('/^[\w_\.\-]+$/', $action)){
			//Run a query to get all the usernames in the database
			$stmt = $mysqli->prepare("select username from users");
			if(!$stmt) {
				$sendArray = array("error" => "Query failed");
				echo json_encode($sendArray);
				exit;
			}
			$stmt->execute();
			$stmt->bind_result($userDatabase);
			
			//Create a new user if that is what the user wants
			if($action == 'createUser'){
				
				//Make sure that no other user already has this username
				while($stmt->fetch()) {
					if($username == $userDatabase) {
						$sendArray = array("error" => "Username already exists");
						echo json_encode($sendArray);
						$stmt->close();
						exit;
					}
				}
				$stmt->close();
				
				//Insert this user's username and password into the table
				$stmt = $mysqli->prepare("insert into users (username, hashpass) values (?, ?)");
				if(!$stmt) {
					$sendArray = array("error" => "Query failed");
					echo json_encode($sendArray);
					exit;
				}
				$stmt->bind_param('ss', $username, password_hash($password, PASSWORD_DEFAULT));
				$stmt->execute();
				$stmt->close();
				
				//Start the session and assign variables
				session_start();
				$_SESSION['username'] = $username;
				$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
				
				//Make the array to send data back to the website
				$sendArray = array ("error" => "success", "token" => $_SESSION['token'], "username" => $_SESSION['username']);
				echo json_encode($sendArray);
				exit;
			}
			
			//Attempt to log into an existing user
			else {
				
				//Find the correct username
				$found = false;
				while($stmt->fetch()) {
					if($username == $userDatabase) {
						$found = true;
						break;
					}
				}
				$stmt->close();
				if(!$found) {
					$sendArray = array("error" => "Username not found");
					echo json_encode($sendArray);
					exit;
				}
				
				//Get the password of that user.		
				$stmt = $mysqli->prepare("select hashpass from users where username = \"" . $username . "\"");
				if(!$stmt) {
					$sendArray = array("error" => "Query failed");
					echo json_encode($sendArray);
					exit;
				}
				$stmt->execute();
				$stmt->bind_result($passwordDatabase);
				$stmt->fetch();
				
				//Check to see if the password is correct, then login if it is
				if(password_verify($password, $passwordDatabase)) {
					//Create the session
					session_start();
					$_SESSION['username'] = $username;
					$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));		
					$sendArray = array("error" => "success", "token" => $_SESSION['token'], "username" => $_SESSION['username']);
					echo json_encode($sendArray);
					exit;
				}
				else {
					$sendArray = array("error" => "Password does not match");
					echo json_encode($sendArray);
					exit;
				}
			}
		}
		else {
			//The user entered invalid characters in the username or password
			$sendArray = array("error" => "Invalid characters used");
			echo json_encode($sendArray);
			exit;
		}
	}
	else if (isset($_POST['logOut'])) {
		session_start();
		session_destroy();
		exit;
	}
	else {
		//The username, password, or the radio buttons were not entered
		$sendArray = array("error" => "One or more fields has not been entered");
		echo json_encode($sendArray);
		exit;
	}
?>