<?php 


 //    // access
	// header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Credentials: true');
	// header('Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT');
	// header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');

	//conecta ao banco
	error_reporting(0);
	date_default_timezone_set('America/Sao_Paulo');
	// date_default_timezone_set('America/Fortaleza');	// sem horario de verao

		
	//busca dados do MySql
	require_once "functionData_offline.php";

	//connection OFFLINE
	$hostname = bd_hostname();
	$username = bd_username();
	$password = bd_password();
	$database = bd_galeria();
	$JWTServerkey = jwt_key();
	$httpHeaderData = apache_request_headers();

	// BUG Apache: need to check if is getting from capitalized or not
	$clientToken = isset($httpHeaderData['authorization']) ? $httpHeaderData['authorization'] : $httpHeaderData['Authorization'];

	//Conexão mysql
	$connection = mysqli_connect($hostname, $username, $password, $database) or die ("Erro na conexão do banco de dados.");

	//Seleciona o banco de dados
	mysqli_set_charset($connection, 'UTF8');	//faz connection usando UTF 8

?>