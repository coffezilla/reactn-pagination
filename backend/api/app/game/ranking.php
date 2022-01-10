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
$resultsPerPage = 160; 
$indexInitSearch = ($resultsPerPage*$pageCurrent)-$resultsPerPage;

// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);
// $isAuth = true;

if($isAuth) {

    ######################################################################

    $dataResponse['ranking'] = array();

    // clubes com maior pontuacao
    // get all teams from the ranking
    $queryTeams = mysqli_query($connection, "SELECT
    tem.tem_name,
    tem.tem_id,
    tem.tem_points,
    tem.tem_emblem_url
    FROM teams AS tem
    WHERE tem.tem_status <> 0
    ORDER BY tem.tem_points DESC
    LIMIT $indexInitSearch, $resultsPerPage");

    if (mysqli_num_rows($queryTeams)==0) {
        // no team found
    } else {

        $numberRanking = $indexInitSearch;
        while($dataTeam = mysqli_fetch_assoc($queryTeams)) {
            $numberRanking = $numberRanking+1;
            $temId = $dataTeam['tem_id'];
            $temName = $dataTeam['tem_name'];
            $temPoints = $dataTeam['tem_points'];
            $temEmblemUrl = $dataTeam['tem_emblem_url'];
            
            array_push($dataResponse['ranking'], array(
                'position' => $numberRanking,
                'team' => array(
                    "id" => $temId,
                    "name" => $temName,
                    "value" => $temPoints.' Pontos',
                    "emblem" => $temEmblemUrl,
                ),
            ));  
        }

        $dataResponse['status'] = 1;
    } 

    ######################################################################
} else { /* invalid token */ }

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
