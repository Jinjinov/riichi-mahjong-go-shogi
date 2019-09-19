<?php
    require 'reCAPTCHA.php';
    
    $captcha;

    if(isset($_POST['g-recaptcha-response']))
        $captcha=$_POST['g-recaptcha-response'];

    if(!$captcha)
    {
        //echo '<h2>Please check the the captcha form.</h2>';
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
        //echo json_encode(array("success"=>false, "message"=>"bye!"));
    }
    else if (isset($_SESSION['initialized']))
    {
        //echo json_encode(array("success"=>true, "message"=>"hello"));

        if (isset($_POST["game"]) && isset($_POST["time"]) && isset($_POST["location"]) && isset($_POST["username"]) && isset($_POST["email"]))
        {
            $game = $_POST["game"];
            $time = $_POST["time"];
            $id = $_POST["location"];
            $username = $_POST["username"];
            $email = $_POST["email"];

            $ids = $_SESSION['ids'];
            $locations = $_SESSION['locations'];

            if(array_key_exists($id, $ids))
            {
                $location = $ids[$id];

                if (($timestamp = strtotime($time)) !== false)
                {
                    if (($dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $time)) !== FALSE)
                    {
                        echo $game;
                        echo $username;
                        echo $email;
                    }
                }
            }
        }
    }
?>