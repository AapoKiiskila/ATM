<?php

session_start();

require_once "connection.php";

$unsuccessful = 0;

if (isset($_GET['delID'])) {
	$id = $_GET['delID'];
	
    try {
        $sql = 'SELECT card.idcard FROM customer 
        JOIN card ON customer.idcustomer = card.idcustomer
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
        if($value['idcard'] === $id) {
	
            $sql = "CALL delete_card (:id)";
            
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
        $_SESSION["error"] = 'The card in question cannot be removed.';
        header("Location: index.php");
        exit;
    }
} else {
	header("Location: index.php");
	exit;
}

?>