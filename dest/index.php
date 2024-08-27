<?php
/**
 * Created by IntelliJ IDEA.
 * User: Vilian Popov
 * Date: 19 November 2022
 */

require_once("IMPORTANT_INFO.php"); // I get the password

$host = "devweb2022.cis.strath.ac.uk";
$user = "jwb20147"; //my username
$pass = getSQLpassword(); //set MySQL password
$dbname = $user;
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error){
    die("Connection failed.");
}

$sql = "SELECT * FROM `artList`;";

if(!($result = $conn->query($sql))){
    die("We are terribly sorry. Something went wrong with our database.");
}

$paintingsPerPage = 12;
$allRows = $result->num_rows;
$maxPages = ceil($allRows/$paintingsPerPage);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Cara's Store</title>
    <script>
        function generatePaintingPages(){
            let currPage = document.getElementById("pageNumber").getAttribute("value");
            const maxPage = document.getElementById("pageLimit").getAttribute("value");
            const pictureDiv = document.getElementById("paintings");
            const paintingsPerPage = 12;

            let imagesGenerated = 0;
            const paintingsPerBatch = 4;
            let startFromID = (currPage - 1) * paintingsPerPage;
            pictureDiv.innerHTML = "";

            // I will be loading the picture divs in batches of 4.
            // Firstly, the client sees the first 4 pictures. While they are looking at them, the others are
            // already being fetched from the server until the page loads completely.

            while (imagesGenerated < 12){
                fetch("generatePaintings.php",{
                    headers:{
                        'Content-type': 'text/html'
                    },
                    method: 'POST',
                    body: JSON.stringify({"currPage":currPage, "batchLength": paintingsPerBatch, "startFrom":startFromID})
                })
                    .then(resp => {
                        if(resp.status !== 200) alert("Something went wrong gathering the photos.");
                        else return resp.text();
                    })
                    .then(htmlTXT =>{
                        pictureDiv.innerHTML += htmlTXT.toString();
                    })
                    .catch(() => {
                        alert("Something went terribly wrong. We apologise.")
                    });
                if((currPage - 1) <= 0) document.getElementById("backButton").disabled = true;
                if((currPage + 1) > maxPage)document.getElementById("nextButton").disabled = true;
                imagesGenerated += paintingsPerBatch;
                startFromID += paintingsPerBatch;
            }
        }

        function prevPage(){
            document.getElementById("nextButton").disabled = false;
            const pageNumElement = document.getElementById("pageNumber");
            let prevPage = parseInt(pageNumElement.getAttribute("value")) - 1;
            pageNumElement.setAttribute("value", prevPage.toString());
            generatePaintingPages();
        }

        function nextPage() {
            document.getElementById("backButton").disabled = false;
            const pageNumElement = document.getElementById("pageNumber");
            let nextPage = parseInt(pageNumElement.getAttribute("value")) + 1;
            pageNumElement.setAttribute("value", nextPage.toString());
            generatePaintingPages();
        }

    </script>
</head>
<body>
<header>
    <div class="mainH">
        <h1>Cara's Paintings Store</h1>
    </div>
</header>

<main>
    <div id="outerPaintingContainer">
        <h1>All Paintings</h1>
        <div id="paintings"></div>
    </div>

    <div class="pageSelector">
        <input type="hidden" id="pageLimit" value="<?php echo $maxPages; ?>">
        <input type="hidden" id="pageNumber" value="1">
        <button id="backButton" onclick="prevPage()" disabled>Back</button>
        <button id="nextButton" onclick="nextPage()">Next</button>
    </div>
</main>

<script>
    window.addEventListener('load', generatePaintingPages);
</script>
</body>
</html>