<?php
    require "../connect.php";
    require "../editor.php";

    for ($i=1; $i <= 100*100; $i++) {

        if (rand(1,5) == 1) {
            build($pdo, "Forest", "Mother Nature", $i, "");
        } elseif (rand(1,10) == 1) {
            build($pdo, "Mine", "Mother Nature", $i, "");
        }

    }
?>