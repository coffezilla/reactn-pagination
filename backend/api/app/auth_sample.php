<?php 

header('Content-Type: application/json; charset=UTF-8');

include "../connect/bd_connect.php";

//
$dataResponse = array();
$dataResponse['status'] = 0;
$dataResponse['message'] = '';
$errors = array();

// ========================================================
// NEW VAR

// $userEmail = addslashes(trim($_GET['email']));
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

    // ========================================================

    $dataResponse['status'] = 1;

    // ========================================================

} else {
    // nao autehnticado
    $dataResponse['status'] = 2;
}

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
