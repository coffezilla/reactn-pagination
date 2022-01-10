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

$torReference = addslashes(trim($_GET['tref']));
$seasonId = addslashes(trim($_GET['sid']));

// JWT auth 
include "../connect/auth.php";
$isAuth = verifyAuth($clientToken, $JWTServerkey);
$isAuth = true;

if($isAuth) {

    ######################################################################

    $dataResponse['fixtures'] = array();


    // all tournament phases
    $queryTournamentReferences = mysqli_query($connection, "SELECT
    tse.tse_id,
    tse.tor_id,
    tor.tor_name,
    tse.tse_status,
    tse.tse_rounds,
    tor.tor_type,
    tse.sea_id,
    tor.tor_reference,
    tor.tor_order,
    tor.tor_phase,
    tor.tor_phase_name,
    tor.tor_final_stage
    FROM tournament_season AS tse
    INNER JOIN tournaments AS tor ON tor.tor_id = tse.tor_id
    WHERE tor.tor_status <> 0
    AND tse.sea_id = '{$seasonId}'
    AND tor.tor_reference = '{$torReference}'
    AND ( tse.tse_status = 1 OR tse.tse_status = 2 OR tse.tse_status = 3 )
    ORDER BY tor.tor_order");
    if (mysqli_num_rows($queryTournamentReferences)==0) {} else {
        $tournamentIndex = 1;
        while($dataReferenceFixtures = mysqli_fetch_assoc($queryTournamentReferences)) {
            $tseId = $dataReferenceFixtures['tse_id'];
            $torOrder = $dataReferenceFixtures['tor_order'];
            $tseStatus = $dataReferenceFixtures['tse_status'];
            $torPhaseName = $dataReferenceFixtures['tor_phase_name'];
            $torPhase = $dataReferenceFixtures['tor_phase'];
            $torFinalStage = $dataReferenceFixtures['tor_final_stage'];

            $queryTournamentSeasons = mysqli_query($connection, "SELECT
            tse.tse_id,
            tse.tor_id,
            tor.tor_name,
            tse.tse_status,
            tse.sea_id,
            tor.tor_type
            FROM tournament_season AS tse
            INNER JOIN tournaments AS tor ON tor.tor_id = tse.tor_id
            WHERE tor.tor_status <> 0
            AND tse.tse_id = '{$tseId}'
            AND ( tse.tse_status = 1 OR tse.tse_status = 2 OR tse.tse_status = 3 )
            ORDER BY tse_status, tse_id DESC ");

            if (mysqli_num_rows($queryTournamentSeasons)==0) {
                // no data found from this tournament
            } else {

                $tournamentFixturePhase = array();
                $torPhaseOrder = $torOrder;
                $tseStatus = $tseStatus;
                $torType = $torPhase;


                while($dataTournamentSeason = mysqli_fetch_assoc($queryTournamentSeasons)) {
                    $torSeasonId = $dataTournamentSeason['tse_id'];
                    $torId = $dataTournamentSeason['tor_id'];
                    $torName = $dataTournamentSeason['tor_name'];
                    $seasonId = $dataTournamentSeason['sea_id'];
                    $tseStatusText = $tseStatus == 1 ? "Finalizado" : "Em andamento";


                    // em andamento
                    if($tseStatus == 2 || $tseStatus == 3) {

                        $countPosition = 0;
                        $queryFixtures = mysqli_query($connection, "SELECT
                        fix.spo_id,
                        tem.tem_name,
                        tem.tem_id,
                        tem.tem_emblem_url,
                        fix.fix_points,
                        fix.fix_id,
                        fix.fix_total_matches,
                        fix.fix_victory,
                        fix.fix_draw,
                        fix.fix_lose,
                        fix.fix_goal_pro,
                        fix.fix_goal_against,
                        fix.fix_goal_diff
                        FROM tournament_fixtures AS fix
                        INNER JOIN teams AS tem ON tem.tem_id = (SELECT spo.tem_id FROM tournament_spot AS spo WHERE spo.spo_id = fix.spo_id)
                        WHERE fix.fix_status <> 0
                        AND fix.tse_id = '{$torSeasonId}'
                        ORDER BY fix.fix_points DESC,  fix.fix_victory DESC, fix.fix_goal_pro DESC, fix.fix_goal_against, fix.fix_goal_diff DESC");
                    } else {
                        $countPosition = 0;
                        $queryFixtures = mysqli_query($connection, "SELECT
                        fix.spo_id,
                        tem.tem_name,
                        tem.tem_id,
                        tem.tem_emblem_url,
                        fix.fix_points,
                        fix.fix_id,
                        fix.fix_total_matches,
                        fix.fix_victory,
                        fix.fix_draw,
                        fix.fix_lose,
                        fix.fix_goal_pro,
                        fix.fix_goal_against,
                        fix.fix_goal_diff
                        FROM tournament_fixtures AS fix
                        INNER JOIN teams AS tem ON tem.tem_id = (SELECT spo.tem_id FROM tournament_spot_history AS spo WHERE spo.spo_id = fix.spo_id AND spo.tse_id = fix.tse_id AND spo.hspo_status = 1 )
                        WHERE fix.fix_status <> 0
                        AND fix.tse_id = '{$torSeasonId}'
                        ORDER BY fix.fix_points DESC,  fix.fix_victory DESC, fix.fix_goal_pro DESC, fix.fix_goal_against, fix.fix_goal_diff DESC");                
                    }


                    if (mysqli_num_rows($queryFixtures)==0) {} else {
                        while($dataFixture = mysqli_fetch_assoc($queryFixtures)) {
                            $countPosition +=1;
           
                            array_push($tournamentFixturePhase, array(
                                "position" => $countPosition,
                                "team" => array(
                                    "id" => $dataFixture['tem_id'],
                                    "name" => $dataFixture['tem_name'],
                                    "emblem" => $dataFixture['tem_emblem_url']
                                ),
                                "pt" => $dataFixture['fix_points'],
                                "pj" => $dataFixture['fix_total_matches'],
                                "v" => $dataFixture['fix_victory'],
                                "d" => $dataFixture['fix_draw'],
                                "l" => $dataFixture['fix_lose'],
                                "gp" => $dataFixture['fix_goal_pro'],
                                "gc" => $dataFixture['fix_goal_against']
                            ));
                        }
                    }
                }


                $dataResponse['prizes_ticket'] = array();
                $queryTournamentPrizesTicket = mysqli_query($connection, "SELECT
                ptk.ptk_position,
                ptk.spo_id,
                spo.tor_id,
                tor.tor_name,
                tor.tor_reference
                FROM prizes_ticket AS ptk
                INNER JOIN tournament_spot AS spo ON spo.spo_id = ptk.spo_id
                INNER JOIN tournaments AS tor ON tor.tor_id = spo.tor_id
                WHERE ptk.tor_id = '{$torId}'");
                if (mysqli_num_rows($queryTournamentPrizesTicket)==0) {
                    // no tournament found
                } else {
                    while($dataTickets = mysqli_fetch_assoc($queryTournamentPrizesTicket)) {

                        array_push($dataResponse['prizes_ticket'], 
                            array(
                                "position" => $dataTickets['ptk_position'],
                                "reference" => $dataTickets['tor_reference'],
                                "prize" => $dataTickets['tor_name']
                            )
                        );

                    }
                }                

                // register current fixture this phase
                array_push($dataResponse['fixtures'], array(
                    "prizes" => $dataResponse['prizes_ticket'],
                    "info" => array(
                        "phaseOrder" => $torPhaseOrder,
                        "phase" => $torPhase,
                        "phaseName" => $torPhaseName,
                        "status" => $tseStatus,
                        "type" => $torType,
                        "finalStage" => $torFinalStage,
                        "tse" => $tseId,
                    ),
                    "lines" => $tournamentFixturePhase)
                );

            }

        }
    }      


    $dataResponse['status'] = 1;

    ######################################################################
} else { /* invalid token */ }

$resultadosJson = json_encode($dataResponse);
echo $resultadosJson;
