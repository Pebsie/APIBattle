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
            /** @api {GET} /api.php?a=get&scope=world Retrieve an array of information on every tile in the world
            * @apiName getWorld
            * @apiGroup Retrieval
            * @apiVersion 0.1.0
            * @apiSuccess {Number} id The position in the world of the tile.
            * @apiSuccess {String} buildingType The type of building on this tile.
            * @apiSuccess {Number} units The number of units on this tile.
            * @apiSuccess {String} username The username of the player who owns this tile. Mother Nature is the default name for all unclaimed tiles.
            * @apiSuccess {String} special Special properties of this tile, separated by a comma.
            * @apiSuccessExample Example singular output element in JSON array:
            * {
            *   "id":"1",
            *   "buildingType":"Grass",
            *   "units":"0",
            *   "username":"Mother Nature",
            *   "special":""
            * }
            * @apiSuccessExample Example singular output element of a Barrcks currently being built with 3 minutes remaining in JSON array:
            * {
            *   "id":"421",
            *   "buildingType":"Building",
            *   "units":"4",
            *   "username":"Player1",
            *   "special":"3,Barracks"
            * }
            */


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
                /** @api {GET} /api.php?a=get&scope=player&type=data Retrieve an array of information on every tile in the world
                * @apiName getWorld
                * @apiGroup Retrieval
                * @apiVersion 0.1.0
                * @apiSuccess {Number} id The position in the world of the tile.
                * @apiSuccess {String} buildingType The type of building on this tile.
                * @apiSuccess {Number} units The number of units on this tile.
                * @apiSuccess {String} username The username of the player who owns this tile. Mother Nature is the default name for all unclaimed tiles.
                * @apiSuccess {String} special Special properties of this tile, separated by a comma.
                * @apiSuccessExample Example singular output element in JSON array:
                * {
                *   "id":"1",
                *   "buildingType":"Grass",
                *   "units":"0",
                *   "username":"Mother Nature",
                *   "special":""
                * }
                * @apiSuccessExample Example singular output element of a Barrcks currently being built with 3 minutes remaining in JSON array:
                * {
                *   "id":"421",
                *   "buildingType":"Building",
                *   "units":"4",
                *   "username":"Player1",
                *   "special":"3,Barracks"
                * }
                */

                $statement = $pdo->prepare("SELECT username, gold, wood, stone, modifier, pop, food FROM player WHERE authcode='".$authcode."';");
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

                    if ($canBuild) { // determine whether the player has the requirement to build this building

                        $row['canBuild'] = true;

                    } else {
                        
                        $row['canBuild'] = false;

                    }

                    array_push( $finalResult, $row );

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
                if ($tile['buildingType'] == "Grass" && $tile['username'] == $pl['username'] && $pl['gold'] >= $row['goldCost'] && $pl['wood'] >= $row['woodCost'] && $pl['stone'] >= $row['stoneCost']) {
                    build($pdo, "Building", $pl['username'], $_GET['position'], $row['timeToBuild'].",".$_GET['type'], 1);

                    $stmt = $pdo->prepare("UPDATE player SET gold=".($pl['gold']-$row['goldCost']).", wood=".($pl['wood']-$row['woodCost']).", stone=".($pl['stone']-$row['stoneCost'])." WHERE username='".$pl['username']."';");
                    $stmt->execute();
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

        $stmt = $pdo->prepare("SELECT * FROM buildings WHERE buildingType='".$newTile["buildingType"]."'");
        $stmt->execute();
        $newTileBuilding = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM buildings WHERE buildingType='".$tile["buildingType"]."'");
        $stmt->execute();
        $curTileBuilding = $stmt->fetch(PDO::FETCH_ASSOC);

        $number = $_GET['number'];

        if ($newTileBuilding['impassable'] == "false" && $curTileBuilding['impassable'] == "false") { // You cannot move tiles from or onto 

            if ($number >= $tile['units']) { $number = $tile['units'] - 1; }

            if ($tile['username'] == $pl['username'] && $tile['units'] > 1) {
                if ($newTile['username'] == "Mother Nature") {
                    $stmt = $pdo->prepare("UPDATE world SET units=".($tile['units']-$number)." WHERE id=".$tile['id']);
                    $stmt->execute();

                    $stmt = $pdo->prepare("UPDATE world SET username='".$pl['username']."', units=".$number." WHERE id=".$newTile['id']);
                    $stmt->execute();

                    $stmt = $pdo->prepare("UPDATE player SET gold=".($pl['gold']+1)." WHERE username='".$pl['username']."';");
                    $stmt->execute();
                } elseif ($newTile['username'] == $tile['username']) {
                    $stmt = $pdo->prepare("UPDATE world SET units=".($tile['units']-$number)." WHERE id=".$tile['id']);
                    $stmt->execute();
            
                    $stmt = $pdo->prepare("UPDATE world SET units=".($number+$newTile['units'])." WHERE id=".$newTile['id']);
                    $stmt->execute();
                } else { // this is a battle

                    if (rand(0,2) == 1) {
                        $atk = rand(0, $newTile['units']); // number of units the attacker has lost
                    } else {
                        $atk = rand(0, floor($newTile['units']/3));
                    }

                    if (rand(0,1) == 1) {
                        $def = rand(0, $number); // number of units the defender has lost
                    } else {
                        $def = rand(0, floor($number/3));
                    }

                    echo $atk.",".$def.",".$tile['units'].",".$newTile['units'];
                    $tile['units'] -= $atk;
                    $newTile['units'] -= $def;
                    

                    $stmt = $pdo->prepare("UPDATE world SET units=".$tile['units']." WHERE id=".$tile['id']);
                    $stmt->execute();

                    if ($newTile['units'] < 0) {
                        $stmt = $pdo->prepare("UPDATE world SET units=0, username='Mother Nature' WHERE id=".$newTile['id']);
                        $stmt->execute();
                    } else {
                        $stmt = $pdo->prepare("UPDATE world SET units=".$newTile['units']." WHERE id=".$newTile['id']);
                        $stmt->execute();
                    }
                
                }
            }
        } 

    } elseif ($a == "login") {

        $username = $_GET['username'];
        $stmt = $pdo->prepare("SELECT * FROM player WHERE username='".$username."'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($_GET['password'], $result['password'])) {
            echo json_encode($result);
        } else {
            
            echo '{"status":"failed","reason":"The username or password was wrong!"}'; 
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
            
            $stmt = $pdo->prepare("SELECT * FROM player WHERE username='".$username."'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode($result);

        } else { echo '{"status":"failed","reason":"A user already exists with this username!"}'; }
    }
