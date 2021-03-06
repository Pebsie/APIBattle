<?php
    require "../connect.php";
    require "../editor.php";
    $log = array();
    echo "Generating world... ";
    for ($i=1; $i <= 100*100; $i++) {
        $sql = "INSERT INTO world (buildingType, username, units) VALUES ('Grass', 'Mother Nature', 0);";
        $query = $pdo->prepare($sql);
        $query->execute();

        if (($log[$i-1] == "Forest" || $log[$i-100] == "Forest") && rand(1,100) < 35) {
            build($pdo, "Forest", "Mother Nature", $i, NULL, 0);
            $log[$i] = "Forest";
        } elseif (($log[$i-1] == "Skeleton" || $log[$i-100] == "Skeleton") && rand(1,100) < 50) {
            build($pdo, "Skeleton", "Dark World", $i, NULL, rand(100,350));
            $log[$i] = "Skeleton";
        } elseif (rand(1,10) == 1) {
            build($pdo, "Forest", "Mother Nature", $i, NULL, 0);
            $log[$i] = "Forest";
        } elseif (rand(1,1500) == 1)  {
            build($pdo, "Skeleton", "Dark World", $i, NULL, rand(100,350));
            $log[$i] = "Skeleton";
        } elseif (rand(1,250) == 1) {
            build($pdo, "Mine", "Mother Nature", $i, NULL, 0);
            $log[$i] = "Mine";
        }

    }

    echo "done.";
?>