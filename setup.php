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
        require "modules/baseBuildings.php";

    
    } else {
?>
    <h1>Setup</h1>
    <h2>Please enter database details</h2>
    
    <form method="POST" action="setup.php">
        Username: <input type="text" name="username" default="mysql_username" /> <br />
        Password: <input type="password" name="password" default="password" /> <br />
        Server address: <input type="text" name="server" default="mysql_server" /> <br />
        Database name: <input type="text" name="dbname" default="mysql_dbname" /> <br />
        <input type="submit" value="Install" /> <br />
    </form>
    <? } ?>