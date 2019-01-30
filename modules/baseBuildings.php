<?php
    require "../connect.php";
    require "../editor.php";
    addBuilding($pdo, 'Castle', 1, 1, 1, 1, 1, "unit", 10, 5, "God", "false");
    addBuilding($pdo, 'House', 1, 5, 1, 1, 5, "pop", 1, 1, NULL, "false");
    addBuilding($pdo, 'Barracks', 3, 10, 5, 5, 10, "unit", 1, 10, "House", "false");
    addBuilding($pdo, 'Farm', 3, 3, 5, 2, 5, "food", 2, 5, "House", "false");
    addBuilding($pdo, 'Mine', 100, 50, 100, 30, 10, "stone", 10, 100, "God", "false");
    addBuilding($pdo, 'Forest', 100, 100, 100, 3, 10, "wood", 3, 20, "God", "false");
    addBuilding($pdo, 'Wall', 1, 1, 1, 10, 1, "unit", 100, 5, "Barracks", "false");
    addBuilding($pdo, 'Skeleton', 1, 1, 1, 1, 1, "unit", 20, 5, "God", true, "false");
?>