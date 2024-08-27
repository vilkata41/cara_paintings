<?php

require_once("IMPORTANT_INFO.php"); // I get the password

$host = "devweb2022.cis.strath.ac.uk";
$user = "jwb20147"; //my username
$pass = getSQLpassword(); //set MySQL password
$dbname = $user;
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error){
    die("Connection failed.");
}

function safePost($conn, $name, $defaultVal){
    return isset($_POST[$name])?$conn->real_escape_string(strip_tags($_POST[$name])):$defaultVal;
}

$productID = safePost($conn,"id",-1);

if($productID === -1){
    die("Are you sure you should be at this page? Please go back...");
}

$sql = "SELECT * FROM `artList` WHERE `id` = $productID;";
$queryResult = $conn->query($sql);

if(!$queryResult){
    die("This item doesn't exist anymore. We are sorry.");
}
if(!$currArtwork = $queryResult->fetch_assoc()){
    die("This item doesn't exist anymore. We are sorry.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase from Cara</title>
    <link rel="stylesheet" href="form.css">
    <script>
        function checkForm(event){
            event.preventDefault();
            let form = document.getElementById("orderForm");
            let warningP = document.getElementById("warningP");
            if(!form.phone.value && !form.email.value){
                form.phone.style.border = "#e16c68 solid";
                form.email.style.border = "#e16c68 solid";
                warningP.innerHTML = "Please, enter at least one contact option (phone or email).";
                return false;
            }
            else form.submit();
        }
    </script>
</head>
<body>
<h2 class="mediumTitle">Selected product:</h2>

<div id="chosenProduct">
    <?php echo "<img src='data:image/jpg;base64,".base64_encode($currArtwork["picture"])."'/>";?>
    <div class="productDescription">
        <h1 class="artworkName"><?php echo $currArtwork["name"];?></h1>
        <p>Completion date of the artwork: <?php echo date("d F Y", strtotime($currArtwork["date_of_completion"]));?></p>
        <p>Dimensions (mm): <?php echo $currArtwork["width"];?> x <?php echo $currArtwork["height"];?></p>
        <p>Price: £<?php echo $currArtwork["price"];?></p>
        <p><?php echo $currArtwork["description"];?></p>
    </div>
</div>

<br>
<div class="clientForm">
    <h2 class="mediumTitle">To complete the order, please fill in the form.</h2>
    <p id="warningP"></p>
    <form id="orderForm" action="orderPlaced.php" method="post" onsubmit="checkForm()">
        <div id="clientFields">
            <input type="text" id="name" name="name" placeholder="Name" required>
            <input type="tel" id="phone" name="phone" placeholder="Phone Number" pattern="[0-9]*">
            <input type="email" id="email" name="email" placeholder="Email">
            <input type="text" id="address" name="address" placeholder="Postal Address" required>
        </div>
        <br>
        <input type="hidden" name="pictureId" value="<?php echo $productID;?>">
        <input type="submit" value="Order">
    </form>
</div>

<script>
    const form = document.getElementById("orderForm");
    form.addEventListener('submit', checkForm);
</script>
</body>
</html>
