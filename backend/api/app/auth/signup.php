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

$userName = addslashes(trim($_POST['name']));

$userPassword = addslashes(trim($_POST['password']));
$userPassword = str_replace(" ", "", $userPassword);
$userPasswordMd5 = md5($userPassword);

$currentTimestamp = Date('Y-m-d H:i:s');
$currentTimestampClean = str_replace(" ", "", $currentTimestamp);

// verify
$isNewUser = false;
// verify
$checkers = array($userEmail, $userName, $userPassword);
$validInputs = checkEmptyData($checkers, 1);


// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);


if($isAuth) {
    // JWT auth
    // limpa posts anti sql inject
    // trim em campos sem espaco
    // verifica se nao esta vazio
    // verifica se possuem caracteres minimos
    // check input

    if($validInputs) {

        // email
        // cannot create using email already used even if has status 0
        $queryUsers = mysqli_query($connection, "SELECT 
        COUNT(usr.usr_id) 
        FROM users AS usr
        WHERE usr.usr_email = '{$userEmail}' ");
        $userEmailFound = mysqli_result($queryUsers, 0);


        if( $userEmailFound == 0 ) {

            if( $userNicknameFound == 0 ) {
                $isNewUser = true;
            } else {
                $dataResponse['status'] = 4;
                $dataResponse['message'] = 'Este nickname já foi utilizado';
            }

        } else {
            $dataResponse['status'] = 3;
            $dataResponse['message'] = 'Este e-mail já foi utilizado';
        }
    } else {
        $dataResponse['message'] = 'Campo em branco';
        $dataResponse['status'] = 2;
    }


    if($isNewUser && $validInputs) {
        // get current new user id
        $queryUsers = mysqli_query($connection, "SELECT COUNT(usr_id) FROM users");
        $userTotal = mysqli_result($queryUsers, 0);
        $userIdCurrent = ($userTotal+1);  

        // cria novo stadium
        mysqli_query($connection, "INSERT INTO users VALUES (
        '',
        1,
        '{$userName}',
        'last name',
        '{$userEmail}',
        '{$userPasswordMd5}',
        0);") or die("erro sign up");

        $token = createJWTAuth($userEmail, $currentTimestampClean, $JWTServerkey);

        $dataResponse['token'] = $token;
        $dataResponse['timestamp'] = $currentTimestamp;
        $dataResponse['email'] = $userEmail;
        $dataResponse['status'] = 1;

        $dataResponse['user'] = array(
            'id' => $userIdCurrent,
            'email' => $userEmail,
        );    

    }

}
$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;


function mysqli_result($res,$row=0,$col=0){ 
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}