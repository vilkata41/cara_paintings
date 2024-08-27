<?php

$painting_picture = file_get_contents(strip_tags($_FILES["picture"]["tmp_name"]));

if (!$painting_picture) die("Go back, this page isn't for you.");

require_once("IMPORTANT_INFO.php"); // I get the password

$host = "devweb2022.cis.strath.ac.uk";
$user = "jwb20147"; //my username
$pass = getSQLpassword(); //set MySQL password
$dbname = $user;
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error){
    die("Connection to database failed.");
}

function safePost($conn, $name, $defaultVal){
    return isset($_POST[$name])?$conn->real_escape_string(strip_tags($_POST[$name])):$defaultVal;
}

$painting_data = array();

$painting_data[] = safePost($conn, "name", "");
$painting_data[] = safePost($conn, "DOC", "");
$painting_data[] = safePost($conn, "width", "");
$painting_data[] = safePost($conn, "height", "");
$painting_data[] = safePost($conn, "price", "");
$painting_data[] = safePost($conn, "description", "");

foreach($painting_data as $field){
    if(!$field){
        die("Data mismatch.");
    }
}

$painting_data[1] = date("Y-m-d", strtotime($painting_data[1]));
$painting_picture = $conn->real_escape_string($painting_picture);

$sql = "INSERT INTO `artList` (`id`, `name`, `date_of_completion`, `width`, `height`, `price`, `description`, `picture`)".
    "VALUES (NULL, '$painting_data[0]', '$painting_data[1]', '$painting_data[2]', '$painting_data[3]', '$painting_data[4]', '$painting_data[5]', '$painting_picture');";
if($conn->query($sql)) {
    $id = mysqli_insert_id($conn);
    echo $id;
}

else echo("Failure");

