<?php
    if ($_POST['dbname']) {

        $authFile = fopen("auth.php", "w");
        $authWrite = '
        <?php
        $mysql_username = "'.$_POST["username"].'";
        $mysql_password = "'.$_POST["password"].'";
        $mysql_server = "'.$_POST["server"].'";
        $mysql_dbname = "'.$_POST['dbname'].'";
        $cron_verify = "'.rand(100000,999999).'";
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
            authcode varchar(255),
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
        echo "<h3>World</h3>";
        $sql = "CREATE TABLE world (
            id int NOT NULL AUTO_INCREMENT,
            buildingType varchar(255) NOT NULL,
            units int,
            username varchar(255) NOT NULL,
            special text,
            PRIMARY KEY (id));";
        $query = $pdo->prepare($sql);
        $query->execute();

        // Set up world table
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
            impassable varchar(255),
            PRIMARY KEY (id));";
        $query = $pdo->prepare($sql);
        $query->execute();

        echo "<h2>Content options</h2>";
        echo "<a href='modules/baseBuildings.php'>Click here to install default buildings</a><br />";
        echo "<a href='modules/generateWorld.php'>Click here to populate the world with the default algorithm</a>";

        echo "<p><em>You don't have to use the default buildings and world generation algorithm! Feel free to create your own. You can see how all of this works <a href='https://github.com/pebsie/apibattle'>on GitHub.</a></p>";
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