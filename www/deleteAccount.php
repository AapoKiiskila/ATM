<?php

session_start();

require_once "connection.php";

$unsuccessful = 0;

if (isset($_GET['delID'])) {
	$id = $_GET['delID'];
	
    try {
        $sql = 'SELECT account.idaccount FROM customer 
        JOIN customer_has_account ON customer.idcustomer = customer_has_account.idcustomer
        JOIN account ON customer_has_account.idaccount = account.idaccount
        WHERE customer.idcustomer = :idCustomer';
    
        //Muuttujia
        $statement = $connect->prepare($sql);
    
        $paramCustomerId = $_SESSION["userID"];
        $statement->bindparam(":idCustomer", $paramCustomerId);
    
        $statement->execute();
        $result = $statement->fetchAll();
    } catch (Exception $error) {
        echo "Error" . $error->getMessage();
    }

    foreach ($result as $value) {
        if($value['idaccount'] == $id) {
            $sql = "CALL delete_account (:id)";
            
            $statement = $connect->prepare($sql);
            $statement->bindParam(':id', $id);
            $statement->execute();
	
            $_SESSION["deleteSuccess"] = "Item deleted successfully.";
            $unsuccessful = 0;
            header("Location: index.php");
            exit;
        } else {
            $unsuccessful = 1;
        }
    }

    if ($unsuccessful === 1) {
        $_SESSION["error"] = 'The account in question cannot be removed.';
        header("Location: index.php");
        exit;
    }
} else {
	header("Location: index.php");
	exit;
}

?>