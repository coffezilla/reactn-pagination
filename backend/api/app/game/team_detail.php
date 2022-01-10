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

$teamId = addslashes(trim($_GET['tid']));
$playerSpecialtyText = array("-", "Goleiro", "Defesa", "Meio", "Ataque");
$playerSpecialtyLetter = array("-", "GK", "DF", "MC", "AT");
$teamTraining = array("Foco em recuperacao", "Foco em ensaio de jogadas");
$playerCountryText = array("Brasil", "EUA", "Espanha", "Itália", "França", "Arábia Saudita", "Japão", "Inglaterra", "Portugal", "Alemanha", "Argentina", "Peru", "Bolívia", "Paraguai", "Chile", "Uruguai", "México", "Colômbia", "Equador", "Bélgica", "Rússia", "Grécia", "Holanda", "Suécia", "Turquia", "Escócia", "Ucrânia", "China", "Venezuela");
$formationText = array("4-4-2", "4-3-3", "3-5-2", "5-3-2", "4-5-1");
$strategyText = array("Moderado", "Ultra defensivo", "Contra-ataque", "Ataque total");


// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);
// $isAuth = true;

if($isAuth) {

    ######################################################################

        // get team data
        $queryTeamsTotal = mysqli_query($connection, "SELECT
        sum(tem.tem_fans) AS total_fans
        FROM teams AS tem
        WHERE tem.tem_status = 1");
        $dataTeamTotal = mysqli_fetch_assoc($queryTeamsTotal);
        $totalSupporters = $dataTeamTotal['total_fans'];

        // get team data
        $queryTeams = mysqli_query($connection, "SELECT
        tem.tem_name,
        tem.tem_points,
        tem.tem_money,
        tem.tem_fans,
        tem.tem_country,
        tem.tem_fans_mood,
        tem.tem_formation,
        tem.spn_id,
        tem.tem_auto_squad,
        tem.tem_auto_transfers,
        tem.tem_auto_stamina_low,
        tem.tem_strategy,
        tem.tem_emblem_url,
        tem.tem_training,
        tem.tem_main_stadium,
        tem.tem_default_stadium,
        tem.spn_id,
        tem.tem_tolerance
        FROM teams AS tem
        WHERE tem.tem_id = '{$teamId}'
        AND tem.tem_status <> 0");
        if (mysqli_num_rows($queryTeams)==0) {
            // no team found
        } else {

            $dataTeam = mysqli_fetch_assoc($queryTeams);

            // checa estadio
            $temFansPercentage = (100/$totalSupporters)*$dataTeam['tem_fans'];
            $temMainStadiumId = $dataTeam['tem_main_stadium'];
            $temDefaultStadiumId = $dataTeam['tem_default_stadium'];

            if($temMainStadiumId == 0) {
        
                $queryStadiums = mysqli_query($connection, "SELECT
                std.std_id,
                std.std_name,
                std.std_cost,
                std.std_capacity,
                std.std_level
                FROM stadiums AS std 
                WHERE std.std_id = '{$temDefaultStadiumId}'");

                if (mysqli_num_rows($queryStadiums)==0) {} else {
                    $dataStadium = mysqli_fetch_assoc($queryStadiums);
                    $stdName = $dataStadium['std_name'];
                    $stdId = $dataStadium['std_id'];
                    $stdCost = $dataStadium['std_cost'];
                    $stdCapacity = $dataStadium['std_capacity'];
                    $stdLevel = $dataStadium['std_level'];
                }           

            } else {

                $queryStadiums = mysqli_query($connection, "SELECT
                std.std_id,
                std.std_name,
                std.std_cost,
                std.std_capacity,
                std.std_level
                FROM stadiums AS std 
                WHERE std.std_id = '{$temMainStadiumId}'");

                if (mysqli_num_rows($queryStadiums)==0) {} else {
                    $dataStadium = mysqli_fetch_assoc($queryStadiums);
                    $stdName = $dataStadium['std_name'];
                    $stdId = $dataStadium['std_id'];
                    $stdCost = $dataStadium['std_cost'];
                    $stdCapacity = $dataStadium['std_capacity'];
                    $stdLevel = $dataStadium['std_level'];
                }

            }

        
            // coach
            $queryCoaches = mysqli_query($connection, "SELECT
            cct.usr_id,
            usr.usr_name,
            usr.usr_nickname
            FROM coaches_contract AS cct
            INNER JOIN users AS usr ON usr.usr_id = cct.usr_id
            WHERE cct.tem_id = '{$teamId}'
            AND cct.cct_status = 2
            ORDER BY cct.cct_id DESC");
           if (mysqli_num_rows($queryCoaches)==0) {
                $coachId = 0;
                $coachName = "Sem técnico";
                $coachNickname = "Sem técnico";
            } else {
                $dataCoach = mysqli_fetch_assoc($queryCoaches);
                $coachId = $dataCoach['usr_id'];
                $coachName = $dataCoach['usr_name'];
                $coachNickname = $dataCoach['usr_nickname'];
            }
            
            // sponsors
            $spnId = $dataTeam['spn_id'];

            if($spnId == 0) {
        
                $spnName = "Sem patrocinio";
                $spnMoney = 0;
                $spnLevel = 0;

            } else {
                $querySponsors = mysqli_query($connection, "SELECT
                spn.spn_name,
                spn.spn_money,
                spn.spn_level
                FROM sponsors AS spn
                WHERE spn.spn_id = '{$spnId}'");

                if (mysqli_num_rows($querySponsors)==0) {} else {
                    $dataSponsor = mysqli_fetch_assoc($querySponsors);
                    $spnName = $dataSponsor['spn_name'];
                    $spnMoney = $dataSponsor['spn_money'];
                    $spnLevel = $dataSponsor['spn_level'];
                }           
            }


            // overall
            $squadStatsGk = array();
            $squadStatsGkSpecialty = array();
            $squadStatsDef = array();
            $squadStatsDefSpecialty = array();
            $squadStatsMid = array();
            $squadStatsMidSpecialty = array();
            $squadStatsAta = array();
            $squadStatsAtaSpecialty = array();

            // avg de todos os players independend da especialidade
            $squadStatGkGeneral = 0;
            $squadStatDefGeneral = 0;
            $squadStatMidGeneral = 0;
            $squadStatAtaGeneral = 0;
            
            // busca todas as matches desta season
            $queryStatAvg = mysqli_query($connection, "SELECT
            pla.pla_stat_gk,
            pla.pla_stat_def,
            pla.pla_stat_mid,
            pla.pla_stat_ata,
            pla.pla_specialty
            FROM players AS pla
            WHERE pla.tem_id = '{$teamId}'
            AND (pla.pla_status = 1 OR pla.pla_status = 2)");
            while($dataPlayer = mysqli_fetch_assoc($queryStatAvg)) {

                // average specialty
                if($dataPlayer['pla_specialty'] == 1) {
                    array_push($squadStatsGkSpecialty, $dataPlayer['pla_stat_gk']);
                } else if($dataPlayer['pla_specialty'] == 2) {
                    array_push($squadStatsDefSpecialty, $dataPlayer['pla_stat_def']);
                } else if($dataPlayer['pla_specialty'] == 3) {
                    array_push($squadStatsMidSpecialty, $dataPlayer['pla_stat_mid']);
                } else if($dataPlayer['pla_specialty'] == 4) {
                    array_push($squadStatsAtaSpecialty, $dataPlayer['pla_stat_ata']);
                }

                // average geral
                array_push($squadStatsGk, $dataPlayer['pla_stat_gk']);
                array_push($squadStatsDef, $dataPlayer['pla_stat_def']);
                array_push($squadStatsMid, $dataPlayer['pla_stat_mid']);
                array_push($squadStatsAta, $dataPlayer['pla_stat_ata']);  

            }
            
            for($i = 0 ; $i<count($squadStatsGk) ; $i++) {
                $squadStatGkGeneral += $squadStatsGk[$i];
                $squadStatDefGeneral += $squadStatsDef[$i];
                $squadStatMidGeneral += $squadStatsMid[$i];
                $squadStatAtaGeneral += $squadStatsAta[$i];
            }

            // encontra media de cada specialty geral. Nao considera peso 
            $squadStatGkGeneral = $squadStatGkGeneral/count($squadStatsGk);
            $squadStatDefGeneral = $squadStatDefGeneral/count($squadStatsDef);
            $squadStatMidGeneral = $squadStatMidGeneral/count($squadStatsMid);
            $squadStatAtaGeneral = $squadStatAtaGeneral/count($squadStatsAta);

            // average gk
            $playersAvgGk = 1;
            $teamAvgGkBalanced = 1;
            if(count($squadStatsGkSpecialty) > 0) {
                $playersAvgGk = 0;
                foreach ($squadStatsGkSpecialty as $key => $value) {
                    $playersAvgGk += $value;
                }
                $playersAvgGk = $playersAvgGk/count($squadStatsGkSpecialty);
                $teamAvgGkBalanced = floor(($squadStatGkGeneral*0.1)+($playersAvgGk*0.9));
            }
            
            // average def
            $playersAvgDef = 1;
            $teamAvgDefBalanced = 1;
            if(count($squadStatsDefSpecialty) > 0) {
                $playersAvgDef = 0;
                foreach ($squadStatsDefSpecialty as $key => $value) {
                    $playersAvgDef += $value;
                }
                $playersAvgDef = $playersAvgDef/count($squadStatsDefSpecialty);
                $teamAvgDefBalanced = floor(($squadStatDefGeneral*0.1)+($playersAvgDef*0.9));
            }

            // average mid
            $playersAvgMid = 1;
            $teamAvgMidBalanced = 1;
            if(count($squadStatsMidSpecialty) > 0) {
                $teamAvgMidBalanced = 0;
                foreach ($squadStatsMidSpecialty as $key => $value) {
                    $playersAvgMid += $value;
                }
                $playersAvgMid = $playersAvgMid/count($squadStatsMidSpecialty);
                $teamAvgMidBalanced = floor(($squadStatMidGeneral*0.1)+($playersAvgMid*0.9));
            }

            // average ata
            $playersAvgAta = 1;
            $teamAvgAtaBalanced = 1;
            if(count($squadStatsAtaSpecialty) > 0) {
                $playersAvgAta = 0;
                foreach ($squadStatsAtaSpecialty as $key => $value) {
                    $playersAvgAta += $value;
                }
                $playersAvgAta = $playersAvgAta/count($squadStatsAtaSpecialty);
                $teamAvgAtaBalanced = floor(($squadStatAtaGeneral*0.1)+($playersAvgAta*0.9));
            }

            // trophies
            $championship = array();
            $totalChampions = 0;

            $queryChampions = mysqli_query($connection, "SELECT
            cha.cha_date,
            cha.cct_id,
            cha.tse_id,
            tse.sea_id,
            tor.tor_name
            FROM champions AS cha
            INNER JOIN tournament_season AS tse ON tse.tse_id = cha.tse_id
            INNER JOIN tournaments AS tor ON tor.tor_id = tse.tor_id
            WHERE cha.tem_id = '{$teamId}'
            AND cha.cha_status = 1");
            
            if (mysqli_num_rows($queryChampions)==0) {} else {
                while($dataChampion = mysqli_fetch_assoc($queryChampions)) {
                    $coachContract = $dataChampion['cct_id'];
                    $coachData = array(
                        "id" => 0,
                        "nickname" => "", 
                    );

                    if($coachContract > 0) {
                        $queryCoachChampion = mysqli_query($connection, "SELECT
                        usr.usr_nickname,
                        usr.usr_id
                        FROM coaches_contract AS cct
                        INNER JOIN users AS usr ON usr.usr_id = cct.usr_id
                        WHERE cct.cct_id = '{$coachContract}'
                        AND usr.usr_status = 1");
                        if (mysqli_num_rows($queryChampions)==0) {} else {
                            $dataCoach = mysqli_fetch_assoc($queryCoachChampion);
                            $coachData["id"] = $dataCoach["usr_id"];
                            $coachData["nickname"] = $dataCoach["usr_nickname"];
                        }
                    }

                    array_push($championship, array(
                        "date" => $dataChampion['cha_date'],
                        "season" => $dataChampion['sea_id'],
                        "name" => $dataChampion['tor_name'],
                        "id" => $dataChampion['tse_id'],
                        "coach" => $coachData,
                    ));

                    $totalChampions++;
                }
            }


            // squad
            $squadTeam = array();
            $querySquad = mysqli_query($connection, "SELECT 
            sqd.sqd_id,
            sqd.sqd_specialty,
            sqd.sqd_position,
            pla.tem_id,
            pla.pla_face,
            pla.pla_id,
            pla.pla_name,
            pla.pla_number,
            pla.pla_specialty,
            pla.pla_stat_stamina,
            pla.pla_stat_gk,
            pla.pla_stat_def,
            pla.pla_stat_mid,
            pla.pla_stat_ata,
            pla.pla_stat_pen,
            pla.pla_age,
            pla.pla_status,
            pla.pla_xp,
            pla.pla_class,
            ROUND(AVG(pla.pla_stat_gk + pla.pla_stat_def + pla.pla_stat_mid + pla.pla_stat_ata + pla.pla_stat_pen)/5) AS overall
            FROM teams_squads AS sqd
            INNER JOIN players AS pla ON pla.pla_id = sqd.pla_id
            WHERE sqd.tem_id = '{$teamId}'
            AND sqd.sqd_status <> 0
            AND pla.pla_status <> 0
            AND sqd.sqd_position < 12
            OR pla.pla_id = 0
            GROUP BY pla.pla_id
            ORDER BY sqd_position DESC");
            if (mysqli_num_rows($querySquad)==0) {
                // no player found in the field
            } else {

                while($dataSquad = mysqli_fetch_assoc($querySquad)) {
                    $sqdId = $dataSquad['sqd_id'];
                    $plaStatOverall = $dataSquad['overall'];
                    $plaSpecialty = $dataSquad['pla_specialty'];
                    $plaStatGk = $dataSquad['pla_stat_gk'];
                    $plaStatDef = $dataSquad['pla_stat_def'];
                    $plaStatMid = $dataSquad['pla_stat_mid'];
                    $plaStatAta = $dataSquad['pla_stat_ata'];
                    $plaStatPen = $dataSquad['pla_stat_pen'];


                    // check important stat for this player
                    $statOverallSpecialty = 0;
                    if($plaSpecialty==1) {
                        $statOverallSpecialty = $plaStatGk;
                    } else if($plaSpecialty==2) {
                        $statOverallSpecialty = $plaStatDef;
                    } else if($plaSpecialty==3) {
                        $statOverallSpecialty = $plaStatMid;
                    } else if($plaSpecialty==4) {
                        $statOverallSpecialty = $plaStatAta;
                    }
                    // stat overall balanced by specialty
                    $plaStatOverallBalanced = floor(($plaStatOverall*0.1)+($statOverallSpecialty*0.9));

                    $plaClass = $dataSquad['pla_class'];
                    $plaFace = $dataSquad['pla_face'];
                  
                    $sqdPosition = $dataSquad['sqd_position'];
                    $sqdSpecialty = $dataSquad['sqd_specialty'];
                    $plaStamina = $dataSquad['pla_stat_stamina'];
                    $plaName = $dataSquad['pla_name'];
                    $plaId = $dataSquad['pla_id'];
                    $plaSpecialty = $dataSquad['pla_specialty'];

                    array_push($squadTeam, array(

                        'position' => $sqdPosition,
                        'name'=> $plaName,
                        'face' => $plaFace,
                        'class' => $plaClass,
                        'id' => $plaId,
                        'specialty' => $plaSpecialty,
                        'stamina' => $plaStamina,
                        'ovr' => $plaStatOverallBalanced,
                        'def' => $plaStatDef,
                        'mid' => $plaStatMid,
                        'ata' => $plaStatAta,
                        'gk' => $plaStatGk,
                        'pen' => $plaStatPen
                    ));
                }
            }

            $dataResponse['status'] = 1;
            $dataResponse['team'] = array(
                "overall" => array(
                    "ovr" => floor(($teamAvgGkBalanced+$teamAvgDefBalanced+$teamAvgMidBalanced+$teamAvgAtaBalanced)/4),
                    "gk" => $teamAvgGkBalanced,
                    "def" => $teamAvgDefBalanced,
                    "mid" => $teamAvgMidBalanced,
                    "ata" => $teamAvgAtaBalanced
                ),
                "squad" => $squadTeam,
                "id" => $teamId,
                "country" => $playerCountryText[$dataTeam['tem_country']-1],
                "name" => $dataTeam['tem_name'],
                "points" => $dataTeam['tem_points'],
                "nickname" => $dataTeam['tem_nickname'],
                "emblem" => $dataTeam['tem_emblem_url'],
                "supporters" => floor(($temFansPercentage*100))/100,
                
                
                "supportersMood" => $dataTeam['tem_fans_mood'],
                "money" => $dataTeam['tem_money'],
                "formation" => $dataTeam['tem_formation'],
                "formationText" => $formationText[$dataTeam['tem_formation']-1],
                "strategy" => $strategyText[$dataTeam['tem_strategy']-1],
                "squadCost" => '$ 12M',
                "winner" => $totalChampions,
                "sponsor" => array(
                    "id" => $dataTeam['spn_id'],
                    "name" => $spnName
                ),
                "coach" => array(
                    "id" => $coachId,
                    "name" => $coachName,
                    "nickname" => $coachNickname,
                ),
                "stadium" => array(
                    "id" => $stdId,
                    "name" => $stdName
                ),
                "tolerance" => $dataTeam['tem_tolerance'],
                "trophies" => $championship,
            );
        }


    $dataResponse['status'] = 1;

    ######################################################################
} else { /* invalid token */ }

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
