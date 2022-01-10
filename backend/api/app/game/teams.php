<?php 
#
# GET
# NEED: AUTH
#

header('Content-Type: application/json; charset=UTF-8');

include "../connect/bd_connect.php";

//
$dataResponse = array();
$dataResponse['status'] = 0;
$errors = array();

$pageCurrent = 1;
$resultsPerPage = 200; 
$indexInitSearch = ($resultsPerPage*$pageCurrent)-$resultsPerPage;

$countries = array(
"Brasil",
"EUA",
"Espanha",
"Itália",
"França",
"Arábia",
"Japão",
"Inglaterra",
"Portugal",
"Alemanha",
"Argentina",
"Peru",
"Bolívia",
"Paraguai",
"Chile",
"Uruguai",
"México",
"-",
"Equador",
"Colômbia",
"-",
"Rússia",
"-",
"Holanda",
"Turquia",
"Escócia",
"Ucrânia",
"China",
"Venezuela");


// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);

if($isAuth) {

    ######################################################################

    $dataResponse['countries'] = array();

    $countryIdCurrent = 0;

    $queryTeams = mysqli_query($connection, "SELECT
    tem.tem_name,
    tem.tem_id,
    tem.tem_emblem_url,
    tem.tem_country,
    (SELECT cct.usr_id FROM coaches_contract AS cct WHERE cct.tem_id = tem.tem_id AND cct.cct_status = 2 LIMIT 1) AS usr_id
    FROM teams AS tem
    WHERE tem.tem_status = 1
    ORDER BY tem.tem_country, tem.tem_name
    LIMIT $indexInitSearch, $resultsPerPage") or die ("Teams Not Found");

    if (mysqli_num_rows ($queryTeams) > 0) {


    	$teamsByCountry = array();

        // teams availables
        while($dataTeam = mysqli_fetch_assoc($queryTeams)) {

			$countryId = $dataTeam['tem_country'];

			// novo pais
        	if($countryId != $countryIdCurrent && $countryIdCurrent != 0) {

        		// tip: ultimo id do pais
        		$lastCountryId = intval($countryId-1);
        		array_push($dataResponse['countries'], array(
        			"country" => array(
        				"id" => $lastCountryId,
        				"name" => $countries[$lastCountryId-1]
        			),
        			"teams" => $teamsByCountry,
        		));
        		$teamsByCountry = array();
        	}

        	$countryIdCurrent = $countryId;

            $coachId = $dataTeam['usr_id'];
            $coachName = '';
            $teamAvailableStatus = ($dataTeam['usr_id'] == '') ? 'enabled' : 'disabled';

            // search coach data
            if($teamAvailableStatus == 'disabled') {                
                $queryTeam = mysqli_query($connection, "SELECT
                usr.usr_name
                FROM users AS usr
                WHERE usr.usr_id = '{$coachId}'
                AND usr.usr_status = 1") or die ("User Not Found"); 
                $dataUser = mysqli_fetch_assoc($queryTeam);
                $coachName = $dataUser['usr_name'];
            }


            array_push($teamsByCountry, 
                array(
                    "name" => $dataTeam['tem_name'], 
                    "id" => $dataTeam['tem_id'],
                    "emblem" => $dataTeam['tem_emblem_url'],
                    "coach" => array("id" => $coachId, "name" => $coachName),
                    "status" => $teamAvailableStatus
                )
            );


        }

		// tip: ultimo registro
		$lastCountryId = intval($countryId);
		array_push($dataResponse['countries'], array(
			"country" => array(
				"id" => $lastCountryId,
				"name" => $countries[$lastCountryId-1]
			),
			"teams" => $teamsByCountry,
		));        
    }



    $dataResponse['status'] = 1;

    ######################################################################
} else { /* invalid token */ }

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
