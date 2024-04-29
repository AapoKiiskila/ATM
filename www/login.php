<?php
	//Käynnistetään sessio
	session_start();

	//Onko jo kirjautunut sisään
	if(isset($_SESSION["login"])) {
		header("location: index.php");
		exit;
	}

	//Muodostetaan yhteys tietokantaan
	require_once "connection.php";

	//Muuttujat
	$idCustomer = $password = "";
	$error = [];

	if($_SERVER["REQUEST_METHOD"] == "POST") {
	  
		//Onko käyttäjänimi tyhjä
		if(empty(trim($_POST["userId"]))){
			array_push($error, "Please enter the User Id.");
		} else {
			$idCustomer = trim($_POST["userId"]);
		}
		
		//Onko salasana tyhjä
		if(empty(trim($_POST["Password"]))){
			array_push($error, "Please enter the password.");
		} else {
			$password = trim($_POST["Password"]);
		}
		
		//Tietojen vahvistus
		if(empty($error)){
			$sql = "SELECT credentials.idcredentials, credentials.password, credentials.idcustomer, customer.fname, customer.lname 
			FROM credentials JOIN customer ON credentials.idcustomer = customer.idcustomer 
			WHERE credentials.idcredentials = :idcredentials LIMIT 1";
			
			if($statement = $connect->prepare($sql)){
				
				$paramId = trim($_POST["userId"]);
				
				$statement->bindparam(":idcredentials", $paramId);
				
				if($statement->execute()) {
					
					//Onko käyttäjä olemassa
					if($statement->rowCount() == 1){
						if($row = $statement->fetch()){
							
							$idCustomer = $row["idcustomer"];
							$idCredentials = $row["idcredentials"];
							$hashedPassword = $row["password"];
							$fname = $row["fname"];
							$lname = $row["lname"];
							
							//Varmistetaan salasana
							if(password_verify($password, $hashedPassword)){
								//Tiedot sessionmuuttujiin
								$_SESSION["userID"] = $idCustomer;
								$_SESSION["credentialsID"] = $idCredentials;
								$_SESSION["login"] = $idCustomer;
								$_SESSION["activity"] = time();	
								$_SESSION["name"] = $fname . " " . $lname;

								//Käyttäjä oikealle sivulle
								header("location: index.php");
								exit;
							} else {
								//Error-viesti
								array_push($error, "Wrong username or password.");
							}
						}
					} else {
						array_push($error, "Wrong username or password.");
					}
				} else {
					array_push($error, "Something went wrong. Please try again later.");
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Log In</title>
	<link rel="icon" type="image/x-icon" href="happymoney.svg">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
	
	<!--Font Awesome ikoneita-->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
	
	<link rel="stylesheet" href="stylesheet.css">
</head>

<body class="bg-dark text-white ">
	<?php include 'header.php';?>

	<main class="container text-center">


	<div class="row">
		<div class="col-md-3 mx-auto"></div>

		<div style="background-color:rgba(0, 0, 0, 0.65); padding: 30px;" class="col-md-6 mx-auto">
			<?php
			if(!empty($error)) {
				echo '<div id="error" class="alert alert-danger alert-dismissible"> ';
				for ($i = 0; $i < count($error); $i++){
					echo $error[$i] . "<br>";
				}
				echo '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
				$error = [];
			}
			?>
		  <h1>Log in</h1><br>
		  <div class="form-group">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" novalidate>
				<label for="userId">Customer Id: </label>
				<input type="text" class="form-control" id="userId" name="userId" placeholder="Id" minlength="2" maxlength="255" required>
				<br>
				<label for="Password">Password: </label>
				<input type="password" class="form-control" id="Password" name="Password" placeholder="Password" required>
				<br>
				<input class="btn btn-secondary" type="submit" name="submit" value="Submit">
			</form>
		  </div>
		  </div>
		  <div class="col-md-3 mx-auto"></div>
		</div>
	</div>
	<?php include 'footer.php' ?>
	</main>
	
	<script>
	//Muuttujat
	var error=document.getElementById("error");
	
	//Tarkistetaan, mikä alert kyseessä
	if (error) {
		setTimeout(addAnimation(error), 1);
	} else {}
	
	//Animaatiofunktio ja piilottaminen
	function addAnimation(object) {
		object.classList.add("animation")
		setTimeout(hide(object), 4990);
	}
	
	function hide(object) {
		object.classList.add("hidden");
	}
	
	</script>
</body>
</html>