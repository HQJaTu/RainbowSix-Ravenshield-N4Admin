<?php
// Script (C)opyright 2003 by =TSAF=Muschel
// Released under GNU GENERAL PUBLIC LICENSE
// www.tsaf.de , muschel@tsaf.de
if (!isset($getscoretable)) {
    die ("Script not directly useable!");
}
$actualtime = time();
$look = "Select * FROM " . $updatetable . " WHERE gamemodeid=?";
$lookedforupdate = db_query($db, $look, 'i', $modeid);

if (db_num_rows($lookedforupdate) == 0) {
    $insertnew = "INSERT INTO " . $updatetable . " VALUES ('',?,?,'0')";
    $insertednew = db_modify($db, $insertnew, 'ii', $modeid, $actualtime);
    $ladder_update_time = -1;
}
$lookedforupdate = db_query($db, $look, 'i', $modeid);
if (db_num_rows($lookedforupdate) == 0) {
    die ("Database Error?!");
}
$datasetforupdate = db_fetch_array($lookedforupdate);
if ($datasetforupdate['inupdate'] == "1") {
    die ("</table>Ladder in Update, try again later!");
}
if (($datasetforupdate['lastupdatetime'] + $ladder_update_time) < $actualtime) {
    $lock = "UPDATE " . $updatetable . " SET inupdate='1' WHERE gamemodeid=?";
    db_modify($db, $lock, 'i', $modeid);

    echo "</table>Ladder in Update, please wait!";

    $wegdamit = db_modify($db, "DELETE FROM $writeladdertable");

    $resstats = db_query($db, "SELECT fromid, sum(kills) as sumkills, sum(deaths) as sumdeaths ,sum(roundsplayed) as sumrounds,sum(fired) as sumfired,sum(hits) as sumhits  FROM $getscoretable GROUP BY fromid");

    while ($stats = db_fetch_array_assoc($resstats)) {

        if ($stats['sumhits'] == 0) {
            $hitfix = 0.00001;
        } else {
            $hitfix = 0;
        }
        if ($stats['sumfired'] == 0 and $stats['sumhits'] > 0) {
            $firedcount = 1;
        } else {
            $firedcount = $stats['sumfired'];
        }
        $deathcount = $stats['sumdeaths'];
        if ($deathcount == 0) {
            $deathcount = 1;
        }
        if ($firedcount == 0) {
            $hitfix = 0.00001;
        } else {
            $hitfix = 0;
        }

        $score = CalcScore($stats['sumkills'], $deathcount, $stats['sumhits'], $firedcount + $hitfix, $stats['sumrounds']);
        if ($score != 0) {
            $newscoreset = "INSERT INTO " . $writeladdertable . " VALUES ('','" . $stats['fromid'] . "','" . $score . "')";
            db_modify($db, $newscoreset, 'ii', $stats['fromid'], $score);
        }

    }
    $newtimeset = "UPDATE " . $updatetable . " SET lastupdatetime=?,inupdate='1' WHERE gamemodeid=?";
    db_modify($db, $newtimeset, 'ii', $actualtime, $modeid);
    echo "<meta http-equiv=\"refresh\" content=\"0\">";
    die ("<br>ok!");
} else {
    echo "<tr><td align=center class=bigheader background=\"images/" . $design[$dset] . "_header.gif\">";
    echo $text_ladderlastupdated . "&nbsp;" . date("d M  H:i:s (T)", $datasetforupdate['lastupdatetime']);
    echo "</td></tr>";
    echo "<tr><td align=center class=header background=\"images/" . $design[$dset] . "_middle.gif\">";
    echo "<b>(" . $text_interval . $ladder_update_time . " s)</b>";
    echo "</td></tr>";
}
