<?php


//
function createJWTAuth($email, $timeStamp, $key) {

    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];
    $header = json_encode($header);
    $header = base64UrlEncode($header);
    
    $payload = [
        'email' => $email,
        'timestamp' => $timeStamp
    ];
    $payload = json_encode($payload);
    $payload = base64UrlEncode($payload);
    
    $signature = hash_hmac('sha256', $header . "." . $payload , $key , true );
    $signature = base64UrlEncode($signature);
   

    return $header.".".$payload.".".$signature;
}

//
function verifyAuth($headerToken, $key) {


            
    $bearer = explode (' ', $headerToken);
    $isValidAuth = 0;

    $token = explode('.', $bearer[1]);
    $header = $token[0];
    $payload = $token[1];
    $sign = $token[2];

    $valid = hash_hmac('sha256', $header . "." . $payload , $key , true);
    $valid = base64UrlEncode($valid);


    if ($sign == $valid) {
        return true;
    } else {
        return false;
    }
}


//
function base64UrlEncode ($value) {
    $b64 = base64_encode($value);
    if ($b64 === false) {
        return false;
    }
    $url = strtr($b64, '+/', '-_');
    return rtrim($url, '=');
}


// 
function getAuthorizatedUserData($connection, $userEmail, $currentTimestampClean,  $JWTServerkey, $clientToken) {

    $responseData = array(
        'status' => '0',
        'id' => '0',
        'email' => '0'
    );
    $emailValidation = createJWTAuth($userEmail, $currentTimestampClean, $JWTServerkey);

    // check if email is correct
    if('Bearer '.$emailValidation === $clientToken) {

        // find user id
        $queryUsers = mysqli_query($connection, "SELECT 
        usr_id,
        usr_email
        FROM users
        WHERE usr_email = '{$userEmail}' AND usr_status = 1
        ORDER BY usr_id
        DESC
        LIMIT 1") or die ("User Not Found");

        // check if user was found
        if (mysqli_num_rows ($queryUsers) > 0) {
            $dataUser = mysqli_fetch_assoc($queryUsers);
            $responseData['status'] = 1;
            $responseData['id'] = $dataUser['usr_id'];
            $responseData['email'] = $dataUser['usr_email'];
        } else {

            // no user
            $responseData['status'] = 3;
        }

    } else {
        $responseData['status'] = 4;
        
    }

    // not auth
    $responseData['v1'] = $emailValidation;
    $responseData['v2'] = $clientToken;
    $responseData['livre'] = $userEmail.' - '.$currentTimestampClean;

    return $responseData;
}
