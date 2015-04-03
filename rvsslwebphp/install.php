<?php
require("config.inc.php");
require("requires/PasswordHash.php");

$statstables[] = 'StatsMissionCoop';
$statstables[] = 'StatsHostageCoop';
$statstables[] = 'StatsTerrorHuntCoop';
$statstables[] = 'StatsHostage';
$statstables[] = 'StatsSurvival';
$statstables[] = 'StatsTeamSurvival';
$statstables[] = 'StatsBomb';
$statstables[] = 'StatsPilot';
$statstables[] = 'StatsTerroristHuntAdvMode';
$statstables[] = 'StatsScatteredHuntAdvMode';
$statstables[] = 'StatsCaptureTheEnemyAdvMode';
$statstables[] = 'StatsCountDownMode';
$statstables[] = 'StatsKamikazeMode';

$laddertables[] = 'LadderMissionCoop';
$laddertables[] = 'LadderHostageCoop';
$laddertables[] = 'LadderTerrorHuntCoop';
$laddertables[] = 'LadderHostage';
$laddertables[] = 'LadderSurvival';
$laddertables[] = 'LadderTeamSurvival';
$laddertables[] = 'LadderBomb';
$laddertables[] = 'LadderPilot';
$laddertables[] = 'LadderTerroristHuntAdvMode';
$laddertables[] = 'LadderScatteredHuntAdvMode';
$laddertables[] = 'LadderCaptureTheEnemyAdvMode';
$laddertables[] = 'LadderCountDownMode';
$laddertables[] = 'LadderKamikazeMode';


if (!isset($_GET["step"])) {
    $step = '1';
    ?>
    <form action="install.php">
        <b><u>Step 1</u></b>
        <br><br>
        Updated for Athena Sword by Munkey (ravenshield.theplatoon.com), Neo4E656F (www.koalaclaw.com) and Wizard
        (www.smakclan.com)
        <br><br>
        Welcome to the RavenShield Serverlist V1.12 install Script.<br>(Please open the config.inc.php file in a text
        editor and enter the correct information before.)
        <br>
        <hr>
        <input type="hidden" name="step" value="2"><input type="submit" value="To Step 2"></form>
    <hr>
    Serverlist for RainbowSix3 , Raven Shield by UBI-Soft<br>
    Copyright (C) 2003 =TSAF=Muschel<br>
    http://www.tsaf.de , muschel@tsaf.de<br>
    <br>
    This program is free software; you can redistribute it and/or modify<br>
    it under the terms of the GNU General Public License as published by<br>
    the Free Software Foundation version 2.<br>
    <br>
    This program is distributed in the hope that it will be useful,<br>
    but WITHOUT ANY WARRANTY; without even the implied warranty of<br>
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br>
    GNU General Public License for more details.<br>
    <br>
    You should have received a copy of the GNU General Public License<br>
    along with this program; if not, write to the Free Software<br>
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br>
    <br>
    <a href="gpl.txt">View the GNU General Public License</a>
    <?php
} else if ($_GET["step"] == '2') {
    ?>
    <form action="install.php">
        <b><u>Step 2</u></b><br><br>
        We will install the following tables into the <b><?php print $dbDatabase ; ?></b> Database.
        <hr>
        <br>
        Table 1: <b><?php print $dbtable1 ; ?></b><br>
        Table 2: <b><?php print $dbtable2 ; ?></b><br>
        Table 3: <b><?php print $dbtable3 ; ?></b><br>
        Table 4: <b><?php print $dbtable5 ; ?>GameMode</b><br>
        Table 5: <b><?php print $dbtable5 ; ?>GameModeInBeacon</b><br>
        Table 6: <b><?php print $dbtable4 ; ?>ServerIdentsNames</b><br>
        Table 7: <b><?php print $dbtable4 ; ?>Player</b><br>
        Table 8: <b><?php print $dbtable4 ; ?>Nicks</b><br>
        Table 9: <b><?php print $dbtable6 ; ?>Update</b><br>
        <?php
        foreach ($statstables as $item) {
            echo "Statstable: <b>" . $dbtable4 . $item . "</b><br>";
        }
        foreach ($laddertables as $item) {
            echo "Statstable: <b>" . $dbtable6 . $item . "</b><br>";
        }
        ?>
        <br>
        <hr>
        <input type="hidden" name="step" value="3"><input type="submit" value="To Step 3"></form>
    <?php
} else if ($_GET["step"] == '3') {
    $db = db_connect($dbHost, $dbUser, $dbPass) or die ("<CENTER>Connect-Error to MySQL! Check $dbHost, $dbUser and $dbPass in config.inc.php!");
    db_select_db($dbDatabase, $db) or die ("<CENTER>Connect-Error to Database! Check $dbDatabase in config.inc.php!");

    db_modify($db, "DROP TABLE IF EXISTS $dbtable1");
    db_modify($db, "CREATE TABLE $dbtable1 (
  id int(11) NOT NULL auto_increment,
  ip varchar(255) NOT NULL default '',
  bp varchar(255) NOT NULL default '',
  sort int(255) NOT NULL default '0',
  text varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
)");

    db_modify($db, "DROP TABLE IF EXISTS $dbtable2");
    db_modify($db, "CREATE TABLE $dbtable2 (
  id int(11) NOT NULL auto_increment,
  language char(3) NOT NULL default '1',
  css char(3) NOT NULL default '',
  aname varchar(20) NOT NULL,
  apass varchar(80) NOT NULL,
  PRIMARY KEY  (id)
)");

    db_modify($db, "INSERT INTO $dbtable2 VALUES (1, '0', '2', 'admin', '" . create_hash('admin') . "')");

    db_modify($db, "DROP TABLE IF EXISTS $dbtable3");
    db_modify($db, "CREATE TABLE $dbtable3 (
  id int(11) NOT NULL auto_increment,
  map varchar(50) NOT NULL default '',
  link varchar(200) NOT NULL default '',
  PRIMARY KEY  (id)
)");

    db_modify($db, "INSERT INTO $dbtable3 VALUES (1, 'Prison', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (5, 'Peaks', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (6, 'Presidio', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (7, 'MeatPacking_Day', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (8, 'Warehouse', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (9, 'Alpines', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (10, 'Island', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (11, 'Bank', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (12, 'Import_Export', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (13, 'Garage', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (14, 'MeatPacking', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (15, 'Streets', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (16, 'Oil_Refinery', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (17, 'Parade', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (18, 'Penthouse', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (19, 'Airport', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (20, 'Mountain_High', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (21, 'Island_Dawn', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (22, 'Shipyard', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (24, 'Training', 'release.html')");
    db_modify($db, "INSERT INTO $dbtable3 VALUES (25, 'Airport_Night', 'release.html')");

    db_modify($db, "DROP TABLE IF EXISTS " . $dbtable5 . "GameMode");
    db_modify($db, "CREATE TABLE " . $dbtable5 . "GameMode (
  id int(11) NOT NULL auto_increment,
  text varchar(20) NOT NULL default '',
  statstablename varchar(30) NOT NULL default '',
  laddertablename varchar(30) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY text (text),
  UNIQUE KEY statstablename (statstablename),
  UNIQUE KEY LadderTable (laddertablename)
)");

    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (1, 'missioncoop', 'StatsMissionCoop', 'LadderMissionCoop')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (2, 'hostagecoop', 'StatsHostageCoop', 'LadderHostageCoop')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (3, 'terrorhuntcoop', 'StatsTerrorHuntCoop', 'LadderTerrorHuntCoop')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (4, 'hostage', 'StatsHostage', 'LadderHostage')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (5, 'survival', 'StatsSurvival', 'LadderSurvival')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (6, 'teamsurvival', 'StatsTeamSurvival', 'LadderTeamSurvival')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (7, 'bomb', 'StatsBomb', 'LadderBomb')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (8, 'pilot', 'StatsPilot', 'LadderPilot')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (9, 'terroristhuntadvmode', 'StatsTerroristHuntAdvMode', 'LadderTerroristHuntAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (10, 'scatteredhuntadvmode', 'StatsScatteredHuntAdvMode', 'LadderScatteredHuntAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (11, 'capturetheenemymode', 'StatsCaptureTheEnemyAdvMode', 'LadderCaptureTheEnemyAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (12, 'countdownmode', 'StatsCountDownMode', 'LadderCountDownMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameMode VALUES (13, 'kamikazemode', 'StatsKamikazeMode', 'LadderKamikazeMode')");

    db_modify($db, "DROP TABLE IF EXISTS " . $dbtable5 . "GameModeInBeacon");
    db_modify($db, "CREATE TABLE " . $dbtable5 . "GameModeInBeacon (
  id int(11) NOT NULL auto_increment,
  fromgamemodeid int(11) NOT NULL default '0',
  beacontext varchar(30) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY beacontext (beacontext)
)");

    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (1, 1, 'RGM_MissionMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (2, 2, 'RGM_HostageRescueCoopMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (3, 3, 'RGM_TerroristHuntCoopMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (4, 4, 'RGM_HostageRescueAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (5, 5, 'RGM_DeathmatchMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (6, 6, 'RGM_TeamDeathmatchMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (7, 7, 'RGM_BombAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (8, 8, 'RGM_EscortAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (9, 9, 'RGM_TerroristHuntAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (10, 10, 'RGM_ScatteredHuntAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (11, 11, 'RGM_CaptureTheEnemyAdvMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (12, 12, 'RGM_CountDownMode')");
    db_modify($db, "INSERT INTO " . $dbtable5 . "GameModeInBeacon VALUES (13, 13, 'RGM_KamikazeMode')");


    db_modify($db, "DROP TABLE IF EXISTS " . $dbtable4 . "ServerIdentsNames");
    db_modify($db, "CREATE TABLE " . $dbtable4 . "ServerIdentsNames (
  id int(11) NOT NULL auto_increment,
  serverident varchar(20) NOT NULL default '',
  servername varchar(30) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY serverident (serverident)
)");

    db_modify($db, "DROP TABLE IF EXISTS " . $dbtable6 . "Update");
    db_modify($db, "CREATE TABLE " . $dbtable6 . "Update (
  id int(11) NOT NULL auto_increment,
  gamemodeid int(11) NOT NULL default '0',
  lastupdatetime int(11) NOT NULL default '0',
  inupdate int(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY gamemodeid (gamemodeid)
)");

    db_modify($db, "DROP TABLE IF EXISTS " . $dbtable4 . "Nicks");
    db_modify($db, "CREATE TABLE " . $dbtable4 . "Nicks (
  id int(11) NOT NULL auto_increment,
  fromid int(11) NOT NULL default '0',
  nick varchar(30) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY fromid (fromid)
)");

    db_modify($db, "DROP TABLE IF EXISTS " . $dbtable4 . "Player");
    db_modify($db, "CREATE TABLE " . $dbtable4 . "Player (
  id int(11) NOT NULL auto_increment,
  serverident varchar(20) NOT NULL default '',
  ubiname varchar(30) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY serverident (serverident)
)");

    foreach ($statstables as $item) {
        db_modify($db, "DROP TABLE IF EXISTS " . $dbtable4 . $item);
        db_modify($db, "CREATE TABLE " . $dbtable4 . $item . " (
  id int(11) NOT NULL auto_increment,
  fromid int(11) NOT NULL default '0',
  kills int(11) NOT NULL default '0',
  deaths int(11) NOT NULL default '0',
  map varchar(40) NOT NULL default '',
  roundsplayed int(11) NOT NULL default '0',
  fired int(11) NOT NULL default '0',
  hits int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
)");
    }

    foreach ($laddertables as $item) {
        db_modify($db, "DROP TABLE IF EXISTS " . $dbtable6 . $item);
        db_modify($db, "CREATE TABLE " . $dbtable6 . $item . " (
  id int(11) NOT NULL auto_increment,
  fromid int(11) NOT NULL default '0',
  score int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY score (score)
)");
    }



    ?>
    <form action="index.php">
        <b><u>Step 3</u></b><br><br>
        Thanks for installing the RavenShield Serverlist. Please DELETE the install.php file.<br>
        The default Serverlist-Admin is admin/admin, change it!
        <hr>
    </form>
    <?php
}
?>
</body>
</html>
