<?php
    /* EDITOR 
    This file contains functions that are useful for adding things to the base game such as buildings. */

    function addBuilding($pdo, $buildingType, $goldCost, $woodCost, $stoneCost, $timeToBuild, $timeToDeposit, $attribute, $depositValue, $hp, $requirement) {
        $sql = "INSERT INTO buildings (buildingType, goldCost, woodCost, stoneCost, timeToBuild, timeToDeposit, attribute, depositValue, hp, requirement) VALUES ('".$buildingType."', ".$goldCost.", ".$woodCost.", ".$stoneCost.", ".$timeToBuild.", ".$timeToDeposit.", '".$attribute."', ".$depositValue.", ".$hp.", '".$requirement."'));";
        $query = $pdo->prepare($sql);
        $query->execute();
        echo "Added ".$buildingType." <br />";
    }

?>