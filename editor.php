<?php
    /* EDITOR 
    This file contains functions that are useful for adding things to the base game such as buildings. */

    function addBuilding($pdo, $buildingType, $goldCost, $woodCost, $stoneCost, $timeToBuild, $timeToDeposit, $attribute, $depositValue, $hp, $requirement) {
        $sql = "INSERT INTO buildings (buildingType, goldCost, woodCost, stoneCost, timeToBuild, timeToDeposit, attribute, depositValue, hp, requirement) VALUES ('".$buildingType."', ".$goldCost.", ".$woodCost.", ".$stoneCost.", ".$timeToBuild.", ".$timeToDeposit.", '".$attribute."', ".$depositValue.", ".$hp.", '".$requirement."');";
        $query = $pdo->prepare($sql);
        $query->execute();
        echo "Added ".$buildingType." <br />";
    }

    function build($pdo, $buildingType, $owner, $position, $special) {
        $sql = "SELECT * FROM buildings WHERE buildingType='".$buildingType."'";
        $query = $pdo->prepare($sql);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        
        $sql = "UPDATE building SET buildingType='".$buildingType."', special='".$special."' WHERE id=".$position.";";
        $query = $pdo->prepare($sql); 
        $query->execute();

        echo "Built ".$buildingType." at position ".$position." (owned by ".$owner.").";
    }

?>