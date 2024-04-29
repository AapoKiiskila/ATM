<?php
	//Käynnistetään sessio
	session_start();

    //Tarkistetaan sisäänkirjautuminen
    require_once "loginCheck.php";

	//Muodostetaan yhteys tietokantaan
	require_once "connection.php";

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
    } catch (Exception $error) {
        echo "Error" . $error->getMessage();
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
		<div class="col-md-2 mx-auto"></div>

		<div style="background-color:rgba(0, 0, 0, 0.65); padding: 30px;" class="col-md-8 mx-auto">
            <?php if(!empty($_SESSION["success"])) {
                    echo '<div id="success" class="alert alert-success alert-dismissible"> ' . $_SESSION["success"] . '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                    $_SESSION["success"] = "";
                }
                
                if(!empty($_SESSION["error"])) {
                    echo '<div id="error" class="alert alert-danger alert-dismissible"> ' . $_SESSION["error"] . '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                    $_SESSION["error"] = "";
                }
                
                if(!empty($_SESSION["updatesuccess"])) {
                    echo '<div id="update" class="alert alert-success alert-dismissible"> ' . $_SESSION["updatesuccess"] . '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                    $_SESSION["updatesuccess"] = "";
                }
                
                if(!empty($_SESSION["deleteSuccess"])) {
                    echo '<div id="deleted" class="alert alert-success alert-dismissible"> ' . $_SESSION["deleteSuccess"] . '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                    $_SESSION["deleteSuccess"] = "";
                }
			?>
		  <h1>User information</h1><br>
		  <div class="form-group">
            <table id="table" class="table table-dark table-striped">
                <thead>
					<tr class="font-weight-bold">
						<td>First name</td>
						<td>Last name</td>
						<td>Phone number</td>
                        <td>Date of birth</td>
                        <td>Address</td>
					</tr>
				</thead>
				<tbody>
                <?php 
					if ($result === null) {
						echo "An error has occured. Try again later.";
					} else {
                        echo '<tr><td>' . htmlspecialchars($result['fname']) . '</td>
                        <td>' . htmlspecialchars($result['lname']) . "</td>
                        <td>" . htmlspecialchars($result['phone_number']) . "</td>
                        <td>" . htmlspecialchars($result['birthdate']) . "</td>
                        <td>" . htmlspecialchars($result['address']) . "</td>
                        </tr>";
					}
				?>
				</tbody>
			</table><br><br>
            <a href="editOwnDetails.php">Edit user information</a>
		  </div>
		  </div>
		  <div class="col-md-2 mx-auto"></div>
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