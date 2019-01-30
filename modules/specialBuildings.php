<?php
    require "../connect.php";
    require "../editor.php";
    addBuilding($pdo, 'Wall', 1, 1, 1, 10, 1, "unit", 100, 5, "Barracks");
    addBuilding($pdo, 'Skeleton', 1, 1, 1, 1, 1, "unit", 20, 5, "God", true);
?>