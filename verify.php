<?php
    require 'reCAPTCHA.php';
    
    $captcha;

    if(isset($_POST['g-recaptcha-response']))
        $captcha=$_POST['g-recaptcha-response'];

    if(!$captcha){
        echo '<h2>Please check the the captcha form.</h2>';
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
        echo json_encode(array("success"=>false, "message"=>"bye!"));
    }
    else
    {
        echo json_encode(array("success"=>true, "message"=>"hello"));
    }
?>