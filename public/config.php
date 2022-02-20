<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Tuupola\Middleware\HttpBasicAuthentication;
use Firebase\JWT\JWT;

require '../vendor/autoload.php';
define('FILTER_SANITIZE_STRING ', 513);

// to show error with detalis you should make this 
$config = [
    'settings' => [
        'displayErrorDetails' => true
    ]
];


$con = new \Slim\Container($config);

$app = new \Slim\App($con);
// the file for middleware layer 
//require '../src/middleware.php';
// the get method test 



//get get users from data base 
//----------------------------------------------------------------------------
$dsn = 'mysql:host=localhost;dbname=users';
$user = 'root';
$pass = '';
$option = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

try {
    $con = new PDO($dsn, $user, $pass, $option);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo " you are connected to database";

} catch (PDOException $e) {
    echo "Failded to connected" . $e->getMessage();
}

$stmt = $con->prepare('SELECT * from users ');
$stmt->execute();
$data = $stmt->fetchAll();
// echo "<pre>";
// print_r($data);
// echo "</pre>";
//----------------------------------------------------------------------
$data_array = [];
foreach ($data as $row) {
    $data_array[$row['username']] = $row['password'];
}

// echo "<pre>";
// print_r($data_array);
// echo "</pre>";

// the authebtucatuib basic  & put all user and their password in the basic Auth~  to check 
$app->add(new HttpBasicAuthentication([
    "path" => ["/getjwttoken"], /* or ["/admin", "/api"] */
    "realm" => "Protected",
    "users" => $data_array
]));


// if username and password is valid this path will send token to get api page 
$app->get('/getjwttoken', function ($request,  $response) {

    $header_data = $request->getHeaders();
    $header_auth_json_deocde = substr($header_data['HTTP_AUTHORIZATION'][0], 6, strlen($header_data['HTTP_AUTHORIZATION'][0]));
    $header_auth_json_encode = base64_decode($header_auth_json_deocde);

    $name = (explode(':', $header_auth_json_encode))[0];
    $pass = (explode(':', $header_auth_json_encode))[1];
    function jwtgettoken($name, $pass)
    {

        //--------------------generate token -------------------------------
        // get the data now 
        $now = new DateTime();
        // get the data now + 2 minutes
        $future = new DateTime("now +1440 minutes");
        //$server = $request->getServerParams();
        // make array with start and end time 
        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "name" => $name,
            'password' => $pass,
            //"sub"=> $_SERVER['PHP_AUTH_USER']
            //"sub" => $server["PHP_AUTH_USER"],
        ];
        // make the secret key to make encode to data 
        $secret = "supersecretkeyyoushouldnotcommittogithub";
        // make encode to data 
        $token = JWT::encode($payload, $secret, "HS512");
        return $token;
    }
    //-------------------end generate token -----------------------------
    $data["status"] = "ok";
    $data["token"] = jwtgettoken($name, $pass);
    $response->withStatus(201)->withHeader("Content-Type", "application/json");
    $response = $response->withjson($data);


    return $response;
});

// make jwt authentication to this path 
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "path" => ["/jwttokentest"],
    "ignore" => ["/jwttoken"],
    "secret" => "supersecretkeyyoushouldnotcommittogithub"
]));

$app->get('/jwttokentest', function ($request,  $response, array $args) {
    // get the data which in the token after check that it is valid
    $username = ($request->getAttribute('token'))['name'];


    $dsn = 'mysql:host=localhost;dbname=users';
    $user = 'root';
    $pass = '';
    $option = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    );

    try {
        $con = new PDO($dsn, $user, $pass, $option);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //echo " you are connected to database";

    } catch (PDOException $e) {
        echo "Failded to connected" . $e->getMessage();
    }

    $stmt = $con->prepare("SELECT * FROM `api` WHERE username = '" . $username . "' ");
    $stmt->execute();
    $data = $stmt->fetch();

    $response = $response->withjson($data);

    return $response;
});



















$app->run();
