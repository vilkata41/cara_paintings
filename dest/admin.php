<?php
session_set_cookie_params(["samesite" => "lax"]);
session_start();
ob_start();

$encryptedPassword="d56963fbad09a2b894c7cf6ed6fe3cd5";
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

$orders_sql = "SELECT * FROM `orders`;";
$allOrders = $conn->query($orders_sql);

if(!$allOrders){
    die("Sorry, something went wrong trying to find the orders.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="admin.css">
    <title>Cara's Admin Page</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        function showItem(id) {
            document.getElementById(id).style.display = "block";
            if (id === "paintingsList") {
                const ordList = document.getElementById("ordersList");
                ordList.style.filter = "blur(2px)";
                let buttons = ordList.getElementsByTagName("button");
                for (let b of buttons){
                    b.disabled = true;
                }
            }
        }

        function hideItem(id){
            document.getElementById(id).style.display = "none";
            if (id === "paintingsList") {
                const ordList = document.getElementById("ordersList");
                ordList.style.filter = "none";

                let buttons = ordList.getElementsByTagName("button");
                for (let b of buttons) {
                    b.disabled = false;
                }
            }
        }

        function newPaintingForm(id){ // I get the painting form div from a php file on the server using ajax
            const element = document.getElementById(id);
            fetch("newPaintingForm.php",
                {
                    headers:{
                        'Content-type': 'text/html'
                    },
                    method: 'POST',
                    body: JSON.stringify({"source": "admin.php"})
                })
                .then(resp => {
                    if(resp.status === 200) return resp.text();
                })
                .then(div => {
                    element.innerHTML = div.toString();
                    element.style.display = "block";
                })
                .catch(() => {alert("Something went wrong when looking for the form.")});
        }

        function addNewPainting(){
            let valid = document.getElementById("newPaintingForm").reportValidity();
            let errPara = document.getElementById("errorsMessage");
            errPara.innerHTML = ""
            if(!valid) {
                errPara.innerHTML += "All fields are required. ";
                return;
            }

            let enteredDate = new Date(document.getElementById("DOC").value);
            let now = new Date();
            if(now < enteredDate) {
                errPara.innerHTML += "Please, enter a date that's not in the future. ";
                return;
            }

            if(isNaN(document.getElementById("width").value) || isNaN(document.getElementById("height").value) || isNaN(document.getElementById("price").value)){
                errPara.innerHTML += "Width, Height, and Price need to be numeric values. ";
                return;
            }

            let file = document.querySelector('#newPaintingForm>input[name="picture"]');
            if(file.files[0].size > 5000000){
                errPara.innerHTML += "Images need to be under 5MB. ";
                return;
            }

            const formElem = document.getElementById("newPaintingForm");
            const picFormArray = $("#newPaintingForm").serializeArray();
            const picturesTBody = document.querySelector("#paintingsTable>tbody");
            let newPicId;
            document.getElementById("submitPainting").disabled = true; // doing this to avoid double submissions
            fetch("addNewPainting.php",{
                method: "POST",
                body: new FormData(formElem)
            })
                .then(response => {
                    if(response.status !== 200) alert("Something went wrong when creating a new picture.");
                    return response.text();
                })
                .then(id => {
                    if(id === "Failure" || id === "Connection to database failed." || id === "Data mismatch.") throw 'problem';
                    else newPicId = id;
                })
                .then(() => {
                    let newRow = picturesTBody.insertRow();

                    for(let i = 0; i < 6; i++){
                        newRow.insertCell().append(picFormArray[i].value);
                    }
                    if(newPicId) {
                        let allNewRows = picturesTBody.getElementsByTagName('tr');
                        allNewRows[allNewRows.length - 1].id = "picture_"+newPicId;
                        let name = picFormArray[0].value;
                        newRow.insertCell().innerHTML = "<button class='deleteButton' onclick=deletePicture(" + newPicId + ",'" + name + "')> Delete </button>";
                    }
                    let paintDiv = document.getElementById("addPaintingDiv");
                    paintDiv.innerHTML = ""; //remove the form after adding item.
                    paintDiv.style.display = "none";

                })
                .catch(() => {
                    alert("Something went wrong when creating a new picture. ");
                });
        }

        function deleteOrder(id) {
            if (confirm("Are you sure you want to delete the order with id " + id + "?") === true) {
                fetch("deleteOrder.php",
                    {
                        headers: {
                            'Content-type': 'plain/text'
                        },
                        method: 'POST',
                        body: JSON.stringify({"orderID": id})
                    })
                    .then(response => {
                        if (response.status !== 200) alert("Something went wrong trying to delete this row.");
                    })
                    .then(() => {
                        document.getElementById("order_" + id).remove()
                    })
                    .catch(() => {
                        alert("Something went wrong trying to delete this row.")
                    });
            }
        }

        function deletePicture(id,name) {
            if (confirm("Are you sure you want to delete " + name + "?") === true) {
                fetch("deletePainting.php",
                    {
                        headers: {
                            'Content-type': 'plain/text'
                        },
                        method: 'POST',
                        body: JSON.stringify({"pictureID": id})
                    })
                    .then(response => {
                        if (response.status !== 200) alert("Something went wrong trying to delete this row.");
                    })
                    .then(() => {
                        document.getElementById("picture_" + id).remove()
                    })
                    .catch(() => alert("Something went wrong trying to delete this row."))
            }
        }

    </script>
</head>
<body>

<?php
$stage = safePost($conn, "stage", "1");
if($stage === "1"){
    ?>
        <div class="sign-in">
            <form method="post" action="admin.php">
                <input type="hidden" name="stage" id="stage" value="2">
                <input type="password" name="enteredPassword" id="enteredPassword" placeholder="Password">
                <input type="submit" value="Sign in">
            </form>
        </div>
    <?php
}

else{
$enteredPassword = safePost($conn,"enteredPassword","");
    if($stage === "2" && md5($enteredPassword) !== $encryptedPassword){
        echo "<p> Access denied.</p>";
    }
    else{
        session_regenerate_id();
    ?>

<main>
    <div id="ordersList">
        <button class="hide/show" onclick="showItem('paintingsList')">Show Paintings</button>
        <table id="orders">
            <h2 class="section_title">Orders</h2>
            <tbody>
                <tr>
                    <th>Order ID</th>
                    <th>Client Name</th>
                    <th>Phone Number</th>
                    <th>Email Address</th>
                    <th>Delivery Address</th>
                    <th>Picture Name</th>
                    <th>Picture Price</th>
                    <th>Picture Sizes</th>
                </tr>
                <?php

                while($curr_row = $allOrders->fetch_assoc()){
                    ?>
                <tr id="order_<?php echo$curr_row["order_id"]?>">
                    <td data-label="ID"><?php echo $curr_row["order_id"]?></td>
                    <td data-label="Name"><?php echo $curr_row["name"]?></td>
                    <td data-label="Phone"><?php echo $curr_row["phone"]?></td>
                    <td data-label="Email"><?php echo $curr_row["email"]?></td>
                    <td data-label="Address"><?php echo $curr_row["address"]?></td>

                <?php //Here, I will try to get picture data, if it fails, I just leave NONEXISTENT in the cell.
                $curr_picID = $curr_row["picture_id"];
                $picture_sql = "SELECT *  FROM `artList` WHERE `id` = $curr_picID;";
                $picture_result = $conn->query($picture_sql);
                if($picture_result && ($curr_picture = $picture_result->fetch_assoc())){
                ?>
                    <td data-label="Picture"> <?php echo $curr_picture["name"]?> </td>
                    <td data-label="Price"> <?php echo $curr_picture["price"]?> </td>
                    <td data-label="Size"> <?php echo $curr_picture["width"]?>mm x <?php echo $curr_picture["height"]?>mm </td>
                <?php
                }

                else{
                    ?>
                    <td>NONEXISTENT</td>
                    <td>NONEXISTENT</td>
                    <td>NONEXISTENT</td>
                    <?php
                }?>
                    <td><button class="deleteButton" onclick="deleteOrder(<?php echo $curr_row["order_id"];?>)">Delete</button></td>
                </tr>
                    <?php
                }?>
            </tbody>
        </table>
    </div>

    <div id="paintingsList">
        <button class="hide/show" onclick="hideItem('paintingsList')">Hide Paintings</button>
        <button class="newButton" onclick="newPaintingForm('addPaintingDiv')">New</button>
        <div id="addPaintingDiv"></div>
        <table id="paintingsTable">
            <tr>
                <th>Picture Name</th>
                <th>Date of Completion</th>
                <th>Width</th>
                <th>Height</th>
                <th>Price</th>
                <th>Description</th>
            </tr>
            <?php //we fill the table with the data that's already there

            $allPics_sql = "SELECT * FROM `artList`;";
            $allPics = $conn->query($allPics_sql);

            if(!$allPics){
                ?>
        </table>
        <h2 class="section_title">The query has been corrupted.</h2>
            <?php
            }
            else{
                while($curr_picture = $allPics->fetch_assoc()){
                    ?>
                    <tr id="picture_<?php echo $curr_picture["id"];?>">
                        <td><?php echo $curr_picture["name"]?></td>
                        <td><?php echo $curr_picture["date_of_completion"]?></td>
                        <td><?php echo $curr_picture["width"]?></td>
                        <td><?php echo $curr_picture["height"]?></td>
                        <td><?php echo $curr_picture["price"]?></td>
                        <td><?php echo $curr_picture["description"]?></td>
                        <td><button class="deleteButton" onclick="deletePicture(<?php echo $curr_picture["id"];?>,'<?php echo $curr_picture["name"];?>')">
                                Delete
                            </button></td>
                    </tr>
                    <?php
                }?>
            </table>
            <?php } ?>
        <button class="hide/show" onclick="hideItem('paintingsList')">Hide Paintings</button>
    </div>
</main>

    <?php }}?>

<script>
    let formDiv = document.getElementById("addPaintingDiv");
    if(formDiv){
        formDiv.addEventListener('change',(event) => { // the change is listening for file change in all input fields
            if(event.target === document.getElementById("picture")){
                // if we're changing the picture upload input field, we change its display.
                let label = document.getElementById("pictureLabel");
                label.innerHTML = "Picture selected.";
            }
        });
    }

</script>
</body>
</html>
