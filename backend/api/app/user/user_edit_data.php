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

$authTimestamp = addslashes(trim($_POST['auth_timestamp']));
$currentTimestampClean = str_replace(" ", "", $authTimestamp);

$authUserEmail = addslashes(trim($_POST['auth_email']));
$authUserEmail = str_replace(" ", "", $authUserEmail);

// ========================================================
// NEW VAR

$userName = addslashes(trim($_POST['name']));

// ========================================================
// CHECKING VALIDATION

// verify
$checkers = array($authUserEmail, $currentTimestampClean, $userName);
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

            // edit user data
            mysqli_query($connection, "UPDATE users SET
            usr_name = '{$userName}'
            WHERE usr_id = '{$userId}'") or die("update error");    

            $dataResponse['status'] = 1;

            // ========================================================

        } else {
            $dataResponse['message'] = 'Usuário não autenticado';
            $dataResponse['status'] = 4;
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
