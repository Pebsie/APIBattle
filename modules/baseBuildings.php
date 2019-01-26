<?php
    require "connect.php";
    require "editor.php";
    addBuilding($pdo, 'House', 1, 5, 1, 1, 5, "pop", 1, 1, NULL);
    addBuilding($pdo, 'Barracks', 3, 10, 5, 5, 10, "unit", 1, 10, "House");
    addBuilding($pdo, 'Farm', 3, 3, 5, 2, 5, "food", 2, 5, "House");
    addBuilding($pdo, 'Mine', 100, 50, 100, 30, 10, "stone", 10, ß100, "God");
    addBuilding($pdo, 'Forest', 100, 100, 100, 3, 10, "Wood", 3, 20, "God");
?>