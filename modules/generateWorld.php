<?php
    require "../connect.php";
    require "../editor.php";

    for ($i=1; $i <= 100*100; $i++) {
        $sql = "INSERT INTO buildings (buildingType, owner) VALUES ('Grass', 'Mother Nature');";
        $query = $pdo->prepare($sql);
        $query->execute();

        if (rand(1,10) == 1) {
            build($pdo, "Forest", "Mother Nature", $i, NULL);
        } elseif (rand(1,250) == 1) {
            build($pdo, "Mine", "Mother Nature", $i, NULL);
        }

    }
?>