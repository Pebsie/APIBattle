<?php
    require "connect.php";
    require "editor.php";

    $sql = "SELECT * FROM world";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $row) {

        // echo "Tile #".$row['id']." (owned by ".$row['username']." with ".$row['units']." stationed soldiers) ";
        $stmt = $pdo->prepare("SELECT * FROM buildings WHERE buildingType='".$row["buildingType"]."';");
        $stmt->execute();
        $buildingData = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM player WHERE username='".$row["username"]."';");
        $stmt->execute();
        $pl = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($row['buildingType'] == "Building") {
            $data = explode(',', $row['special']);
            $time = (int) $data[0];

            $time--;
            // echo "is building a ".$data[1]." and will be complete in ".$time." cycles. ";
            if ($time < 1) {

                build($pdo, $data[1], $row['username'], $row['id'], "", 1);
                // echo "Construction is complete!";

            } else {

                build($pdo, "Building", $row['username'], $row['id'], $time.",".$data[1], 0);

            }

        } else {

            if ($buildingData['attribute'] == "unit") {
                if ($pl['pop'] >= $buildingData['depositValue'] && $row['units'] <= 100) {
                    build($pdo, $row['buildingType'], $row['username'], $row['id'], "", $buildingData['depositValue']);
                    $sql = "UPDATE player SET pop-=".$buildingData['depositValue']." WHERE username='".$pl['username']."';";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                }
            } else {
                $sql = "UPDATE player SET ".$buildingData['attribute']."=".($pl[$buildingData['attribute']]+$buildingData['depositValue'])." WHERE username='".$row['username']."';";
                echo $sql;
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }

        }

        // echo "<br />";

    }

    $sql = "SELECT * FROM player";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $row) {


        $newFood = $row['food']-$row['pop'];
        if ($newFood < 0) {
            $row['pop']-= abs($newFood);
            $newFood = 0;
        }
        $newGold = floor($row['gold']+$row['pop']);

        $sql = "UPDATE player SET pop=".$row['pop'].", food=".$newFood.", gold=".$newGold." WHERE username='".$row['username']."';";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
