<?php

header("Content-type: text/html");
$post_result = (file_get_contents("php://input"));
if (!$post_result) die("Please, leave this page immediately.");

// First of all, I need to connect to MYSQL database.
require_once("IMPORTANT_INFO.php"); // I get the password

$host = "devweb2022.cis.strath.ac.uk";
$user = "jwb20147"; //my username
$pass = getSQLpassword(); //set MySQL password
$dbname = $user;
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error){
    die("Connection failed.");
}

$json_decoded = json_decode(stripslashes($post_result));
$curr_page = $json_decoded->currPage;
$paintingsPerBatch = $json_decoded->batchLength;
$startFrom = $json_decoded->startFrom;

$sql = "SELECT * FROM `artList` ORDER BY `id` LIMIT $paintingsPerBatch OFFSET $startFrom;";

if(!($result = $conn->query($sql))){
    die("We are terribly sorry. Something went wrong with our database.");
}

$allRows = $result->num_rows;
$morePaintings = $paintingsPerBatch;

while(($painting = $result->fetch_assoc()) && ($morePaintings > 0)){

    /*    For every painting, I will generate a div with its data. There will be a hidden form in the end.
        I will only pass the ID of the painting and then fetch the data in the form.php file for the specific picture.
        I only do this because it is not convenient to pass all the data in the request when I can just re-fetch it
        from the database.
    */

    $formatted_date = date("d F Y", strtotime($painting["date_of_completion"]));
    ?>
    <div class="singlePainting">
        <?php echo "<img src='data:image/jpg;base64,".base64_encode($painting["picture"])."' alt='".$painting["name"]."'/>";?>
        <h1><?php echo $painting["name"];?></h1>
        <p class="pricePara">Price: £<?php echo $painting["price"];?></p>
        <p class="datePara">Created on: <?php echo $formatted_date;?></p>
        <br>
        <p>Width (mm): <?php echo $painting["width"];?></p>
        <p>Height (mm): <?php echo $painting["height"];?></p>
        <p><?php echo $painting["description"];?></p>

        <form method="post" action="form.php">
            <input type="hidden" name="id" value="<?php echo $painting["id"];?>">
            <input type="submit" value="Purchase">
        </form>
    </div>
    <?php
    $morePaintings--;
}
?>