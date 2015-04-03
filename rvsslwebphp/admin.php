<?php
// Script (C)opyright 2003 by =TSAF=Muschel
// Released under GNU GENERAL PUBLIC LICENSE
// www.tsaf.de , muschel@tsaf.de
error_reporting(2047);
if (!isset($Submit)) {
    $Submit = "main";
}
require("config.inc.php");
require("requires/PasswordHash.php");

$db = db_connect($dbHost, $dbUser, $dbPass) or die ("<CENTER>Connect-Error to MySQL! Check $dbHost, $dbUser and $dbPass in config.inc.php!");
db_select_db($dbDatabase, $db) or die ("<CENTER>Connect-Error to Database! Check $dbDatabase in config.inc.php!");

$res = db_query($db, "SELECT * FROM " . $dbtable2 . " WHERE id=?", 'i', 1);
$dbrow = db_fetch_array($res);
$lset = $dbrow['language'];
$dset = $dbrow['css'];
$customlanguage = $language[$lset];
if (!$customlanguage) {
    $customlanguage = 1;
}
$css = "css/" . $design[$dset] . "_css.css";

require('language/' . $customlanguage . '.inc.php');
BuildPlayerAndNicksTables();
BuildStatsTablesArray($db);

$adminname = $dbrow['aname'];
$adminpassword = $dbrow['apass'];

$loggedin = False;
$namecookie = crc32($adminname . $adminpassword . "serverliste");

if (isset($_POST['admin']) and isset($_POST['pw'])) {
    if ($_POST['admin'] == $adminname and validate_password($_POST['pw'], $adminpassword)) {
        setcookie("RVSServerliste", "$namecookie", time() + 3600, "/");
        $loggedin = True;
    }
}

if (isset($_GET['Submit'])) {
    $submit = $_GET['Submit'];
} else {
    $submit = 'main';
}
if ($submit == "Logoff") {
    setcookie("RVSServerliste", "", time() - 3600, "/");
} else {
    if (isset($_COOKIE["RVSServerliste"])) {
        $usrcookie = $_COOKIE["RVSServerliste"];
    } else {
        $usrcookie = "";
    }
    if ($usrcookie == $namecookie) {
        $loggedin = True;
    }
}

if ($loggedin == True) {
    require("header.php");
    if ($submit) {

        ?>
<script language="javascript">
<!--
if (document.images) { on = new Image(); on.src = "images/indicator.gif"; off = new Image(); off.src ="images/clear.gif"; }
function mi(n) { if (document.images) {document[n].src = eval("on.src");}}
function mo(n) { if (document.images) {document[n].src = eval("off.src");}}
// -->
</script>
<link rel="stylesheet" type="text/css" href="<?php print $text_serverlist; ?>">
<body class=body><center>
<table border=0 cellspacing=0 width="<?php print $text_serverlist;; ?>"><form name="listadm" action="admin.php">
<tr><td align=center class=bigheader background="images/<?php print $design[$dset]; ?>_header.gif"><b>Raven-Shield <?php print $text_serverlist; ?> Admin</b></td>
</tr><tr><td><hr></td></tr></table>
<?php

        $res = db_query($db, "SELECT * FROM $dbtable1 ORDER BY sort");
        $serveranzahl = db_num_rows($res);
        $q = 0;
        while ($dbrow = db_fetch_array($res)) {
            $listdata[$q]['id'] = $dbrow['id'];
            $listdata[$q]['ip'] = $dbrow['ip'];
            $listdata[$q]['bp'] = $dbrow['bp'];
            $listdata[$q]['sort'] = $dbrow['sort'];
            $listdata[$q++]['text'] = $dbrow['text'];
        }

        $res = db_query($db, "SELECT * FROM $dbtable3 ORDER by map");
        $linkanzahl = db_num_rows($res);
        $q = 0;
        while ($dbrow = db_fetch_array($res)) {
            $mapdata[$q]['id'] = $dbrow['id'];
            $mapdata[$q]['map'] = $dbrow['map'];
            $mapdata[$q++]['link'] = $dbrow['link'];
        }

        $res = db_query($db, "SELECT * FROM " . $dbtable4 . "ServerIdentsNames");
        $identanzahl = db_num_rows($res);
        $q = 0;
        while ($dbrow = db_fetch_array($res)) {
            $identdata[$q]['id'] = $dbrow['id'];
            $identdata[$q]['ident'] = $dbrow['serverident'];
            $identdata[$q++]['name'] = $dbrow['servername'];
            $allidents[] = $dbrow['serverident'];
        }

        switch ($submit) {
            case "Login":
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php\">";
                break;

            case "Change Serverlist Entry":
                $wahl = db_modify($db, "UPDATE $dbtable1 SET ip=?,bp=?,sort=?,text=? WHERE id=?", 'ssisi',
                                  $_GET['ipneu'], $_GET['bpneu'], $_GET['sortneu'], $_GET['textneu'], $_GET['isneu']);
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverlist\">";
                break;

            case "Change Link-Entry":
                $wahl = db_modify($db, "UPDATE $dbtable3 SET map=?,link=? WHERE id=?", 'ssi',
                                  $_GET['mapneu'], $_GET['linkneu'], $_GET['linkid']);
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=dllinks\">";
                break;

            case "Change Ident-Entry":
                if (in_array($_GET['sidentneu'], $allidents) and $_GET['identold'] != $_GET['sidentneu']) {
                    echo "Serverident allready in Database, canceled!<br><br>";
                    echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; url=admin.php?Submit=serverident\">";
                } else {
                    if ($_GET['identold'] != $_GET['sidentneu']) {
                        echo "Updating Playertable for new Serverident!<br><br>";
                        $wahl = db_modify($db, "UPDATE $Playertable SET serverident=? WHERE serverident=?", 'ss',
                                          $_GET['sidentneu'], $_GET['identold']);
                    }
                    $wahl = db_modify($db, "UPDATE " . $dbtable4 . "ServerIdentsNames SET serverident=?,servername=? WHERE id=?", 'ssi',
                                      $_GET['sidentneu'], $_GET['snameneu'], $_GET['identid']);
                    echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverident\">";
                }
                break;

            case "New Serverlist Entry":
                $sql = "INSERT INTO $dbtable1 VALUES('',?,?,?,?)";
                $result = db_modify($db, $sql, 'ssis',
                                    $_GET['ipneu'], $_GET['bpneu'], $_GET['sortneu'], $_GET['textneu']);
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverlist\">";
                break;

            case "New Ident":
                if (isset($allidents)) {
                    if (in_array($_GET['sidentneu'], $allidents)) {
                        echo "Serverident allready in Database, canceled!<br><br>";
                        echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; url=admin.php?Submit=serverident\">";
                    } else {
                        $sql = "INSERT INTO " . $dbtable4 . "ServerIdentsNames VALUES('',?,?)";
                        $result = db_modify($db, $sql, 'ss',
                                            $_GET['sidentneu'], $_GET['snameneu']);
                        echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverident\">";
                    }
                } else {
                    $sql = "INSERT INTO " . $dbtable4 . "ServerIdentsNames VALUES('',?,?)";
                    $result = db_modify($db, $sql, 'ss',
                                        $_GET['sidentneu'], $_GET['snameneu']);
                    echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverident\">";
                }
                break;

            case "New Link":
                $sql = "INSERT INTO $dbtable3 VALUES('',?,?)";
                $result = db_modify($db, $sql, 'ss',
                                    $_GET['mapneu'], $_GET['linkneu']);
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=dllinks\">";
                break;

            case "deletelistip":
                $result = db_modify($db, "DELETE FROM $dbtable1 WHERE id=?", 'i', $_GET['isneu']);
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverlist\">";
                break;

            case "deletelink":
                $result = db_modify($db, "DELETE FROM $dbtable1 WHERE id=?", 'i', $_GET['lneu']);
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=dllinks\">";
                break;

            case "deleteident":
                echo "Warning!<br>This will delete all Player and Stats from the Ident: <b>" .
                     html_entity_decode($_GET['delidentname']) . "</b> !<br><br>";
                echo "<a class=nav href=\"admin.php?Submit=deleteidentconfirmed&delident=" .
                     html_entity_decode($_GET['delident']) .
                     "&delidentname=" . html_entity_decode($_GET['delidentname']) .
                     "\">&gt;&gt;&gt; Confirm delete Ident '" . html_entity_decode($_GET['delidentname']) .
                     "' &lt;&lt;&lt;</a><br><br>";
                echo "<a class=nav href=\"admin.php?Submit=serverident\">>>> CANCEL <<<</a><br><br>";
                break;

            case "deleteidentconfirmed":
                echo "Deleting Ident and Player+Stats of it, please wait!<br><br>";

                $look = "SELECT id FROM " . $Playertable . " WHERE serverident=?";
                $res = db_query($db, $look, 's', $_GET['delidentname']);

                while ($dbrow = db_fetch_array($res)) {
                    $delid = (int)$dbrow['id'];
                    foreach ($Statstable as $statstbl) {
                        $deletestats = "DELETE FROM " . $statstbl . " WHERE fromid=?";
                        db_modify($db, $deletestats, 'i', $_GET['delid']);
                        $deletenicks = "DELETE FROM " . $Nicktable . " WHERE fromid=?";
                        db_modify($db, $deletestats, 'i', $_GET['delid']);
                    }
                }
                $deleteplayers = "DELETE FROM " . $Playertable . " WHERE serverident=?";
                db_modify($db, $deleteplayers, 's', $_GET['delidentname']);
                db_modify($db, "DELETE FROM " . $dbtable4 . "ServerIdentsNames WHERE id=?", 'i', $_GET['delident']);
                db_modify($db, "DELETE FROM " . $dbtable6 . "Update");
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverident\">";
                break;

            case "Cancel Edit Links":
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=dllinks\">";
                break;

            case "Cancel Edit Serverlist":
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverlist\">";
                break;

            case "Cancel Admin Config":
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=main\">";
                break;

            case "Cancel Edit Ident":
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=serverident\">";
                break;

            case "language":
                $wahl = db_modify($db, "UPDATE $dbtable2 SET language=? WHERE id=?", 'si', $_GET['lan'], 1);
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=main\">";
                break;

            case "design":
                $wahl = db_modify($db, "UPDATE $dbtable2 SET css=? WHERE id=?", 'si', $_GET['deset'], 1);
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=main\">";
                break;

            case "SetAdmin":
                $pass = create_hash($_GET['passneu']);
                $wahl = db_modify($db, "UPDATE $dbtable2 SET aname=?,apass=? WHERE id=?", 'ssi', $_GET['nameneu'], $pass, 1);
                $namecook = crc32($_GET['nameneu'] . $pass . "serverliste");
                setcookie("RVSServerliste", "$namecook", time() + 3600, "/");
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; url=admin.php?Submit=main\">";
                break;

            case "forceladderupdate":
                echo "<br>Ladders forces to Update!<br><br>";
                db_modify($db, "DELETE FROM " . $dbtable6 . "Update");
                echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"1; url=admin.php?Submit=main\">";
                break;

            case "main":
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=bigheader background=\"images/" . $design[$dset] . "_header.gif\">";
                echo "<b>Main</b>";
                echo "</td></tr><tr>";
                echo "<td align=center class=randende>";
                echo "<a class=nav href=\"admin.php?Submit=serverlist\">Serverlist IP-Config</a>";
                echo "</td></tr><tr>";
                echo "<td align=center class=randende>";
                echo "<a class=nav href=\"admin.php?Submit=dllinks\">Map Downloadlinks-Config</a>";
                echo "</td></tr><tr>";
                echo "<td align=center class=randende>";
                echo "<a class=nav href=\"admin.php?Submit=serverident\">Stats Serverident-Config</a>";
                echo "</td></tr></table><br>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=500><tr>";
                echo "<td align=center class=bigheader background=\"images/" . $design[$dset] . "_header.gif\" width=\"10%\"><b>Language</b></td>";
                echo "<td align=center class=bigheader background=\"images/" . $design[$dset] . "_header.gif\" width=\"10%\"><b>Design</b></td>";
                echo "<td align=center class=bigheader background=\"images/" . $design[$dset] . "_header.gif\" width=\"10%\"><b>Admin</b></td>";
                echo "</tr><tr><td align=center class=rand width=\"10%\">";
                $counter = 0;
                foreach ($language as $item) {
                    echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"lang$counter\"><a class=nav href=\"admin.php?lan=" . $counter . "&Submit=language\" onMouseOver=\"mi('lang$counter')\" onMouseOut=\"mo('lang$counter')\"><b>" . $item . "</a><br>";
                    $counter++;
                }
                echo "</td><td align=\"center\" class=rand width=\"10%\">";
                $counter = 0;
                foreach ($design as $item) {
                    echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"design$counter\"><a class=nav href=\"admin.php?deset=" . $counter . "&Submit=design\" onMouseOver=\"mi('design$counter')\" onMouseOut=\"mo('design$counter')\"><b>" . $item . "</a><br>";
                    $counter++;
                }
                echo "</td>";
                echo "<td align=center class=randende width=\"10%\"><img src=\"images/clear.gif\" width=10 height=10 name=\"admconf\"><a class=nav href=\"admin.php?Submit=adminconfig\" onMouseOver=\"mi('admconf')\" onMouseOut=\"mo('admconf')\"><b>Config-Login</a><br>";
                echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"screens\"><a class=nav href=\"screenscheck.php\" target=\"_blank\" onMouseOver=\"mi('screens')\" onMouseOut=\"mo('screens')\"><b>Mapimages-Check</a><br>";
                echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"force\"><a class=nav href=\"admin.php?Submit=forceladderupdate\" onMouseOver=\"mi('force')\" onMouseOut=\"mo('force')\"><b>Force Ladderupdate</a></td>";
                break;

            case "serverlist":
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\">";
                echo "<font class=headers>Server IP</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\">";
                echo "<font class=headers>Server Beacon Port</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\">";
                echo "<font class=headers>" . $text_displayorder . "</font></td>";
                echo "<td align=center class=tabfarbe-5>";
                echo "<font class=headers>" . $text_comment . "</font></td>";
                echo "<td align=center class=tabfarbe-5></td>";
                echo "</tr>";
                if (isset($listdata)) {
                    foreach ($listdata as $listarraykey => $listentry) {
                        echo "<tr>";
                        echo "<td align=center class=tabfarbe-5><img src=\"images/clear.gif\" width=10 height=10 name= \"edit" . $listentry['id'] . "\">";
                        echo "<a class=nav href=\"admin.php?Submit=listedit&pedit=" . $listarraykey . "\" onMouseOver=\"mi('edit" . $listentry['id'] . "')\" onMouseOut=\"mo('edit" . $listentry['id'] . "')\">";
                        echo "<b>" . $text_edit . "</a></td>";
                        echo "<td align=center class=tabfarbe-5><font class=normal>" . $listentry['ip'] . "</font></td>";
                        echo "<td align=center class=tabfarbe-5><font class=normal>" . $listentry['bp'] . "</font></td>";
                        echo "<td align=center class=tabfarbe-5><font class=normal>" . $listentry['sort'] . "</font></td>";
                        echo "<td align=center class=tabfarbe-5><font class=normal>" . $listentry['text'] . "</font></td>";
                        echo "<td align=center class=tabfarbe-5><img src=\"images/clear.gif\" width=10 height=10 name=\"del" . $listentry['id'] . "\">";
                        echo "<a class=nav href=\"admin.php?Submit=deletelistip&isneu=" . $listentry['id'] . "\" onMouseOver=\"mi('del" . $listentry['id'] . "')\" onMouseOut=\"mo('del" . $listentry['id'] . "')\">";
                        echo "<b>" . $text_delete . "</a></td>";
                        echo "</tr>";
                    }
                }
                echo "</table></table>";
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\" text=\"#FFFFFF\">";
                echo "<tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers>Server IP</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers>Server Beacon Port</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers><?php print $text_displayorder; ?></font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>" . $text_comment . "</font></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5>";
                echo "<input class=textfield type=text name=ipneu size=15 maxlength=100 value=\"0.0.0.0\" class=editbox title=\"New Server�s IP\"></td>";
                echo "<td align=center class=tabfarbe-5>";
                echo "<input class=textfield type=text name=bpneu size=5 maxlength=5 value=\"8777\" class=editbox title=\"New Server�s ServerBeaconPort\"></td>";
                echo "<td align=center class=tabfarbe-5>";
                echo "<input class=textfield type=text name=sortneu size=4 maxlength=4 value=\"1\" class=editbox title=\|New Server�s Display-Order\"></td>";
                echo "<td align=center class=tabfarbe-5>";
                echo "<input class=textfield type=text name=textneu size=40 maxlength=40 value=\"" . $text_comment . "\" class=editbox title=\"New Server�s Comment\"></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5 colspan=4><br>";
                echo "<input type=\"submit\" name=\"Submit\" value=\"New Serverlist Entry\" class=\"button\"><br>&nbsp;</td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\">";
                echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"switch\">";
                echo "<a class=nav href=\"admin.php?Submit=main\" onMouseOver=\"mi('switch')\" onMouseOut=\"mo('switch')\"><b>Back to Main</b></a>";
                echo "</td></tr></table></table><br>";
                break;

            case "listedit":

                $pedit = (int)$_GET['pedit'];
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers>Server IP</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers>Server Beacon Port</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers>" . $text_displayorder . "</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>" . $text_comment . "</font></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $listdata[$pedit]['ip'] . "</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $listdata[$pedit]['bp'] . "</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $listdata[$pedit]['sort'] . "</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $listdata[$pedit]['text'] . "</font></td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers>Server IP</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers>Server Beacon Port</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\"><font class=headers>" . $text_displayorder . "</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>" . $text_comment . "</font></td>";
                echo "</tr><tr>";
                echo "<input type=hidden name=\"isneu\" value=\"" . $listdata[$pedit]['id'] . "\" class=editbox>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"ipneu\" size=15 maxlength=100 value=\"" . $listdata[$pedit]['ip'] . "\" class=editbox title=\"New IP\"></td>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"bpneu\" size=5 maxlength=5 value=\"" . $listdata[$pedit]['bp'] . "\" class=editbox title=\"New ServerBeaconport\"></td>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"sortneu\" size=4 maxlength=4 value=\"" . $listdata[$pedit]['sort'] . "\" class=editbox title=\"New Display-Order\"></td>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"textneu\" size=40 maxlength=40 value=\"" . $listdata[$pedit]['text'] . "\" class=editbox title=\"New Comment\"></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5 colspan=4><br><input type=submit name=\"Submit\" value=\"Change Serverlist Entry\" class=button>&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=\"Submit\" value=\"Cancel Edit Serverlist\" class=button><br>&nbsp;</td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                break;

            case "dllinks":
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5><font class=normal></font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Mapname</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Link</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers></font></td>";
                echo "</tr>";
                if (isset($mapdata)) {
                    foreach ($mapdata as $maparraykey => $mapentry) {
                        echo "<tr>";
                        echo "<td align=center class=tabfarbe-5><img src=\"images/clear.gif\" width=10 height=10 name=\"editl" . $mapentry['id'] . "\"><a class=nav href=\"admin.php?Submit=editdllinks&ledit=" . $maparraykey . "\" onMouseOver=\"mi('editl" . $mapentry['id'] . "')\" onMouseOut=\"mo('editl" . $mapentry['id'] . "')\"><b>" . $text_edit . "</a></td>";
                        echo "<td align=center class=tabfarbe-5><font class=normal>" . $mapentry['map'] . "</font></td>";
                        echo "<td align=center class=tabfarbe-5><img src=\"images/clear.gif\" width=10 height=10 name=\"link" . $mapentry['id'] . "\"><a class=nav target=\"_blank\" href=\"" . $mapentry['link'] . "\" onMouseOver=\"mi('link" . $mapentry['id'] . "')\" onMouseOut=\"mo('link" . $mapentry['id'] . "')\">" . $mapentry['link'] . "</a></td>";
                        echo "<td align=center class=tabfarbe-5><img src=\"images/clear.gif\" width=10 height=10 name=\"dell" . $maparraykey . "\"><a class=nav href=\"admin.php?Submit=deletelink&lneu=" . $mapentry['id'] . "\" onMouseOver=\"mi('dell" . $mapentry['id'] . "')\" onMouseOut=\"mo('dell" . $mapentry['id'] . "')\"><b>" . $text_delete . "</a></td>";
                        echo "</tr>";
                    }
                }
                echo "</table></table>";
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Mapname</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Link</font></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"mapneu\" size=25 maxlength=40 value=\"Mapname\" class=editbox title=\"New Maps Name\"></td>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"linkneu\" size=50 maxlength=255 value=\"http://\" class=editbox title=\"New Link to Download\"></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5 colspan=4><br><input type=submit name=\"Submit\" value=\"New Link\" class=button><br>&nbsp;</td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\">";
                echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"switch\">";
                echo "<a class=nav href=\"admin.php?Submit=main\" onMouseOver=\"mi('switch')\" onMouseOut=\"mo('switch')\"><b>Back to Main</b></a>";
                echo "</td></tr></table></table><br>";
                break;

            case "editdllinks":
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Mapname</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Link</font></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $mapdata[$ledit]['map'] . "</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $mapdata[$ledit]['link'] . "</font></td>";
                echo "</tr></table>";
                echo "<tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Mapname</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Link</font></td>";
                echo "</tr><tr>";
                echo "<input type=hidden name=\"linkid\" value=\"" . $mapdata[$ledit]['id'] . "\" class=editbox>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"mapneu\" size=25 maxlength=40 value=\"" . $mapdata[$ledit]['map'] . "\" class=editbox title=\"New Maps Name\"></td>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"linkneu\" size=50 maxlength=255 value=\"" . $mapdata[$ledit]['link'] . "\" class=editbox title=\"New Link to Download\"></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5 colspan=4><br><input type=submit name=\"Submit\" value=\"Change Link-Entry\" class=button>&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=\"Submit\" value=\"Cancel Edit Links\" class=button><br>&nbsp;</td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                break;

            case "adminconfig":
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5 width=\"50%\"><font class=headers>Name</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"50%\"><font class=headers>" . $text_password . "</font></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $adminname . "</font></td>";
                echo "<td align=center class=tabfarbe-5></td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5 width=\"50%\"><font class=headers>Name</font></td>";
                echo "<td align=center class=tabfarbe-5 width=\"50%\"><font class=headers>" . $text_password . "</font></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"nameneu\" size=15 maxlength=15 value=\"" . $adminname . "\" class=editbox title=\"New Name\"></td>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"passneu\" size=15 maxlength=15 value=\"\" class=editbox title=\"New Password\"></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5 colspan=2>&nbsp;<br><input type=submit name=\"Submit\" value=\"SetAdmin\" class=button>&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=\"Submit\" value=\"Cancel Admin Config\" class=button><br>&nbsp;</td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                break;

            case "serverident":
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Serverident</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Servername</font></td>";
                echo "<td align=center class=tabfarbe-5></td>";
                echo "</tr>";
                if (isset($identdata)) {
                    foreach ($identdata as $identarraykey => $idententry) {
                        echo "<tr>";
                        echo "<td align=center class=tabfarbe-5><img src=\"images/clear.gif\" width=10 height=10 name=\"editident" . $idententry['id'] . "\"><a class=nav href=\"admin.php?Submit=editident&identedit=" . $identarraykey . "\" onMouseOver=\"mi('editident" . $idententry['id'] . "')\" onMouseOut=\"mo('editident" . $idententry['id'] . "')\"><b>" . $text_edit . "</a></td>";
                        echo "<td align=center class=tabfarbe-5><font class=normal>" . $idententry['ident'] . "</font></td>";
                        echo "<td align=center class=tabfarbe-5><font class=normal>" . $idententry['name'] . "</a></td>";
                        echo "<td align=center class=tabfarbe-5><img src=\"images/clear.gif\" width=10 height=10 name=\"delident" . $idententry['id'] . "\"><a class=nav href=\"admin.php?Submit=deleteident&delident=" . $idententry['id'] . "&delidentname=" . $idententry['ident'] . "\" onMouseOver=\"mi('delident" . $idententry['id'] . "')\" onMouseOut=\"mo('delident" . $idententry['id'] . "')\"><b>" . $text_delete . "</a></td>";
                        echo "</tr>";
                    }
                }
                echo "</table><tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Serverident</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Servername</font></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"sidentneu\" size=25 maxlength=20 value=\"Ident\" class=editbox title=\"New Serverident\"></td>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"snameneu\" size=35 maxlength=30 value=\"MyServer\" class=editbox title=\"New Servername\"></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5 colspan=4><br><input type=submit name=\"Submit\" value=\"New Ident\" class=button><br>&nbsp;</td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5 width=\"10%\">";
                echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"switch\">";
                echo "<a class=nav href=\"admin.php?Submit=main\" onMouseOver=\"mi('switch')\" onMouseOut=\"mo('switch')\"><b>Back to Main</b></a>";
                echo "</td></tr></table></table><br>";
                break;

            case "editident":
                $identedit = $_GET['identedit'];
                echo "<table border=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Serverident</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Servername</font></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $identdata[$identedit]['ident'] . "</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=normal>" . $identdata[$identedit]['name'] . "</font></td>";
                echo "</tr></table>";
                echo "<tr><td><hr></td></tr></table>";
                echo "<table border=0 cellpadding=0 cellspacing=0 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td width=\"100%\" class=tabfarbe-3>";
                echo "<table border=0 cellspacing=1 width=\"" . $awidth . "\">";
                echo "<tr>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Serverident</font></td>";
                echo "<td align=center class=tabfarbe-5><font class=headers>Servername</font></td>";
                echo "</tr><tr>";
                echo "<input type=hidden name=\"identid\" value=\"" . $identdata[$identedit]['id'] . "\">";
                echo "<input type=hidden name=\"identold\" value=\"" . $identdata[$identedit]['ident'] . "\">";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"sidentneu\" size=25 maxlength=20 value=\"" . $identdata[$identedit]['ident'] . "\" class=editbox title=\"New Serverident\"></td>";
                echo "<td align=center class=tabfarbe-5><input class=textfield type=text name=\"snameneu\" size=35 maxlength=30 value=\"" . $identdata[$identedit]['name'] . "\" class=editbox title=\"New Servername\"></td>";
                echo "</tr><tr>";
                echo "<td align=center class=tabfarbe-5 colspan=4><br><input type=submit name=\"Submit\" value=\"Change Ident-Entry\" class=button>&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=\"Submit\" value=\"Cancel Edit Ident\" class=button><br>&nbsp;</td>";
                echo "</tr></table><tr><td><hr></td></tr></table>";
                break;

        }
        ?>
</tr></table>
<br>
<input type=submit name="Submit" value="Logoff" class=button>
<?php print $text_serverlist; ?></center></form></body></html>
<?php
    }
} else {
    ?>
<link rel="stylesheet" type="text/css" href="<?php print $text_serverlist;; ?>">
<body class=body>
<center>
<table border="0" cellspacing="0" width="<?php print $text_serverlist;; ?>"><form name="listadm" action="admin.php" method="post">
<tr><td align="center" class="oben" background="images/<?php print $text_serverlist; ?> Admin-Login</td>
</tr><tr><td><hr></td></tr></table>
<table border="0" cellpadding="0" cellspacing="0" width="200">
<tr><td width="100%" class="tabfarbe-3">
<table border="0" cellspacing="1" width="200">
<tr>
<td align="center" class="tabfarbe-5" width="10%"><font class=headers>Name</font></td>
<td align="center" class="tabfarbe-5" width="10%"><font class=headers><?php print $text_password; ?></font></td>
</tr><tr>
<td align="center" class="tabfarbe-5" width="10%"><input class="textfield" type="text" name="admin" size="15" maxlength="15"  class="editbox" title="Name"></td>
<td align="center" class="tabfarbe-5" width="10%"><input class="textfield" type="password" name="pw" size="15" maxlength="15"  class="editbox" title="Passwort"></td>
</tr></table></table><br>
<input type="submit" name="Submit" value="Login" class="button" >
<br><br>
<a class=nav href="main.php"><?php print $text_back; ?></a>
<?php print Copyrightextmain(); ?></form></center></body></html>
<?php } ?>
