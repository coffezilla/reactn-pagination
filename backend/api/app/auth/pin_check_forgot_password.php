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

$userPin = addslashes(trim($_POST['pin']));
$userPin = str_replace(" ", "", $userPin);

$currentTimestamp = Date('Y-m-d H:i:s');
$currentTimestampClean = str_replace(" ", "", $currentTimestamp);

// verify
$checkers = array($userEmail, $userPin);
$validInputs = checkEmptyData($checkers, 1);


// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);

if($isAuth) {

    if($validInputs) {

        // check if pin is correct
        $queryUsers = mysqli_query($connection, "SELECT 
        usr_id
        FROM users
        WHERE usr_email = '{$userEmail}' 
        AND usr_status = 1 
        AND  usr_pin_recovery = '{$userPin}'
        ORDER BY usr_id
        DESC
        LIMIT 1") or die ("User Not Found");
        if (mysqli_num_rows ($queryUsers) > 0) {
            $dataResponse['status'] = 1;
        } else {
            $dataResponse['message'] = 'Usuário ou pin errado';
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
