<?php 

header('Content-Type: application/json; charset=UTF-8');

include "../connect/bd_connect.php";
include "../helpers/utils.php";

//
$dataResponse = array();
$dataResponse['status'] = 0;
$dataResponse['message'] = '';
$errors = array();

// ========================================================
// AUTH BASIC

$authTimestamp = addslashes(trim($_GET['auth_timestamp']));
$currentTimestampClean = str_replace(" ", "", $authTimestamp);

$authUserEmail = addslashes(trim($_GET['auth_email']));
$authUserEmail = str_replace(" ", "", $authUserEmail);

// ========================================================
// NEW VAR

// ========================================================
// CHECKING VALIDATION

// verify
$checkers = array($authUserEmail, $currentTimestampClean);
$validInputs = checkEmptyData($checkers, 1);


// ========================================================


// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);

if($isAuth) {

    // check if email is the same as the token email
    if($validInputs) {
        // JWT auth
        $AuthUserData = getAuthorizatedUserData($connection, $authUserEmail, $currentTimestampClean, $JWTServerkey, $clientToken);
        if($AuthUserData['status'] == 1) {
            $userId = $AuthUserData['id'];

            // ========================================================

            // get user data
            $queryUsers = mysqli_query($connection, "SELECT 
            usr_id,
            usr_name,
            usr_email
            FROM users
            WHERE usr_id = '{$userId}' AND usr_status = 1
            ORDER BY usr_id
            DESC
            LIMIT 1") or die ("User Not Found");

            $dataUser = mysqli_fetch_assoc($queryUsers);


            $dataResponse['status'] = 1;
            $dataResponse['user'] = array(
                "name" => $dataUser['usr_name'],  
                "email" => $dataUser['usr_email']
            );


            // ========================================================

        } else {
            $dataResponse['message'] = 'Usuário não autenticado';
            $dataResponse['status'] = 4;
        }
    } else {
        $dataResponse['message'] = 'Campo em branco';
        $dataResponse['status'] = 3;
    }
} else {
    // nao autehnticado
    $dataResponse['status'] = 2;
}

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
