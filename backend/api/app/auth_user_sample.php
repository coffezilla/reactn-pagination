<?php 

header('Content-Type: application/json; charset=UTF-8');

include "../connect/bd_connect.php";

//
$dataResponse = array();
$dataResponse['status'] = 0;
$dataResponse['message'] = '';
$errors = array();

// ========================================================
// AUTH BASIC

$authTimestamp = addslashes(trim($_POST['auth_timestamp']));
$currentTimestampClean = str_replace(" ", "", $authTimestamp);

$authUserEmail = addslashes(trim($_POST['auth_email']));
$authUserEmail = str_replace(" ", "", $authUserEmail);

// ========================================================
// NEW VAR

// $userEmail = addslashes(trim($_POST['email']));
// $userEmail = str_replace(" ", "", $userEmail);

// ========================================================
// CHECKING VALIDATION

$validInputs = false;

// check input
if(
$authUserEmail != '' && strlen($authUserEmail) >= 3 &&
$currentTimestampClean != '' && strlen($currentTimestampClean) >= 3
) {
    // pode passar
    $validInputs = true;

} else {
    $dataResponse['message'] = 'Campo em branco';
    $dataResponse['status'] = 2;
}

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

            $dataResponse['status'] = 1;

            // ========================================================

        } else {
            $dataResponse['message'] = 'Usuário não autenticado';
            $dataResponse['status'] = 4;
        }
    }
} else {
    // nao autehnticado
    $dataResponse['status'] = 2;
}

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
