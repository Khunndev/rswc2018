<?php
function array_msort($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;
}


$flagsrc = '{"PAN":"🇵🇦","TUN":"🇹🇳","ENG":"🏴󠁧󠁢󠁥󠁮󠁧󠁿","POL":"🇵🇱","JPN":"🇯🇵","COL":"🇨🇴","SEN":"🇸🇳","ARG":"🇦🇷","ISL":"🇮🇸","PER":"🇵🇪","DEN":"🇩🇰","CRO":"🇭🇷","NGA":"🇳🇬","RUS":"🇷🇺","KSA":"🇸🇦","EGY":"🇪🇬","URU":"🇺🇾","POR":"🇵🇹","ESP":"🇪🇸","MAR":"🇲🇦","IRN":"🇮🇷","FRA":"🇫🇷","AUS":"🇦🇺","BRA":"🇧🇷","SUI":"🇨🇭","CRC":"🇨🇷","SRB":"🇷🇸","GER":"🇩🇪","MEX":"🇲🇽","SWE":"🇸🇪","KOR":"🇰🇷","BEL":"🇧🇪"}';

$flags = json_decode($flagsrc, true);
$json = file_get_contents("http://worldcup.sfg.io/matches/current");
$data = json_decode($json, true);


if (!empty($data)) {
    $homeTeam = $data[0]['home_team']['code'];
    $homeTeamFlag= $flags[$homeTeam];
    $homeTeamScore = $data[0]['home_team']['goals'];
    $awayTeam = $data[0]['away_team']['code'];
    $awayTeamFlag = $flags[$awayTeam];
    $awayTeamScore = $data[0]['away_team']['goals'];
    $scoreLine = "$homeTeamFlag $homeTeamScore — $awayTeamScore $awayTeamFlag";
} else {
    $scoreLine = "⚽ ผลการแข่งขัน ⚽\\n".date("Y-m-d H:i:s");
};


echo $scoreLine;
echo "\\n--------------------";
$todayJson = file_get_contents("http://worldcup.sfg.io/matches/today");
$todayData = json_decode($todayJson, true);

if (!empty($todayData)) {
    $cnt = count($todayData);
    for ($n = 0; $n < $cnt; $n++) {
        $team1 = $todayData[$n]['home_team']['country'];
        $team1code =  $todayData[$n]['home_team']['code'];
        $team1flag = $flags[$team1code];
        $team1s = $todayData[$n]['home_team']['goals'];
        $team2 = $todayData[$n]['away_team']['country'];
        $team2code =  $todayData[$n]['away_team']['code'];
        $team2flag = $flags[$team2code];
        $team2s = $todayData[$n]['away_team']['goals'];
        $scores = "$team1code $team1flag $team1s – $team2s $team2flag $team2code";
        $scores .= "\\n--------------------\\n";
        if (($todayData[$n]['status']) == "in progress") {
            $time = $todayData[$n]['time'];
            $scores = $scores . " " . $time . " ⚽";
        } else {
            $scores .= "";
        }
        if (($todayData[$n]['status'] == "completed") || ($todayData[$n]['status'] == "in progress")) {
            echo "\\n";

            $arrayEvents = array_merge($todayData[$n]['home_team_events'], $todayData[$n]['away_team_events']);
            $arraySortEvents = array_msort($arrayEvents, array('id'=>SORT_ASC));
            foreach ($arraySortEvents as $val) {
                if (in_array($val['type_of_event'], array('goal', "goal-own", "goal-penalty"))) {
                    $scores .= "\\n🥅";
                    $scores .= $val['player'] . " " . $val['time'];
                }
                if ($val['type_of_event'] == "goal-penalty") {
                    $scores .= " (P)";
                }
                if ($val['type_of_event'] == "goal-own") {
                    $scores .= " (OG)";
                }
                if (in_array($val['type_of_event'], array('red-card', "yellow-card"))) {
                    $scores .= "\\n";
                    $scores .= $val['player'] . " " . $val['time'];
                }
                if ($val['type_of_event'] == "yellow-card") {
                    $scores .= "📒";
                }
                if ($val['type_of_event'] == "red-card") {
                    $scores .= "🎴";
                }
                $scores .= "";
            }
            echo $scores;
            
        } else {
            echo "\\n--------------------\\n".$scores;
        }
    }
}
?>
