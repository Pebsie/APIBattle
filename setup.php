<?php
    if ($_POST['dbname']) {

        $authFile = fopen("auth.php", "w");
        $authWrite = '
        <?php
        $mysql_username = "'.$_POST["username"].'";
        $mysql_password = "'.$_POST["password"].'";
        $mysql_server = "'.$_POST["server"].'";
        $mysql_dbname = "'.$_POST['dbname'].'";
        ?>';
        fwrite( $authFile, $authWrite );

        require "connect.php";

        echo "<h1>Setup</h1>";
        echo "<h2>Database</h2>";

        // Set up player table
        echo "<h3>Player</h3>";
        $sql = "CREATE TABLE player (
            id int NOT NULL AUTO_INCREMENT,
            username varchar(255) NOT NULL,
            password varchar(255),
            gold int,
            wood int,
            stone int,
            modifier int,
            pop int,
            food int,
            PRIMARY KEY (id));";
        $query = $pdo->prepare($sql);
        $query->execute();

        // Set up buildings table
        echo "<h3>Building</h3>";
        $sql = "CREATE TABLE building (
            id int NOT NULL AUTO_INCREMENT,
            buildingType varchar(255) NOT NULL,
            hp int,
            position int,
            username varchar(255) NOT NULL,
            special text,
            PRIMARY KEY (id),
            FOREIGN KEY (buildingType) REFERENCES buildings(buildingType),
            FOREIGN KEY (username) REFERENCES player(owner));";
        $query = $pdo->prepare($sql);
        $query->execute();

        // Set up building table
        echo "<h3>Buildings</h3>";
        $sql = "CREATE TABLE buildings (
            id int NOT NULL AUTO_INCREMENT,
            buildingType varchar(255) NOT NULL,
            goldCost INT,
            woodCost INT,
            stoneCost INT,
            timeToBuild INT,
            timeToDeposit INT,
            attribute varchar(255),
            depositValue INT,
            hp INT,
            requirement varchar(255),
            PRIMARY KEY (id));";
        $query = $pdo->prepare($sql);
        $query->execute();

        echo "<h2>Initial buildings</h2>";
        addBuilding('House', 1, 5, 1, 1, 5, "pop", 1, 1, NULL);
        addBuilding('Barracks', 3, 10, 5, 5, 10, "unit", 1, 10, "House");
        addBuilding('Farm', 3, 3, 5, 2, 5, "food", 2, 5, "House");


        function addBuilding($buildingType, $goldCost, $woodCost, $stoneCost, $timeToBuild, $timeToDeposit, $attribute, $depositValue, $hp, $requirement) {
            $sql = "INSERT INTO buildings (buildingType, goldCost, woodCost, stoneCost, timeToBuild, timeToDeposit, attribute, depositValue, hp, requirement) VALUES ('".$buildingType."', ".$goldCost.", ".$woodCost.", ".$stoneCost.", ".$timeToBuild.", ".$timeToDeposit.", '".$attribute."', ".$depositValue.", ".$hp.", '".$requirement."'));";
            $query = $pdo->prepare($sql);
            $query->execute();
            echo "Added ".$buildingType." <br />";
        }
    } else {
?>
    <h1>Setup</h1>
    <h2>Please enter database details</h2>
    
    <form type="POST" action="setup.php">
        <input type="text" name="username" default="mysql_username" /> <br />
        <input type="password" name="password" default="password" /> <br />
        <input type="text" name="server" default="mysql_server" /> <br />
        <input type="text" name="dbname" default="mysql_dbname" /> <br />
        <input type="submit" value="Install" /> <br />
    </form>
    <? } ?>