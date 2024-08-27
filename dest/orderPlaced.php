<?php
require_once("IMPORTANT_INFO.php"); // I get the password

$host = "devweb2022.cis.strath.ac.uk";
$user = "jwb20147"; //my username
$pass = getSQLpassword(); //set MySQL password
$dbname = $user;
$conn = new mysqli($host, $user, $pass, $dbname);

function safePost($conn, $name, $defaultVal){
    return isset($_POST[$name])?$conn->real_escape_string(strip_tags($_POST[$name])):$defaultVal;
}

if ($conn->connect_error){
    die("Connection to database failed.");
}

$pictureID = safePost($conn,"pictureId",-1);

if($pictureID === -1){
    die("Are you sure you should be at this page? Please go back...");
}

$clientName = safePost($conn,"name","");
$clientPhone = safePost($conn,"phone","");
$clientEmail = safePost($conn,"email","");
$clientAddress = safePost($conn,"address","");

$sql = "INSERT INTO `orders` (`order_id`, `name`, `phone`, `email`, `address`, `picture_id`) 
                      VALUES (NULL, '$clientName', '$clientPhone', '$clientEmail', '$clientAddress', '$pictureID');";

if(!$conn->query($sql)){
    die("Sorry, something went wrong. Try again later.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="form.css">
    <title>Order placed</title>
</head>
<body>
<div id="orderPlaced">
    <h1>Thank You for placing Your order, <?php echo $clientName;?>! We will be in touch with you shortly.</h1>
    <a href="index.php">Back to Main Page</a>
</div>
</body>
</html>
