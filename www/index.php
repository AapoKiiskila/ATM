<?php 
//Aloitetaan session
session_start();

//Tarkistetaan sisäänkirjautuminen
require_once "loginCheck.php";

//Haetaan yheys tietokantaan
require_once "connection.php";

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

try {
	$sql = 'SELECT card.idcard, account.idaccount FROM customer 
	JOIN card ON customer.idcustomer = card.idcustomer
	JOIN card_has_account ON card.idcard = card_has_account.idcard
	JOIN account ON card_has_account.idaccount = account.idaccount
	WHERE customer.idcustomer = :idCustomer';

	//Muuttujia
	$statement = $connect->prepare($sql);

	$paramCustomerId = $_SESSION["userID"];
	$statement->bindparam(":idCustomer", $paramCustomerId);

	$statement->execute();
	$result2 = $statement->fetchAll();
} catch (Exception $error) {
	echo "Error" . $error->getMessage();
}

?>

<!DOCTYPE html>
<html lang="fi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Bank</title>
	<link rel="icon" type="image/x-icon" href="happymoney.svg">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
	
	<!--Font Awesome ikoneita-->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
	
	<!-- Datatables -->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.12.1/r-2.3.0/datatables.min.css"/>
 
	<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.12.1/r-2.3.0/datatables.min.js"></script>

	<!-- Oma stylesheet -->
	<link rel="stylesheet" href="stylesheet.css">
</head>
<body class="bg-dark text-white ">
	<?php include 'header.php';?>
	
	<main class="container text-center">


	<div class="row">
		<div class="col-md-3 mx-auto"></div>

		<div style="background-color:rgba(0, 0, 0, 0.65); padding: 30px;" class="col-md-6 mx-auto">
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
			<div class="form-group">
			
			</div>
			<br>
			<h1>Accounts</h1>
			<br>
			<table id="table" class="table table-dark table-striped">
				<thead>
					<tr class="font-weight-bold">
						<td>Account ID</td>
						<td>Balance</td>
						<td>Credit</td>
					</tr>
				</thead>
				<tbody>
				<?php 
					if (count($result) === 0) {
						echo "<tr><td colspan='3'>You have no accounts.</td></tr>";
					} else {
						foreach ($result as $value) {
							echo '<tr onclick="window.location=\'accountDetails.php?id=' . htmlspecialchars($value['idaccount']) . '\'"><td>' . htmlspecialchars($value['idaccount']) . '</td>
							<td>' . htmlspecialchars($value['balance']) . "</td>
							<td>";
							if($value['credit'] === NULL) {
								echo "No credit";
							} else {
								echo htmlspecialchars($value['credit']);
							}
							echo "</td>
							</tr>";
						}
					}
				?>
				</tbody>
			</table><br><br>
			<h1>All Cards</h1>
			<table id="cardTable" class="table table-dark table-striped">
				<thead>
					<tr class="font-weight-bold">
						<td>Account ID</td>
						<td>Card ID</td>
						<td>Remove card</td>
					</tr>
				</thead>
				<tbody>
				<?php 
					if (count($result2) === 0) {
						echo "<tr><td colspan='2'>You have no cards.</td></tr>";
					} else {
						foreach ($result2 as $value) {
							echo '<tr><td>' . htmlspecialchars($value['idaccount']) . '</td>
							<td>' . htmlspecialchars($value['idcard']) . '</td>
							<td><a href=deleteCard.php?delID=' . htmlspecialchars($value['idcard']) . ' role="button" class="btn btn-danger"  onClick="return confirm(\'Are you sure you want to delete this card?\')">Delete</a></td>
							</tr>';
						}
					}
				?>
				</tbody>
			</table><br><br>
			<button onclick="location.href='createAccount.php';" style="margin-right: 20px" class="btn btn-info">New account</button>
			<button onclick="location.href='createCard.php';" class="btn btn-info">New card</button>
		</div>

		<div class="col-md-3 mx-auto"></div>
	</div>

	</main>
	
	<script>	
	$(document).ready(function () {
		new DataTable('#table');
		new DataTable('#cardTable');
	});
	
	</script>
</body>
</html>