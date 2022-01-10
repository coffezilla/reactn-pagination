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

// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);
// $isAuth = true;

if($isAuth) {

    ######################################################################

    $querySeasons = mysqli_query($connection, "SELECT
    sea.sea_id,
    sea.sea_init,
    sea.sea_end,
    sea.sea_status
    FROM seasons AS sea
    WHERE sea.sea_status <> 0
    ORDER BY sea.sea_id DESC
    LIMIT 1");
    if (mysqli_num_rows($querySeasons)==0) {
        // no season found
    } else {
        while($dataSeason = mysqli_fetch_assoc($querySeasons)) {
            $seasonId = $dataSeason['sea_id'];
            
            $allTournaments = array();

            $queryTournamentSeasons = mysqli_query($connection, "SELECT
            tse.tse_id,
            tse.tor_id,
            tor.tor_name,
            tse.tse_status,
            tse.tse_rounds,
            tor.tor_type,
            tse.sea_id,
            tor.tor_reference
            FROM tournament_season AS tse
            INNER JOIN tournaments AS tor ON tor.tor_id = tse.tor_id
            WHERE tor.tor_status <> 0
            AND tse.sea_id = '{$seasonId}'
            AND ( tse.tse_status = 1 OR tse.tse_status = 2 OR tse.tse_status = 3 )
            
            ORDER BY tor.tor_id");
            if (mysqli_num_rows($queryTournamentSeasons)==0) {} else {
                $tournamentIndex = 1;
                while($dataTournamentSeason = mysqli_fetch_assoc($queryTournamentSeasons)) {
                    $tseId = $dataTournamentSeason['tse_id'];
                    $torReference = $dataTournamentSeason['tor_reference'];
                    $tseStatus = $dataTournamentSeason['tse_status'];
                    $torName = $dataTournamentSeason['tor_name'];
                    $torType = $dataTournamentSeason['tor_type'];
                    $tseRounds = $dataTournamentSeason['tse_rounds'];
                    $seasonInit = $dataTournamentSeason['sea_init'];
                    $seasonEnd = $dataTournamentSeason['sea_end'];
                    array_push($allTournaments, array(
                        "index" => $tournamentIndex,
                        "season" => $seasonId,
                        "reference" => $torReference,
                        "id" => $tseId,
                        "name" => $torName,
                        "status" => $tseStatus,
                        "type" => $torType,
                        "rounds" => $tseRounds
                    ));
                    $tournamentIndex++;
                }
            }    


            // filtrar apenas referencia final do torneio
            $currentReference = 0;
            $currentValue = 0;
            $newAllTournaments = array();
            
            foreach ($allTournaments as $key => $value) {

                if($currentReference != $value['reference']) {

                    // primeiro
                    if($currentReference != 0) {
                        // salva no array
                        array_push($newAllTournaments, $currentValue);
                    }

                    $currentReference = $value['reference'];
                }
                $currentValue = $value;
            }
            
            // ultimo registro
            array_push($newAllTournaments, $currentValue);

            $dataResponse['fixtures'] = $newAllTournaments;

        }
    }


    $dataResponse['status'] = 1;

    ######################################################################
} else { /* invalid token */ }

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
