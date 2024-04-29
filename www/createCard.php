<?php
	//Käynnistetään sessio
	session_start();

    //Tarkistetaan sisäänkirjautuminen
    require_once "loginCheck.php";

	//Muodostetaan yhteys tietokantaan
	require_once "connection.php";

	//Muuttujat
	$errors = [];
    $type = $accountId1 = $accountId2 = $idcard = $pin = $hashedPin = NULL;

	if($_SERVER["REQUEST_METHOD"] == "POST") {
	  
        $type = $_POST["type"];
        $idCard = $_POST["cardId"];
        $pin = $_POST["pin"];
        $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
        if($type === "credit") {
            $accountId1 = $_POST["creditAccountNum"];
        } else if($type === 'debit') {
            $accountId1 = $_POST["debitAccountNum"];
        } else {
            $accountId1 = $_POST["creditAccountNum"];
            $accountId2 = $_POST["debitAccountNum"];
        }
        
        
        if (empty($type)) {
            array_push($errors, "Card type can't be empty.");
        }
        //Card Id:n tarkistukset
        if (empty($idCard)) {
            array_push($errors, "Card Id can't be empty.");
        }

        if (strlen($idCard) < 2) {
            array_push($errors, "Card Id has to be over 2 letters long.");
        }

        if (strlen($idCard) > 255) {
            array_push($errors, "Card Id has to be under 255 letters long.");
        }

        //Credit ja debit-tarkistukset

        if (($type === "credit" || $type === "debit") && empty($accountId1)) {
            array_push($errors, "Account ID can't be empty.");
        }

        if ($type === "kaksoisKortti" && (empty($accountId1) || empty($accountId2))) {
            array_push($errors, "Account ID can't be empty.");
        }

        //Pin-numeron tarkistus
        if (empty($pin)) {
            array_push($errors, "PIN can't be empty.");
        }

        if (strlen($pin) !== 4) {
            array_push($errors, "PIN has to be 4 numbers long.");
        }
        
        if (!empty($errors)) {
            $_SESSION["error"] = "An error has occured. Please check the input and try again.";
        }
        
        if (empty($errors)) {
            try {
                $sql = 'CALL new_card (:customerId, :acc1, :acc2, :cardId, :cardPin)';
            
                $variables = array(":acc1" => $accountId1, ":acc2" => $accountId2, ":cardId" => $idCard, ":cardPin" => $hashedPin, ":customerId" => $_SESSION["userID"]);
            
                $statement = $connect->prepare($sql);
                $statement->execute($variables);
                
                $result = $statement->fetchAll();
                foreach ($result as $value) {
                    if ($result !== NULL) {
                        if($value[0] === "SUCCHEESE") {
                            $_SESSION["success"] = "Card created successfully.";
                
                            header("Location: index.php");
                            exit; 
                        } else {
                            array_push($errors, $value[0]);
                        }
                    }
                }
            } catch (Exception $error) {
                echo "Error" . $error->getMessage();
            }
        }
	}

    try {
        $sql = 'SELECT account.idaccount, account.balance, account.credit FROM account 
        JOIN customer_has_account ON account.idaccount = customer_has_account.idaccount 
        JOIN customer ON customer_has_account.idcustomer = customer.idcustomer
        WHERE customer.idcustomer = :idCustomer
        ORDER BY idaccount ASC';
    
        //Muuttujia
        $statement = $connect->prepare($sql);
    
        $paramCustomerId = $_SESSION["userID"];
        $statement->bindparam(":idCustomer", $paramCustomerId);
    
        $statement->execute();
        $result = $statement->fetchAll();
    } catch (Exception $error) {
        echo "Error" . $error->getMessage();
    }
    
?>
<!DOCTYPE html>
<html lang="fi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>New Card</title>
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
		  <h1>Create New Card</h1><br>
		  <div class="form-group">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" novalidate>
				<label for="type">Card type: </label>
				<select id="type" name="type" class="form-control" required>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                    <option value="kaksoisKortti">Kaksoiskortti</option>
                </select>
				<br>
                <div id="debitAccountDiv">
                    <label for="debitAccountNum">Debit Account Number: </label>
                    <select id="debitAccountNum" name="debitAccountNum" class="form-control">
                        <?php
                            if (count($result) === 0) {
                                echo "You have no accounts.";
                            } else {
                                foreach ($result as $value) {
                                    if($value['credit'] === NULL) {
                                        echo '<option '. htmlspecialchars($value['idaccount']) . '>'. htmlspecialchars($value['idaccount']) . '</option>';
                                    }
                                }
                            }
                        ?>
                    </select>
                </div>
				<br>
                <div id="creditAccountDiv" class="hidden">
                    <label for="creditAccountNum">Credit Account Number: </label>
				    <select id="creditAccountNum" name="creditAccountNum" class="form-control">
                        <?php
                            if (count($result) === 0) {
                                echo "You have no accounts.";
                            } else {
                                foreach ($result as $value) {
                                    if($value['credit'] !== NULL) {
                                        echo '<option '. htmlspecialchars($value['idaccount']) . '>'. htmlspecialchars($value['idaccount']) . '</option>';
                                    }
                                }
                            }
                        ?>
                    </select>
                    <br>
                </div>
                <label for="cardId">Card Id: </label>
                <input type="text" class="form-control" id="cardId" name="cardId"  minlength="2" maxlength="255" required>
                <br>
                <label for="pin">Card PIN: </label>
                <input type="text" class="form-control" id="pin" name="pin"  minlength="2" maxlength="255" required>
                <br>
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

    function valueChanged() {
        if (selectElem.value === "credit") {
            document.getElementById("creditAccountDiv").classList.remove("hidden");
            document.getElementById("debitAccountDiv").classList.add("hidden");
        } else if (selectElem.value === "debit") {
            document.getElementById("creditAccountDiv").classList.add("hidden");
            document.getElementById("debitAccountDiv").classList.remove("hidden");
        } else {
            document.getElementById("creditAccountDiv").classList.remove("hidden");
            document.getElementById("debitAccountDiv").classList.remove("hidden");
        }
    }
	
	</script>
</body>
</html>