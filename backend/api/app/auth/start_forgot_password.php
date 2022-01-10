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

$currentTimestamp = Date('Y-m-d H:i:s');
$currentTimestampClean = str_replace(" ", "", $currentTimestamp);

// verify
$checkers = array($userEmail);
$validInputs = checkEmptyData($checkers, 1);


// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);

if($isAuth) {

    if($validInputs) {

        // get user id
        $queryUsers = mysqli_query($connection, "SELECT 
        usr_id,
        usr_name
        FROM users
        WHERE usr_email = '{$userEmail}' AND usr_status = 1
        ORDER BY usr_id
        DESC
        LIMIT 1") or die ("User Not Found");
        // get user data
        if (mysqli_num_rows ($queryUsers) > 0) { 

            $dataUser = mysqli_fetch_assoc($queryUsers);
            $userId = $dataUser['usr_id'];
            $userName = $dataUser['usr_name'];
            $pinRecovery = substr(rand(111111,999999), 0, 6);

            // exclui usuario
            mysqli_query($connection, "UPDATE users SET
            usr_pin_recovery = '{$pinRecovery}'
            WHERE usr_id = '{$userId}'") or die("update error"); 

            // ======= ENVIAR EMAIL COM LINK
            // email
            $emailTitle = "Alterar senha";
            // sender
            $senderName = "React Native Auth";
            $senderEmail = "atendimento@bhxsites.com.br";
            // receiver
            $receiverName = $userName;
            $receiverEmail = $userEmail;


            // ENVIO DE EMAIL
            require_once("../email/phpmailer/PHPMailerAutoload.php");      
            $mail = new PHPMailer();
            $mail->IsSMTP = ('smtp');
            $mail->Mailer = ('mail');
            $mail->SMTPSecure = 'ssl';
            $mail->SMTPAuth = true;
            $mail->From = $senderEmail;
            $mail->FromName = $senderName;
            $mail->AddReplyTo($senderEmail, $senderName);
            $mail->AddAddress( $receiverEmail, $receiverName);
            $mail->IsHTML(true);
            $mail-> CharSet = 'UTF-8';
            // $mail->AddEmbeddedImage("/images/logo.png", "logomarca");

            // e-mail template
            include '../email/email_recovery_user_password.php';

            $mail->Subject  = $emailTitle;
            $mail->Body = $emailBody;
            $mail->AltBody = $emailBody;

            // $mail->Body = $emailBody;
            // $mail->AltBody = $emailBody;
            $sendedEmail = $mail->Send();
            $mail->ClearAllRecipients();
            $mail->ClearAttachments();
            

            if ($sendedEmail) {  
                $dataResponse['status'] = 1;
            } else {
                $dataResponse['message'] = 'Erro ao enviar solicitação, tente novamente.';
                $dataResponse['status'] = 4;
            }

        } else {
    
            $dataResponse['message'] = 'Usuário nao encontrado';
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
