<?php
// Script (C)opyright 2003 by =TSAF=Muschel
// Released under GNU GENERAL PUBLIC LICENSE
// www.tsaf.de , muschel@tsaf.de
require("config.inc.php");
define(OPERATIONAL, true);
define(LOGGER, TRUE);
define(FAKE, false);
if (!OPERATIONAL) {
    die("kosh!");
}
if ($_SERVER["REQUEST_METHOD"] == "POST" || FAKE) {
    if (LOGGER) {
        # Log the request
        $fh = fopen('/tmp/rvsslwebphp.log', 'a') or
        die("Cannot open log file for writing!");
        fwrite($fh, date('c') . "\n");
        fwrite($fh, var_export($_POST, true));
        fclose($fh);
    }
    if (FAKE) {
        $_POST = array(
            'ident' => 'BBFIN',
            'UB' => '/Xcutor',
            'L1' => '/Xcutor',
            'HI' => '/32',
            'RF' => '/62',
            'O1' => '/10',
            'DE' => '/3',
            'F1' => 'RGM_TerroristHuntCoopMode',
            'E1' => 'Presidio',
            'RP' => '/1',
            'VS' => '310',
            'RT' => '290.63',
            'S1' => '/0',
            'NK' => '/0',
            'TK' => '/0',
            'TD' => '/0',
            'PDD1' => '/0',
            'PBA' => '/0',
            'PBD' => '/0',
            'PDA' => '/0',
            'PDD2' => '/0',
            'KB' => '/',
            'T_D' => '10',
            'T_TK' => '0',
            'T_S' => '0',
            'T_N' => '0',
            'T_K' => '3',
            'DD1' => '0',
            'DD2' => '0',
            'TBD' => '0',
            'PGID' => '/315724591D46481F977ED5160B543CD1',
            'SBPT' => '8777',
            'US' => '/Xcutor',
            'HS' => '/0',
            'RS' => '/0',
            'DS' => '/0',
            'xx' => 'end
',
        );
    }
    $db = db_connect($dbHost, $dbUser, $dbPass) or die ("<CENTER>Connect-Error to MySQL! Check $dbHost, $dbUser and $dbPass in config.inc.php!");
    db_select_db($dbDatabase, $db) or die ("<CENTER>Connect-Error to Database! Check $dbDatabase in config.inc.php!");

    BuildGameModeTranslateArray($db);
    BuildStatsTablesArray($db);
    BuildPlayerAndNicksTables();
    BuildServerIdentNameArrays($db);
    $PostedGameMode = $_POST['F1'];

    if (!isset($Statstable[$GameModeTranslate[$PostedGameMode]])) {
        die ("Webserver does not log this gamemode:" . $PostedGameMode . "!");
    }

    $writetable = $Statstable[$GameModeTranslate[$PostedGameMode]];

    if (!isset($_POST['ident'])) {
        die ("Ident is missing!");
    }
    $ident = $_POST['ident'];
    if (!in_array($ident, $servernameident)) {
        die ("Ident " . $ident . " not known by Webserver!");
    }
    $map = $_POST['E1'];

    $Nick = explode("/", substr($_POST['L1'], 1));
    $Ubi = explode("/", substr($_POST['UB'], 1));
    $Kills = explode("/", substr($_POST['O1'], 1));
    $Deaths = explode("/", substr($_POST['DE'], 1));
    $Fired = explode("/", substr($_POST['RF'], 1));
    $Hits = explode("/", substr($_POST['HI'], 1));
    $StartUbi = explode("/", substr($_POST['US'], 1));
    $StartDeaths = explode("/", substr($_POST['DS'], 1));
    $StartFired = explode("/", substr($_POST['RS'], 1));
    $StartHits = explode("/", substr($_POST['HS'], 1));

    $counter = 0;

    while (isset($StartUbi[$counter])) {
        $StartUbiArray[$StartUbi[$counter]]['startdeaths'] = $StartDeaths[$counter];
        $StartUbiArray[$StartUbi[$counter]]['startfired'] = $StartFired[$counter];
        $StartUbiArray[$StartUbi[$counter]]['starthits'] = $StartHits[$counter];
        $counter++;
    }

    $counter = 0;

    while (isset($Ubi[$counter])) {
        $look = "SELECT id FROM " . $Playertable . " WHERE serverident=? and ubiname=?";
        $res = db_query($db, $look, 'ss', $ident, $Ubi[$counter]);
        if (db_num_rows($res) == 0) {
            $add = "INSERT INTO " . $Playertable . " VALUES('',?,?)";
            db_modify($db, $add, 'ss', $ident, $Ubi[$counter]);
            $res = db_query($db, $look, 'ss', $ident, $Ubi[$counter]);
        }
        $dbrow = db_fetch_array($res);
        $dbubiid = (int)$dbrow['id'];
        if (!$dbubiid) {
            die("Bad id: " . $dbrow['id']);
        }

        $look = "SELECT id FROM " . $Nicktable . " WHERE fromid=? and nick=?";
        $res = db_query($db, $look, 'is', $dbubiid, $Nick[$counter]);
        if (db_num_rows($res) == 0) {
            $add = "INSERT INTO " . $Nicktable . " VALUES ('',?,?)";
            db_modify($db, $add, 'is', $dbubiid, $Nick[$counter]);
        }

        if (isset($StartUbiArray[$Ubi[$counter]]['startdeaths'])) {

            $look = "SELECT * FROM " . $writetable . " WHERE fromid=? and map=?";
            $res = db_query($db, $look, 'ss', $dbubiid, $map);

            if (db_num_rows($res) == 0) {
                $add = "INSERT INTO " . $writetable . " VALUES ('',?,?,?,?,?,?,?)";
                db_modify($db, $add, 'iiisiii', $dbubiid, $Kills[$counter], $Deaths[$counter], $map, 1, $Fired[$counter], $Hits[$counter]);
            } elseif (db_num_rows($res) == 1) {
                $dbrow = db_fetch_array_assoc($res, MYSQL_ASSOC);

                $newkills = (int)$Kills[$counter] + (int)$dbrow['kills'];
                $newdeaths = (int)$Deaths[$counter] + (int)$dbrow['deaths'] - (int)$StartUbiArray[$Ubi[$counter]]['startdeaths'];
                $newfired = (int)$Fired[$counter] + (int)$dbrow['fired'] - (int)$StartUbiArray[$Ubi[$counter]]['startfired'];
                $newhits = (int)$Hits[$counter] + (int)$dbrow['hits'] - (int)$StartUbiArray[$Ubi[$counter]]['starthits'];
                $newrounds = ++$dbrow['roundsplayed'];

                $update = "UPDATE " . $writetable . " SET";
                $update .= " kills=?,";
                $update .= " deaths=?,";
                $update .= " roundsplayed=?,";
                $update .= " fired=?,";
                $update .= " hits=?";
                $update .= " WHERE id=?";
                db_modify($db, $update, 'iiiiii', $newkills, $newdeaths, $newrounds, $newfired, $newhits, $dbrow['id']);
            }
        }

        $counter++;
    }   // end of while
    echo "Gamemode:" . $PostedGameMode . "<br>";
    echo "The web server received the Data!";
} else {
    echo "no data!";
}
