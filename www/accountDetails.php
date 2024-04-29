<?php 

//Aloitetaan session
session_start();

//Tarkistetaan sisäänkirjautuminen
require_once "loginCheck.php";

//Haetaan yheys tietokantaan
require_once "connection.php";

$result=[];

try {
	if (isset($_GET["id"])) {
		$sql = 'SELECT * FROM account 
        JOIN customer_has_account ON account.idaccount = customer_has_account.idaccount 
        JOIN customer ON customer_has_account.idcustomer = customer.idcustomer
        WHERE customer.idcustomer = :idCustomer 
        AND account.idaccount = :idAccount
        LIMIT 1';

        //Muuttujia
        $statement = $connect->prepare($sql);

        $paramCustomerId = $_SESSION["userID"];
        $paramAccountId = $_GET["id"];
        $statement->bindparam(":idCustomer", $paramCustomerId);
        $statement->bindparam(":idAccount", $paramAccountId);

        $statement->execute();
        $result = $statement->fetchAll();
	}
} catch (Exception $error) {
	echo "Error" . $error->getMessage();
}

try {
	if (isset($_GET["id"])) {
		$sql = 'SELECT * FROM withdrawal 
        JOIN account ON withdrawal.idaccount = account.idaccount
        JOIN customer_has_account ON account.idaccount = customer_has_account.idaccount 
        JOIN customer ON customer_has_account.idcustomer = customer.idcustomer
        WHERE customer.idcustomer = :idCustomer 
        AND withdrawal.idaccount = :idAccount';

        //Muuttujia
        $statement = $connect->prepare($sql);

        $paramCustomerId = $_SESSION["userID"];
        $paramAccountId = $_GET["id"];
        $statement->bindparam(":idCustomer", $paramCustomerId);
        $statement->bindparam(":idAccount", $paramAccountId);

        $statement->execute();
        $result2 = $statement->fetchAll();
	}
} catch (Exception $error) {
	echo "Error" . $error->getMessage();
}

try {
	$sql = 'SELECT card.idcard, account.idaccount FROM customer 
	JOIN card ON customer.idcustomer = card.idcustomer
	JOIN card_has_account ON card.idcard = card_has_account.idcard
	JOIN account ON card_has_account.idaccount = account.idaccount
	WHERE customer.idcustomer = :idCustomer
    AND account.idaccount = :idAccount';

	//Muuttujia
	$statement = $connect->prepare($sql);

	$paramCustomerId = $_SESSION["userID"];
    $paramAccountId = $_GET["id"];
	$statement->bindparam(":idCustomer", $paramCustomerId);
    $statement->bindparam(":idAccount", $paramAccountId);

	$statement->execute();
	$result3 = $statement->fetchAll();
} catch (Exception $error) {
	echo "Error" . $error->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Account Details</title>
    <link rel="icon" type="image/x-icon" href="happymoney.svg">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
	
	<!--Font Awesome ikoneita-->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
	<link rel="stylesheet" href="stylesheet.css">

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
			<?php
			if(!empty($_SESSION["error"])) {
				echo '<div id="error2" class="alert alert-danger alert-dismissible"> ' . $_SESSION["error"] . '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
				$_SESSION["error"] = "";
			}
			?>
            <h3>Account details</h3>
		    <div class="form-group">
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
                        if ($result === NULL) {
                            echo "You have no accounts.";
                        } else {
                            foreach ($result as $value) {
                                echo '<tr><td>' . htmlspecialchars($value['idaccount']) . '</td>
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
                <h3>Cards connected to account</h3>
                <br>
                <table id="cardTable" class="table table-dark table-striped">
                    <thead>
                        <tr class="font-weight-bold">
                            <td>Account ID</td>
                            <td>Card ID</td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        if (count($result3) === 0) {
                            echo "<tr><td colspan='2'>You have no cards on this account.</td></tr>";
                        } else {
                            foreach ($result3 as $value) {
                                echo '<tr><td>' . htmlspecialchars($value['idaccount']) . '</td>
                                <td>' . htmlspecialchars($value['idcard']) . "</td>
                                </tr>";
                            }
                        }
                    ?>
                    </tbody>
			    </table><br><br><br>
                <h3>Withdrawals</h3>
                <br>
                <table id="withdrawalTable" class="table table-dark table-striped">
                    <thead>
                        <tr class="font-weight-bold">
                            <td>Amount</td>
                            <td>Timestamp</td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        if (count($result2) === 0) {
                            echo "<tr><td colspan='2'>You have no withdrawals on this account.</td></tr>";
                        } else {
                            foreach ($result2 as $value) {
                                echo '<tr>
                                <td>' . htmlspecialchars($value['amount']) . '</td>
                                <td>' . htmlspecialchars($value['timestamp']) . "</td>
                                </tr>";
                            }
                        }
                    ?>
                    </tbody>
                </table><br><br>
                <a href="<?php echo "deleteAccount.php?delID=" . $result[0]["idaccount"]; ?>" role="button" class="btn btn-danger"  onClick="return confirm('Are you sure you want to delete this account and the cards associated with it?')">Delete</a>
		    </div>
			
		  </div>
		  <div class="col-md-3 mx-auto"></div>
		</div>

	</div>

	</main>
	
	<script>
        $(document).ready(function () {
            $('#withdrawalTable').DataTable();
        });
	</script>
</body>
</html>