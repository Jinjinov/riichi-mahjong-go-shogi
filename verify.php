<?php
    session_start();

    include 'Hybridauth/autoload.php';
    include 'config.php';

    use Hybridauth\Hybridauth;

    $hybridauth = new Hybridauth($config);
    $adapters = $hybridauth->getConnectedAdapters();

    require 'reCAPTCHA.php';
    
    $captcha;

    if(isset($_POST['g-recaptcha-response']))
        $captcha=$_POST['g-recaptcha-response'];

    if(!$captcha)
    {
        echo 'Please check the the captcha form.';
        exit;
    }

    /*
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = ['secret'   => $reCAPTCHA,
             'response' => $_POST['g-recaptcha-response'],
             'remoteip' => $_SERVER['REMOTE_ADDR']];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data) 
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result);
    /**/

    $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$reCAPTCHA."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']), true);
    
    if($response['success'] == false)
    {
        echo json_encode(array("success"=>false, "message"=>"verification failed"));
    }
    else if (isset($_SESSION['initialized']))
    {
        //$ids = $_SESSION['ids'];
        $locations = $_SESSION['locations'];

        if($adapters)
        {
            $username = "notConnected";
            $emailFrom = "notConnected";

            foreach ($adapters as $name => $adapter)
            {
                if($adapter->isConnected())
                {
                    $username = $adapter->getUserProfile()->displayName;
                    $emailFrom = $adapter->getUserProfile()->email;
                }
            }

            if (isset($_POST["game"]) && isset($_POST["time"]) && isset($_POST["location"])) // && isset($_POST["username"]) && isset($_POST["email"]))
            {
                $game = $_POST["game"];
                $time = $_POST["time"];
                //$id = $_POST["location"];
                $index = $_POST["location"];
                //$username = $_POST["username"];
                //$emailFrom = $_POST["email"];

                //if(array_key_exists($id, $ids))
                if(array_key_exists($index, $locations))
                {
                    //$location = $ids[$id];
                    $location = $locations[$index];

                    $gameCount = count($game);
                    if($gameCount > 0 && $gameCount <= 3)
                    {
                        $isValidGame = true;
                        foreach($game as $validGame)
                        {
                            if(strcmp($validGame, "Mahjong") && strcmp($validGame, "Go") && strcmp($validGame, "Shogi"))
                            {
                                $isValidGame = false;
                            }
                        }

                        if($isValidGame)
                        {
                            $games = $game[0];
                            for($i = 1; $i < $gameCount; ++$i)
                            {
                                $games = $games . ", " . $game[$i];
                            }

                            if (($timestamp = strtotime($time)) !== false)
                            {
                                if (($dateTime = DateTime::createFromFormat("Y-m-d\TH:i:s.uP", $time)) !== FALSE)
                                {
                                    $dateTime->setTimezone(new DateTimeZone('Europe/Ljubljana'));

                                    $subject = "Reservation ".$dateTime->format("Y-m-d H:i:s T \(\G\M\T P\)");

                                    $openstreetmap = "http://www.openstreetmap.org/?mlat=".$location['marker'][0]."&mlon=".$location['marker'][1]."&zoom=14";

                                    $googleMaps = "https://www.google.com/maps/search/?api=1&query=".$location['marker'][0].",".$location['marker'][1];

                                    //$message = "Game: ". json_encode($game) ." \n User: $username \n Email: $emailFrom \n Location: ". json_encode($location) ." \n Unix timestamp: $timestamp \n UTC Date: $time \n Date: " . $dateTime->format("Y-m-d H:i:s T \(\G\M\T P\)");
                                    $message = "User: $username"."\n".
                                                "Email: $emailFrom"."\n".
                                                "Game: ".$games."\n".
                                                "Date: ".$dateTime->format("Y-m-d H:i:s T \(\G\M\T P\)")."\n".
                                                "Location: ".$location['address']." ".$location['marker'][0]." ".$location['marker'][1]."\n".
                                                "Open Street Map: $openstreetmap"."\n".
                                                "Google Maps: $googleMaps";

                                    $headers = "From: $emailFrom" . "\r\n" . "Reply-To: $emailFrom" . "\r\n" . 'X-Mailer: PHP/' . phpversion();

                                    if (mail($emailTo, $subject, $message /*, $headers,"-f $emailFrom" */) !== false)
                                    {
                                        echo json_encode(array("success"=>true, "message"=>$message));
                                    }
                                    else
                                    {
                                        echo json_encode(array("success"=>false, "message"=>"mail not sent $message"));
                                    }
                                }
                                else
                                {
                                    echo json_encode(array("success"=>false, "message"=>"invalid date $time timestamp $timestamp"));
                                }
                            }
                            else
                            {
                                echo json_encode(array("success"=>false, "message"=>"invalid time $time"));
                            }
                        }
                        else
                        {
                            echo json_encode(array("success"=>false, "message"=>"invalid games ".json_encode($game)));
                        }
                    }
                    else
                    {
                        echo json_encode(array("success"=>false, "message"=>"invalid game count $gameCount"));
                    }
                }
                else
                {
                    echo json_encode(array("success"=>false, "message"=>"location $id not found"));
                }
            }
            else
            {
                echo json_encode(array("success"=>false, "message"=>"game, time, location not found"));
            }
        }
        else
        {
            echo json_encode(array("success"=>false, "message"=>"not signed in"));
        }
    }
    else
    {
        echo json_encode(array("success"=>false, "message"=>"verification successful"));
    }
?>