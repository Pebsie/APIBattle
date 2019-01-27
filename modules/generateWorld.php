<?php
    require "../connect.php";
    require "../editor.php";

    echo "Generating world... ";
    for ($i=1; $i <= 100*100; $i++) {
        $sql = "INSERT INTO world (buildingType, username, units) VALUES ('Grass', 'Mother Nature', 0);";
        $query = $pdo->prepare($sql);
        $query->execute();

        if (rand(1,10) == 1) {
            build($pdo, "Forest", "Mother Nature", $i, NULL, 0);
        } elseif (rand(1,250) == 1) {
            build($pdo, "Mine", "Mother Nature", $i, NULL, 0);
        }

    }

    echo "done.";
?>