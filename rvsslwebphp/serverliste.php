<?php
// Script (C)opyright 2003 by =TSAF=Muschel
// Released under GNU GENERAL PUBLIC LICENSE
// www.tsaf.de , muschel@tsaf.de
error_reporting(2047);

$allespieler = "";

require("config.inc.php");
if (isset($hideplayers)) {
    setcookie("RVShideoptionplayers", $hideplayers);
} else {
    if (isset($_COOKIE["RVShideoptionplayers"])) {
        $hideplayers = $_COOKIE["RVShideoptionplayers"];
    }
}
if (isset($_GET['hideplayers'])) {
    $hideplayers = $_GET['hideplayers'];
}else {
    $hideplayers = "1";
}
$db = ConnectTheDBandGetDefaults();
BuildGameModeTranslateArray($db);
require('language/' . $customlanguage . '.inc.php');
$res = db_query($db, "SELECT * FROM $dbtable1 ORDER BY sort");
$serveranzahl = db_num_rows($res);
for ($q = 0; $q < $serveranzahl; $q++) {
    $dbrow = db_fetch_array($res);
    $ip[$q] = $dbrow['ip'];
    $bp[$q] = $dbrow['bp'];
}
$spieler = 0;
$zaehler = 0;
require("header.php");
?>
<link rel="stylesheet" type="text/css" href="<?php print $css; ?>">
<script language="javascript">
    <!--
    if (document.images) {
        on = new Image();
        on.src = "images/indicator.gif";
        off = new Image();
        off.src = "images/clear.gif";
    }
    function mi(n)
    {
        if (document.images) {
            document[n].src = eval("on.src");
        }
    }
    function mo(n)
    {
        if (document.images) {
            document[n].src = eval("off.src");
        }
    }
    // -->
</script>
<body class=body>
<center>
    <table border=0 cellspacing=0 width=600>
        <tr>
            <td align=left class=oben background="images/<?php print $design[$dset]; ?>_header.gif">
                <?php print ShowFlags("serverliste.php", ""); ?><?php print $text_listtitle; ?></td>
            <td align=right class=oben background="images/<?php print $design[$dset]; ?>_header.gif"><img src="images/clear.gif"
                                                                                                 width=10 height=10
                                                                                                 name="back"><a
                    class=nav2 href="main.php" onMouseOver="mi('back')" onMouseOut="mo('back')"><b><?php print $text_mainmenu; ?>
                        &nbsp;&nbsp;</a><img src="images/clear.gif" width=10 height=10 name="fresh"><a class="nav2"
                                                                                                       href="javascript:location.reload()"
                                                                                                       onMouseOver="mi('fresh')"
                                                                                                       onMouseOut="mo('fresh')"><?php print $text_refresh; ?></a>&nbsp;
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <hr>
            </td>
        </tr>
    </table>
    <table border=0 cellpadding=0 cellspacing=0 width=600>
        <tr>
            <td align=center class=oben width=10 background="images/<?php print $design[$dset]; ?>_header.gif">L</td>
            <td align=center class=oben width=16 background="images/<?php print $design[$dset]; ?>_header.gif">PB</td>
            <td align=center class=oben width=230
                background="images/<?php print $design[$dset]; ?>_header.gif"><?php print $text_servername; ?></td>
            <td align=center class=oben width=50
                background="images/<?php print $design[$dset]; ?>_header.gif"><?php print $text_player; ?></td>
            <td align=center class=oben width=140
                background="images/<?php print $design[$dset]; ?>_header.gif"><?php print $text_map; ?></td>
            <td align=center class=oben background="images/<?php print $design[$dset]; ?>_header.gif"><?php print $text_gametype; ?></td>
        </tr>
    </table>
    <?php
    for ($i = 0; $i < $serveranzahl; $i++) {
        $antwort = "";

        $fp = fsockopen("udp://" . $ip[$i], $bp[$i], $errno, $errstr);
        if (is_resource($fp)) {
            socket_set_timeout($fp, $socket_timeout);
            fwrite($fp, "REPORTEXT", 9);
            if ($socket_blocking_use == True) {
                socket_set_blocking($fp, True);
            }
            $antworta = fread($fp, 1);
            $anz = socket_get_status($fp);
            if ($anz['unread_bytes'] > 0) {
                $antworta .= fread($fp, $anz['unread_bytes']);
            }
            $anzahl = substr($antworta, 5, 1);
            $packetnr = substr($antworta, strlen($antworta) - 1, 1);
            $sorted[$packetnr] = substr($antworta, 7, strlen($antworta) - 13);

            for ($h = 2; $h <= $anzahl; $h++) {
                $antworta = fread($fp, 1);
                $anz = socket_get_status($fp);
                if ($anz['unread_bytes'] > 0) {
                    $antworta .= fread($fp, $anz['unread_bytes']);
                }
                $packetnr = substr($antworta, strlen($antworta) - 1, 1);
                $sorted[$packetnr] = substr($antworta, 7, strlen($antworta) - 13);
            }
            $antwort = "";
            for ($j = 1; $j <= $anzahl; $j++) {
                $antwort .= $sorted[$j];
            }
            fclose($fp);
        } else {
            $antwort = "no server";
        }

        if ($antwort == "no server") {
            ?>
            <table border=0 cellpadding=0 cellspacing=0 width=600>
                <tr>
                    <td class=rand width=10>&nbsp;</td>
                    <td class=rand width=16>&nbsp;</td>
                    <td class=rand width=281><img src="images/clear.gif" width="10" height="10" name="svr<?php print $i; ?>"><a
                            class=nav href="server.php?beaconport=<?php print $bp[$i]; ?>&ip=<?php print $ip[$i]; ?>"
                            onMouseOver="mi('svr<?php print $i; ?>')" onMouseOut="mo('svr<?php print $i; ?>')"><?php print $ip[$i]; ?>
                            :Beacon(<?php print $bp[$i]; ?>)</a></td>
                    <td align=center class=randende><font class=offline><?php print $text_noconnect; ?></font></td>
                </tr>
            </table>
            <?php
        } elseif ($antwort == "") {
            ?>
            <table border=0 cellpadding=0 cellspacing=0 width=600>
                <tr>
                    <td class=rand width=10>&nbsp;</td>
                    <td class=rand width=16>&nbsp;</td>
                    <td class=rand width=281><img src="images/clear.gif" width="10" height="10" name="svr<?php print $i; ?>"><a
                            class=nav href="server.php?beaconport=<?php print $bp[$i]; ?>&ip=<?php print $ip[$i]; ?>"
                            onMouseOver="mi('svr<?php print $i; ?>')" onMouseOut="mo('svr<?php print $i; ?>')"><?php print $ip[$i]; ?>
                            :Beacon(<?php print $bp[$i]; ?>)</a></td>
                    <td align=center class=randende><font class=offline><?php print $text_offmapch; ?></font></td>
                </tr>
            </table>
            <?php
        } elseif (!strstr($antwort, "EV")) {
            ?>
            <table border=0 cellpadding=0 cellspacing=0 width=600>
            <tr>
                <td class=rand width=10>&nbsp;</td>
                <td class=rand width=16>&nbsp;</td>
                <td class=rand width=281><img src="images/clear.gif" width="10" height="10" name="svr<?php print $i; ?>"><a
                        class=nav href="server.php?beaconport=<?php print $bp[$i]; ?>&ip=<?php print $ip[$i]; ?>"
                        onMouseOver="mi('svr<?php print $i; ?>')" onMouseOut="mo('svr<?php print $i; ?>')"><?php print $ip[$i]; ?>
                        :Beacon(<?php print $bp[$i]; ?>)</a></td>
                <td align=center class=randende><font class=offline><?php print $text_notravenshield; ?></font></td>
            </tr></table><?php
        } elseif (strstr($antwort, "EV")) {
            $daten = explode(" �", $antwort);
            foreach ($daten as $item) {
                $itemlen = strlen($item);
                $dataarray[substr($item, 0, 2)] = substr($item, 3, $itemlen - 3);
            }
            $PlayerList = explode("/", substr($dataarray['L1'], 1));
            if ($dataarray['B1'] <> "0") {
                $f = $dataarray['A1'] / $dataarray['B1'];
                if ($f <= "1") {
                    $farbe = "full";
                } else {
                    if ($f <= "2") {
                        $farbe = "medium";
                    } else {
                        $farbe = "low";
                    }
                }
            } else {
                $farbe = "hight";
            }
            $spieler = $spieler + $dataarray['B1'];
            if ($PlayerList['0'] <> "") {
                foreach ($PlayerList as $item) {
                    $allespieler[$zaehler] = $item . " �" . $dataarray['I1'] . " �" . "server.php?beaconport=" . $bp[$i] . "&ip=" . $ip[$i];
                    $zaehler++;
                }
            }
            if ($dataarray['G1'] <> "1") {
                $locked = 'clear.gif';
            } else {
                $locked = 'locked.gif';
            }
            if ($dataarray['L3'] <> "1") {
                $punks = 'clear.gif';
            } else {
                $punks = 'pb.gif';
            }
            ?>
<table border=0 cellpadding=0 cellspacing=0 width=600>
<tr>
<td class=rand width=10><img src="images/<?php print $locked; ?>"></td>
<td align=center class=rand width=16><img src="images/<?php print $punks; ?>"></td>
<td class=rand width=230><img src="images/clear.gif" width="10" height="10" name="svr<?php print $i; ?>"><a class=nav  href="server.php?beaconport=<?php print $bp[$i]; ?>&ip=<?php print $ip[$i]; ?>" onMouseOver="mi('svr<?php print $i; ?>')" onMouseOut="mo('svr<?php print $i; ?>')"><?php print htmlentities($dataarray['I1']); ?></a></td>
<td align=center class=rand width=50><font class="count<?php print $farbe; ?>"><?php print $dataarray['B1']; ?>&nbsp;/&nbsp;<?php print $dataarray['A1']; ?></font></td>
<td align=center class=rand width=140><font class=normal><?php print $dataarray['E1']; ?></font></td>
<td align=center class=randende><font class=normal><?php print TranslateBeaconGameModeToText($dataarray['F1']); ?></font></td></table>
</tr>
<?php
        }
    }
    ?>
    </table></table><br>
    <table border=0 cellspacing=0 width=600>
        <tr>
            <td align=left class=oben background="images/<?php print $design[$dset]; ?>_header.gif">
                &nbsp;<?php print $text_playerlist; ?></td>
            <td align=right class=oben background="images/<?php print $design[$dset]; ?>_header.gif"><img src="images/clear.gif"
                                                                                                 width=10 height=10
                                                                                                 name="hideplayers"><a
                    class=nav2 href="serverliste.php?hideplayers=<?php print -($hideplayers - 1); ?>"
                    onMouseOver="mi('hideplayers')" onMouseOut="mo('hideplayers')"><?php print $text_showhide[$hideplayers]; ?>
                    &nbsp;</a></td>
        </tr>
        <tr>
            <td colspan=2>
                <hr>
            </td>
        </tr>
    </table>
    <?php
    if ($hideplayers == "1") {
        ?>
        <table border=0 cellpadding=0 cellspacing=0 width=600>
            <tr>
                <td align=center class=oben
                    background="images/<?php print $design[$dset]; ?>_header.gif"><?php print $text_player; ?></td>
                <td align=center class=oben
                    background="images/<?php print $design[$dset]; ?>_header.gif"><?php print $text_servername; ?></td>
            </tr>
            <?php
            if ($allespieler) {
                asort($allespieler);
                $zz = 0;
                foreach ($allespieler as $item) {
                    $ausgabe = explode(" �", $item);
                    echo "<tr><td align=center class=rand><font class=playername>" . htmlentities($ausgabe[0]) . "</font></td><td align=center class=randende><img src=\"images/clear.gif\" width=10 height=10 name=\"nme" . $zz . "\"><a class=nav href=\"" . $ausgabe[2] . "\" onMouseOver=\"mi('nme" . $zz . "')\" onMouseOut=\"mo('nme" . $zz . "')\">" . htmlentities($ausgabe[1]) . "</a></td></tr>";
                    $zz++;
                }
            }
            ?>
        </table>
        <?php
    }
    ?>
    </table>
    <font class=normal><br><?php print $text_playercount1 . $spieler . $text_playercount2; ?><br>
        <?php print Copyrightext(); ?></font></center>
</body></html>
