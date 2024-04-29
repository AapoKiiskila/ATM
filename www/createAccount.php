<?php
	//Käynnistetään sessio
	session_start();

    //Tarkistetaan sisäänkirjautuminen
    require_once "loginCheck.php";

	//Muodostetaan yhteys tietokantaan
	require_once "connection.php";

	//Muuttujat
	$errors = [];
    $type = $balance = $credit = "";
	$balance = 0;
	$credit = NULL;

	if($_SERVER["REQUEST_METHOD"] == "POST") {
	  
        $type = $_POST["type"];
        if($type === "credit") {
			$credit = 0;
        //     $credit= $_POST["credit"];
        //     $balance = $_POST["balance"];
        } else {
			$credit = NULL;
        //     $balance = $_POST["balance"];
        //     $credit = NULL;
        }
        
        
        if (empty($type)) {
            array_push($errors, "Account type can't be empty.");
        }
        
        // if (empty($balance)) {
        //     array_push($errors, "Balance can't be empty.");
        // }

        // if (strlen($balance) > 8) {
        //     array_push($errors, "Balance has to be under 8 numbers long.");
        // }

        // if ($type === "credit" && empty($credit)) {
        //     array_push($errors, "Credit can't be empty.");
        // }

        // if ($type === "credit" && strlen($credit) > 8) {
        //     array_push($errors, "Credit has to be under 8 numbers long.");
        // }

        if (!empty($errors)) {
            $_SESSION["error"] = "An error has occured. Please check the input and try again.";
        }
        
        if (empty($errors)) {
            try {
                $sql = "CALL new_account (:customerId, :balance, :credit)";
            
                $variables = array(":balance" => $balance, ":credit" => $credit, ":customerId" => $_SESSION["userID"]);
            
                $statement = $connect->prepare($sql);
                $statement->execute($variables);
                
                $_SESSION["success"] = "Account created successfully.";
                
                header("Location: index.php");
                exit; 
            } catch (Exception $error) {
                echo "Error" . $error->getMessage();
            }
        }
	}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>New Account</title>
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
		  <h1>Create New Account</h1><br>
		  <div class="form-group">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" novalidate>
				<label for="type">Account type </label>
				<select id="type" name="type" class="form-control" required>
                    <option value="noCredit">No Credit</option>
                    <option value="credit">Credit</option>
                </select>
				<br>
				<!-- <label for="balance">Balance: </label>
				<input type="number" class="form-control" id="balance" name="balance" required>
				<br>
                <div id="creditDiv" class="hidden">
                    <label for="credit">Credit: </label>
				    <input type="number" class="form-control" id="credit" name="credit">
                </div>
                <br> -->
                <input class="btn btn-outline-danger" type="button" name="cancel" value="Cancel" onclick="window.location='index.php'">
				<input class="btn btn-success" type="submit" name="submit" value="Submit">
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

    //Tarkistetaan select-elementin muuttuvuus
    var selectElem = document.getElementById("type");
    selectElem.addEventListener("change", valueChanged);

    /*function valueChanged() {
        if (selectElem.value === "credit") {
            document.getElementById("creditDiv").classList.remove("hidden");
        } else {
            document.getElementById("creditDiv").classList.add("hidden");
        }
    }*/
	
	</script>
</body>
</html>