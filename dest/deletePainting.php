<?php
$post_result = (file_get_contents("php://input"));
if (!$post_result) die("Please, leave this page immediately.");

$result = json_decode(stripslashes($post_result));
$pictureID = $result->pictureID;
if(!$pictureID) die("orderid not gathered");

require_once("IMPORTANT_INFO.php"); // I get the password

$host = "devweb2022.cis.strath.ac.uk";
$user = "jwb20147"; //my username
$pass = getSQLpassword(); //set MySQL password
$dbname = $user;
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error){
    die("Connection to database failed.");
}

$pictureID = $conn->real_escape_string($pictureID);

$sql = "DELETE FROM `artList` WHERE `artList`.`id` = $pictureID;";
$conn->query($sql);