<?php
    /* EDITOR 
    This file contains functions that are useful for adding things to the base game such as buildings. */

    function addBuilding($pdo, $buildingType, $goldCost, $woodCost, $stoneCost, $timeToBuild, $timeToDeposit, $attribute, $depositValue, $hp, $requirement, $impassable) {
        if (!$impassable) {
            $impassable = "false";
        }
        $sql = "INSERT INTO buildings (buildingType, goldCost, woodCost, stoneCost, timeToBuild, timeToDeposit, attribute, depositValue, hp, requirement, impassable) VALUES ('".$buildingType."', ".$goldCost.", ".$woodCost.", ".$stoneCost.", ".$timeToBuild.", ".$timeToDeposit.", '".$attribute."', ".$depositValue.", ".$hp.", '".$requirement."', '".$impassable."');";
        $query = $pdo->prepare($sql);
        $query->execute();
        echo "Added ".$buildingType." <br />";
    }

    function build($pdo, $buildingType, $owner, $position, $special, $units) {
     
        $stmt = $pdo->prepare("SELECT * FROM world WHERE id=".$position.";");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "UPDATE world SET buildingType='".$buildingType."', username='".$owner."', units=".($row['units'] + $units).", special='".$special."' WHERE id=".$position.";";
        $query = $pdo->prepare($sql); 
        $query->execute();

       // echo "Built ".$buildingType." at position ".$position." (owned by ".$owner.").";
    }

?>