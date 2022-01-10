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
$authTimestamp = addslashes(trim($_POST['auth_timestamp']));
$currentTimestampClean = str_replace(" ", "", $authTimestamp);

$authUserEmail = addslashes(trim($_POST['auth_email']));
$authUserEmail = str_replace(" ", "", $authUserEmail);

$userEmail = addslashes(trim($_POST['email']));
$userEmail = str_replace(" ", "", $userEmail);

$userPassword = addslashes(trim($_POST['password']));
$userPassword = str_replace(" ", "", $userPassword);
$userPasswordMd5 = md5($userPassword);

// verify
$validInputs = false;
// verify
$checkers = array($userEmail, $authUserEmail, $userPassword);
$validInputs = checkEmptyData($checkers, 1);


// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);

if($isAuth) {

    // check if email is the same as the token email
    if($userEmail == $authUserEmail && $validInputs) {
        // JWT auth
        $AuthUserData = getAuthorizatedUserData($connection, $authUserEmail, $currentTimestampClean, $JWTServerkey, $clientToken);

        if($AuthUserData['status'] == 1) {
            $userId = $AuthUserData['id'];

            // query
            $queryUsers = mysqli_query($connection, "SELECT 
            usr_id
            FROM users
            WHERE usr_email = '{$userEmail}' AND usr_status = 1
            AND usr_password = '{$userPasswordMd5}'
            ORDER BY usr_id
            DESC
            LIMIT 1") or die ("User Not Found");


            if (mysqli_num_rows ($queryUsers) > 0) {
                // delete user
                mysqli_query($connection, "UPDATE users SET
                usr_status = 0
                WHERE usr_id = '{$userId}'") or die("update error");    

                $currentTimestamp = Date('Y-m-d H:i:s');
                $currentTimestampClean = str_replace(" ", "", $currentTimestamp);

                // novo token
                $token = createJWTAuth('', $currentTimestampClean, $JWTServerkey);

                $dataResponse['token'] = $token;
                $dataResponse['timestamp'] = $currentTimestamp;

                $dataResponse['status'] = 1;

            } else {
                $dataResponse['message'] = 'Usuário ou senha inválido';
                $dataResponse['status'] = 3;
            }
        }

    } else {
        $dataResponse['message'] = 'Email inválido ou campo inválido';
    }


} else {
    // nao autehnticado
    $dataResponse['status'] = 2;
}

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
