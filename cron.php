<?php
    require "connect.php";

    $sql = "SELECT * FROM world";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $stmt = $pdo->prepare("SELECT * FROM buildings WHERE buildingType='".$row["buildingType"]."';");
        $stmt->execute();
        $buildingData = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM player WHERE username='".$row["username"]."';");
        $stmt->execute();
        $pl = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($row['buildingType'] == "Building") {
            $data = explode(",", $row['special']);
            $time = (int) $data[1];
            $time--;
            if ($time < 1) {

                require "editor.php";
                build($pdo, $data[2], $row['username'], $row['id'], "", 1);

            } else {

                build($pdo, "Building", $row['username'], $row['id'], $time.",".$data[2], 0);

            }

        } else {

            if ($buildingData['attribute'] == "unit") {
                if ($pl['pop'] > $buildingData['depositValue']) {
                    build($pdo, $row['buildingType'], $row['username'], $row['id'], "", $buildingData['depositValue']);
                    $sql = "UPDATE player SET pop-=".$buildingData['depositValue']." WHERE username='".$pl['username']."';";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                }
            } else {
                $sql = "UPDATE player SET ".$buildingData['attribute']."+=".$buildingData['depositValue']." WHERE username='".$row['username']."';";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }

        }

    }

    $sql = "SELECT * FROM player";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $newFood = $row['food']-$row['pop'];
        if ($newFood < 0) {
            $row['pop']-=$newFood;
            $newFood = 0;
        }
        $newGold = floor($row['gold']+$row['pop']*0.2);

        $sql = "UPDATE player SET pop=".$row['pop'].", food=".$newFood.", gold=".$newGold." WHERE username='".$row['username']."';";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
