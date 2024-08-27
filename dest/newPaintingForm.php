<?php

header("Content-type: text/html");

$post_result = (file_get_contents("php://input"));
if (!$post_result) die("Go back, this page isn't for you.");
?>
<p id="errorsMessage"></p>
<form id="newPaintingForm" enctype="multipart/form-data">
    <input type='text' id='name' name='name' placeholder="Picture title" required>
    <label for="DOC">Date of Completion</label>
    <input type='date' id='DOC' name='DOC' required>
    <input type='text' id='width' name='width' placeholder="Width (mm)" required>
    <input type='text' id='height' name='height' placeholder="Height (mm)" required>
    <input type='text' id='price' name='price' placeholder="Price" required>
    <textarea id='description' name='description' rows=5 cols=50 placeholder="Description" required></textarea>
    <input type="file" id="picture" name="picture" accept="image/jpeg" class="inputImage" required>
    <label id="pictureLabel" for="picture">Upload a picture...</label>
    <input type="button" id="submitPainting" class='newButton' value='Add' onclick='addNewPainting()'>
    <input type="button" class='deleteButton' value='Cancel' onclick=hideItem('addPaintingDiv')>
</form>