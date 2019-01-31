<?php
    require "../connect.php";
    require "../editor.php";
    //addBuilding($pdo, $buildingType, $goldCost, $woodCost, $stoneCost, $timeToBuild, $timeToDeposit, $attribute, $depositValue, $hp, $requirement, $impassable)

    addBuilding($pdo, 'Grass', 1, 1, 1, 1, 1, "unit", 0, 0, "God", "false");
    addBuilding($pdo, 'Building', 1, 1, 1, 1, "unit", 0, 0, "God", "false");
    addBuilding($pdo, 'Castle', 1, 1, 1, 1, 1, "unit", 2, 5, "God", "false");
    addBuilding($pdo, 'House', 1, 5, 1, 1, 5, "pop", 1, 1, NULL, "false");
    addBuilding($pdo, 'Barracks', 5, 20, 10, 5, 10, "unit", 1, 10, "House", "false");
    addBuilding($pdo, 'Farm', 0, 5, 5, 2, 5, "food", 2, 5, "House", "false");
    addBuilding($pdo, 'Mine', 100, 50, 100, 30, 10, "stone", 1, 100, "God", "false");
    addBuilding($pdo, 'Forest', 100, 100, 100, 3, 10, "wood", 1, 20, "God", "false");
    addBuilding($pdo, 'Wall', 25, 0, 30, 20, 1, "unit", 100, 5, "Barracks", "true");
    addBuilding($pdo, 'Skeleton', 1, 1, 1, 1, 1, "Gold", 2, 5, "God", "Darkworld Portal");
    addBuilding($pdo, 'Outpost', 3, 10, 5, 1, 15, "unit", 1, 10, "Barracks", "false");
?>