<?php
	//Käynnistetään sessio
	session_start();

    //Tarkistetaan sisäänkirjautuminen
    require_once "loginCheck.php";

	//Muodostetaan yhteys tietokantaan
	require_once "connection.php";

	//Muuttujat
	$errors = [];
    $fname = $lname = $phone = $address = "";

	if($_SERVER["REQUEST_METHOD"] == "POST") {
	  
        $fname = $_POST["fname"];
        $lname = $_POST["lname"];
        $phone = $_POST["phone"];
        $address = $_POST["address"];
        
        if (empty($fname)) {
            array_push($errors, "First name can't be empty.");
        }
        
        if (empty($lname)) {
            array_push($errors, "Last name can't be empty.");
        }
        
        if (strlen($fname) < 2) {
            array_push($errors, "First name has to be over 2 letters long.");
        }
        
        if (strlen($fname) > 255) {
            array_push($errors, "First name has to be under 255 letters long.");
        }

        if (strlen($lname) < 2) {
            array_push($errors, "Last name has to be over 2 letters long.");
        }
        
        if (strlen($lname) > 255) {
            array_push($errors, "Last name has to be under 255 letters long.");
        }

        if (empty($address)) {
            array_push($errors, "Address can't be empty.");
        }
        
        if (strlen($address) < 2) {
            array_push($errors, "Address has to be over 2 letters long.");
        }
        
        if (strlen($address) > 255) {
            array_push($errors, "Address has to be under 255 letters long.");
        }

        if (empty($phone)) {
            array_push($errors, "Phone number can't be empty.");
        }
        
        if (strlen($phone) < 2) {
            array_push($errors, "Phone number has to be over 2 numbers long.");
        }
        
        if (strlen($phone) > 255) {
            array_push($errors, "Phone number has to be under 255 numbers long.");
        }

        if (!empty($errors)) {
            $_SESSION["error"] = "An error has occured. Please check the input and try again.";
        }
        
        if (empty($errors)) {
            try {
                $sql = "UPDATE customer SET fname=:fname, lname=:lname, phone_number=:phone, address=:address WHERE idcustomer=:id";
            
                $variables = array(":fname" => $fname, ":lname" => $lname, ":phone" => $phone, ":address" => $address, ":id" => $_SESSION["userID"]);
            
                $statement = $connect->prepare($sql);
                $statement->execute($variables);
                
                $_SESSION["updatesuccess"] = "Information updated successfully.";
                
                header("Location: ownDetails.php");
                exit; 
            } catch (Exception $error) {
                echo "Error" . $error->getMessage();
            }
        }
	} else {
        try {
            $sql = "SELECT credentials.idcredentials, credentials.password, credentials.idcustomer, customer.fname, customer.lname, customer.phone_number, customer.birthdate, customer.address 
            FROM credentials 
            JOIN customer ON credentials.idcustomer = customer.idcustomer 
            WHERE credentials.idcustomer = :idCustomer LIMIT 1";
        
            //Muuttujia
            $statement = $connect->prepare($sql);
        
            $paramCustomerId = $_SESSION["userID"];
            $statement->bindparam(":idCustomer", $paramCustomerId);
        
            $statement->execute();
            $result = $statement->fetch();
            $fname = $result["fname"];
            $lname = $result["lname"];
            $phone = $result["phone_number"];
            $address = $result["address"];
        } catch (Exception $error) {
            echo "Error" . $error->getMessage();
        }
    }
?>
<!DOCTYPE html>
<html lang="fi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Own Details</title>
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
			if(!empty($errors)) {
				echo '<div id="error" class="alert alert-danger alert-dismissible"> ';
				for ($i = 0; $i < count($errors); $i++){
					echo $errors[$i] . "<br>";
				}
				echo '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
				$errors = [];
                $_SESSION["error"] = [];
			}
			?>
		  <h1>Log in</h1><br>
		  <div class="form-group">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" novalidate>
				<label for="fname">First name: </label>
				<input type="text" class="form-control" id="fname" name="fname" minlength="2" maxlength="255" required value="<?php echo $fname; ?>">
				<br>
				<label for="lname">Last name: </label>
				<input type="text" class="form-control" id="lname" name="lname" minlength="2" maxlength="255" required value="<?php echo $lname; ?>">
				<br>
                <label for="phone">Phone number: </label>
				<input type="text" class="form-control" id="phone" name="phone" minlength="2" maxlength="255" required value="<?php echo $phone; ?>">
				<br>
                <label for="address">Address: </label>
				<input type="text" class="form-control" id="address" name="address" minlength="2" maxlength="255" required value="<?php echo $address; ?>">
				<br>
                <input class="btn btn-secondary" name="discard" value="Discard changes" onclick="window.location='ownDetails.php'">
				<input class="btn btn-success" type="submit" name="submit" value="Save changes">
			</form>
		  </div>
		  </div>
		  <div class="col-md-3 mx-auto"></div>
		</div>

	</div>

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