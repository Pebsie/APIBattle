<?php
    $world_json = http_get("../api.php?a=get&scope=world&type=buildings");

    $world = json_decode($world_json);

    $i = 1;
    foreach ($world as $tile) {
        echo "<img src='http://peb.si/b/img/".$world['buildingType'].".png' />";
        $i++;
        if ($i > 100) {
            echo "<br />";
            $i = 1;
        }
    }
?>