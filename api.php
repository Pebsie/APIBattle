<?php
    require "connect.php";
    require "editor.php";
    /*
    apiBattle API - The meat & bones

    FUNCTIONS:
        a
            get - relates to retrieval of data
                scope
                    world - returns data related to the world
                        type
                            buildings - returns in json format the full list of existing buildings (example ?a=get&scope=world&type=buildings)
                            units - returns in json format the full list of existing units (example ?a=get&scope=world&type=units)
                    player - returns data related to the player
                        authcode
                        type
                            data - returns in json format all current attributes of the player (example ?a=get&scope=player&type=data&authcode=*authcode*)
                            buildable - returns in json format the data on all buildings the player can currently build (example ?a=get&scope=player&type=buildable*authcode=*authcode*)
            build - relates to the world of structures
                authcode
                type, position - attempts to build world type at position position. Returns 'true' if built and 'false' if unable to build (example ?a=build&type=*buildingType*&position=*position&authcode=*authcode*)
            move - relates to the moving of units
                authcode
                position, number, newPosition - attempts to move number units from position position to new position newPosition. Returns 'true' if movement was successful and 'false' if unable to move. If enemies are on tile will return battle,*numberOfUnitsKilled*,*numberOfUnitsLost*(example ?a=move&position=*position*&number=*numberOfUnits*&newPosition=*newPosition*)
            login - relates to logging in
                username, password - attempts to login with the specified username and password. If successful returns the authcode. If unsuccessful returns 'false' (example ?a=login&username=*username*&password=*password*)
            register - relates to registration
                username, password - attempts to register with the specified username and password. If successful returns the authcode. If unsuccessful returns 'false' (example ?a=register&username=*username*&password=*password*)
    */

    $a = $_GET['a'];

    if ($a == "get") {
        $scope = $_GET['scope'];
        $type = $_GET['type'];

        if ($scope == "world") {

            $statement = $pdo->prepare("SELECT * FROM world");
            $statement->execute();
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);

        } elseif ($scope == "player") {

            $authcode = $_GET['authcode'];
            $statement = $pdo->prepare("SELECT * FROM player WHERE authcode='".$authcode."';");
            $statement->execute();
            $pl = $statement->fetch(PDO::FETCH_ASSOC);
            
            if ($type == "data") {

                $statement = $pdo->prepare("SELECT * FROM player WHERE authcode='".$authcode."';");
                $statement->execute();
                $results = $statement->fetch(PDO::FETCH_ASSOC);
                echo json_encode($results);
                
            } elseif ($type == "buildable") {

                $statement = $pdo->prepare("SELECT * FROM buildings");
                $statement->execute();
                $results = $statement->fetchAll(PDO::FETCH_ASSOC);

                $finalResult = array();

                foreach ($results as $row) {
                    $canBuild = false;

                    if ($row['requirement'] != NULL) {

                        $statement = $pdo->prepare("SELECT * FROM world WHERE username='".$pl['username']."' AND buildingType='".$row['requirement']."';");
                        $statement->execute();
                        $thisResult = $statement->fetchAll(PDO::FETCH_ASSOC);

                        if ($thisResult) {
                            $canBuild = true;
                        }
                        
                    } else {

                        $canBuild = true;

                    }

                    if ($canBuild) {

                        array_push( $finalResult, $row );

                    }

                }

                echo json_encode($finalResult);

            } elseif ($type == "buildingSum") {

                $statement = $pdo->prepare("SELECT * FROM world WHERE username='".$pl["username"]."';");
                $statement->execute();
                echo $statement->rowCount();
                
            }
        }

    } elseif ($a == "build") {

        $authcode = $_GET['authcode'];
        $statement = $pdo->prepare("SELECT * FROM player WHERE authcode='".$authcode."';");
        $statement->execute();
        $pl = $statement->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM buildings WHERE buildingType='".$_GET["type"]."';");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM world WHERE id=".$_GET["position"].";");
        $stmt->execute();
        $tile = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {  // this building type is valid
            if ($_GET['type'] == "Castle") {

                $statement = $pdo->prepare("SELECT * FROM world WHERE username='".$pl["username"]."';");
                $statement->execute();
                if ($statement->rowCount() == 0) {
                    if ($tile['buildingType'] == "Grass" && $tile['username'] == "Mother Nature") {
                        build($pdo, "Castle", $pl['username'], $_GET['position'], "", 25);
                        echo "true";
                    }
                }
            } else {
                if ($tile['buildingType'] == "Grass" && $tile['username'] == $pl['username']) {
                    build($pdo, "Building", $pl['username'], $_GET['position'], $row['timeToBuild'].",".$_GET['type'], 1);
                    echo "true";
                }
            }
        }

    } elseif ($a == "move") {

        $stmt = $pdo->prepare("SELECT * FROM world WHERE id=".$_GET["position"].";");
        $stmt->execute();
        $tile = $stmt->fetch(PDO::FETCH_ASSOC);

        $authcode = $_GET['authcode'];
        $statement = $pdo->prepare("SELECT * FROM player WHERE authcode='".$authcode."';");
        $statement->execute();
        $pl = $statement->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM world WHERE id=".$_GET["newPosition"].";");
        $stmt->execute();
        $newTile = $stmt->fetch(PDO::FETCH_ASSOC);

        $number = $_GET['number'];
        if ($number >= $tile['units']) { $number = $tile['units'] - 1; }

        if ($tile['username'] == $pl['username']) {
            if ($newTile['username'] == "Mother Nature") {
                $stmt = $pdo->prepare("UPDATE world SET units=".($tile['units']-$number)." WHERE id=".$tile['id']);
                $stmt->execute();

                $stmt = $pdo->prepare("UPDATE world SET units=".$number.", owner='".$pl['username']."' WHERE id=".$newTile['id']);
                $stmt->execute();
            } elseif ($newTile['username'] == $tile['username']) {
                $stmt = $pdo->prepare("UPDATE world SET units=".($tile['units']-$number)." WHERE id=".$tile['id']);
                $stmt->execute();

                $stmt = $pdo->prepare("UPDATE world SET units=".$number." WHERE id=".$newTile['id']);
                $stmt->execute();
            } else { // this is a battle
                $atk = rand(1, $newTile['units']);
                $def = rand(1, $tile['units']);
                $tile['units'] -= $atk;
                $newTile['units'] -= $def;
                echo $atk.",".$def;
                if ($newTile['units'] < 0) {
                    $stmt = $pdo->prepare("UPDATE world SET units=0, username='Mother Nature' WHERE id=".$newTile['id']);
                    $stmt->execute();
                }
            
            }
        }

    } elseif ($a == "login") {

        $username = $_GET['username'];
        $stmt = $pdo->prepare("SELECT * FROM player WHERE username='".$username."'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($_GET['password'], $result['password'])) {
            echo $result['authcode'];
        } else {
            echo "false";
        }

    } elseif ($a == "register") {

        $username = $_GET['username'];
        $password = password_hash($_GET['password'],PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("SELECT * FROM player WHERE username='".$username."'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) { // account doesn't exist

            $authcode = rand(10000, 99999);
            $sql = "INSERT INTO player (username, password, authcode, gold, wood, stone, modifier, pop, food) VALUES ('".$username."', '".$password."', '".$authcode."', 10,20,20,1,4,50);";
            $query = $pdo->prepare($sql);
            $result = $query->execute();
            echo $authcode;

        } else { echo "false"; }
    }
