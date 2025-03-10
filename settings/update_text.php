<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['homepageText'])) {
    $homepageText = $_POST['homepageText'];
    $file = 'homepage_text.txt';

    if (file_put_contents($file, $homepageText)) {
        echo "Homepage text updated successfully.";
    } else {
        echo "Failed to update homepage text.";
    }
}
