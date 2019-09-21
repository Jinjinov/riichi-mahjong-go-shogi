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
        if (isset($_POST["game"]) && isset($_POST["time"]) && isset($_POST["location"]) && isset($_POST["username"]) && isset($_POST["email"]))
        {
            $game = $_POST["game"];
            $time = $_POST["time"];
            //$id = $_POST["location"];
            $index = $_POST["location"];
            $username = $_POST["username"];
            $emailFrom = $_POST["email"];

            if($adapters)
            {
                foreach ($adapters as $name => $adapter)
                {
                    if($adapter->isConnected())
                    {
                        $username = $adapter->getUserProfile()->displayName;
                        $emailFrom = $adapter->getUserProfile()->email;
                    }
                }
            }

            //$ids = $_SESSION['ids'];
            $locations = $_SESSION['locations'];

            //if(array_key_exists($id, $ids))
            if(array_key_exists($index, $locations))
            {
                //$location = $ids[$id];
                $location = $locations[$index];

                if (($timestamp = strtotime($time)) !== false)
                {
                    if (($dateTime = DateTime::createFromFormat("Y-m-d\TH:i:s.uP", $time)) !== FALSE)
                    {
                        $dateTime->setTimezone(new DateTimeZone('Europe/Ljubljana'));

                        $message = "Game: ". json_encode($game) ." \n User: $username \n Email: $emailFrom \n Location: ". json_encode($location) ." \n Time: $timestamp \n UTC Date: $time \n Date: " . $dateTime->format("Y-m-d H:i:s T \(\G\M\T P\)");

                        $headers = "From: $emailFrom" . "\r\n" . "Reply-To: $emailFrom" . "\r\n" . 'X-Mailer: PHP/' . phpversion();

                        if (mail($emailTo, "Reservation", $message /*, $headers,"-f $emailFrom" */) !== false)
                        {
                            echo json_encode(array("success" => true, "message" => $message));
                        }
                        else
                        {
                            echo json_encode(array("success"=>true, "message"=>"mail not sent $message"));
                        }
                    }
                    else
                    {
                        echo json_encode(array("success"=>true, "message"=>"invalid date $time timestamp $timestamp "));
                    }
                }
                else
                {
                    echo json_encode(array("success"=>true, "message"=>"invalid time $time"));
                }
            }
            else
            {
                $msg = json_encode($ids);
                echo json_encode(array("success"=>true, "message"=>"location $id not found in $msg"));
            }
        }
        else
        {
            echo json_encode(array("success"=>true, "message"=>"initialized"));
        }
    }
    else
    {
        echo json_encode(array("success"=>true, "message"=>"verification successful"));
    }
?>