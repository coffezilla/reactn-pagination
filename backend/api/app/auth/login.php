<?php 

header('Content-Type: application/json; charset=UTF-8');

include "../connect/bd_connect.php";
include "../helpers/utils.php";

//
$dataResponse = array();
$dataResponse['status'] = 0;
$dataResponse['message'] = '';
$errors = array();

// var
$userEmail = addslashes(trim($_POST['email']));
$userEmail = str_replace(" ", "", $userEmail);

$userPassword = addslashes(trim($_POST['password']));
$userPassword = str_replace(" ", "", $userPassword);
$userPasswordMd5 = md5($userPassword);

$currentTimestamp = Date('Y-m-d H:i:s');
$currentTimestampClean = str_replace(" ", "", $currentTimestamp);

// verify
$checkers = array($userEmail, $userPassword, $currentTimestamp);
$validInputs = checkEmptyData($checkers, 1);


// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);

if($isAuth) {

    // JWT auth
    $token = createJWTAuth($userEmail, $currentTimestampClean, $JWTServerkey);

    if($validInputs) {
        // query
        $queryUsers = mysqli_query($connection, "SELECT 
        usr_id
        FROM users
        WHERE usr_email = '{$userEmail}' 
        AND usr_status = 1
        AND usr_password = '{$userPasswordMd5}'
        ORDER BY usr_id DESC
        LIMIT 1") or die ("User Not Found");


        if (mysqli_num_rows ($queryUsers) > 0) {
            $dataUser = mysqli_fetch_assoc($queryUsers);
            $userId = $dataUser['usr_id'];
              
            $dataResponse['token'] = $token;
            $dataResponse['timestamp'] = $currentTimestamp;

            $dataResponse['user'] = array(
                'id' => $dataUser['usr_id'],
                'email' => $userEmail,
            );
            $dataResponse['status'] = 1;

        } else {
            $dataResponse['message'] = 'Usuário ou senha inválido';
            $dataResponse['status'] = 3;
        }
    } else {
        $dataResponse['message'] = 'Campo em branco';
        $dataResponse['status'] = 2;
    }

} else {
    // nao autehnticado
    $dataResponse['status'] = 2;
}

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
