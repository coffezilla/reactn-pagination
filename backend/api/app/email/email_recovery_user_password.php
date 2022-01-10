<?php 

$pin = $pinRecovery;
$baseURL = 'https://www.efm.bhxsites.com.br/reset-password';

$emailBody = '
<!DOCTYPE html>
<html>
<head>
  <title>E-mail</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <style type="text/css">.top-header{background-color:#fff;padding:0px 0;text-align:center;}.footer{background-color:#f7f7f7;font-size:12px;line-height:18px}.bt-footer{background-color:#000;color:#fff;text-align:center;font-size:14px;font-weight:700; display: block; padding: 17px 0px; text-decoration: none;}.txt-r{text-align:right}.txt-c{text-align:center}.carrinho-total{background-color:#f5f5f5;font-size:14px}.resumo-pedido{font-size:14px}img{display:block;max-width:100%;text-align:center;margin:auto;height:auto}td{padding:0}table{border:1px solid #e8e8e8;font-family:Helvetica,Arial,sans-serif;background-color:#fff;padding:0;font-size:16px}td{padding:20px}th{padding:20px;text-align:left;background-color:#eee}h2{font-family:Helvetica,Arial,sans-serif;padding:10px 0 20px;margin:0;font-size:20px}h3{font-size:30px;padding:0px;}p{font-family:Helvetica,Arial,sans-serif;margin:0;line-height:25px;padding:0 0 20px;color:#545454}ul{margin:0 0 10px;padding:0}li{list-style:none;list-style-position:inside;line-height:25px;font-family:Helvetica,Arial,sans-serif;color:#545454}.botao{font-family:Helvetica,Arial,sans-serif;line-height:20px;font-size:16px;padding:12px 20px;background:#03a9f4;display:inline-block;color:#fff;text-decoration:none;margin:10px 0}</style>
</head>
<body width="600px" text="#585858">
  

  <br>
  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px;border: 1px solid #e8e8e8;font-family: Helvetica,Arial,sans-serif;background-color: #fff;padding: 0;font-size: 16px;">
    <tr>
      <th style="padding: 20px;text-align: left;background-color: #eee;">'.$emailTitle.'</th>
    </tr>
    <tr>
      <td style="padding: 20px;">
          <p style="list-style: none;list-style-position: inside;line-height: 25px;font-family: Helvetica,Arial,sans-serif;color: #545454;">Este é um e-mail automático gerado pois você solicitou a alteração de senha da sua conta no <strong>React Native Auth</strong> da BHX Sites.</p>
          <p style="list-style: none;list-style-position: inside;line-height: 25px;font-family: Helvetica,Arial,sans-serif;color: #545454;">Utilize o código PIN para alterar a senha pelo aplicativo</p>
          <p style="list-style: none;list-style-position: inside;line-height: 25px;font-family: Helvetica,Arial,sans-serif;color: #545454;">PIN: '.$pin.'</p>
      </td>
    </tr>
  </table>
  <br>

</body>
</html>';

// echo $emailBody;

?>