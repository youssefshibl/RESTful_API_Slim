<?php

// check if the request post or not 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // get the username and password 
    $username = $_POST['username'];
    $pass = $_POST['pass'];
    // encode_base64 the username and password to send it in header to get token 
    $base64 = base64_encode($username . ":" . $pass);

    $arrContextOptions = array(

        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
        //put the authrization basic in header of request 
        'http' => array(
            'header' =>  'authorization: Basic ' . $base64
        )
    );
    // send the request and get the token to enter to api 
    $data = file_get_contents("http://localhost/project_one/public/config.php/getjwttoken", false, stream_context_create($arrContextOptions));
    // decode json data to get the token 
    $data = json_decode($data, true);
    $token =  $data['token'];
    //echo $token;

    // go to jwttokentest with the header token 

    $arrContextOptionsone = array(
        'http' => array(
            'header' => 'authorization: Bearer '  .  $token
        )
    );

    //send the request to api page to get json data 
    $newdata = file_get_contents("http://localhost/project_one/public/config.php/jwttokentest", false, stream_context_create($arrContextOptionsone));
    // change the content-type of header 
    header('content-type:application/json');
    // push data as a json data 
    echo $newdata;


    //echo $newdata;
} else {
    // get the username and passwoerd 
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="main.css">
        <title>Document</title>
        <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@1,200&family=Poppins:wght@300&display=swap" rel="stylesheet">

    </head>

    <body>
        <div class="form-">
            <form action="" method="POST">
                <input type="text" placeholder="username" name="username" class="input-ele" required='required'>
                <input type="password" name="pass" id="" class="input-ele" required='required' placeholder="password">
                <input type="submit" value="go" id="" class="input-ele-sub">
            </form>
        </div>
    </body>

    </html>
<?php


}

?>