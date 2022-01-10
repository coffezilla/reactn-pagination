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

    // get last matches
    $allRounds = array();
    $queryLastMatches = mysqli_query($connection, "SELECT
    mtc.mtc_id,
    mtc.mtc_status,
    mtc.mtc_time_of_day,
    mtc.mtc_weather,
    mtc.mtc_round,
    mtc.mtc_schedule,
    mtc.spo_home_id,
    mtc.spo_home_goal,
    mtc.mtc_public,
    mtc.std_id,
    mtc.mtc_money,
    mtc.spo_away_id,
    mtc.spo_away_goal,
    tor.tor_reference,
    tse.sea_id,

    (SELECT tem.tem_nickname FROM teams AS tem WHERE tem.tem_id = (SELECT spo.tem_id FROM tournament_spot AS spo WHERE spo.spo_id = mtc.spo_home_id)) AS team_home_nickname,
    (SELECT tem.tem_emblem_url FROM teams AS tem WHERE tem.tem_id = (SELECT spo.tem_id FROM tournament_spot AS spo WHERE spo.spo_id = mtc.spo_home_id)) AS team_home_emblem,
    (SELECT spo.tem_id FROM tournament_spot AS spo WHERE spo.spo_id = mtc.spo_home_id) AS team_home_id,

    (SELECT tem.tem_nickname FROM teams AS tem WHERE tem.tem_id = (SELECT spo.tem_id FROM tournament_spot AS spo WHERE spo.spo_id = mtc.spo_away_id)) AS team_away_nickname,
    (SELECT tem.tem_emblem_url FROM teams AS tem WHERE tem.tem_id = (SELECT spo.tem_id FROM tournament_spot AS spo WHERE spo.spo_id = mtc.spo_away_id)) AS team_away_emblem,
    (SELECT spo.tem_id FROM tournament_spot AS spo WHERE spo.spo_id = mtc.spo_away_id) AS team_away_id,

    (SELECT tem.tem_nickname FROM teams AS tem WHERE tem.tem_id = (SELECT spo.tem_id FROM tournament_spot_history AS spo WHERE spo.spo_id = mtc.spo_home_id AND spo.tse_id = mtc.tse_id AND spo.hspo_status = 1)) AS team_home_nickname_history,
    (SELECT tem.tem_emblem_url FROM teams AS tem WHERE tem.tem_id = (SELECT spo.tem_id FROM tournament_spot_history AS spo WHERE spo.spo_id = mtc.spo_home_id AND spo.tse_id = mtc.tse_id AND spo.hspo_status = 1)) AS team_home_emblem_history,
    (SELECT spo.tem_id FROM tournament_spot_history AS spo WHERE spo.spo_id = mtc.spo_home_id AND spo.tse_id = mtc.tse_id AND spo.hspo_status = 1) AS team_home_id_history,

    (SELECT tem.tem_nickname FROM teams AS tem WHERE tem.tem_id = (SELECT spo.tem_id FROM tournament_spot_history AS spo WHERE spo.spo_id = mtc.spo_away_id AND spo.tse_id = mtc.tse_id AND spo.hspo_status = 1)) AS team_away_nickname_history,
    (SELECT tem.tem_emblem_url FROM teams AS tem WHERE tem.tem_id = (SELECT spo.tem_id FROM tournament_spot_history AS spo WHERE spo.spo_id = mtc.spo_away_id AND spo.tse_id = mtc.tse_id AND spo.hspo_status = 1)) AS team_away_emblem_history,
    (SELECT spo.tem_id FROM tournament_spot_history AS spo WHERE spo.spo_id = mtc.spo_away_id AND spo.tse_id = mtc.tse_id AND spo.hspo_status = 1) AS team_away_id_history,

    mtc.mtc_winner,
    tor.tor_name,
    tor.tor_phase_name,
    tse.tse_status
    FROM matches AS mtc
    INNER JOIN tournament_season AS tse ON tse.tse_id = mtc.tse_id
    INNER JOIN tournaments AS tor ON tor.tor_id = tse.tor_id        
    WHERE mtc.mtc_schedule = (
    SELECT
    mtcn.mtc_schedule
    FROM matches AS mtcn
    WHERE mtcn.mtc_status = 1
    ORDER BY mtcn.mtc_schedule DESC
    LIMIT 1)");

    if (mysqli_num_rows($queryLastMatches)==0) {} else {
        
        while($dataLastMatch = mysqli_fetch_assoc($queryLastMatches)) {

            $matchWeatherArr = array("chuva", "normal");
            $matchTimeOfDayArr = array("dia", "noite");

            
            // get stadium
            $matchStadium = $dataLastMatch['std_id'];
            $matchId = $dataLastMatch['mtc_id'];
            $seasonId = $dataLastMatch['sea_id'];
            $torReference = $dataLastMatch['tor_reference'];
            $matchTimeOfDay = $dataLastMatch['mtc_time_of_day'];
            $matchTimeOfDayText = $matchTimeOfDayArr[$dataLastMatch['mtc_time_of_day']-1];
            $matchWeather = $dataLastMatch['mtc_weather'];
            $matchWeatherText = $matchWeatherArr[$dataLastMatch['mtc_weather']-1];
            $matchStatus = $dataLastMatch['mtc_status'];
            

            if($dataLastMatch['tse_status'] == 1) {


                $teamHomeId = $dataLastMatch['team_home_id_history'];
                $teamHomeNickname = $dataLastMatch['team_home_nickname_history'];
                $teamHomeEmblem = $dataLastMatch['team_home_emblem_history'] == '' ? 'default' : $dataLastMatch['team_home_emblem_history'];
                
                $teamAwayId = $dataLastMatch['team_away_id_history'];
                $teamAwayNickname = $dataLastMatch['team_away_nickname_history'];
                $teamAwayEmblem = $dataLastMatch['team_away_emblem_history'] == '' ? 'default' : $dataLastMatch['team_away_emblem_history'];

            } else {

                $teamHomeId = $dataLastMatch['team_home_id'];
                $teamHomeNickname = $dataLastMatch['team_home_nickname'];
                $teamHomeEmblem = $dataLastMatch['team_home_emblem'] == '' ? 'default' : $dataLastMatch['team_home_emblem'];
                
                $teamAwayId = $dataLastMatch['team_away_id'];
                $teamAwayNickname = $dataLastMatch['team_away_nickname'];
                $teamAwayEmblem = $dataLastMatch['team_away_emblem'] == '' ? 'default' : $dataLastMatch['team_away_emblem'];

            }

            $mtcPublic = $dataLastMatch['mtc_public'];
            $mtcMoney = $dataLastMatch['mtc_money'];

            // status da partida - 1 partida finalizada | 2 partida nao realizada ainda
            $teamHomeGoal = $matchStatus == 1 ? $dataLastMatch['spo_home_goal'] : '';
            $teamAwayGoal = $matchStatus == 1 ? $dataLastMatch['spo_away_goal'] : '';

            $matchDate = $dataLastMatch['mtc_schedule'];


            array_push($allRounds, array(
                "home" => array(
                    "id" => $teamHomeId,
                    "name" => $teamHomeNickname,
                    "emblem" => $teamHomeEmblem,
                    "score" => $teamHomeGoal
                ),
                "away" => array(
                    "id" => $teamAwayId,
                    "name" => $teamAwayNickname,
                    "emblem" => $teamAwayEmblem,
                    "score" => $teamAwayGoal
                ),
                "match" => array(
                    "id" => $matchId,
                    "round" => $dataLastMatch['mtc_round'],
                    "scheduel" => $matchDate,
                    "tournament" => array(
                        // "id" => $torReference,
                        "ref" => $torReference,
                        "phase_name" => $dataLastMatch['tor_phase_name'],
                        "season" => $seasonId,
                        "name" => $dataLastMatch['tor_name']
                    )                    
                )
            ));

        }
    }
    $dataResponse['lastMatches'] = $allRounds;


    $dataResponse['status'] = 1;

    ######################################################################
} else { /* invalid token */ }

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
