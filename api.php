<?php
    require "connect.php";

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
            build - relates to the building of structures
                authcode
                type, position - attempts to build building type at position position. Returns 'true' if built and 'false' if unable to build (example ?a=build&type=*buildingType*&authcode=*authcode*)
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

            if ($type == "buildings") {

                $statement = $pdo->prepare("SELECT * FROM building");
                $statement->execute();
                $results = $statement->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($results);

            } elseif ($type == "units") {

                $statement = $pdo->prepare("SELECT * FROM units");
                $statement->execute();
                $results = $statement->fetchAll(PDO::FECTH_ASSOC);
                echo json_encode($results);
            
            }

        } elseif ($scope == "player") {

            $authcode = $_GET['authcode'];
            
            if ($type == "data") {

                $statement = $pdo->prepare("SELECT * FROM player WHERE authcode='".$authcode."';");
                $statement->execute();
                $results = $statement->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($results);
                
            } elseif ($type == "buildable") {

                $statement = $pdo->prepare("SELECT * FROM player WHERE authcode='".$authcode."';");
                $statement->execute();
                $pl = $statement->fetchAll(PDO::FETCH_ASSOC);

                $statement = $pdo->prepare("SELECT * FROM buildings");
                $statement->execute();
                $results = $statement->fetchAll(PDO::FETCH_ASSOC);

                $finalResult = array();

                foreach ($results as $row) {
                    $canBuild = false;

                    if ($row['requirement'] != NULL) {

                        $statement = $pdo->prepare("SELECT * FROM building WHERE username='".$pl['username']."' AND buildingType='".$row['requirement']."';");
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

            }

        } elseif ($a == "build") {

            //

        } elseif ($a == "move") {

            

        } elseif ($a == "login") {

            // login

        } elseif ($a == "register") {

            $username = $_GET['username'];
            $password = password_hash($_GET['password'],PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("SELECT * FROM users WHERE name='".$username."'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) { // account doesn't exist
                
                $authcode = rand(10000, 99999);
                $sql = "INSERT INTO player (username, password, authcode, gold, wood, stone, modifier, pop, food) VALUES ('".$username."', '".$password."', '".$authcode."', 10,20,20,1,4,50);";
                $result = $pdo->query($sql);
                echo $authcode;

            } else { echo "false"; }
        }
    }