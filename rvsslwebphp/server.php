<?php
// Script (C)opyright 2003 by =TSAF=Muschel
// Released under GNU GENERAL PUBLIC LICENSE
// www.tsaf.de , muschel@tsaf.de
error_reporting(2047);
if (!isset($_GET['ip']) or !isset($_GET['beaconport'])) {
    die ("No IP or ServerBeaconPort in URL !!");
}
require("config.inc.php");
if (isset($_GET['hideinfo'])) {
    setcookie("RVShideoptioninfo", $_GET['hideinfo']);
} else {
    if (isset($_COOKIE["RVShideoptioninfo"])) {
        $_GET['hideinfo'] = $_COOKIE["RVShideoptioninfo"];
    }
}
if (!isset($_GET['hideinfo'])) {
    $_GET['hideinfo'] = "1";
}
if (isset($hidemaps)) {
    setcookie("RVShideoptionmaps", $hidemaps);
} else {
    if (isset($_COOKIE["RVShideoptionmaps"])) {
        $hidemaps = $_COOKIE["RVShideoptionmaps"];
    }
}
if (!isset($hidemaps)) {
    $hidemaps = "1";
}
DefDiffLevels();
$db = ConnectTheDBandGetDefaults();
BuildGameModeTranslateArray($db);
require('language/' . $customlanguage . '.inc.php');
$res = db_query($db, "SELECT * FROM $dbtable3");
$linksanzahl = db_num_rows($res);
for ($q = 0; $q < $linksanzahl; $q++) {
    $dbrow = db_fetch_array($res);
    $maplink[$dbrow['map']] = $dbrow['link'];
}
// ??? Weird. Original was $ip = gethostbyname($ip);
$hostname = gethostbyname($_GET['ip']);
require("header.php");
?>
<LINK rel='stylesheet' HREF="<?php print $css; ?>" TYPE='text/css'>
<body class=body>
<script language="javascript" type="text/javascript">
    <!--
    var win = null;
    function NewWindow(mypage, myname, w, h, pos, infocus)
    {
        if (pos == "random") {
            myleft = (screen.width) ? Math.floor(Math.random() * (screen.width - w)) : 100;
            mytop = (screen.height) ? Math.floor(Math.random() * ((screen.height - h) - 75)) : 100;
        }
        if (pos == "center") {
            myleft = (screen.width) ? (screen.width - w) / 2 : 100;
            mytop = (screen.height) ? (screen.height - h) / 2 : 100;
        }
        else if ((pos != 'center' && pos != "random") || pos == null) {
            myleft = 20;
            mytop = 20
        }
        settings = "width=" + w + ",height=" + h + ",top=" + mytop + ",left=" + myleft + ",scrollbars=no,location=no,directories=no,status=no,menubar=no,toolbar=no,resizable=no,dependent=no";
        win = window.open(mypage, myname, settings);
        win.focus();
    }
    // -->
</script>
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
<center>
    <table border=0 cellspacing=0 width="<?php print $swidth; ?>">
        <tr>
            <td align=left class=bigheader background="images/<?php print $design[$dset]; ?>_header.gif">
                <?php print ShowFlags("server.php", "&beaconport=" . $_GET['beaconport'] . "&ip=" . $_GET['ip']) ?><?php print $text_serverheader; ?></td>
            <td align=right class=bigheader background="images/<?php print $design[$dset]; ?>_header.gif"><img
                    src="images/clear.gif" width=10 height=10 name="fresh"><a class="nav2"
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
    <font class=normal>
        <?php

        $beaconconnect = fsockopen("udp://" . $_GET['ip'], $_GET['beaconport'], $errno, $errstr);
        if (is_resource($beaconconnect)) {
            socket_set_timeout($beaconconnect, $socket_timeout);
            fwrite($beaconconnect, "REPORTEXT", 9);
            if ($socket_blocking_use == True) {
                socket_set_blocking($beaconconnect, True);
            }
            $antworta = fread($beaconconnect, 1);
            $anz = socket_get_status($beaconconnect);
            if ($anz['unread_bytes'] > 0) {
                $antworta .= fread($beaconconnect, $anz['unread_bytes']);
            }
            $anzahl = substr($antworta, 5, 1);
            $packetnr = substr($antworta, strlen($antworta) - 1, 1);
            $sorted[$packetnr] = substr($antworta, 7, strlen($antworta) - 13);

            for ($i = 2; $i <= $anzahl; $i++) {
                $antworta = fread($beaconconnect, 1);
                $anz = socket_get_status($beaconconnect);
                if ($anz['unread_bytes'] > 0) {
                    $antworta .= fread($beaconconnect, $anz['unread_bytes']);
                }
                $packetnr = substr($antworta, strlen($antworta) - 1, 1);
                $sorted[$packetnr] = substr($antworta, 7, strlen($antworta) - 13);
            }
            $antwort = "";
            for ($j = 1; $j <= $anzahl; $j++) {
                $antwort .= $sorted[$j];
            }
            fclose($beaconconnect);
        }

        if (strstr($antwort, "EV")) {
        $daten = explode(" ¶", $antwort);
        foreach ($daten as $item) {
            $dataarray[substr($item, 0, 2)] = substr($item, 3, strlen($item) - 3);
        }

        $counter = 0;
        if ($dataarray['G1'] <> "1") {
            $passwordedpic = 'public.gif';
        } else {
            $passwordedpic = 'private.gif';
        }
        $MapCycle = explode("/", $dataarray['K1']);
        $GameTypelist = explode("/", $dataarray['J1']);
        $PlayerList = explode("/", substr($dataarray['L1'], 1));
        $PlayerTime = explode("/", substr($dataarray['M1'], 1));
        $PlayerPing = explode("/", substr($dataarray['N1'], 1));
        $PlayerKills = explode("/", substr($dataarray['O1'], 1));
        $pic = strtolower($dataarray['E1']);
        if (!file_exists('./mapimages/' . $pic . '.jpg')) {
            $pic = "nopic";
        }
        $InTeam = explode("/", substr($dataarray['TE'], 1));
        $Deaths = explode("/", substr($dataarray['DE'], 1));
        $Alive = explode("/", substr($dataarray['HE'], 1));
        $Ubi = explode("/", substr($dataarray['UB'], 1));
        $PWpn = explode("/", substr($dataarray['PW'], 1));
        $SWpn = explode("/", substr($dataarray['SW'], 1));
        $PWpnG = explode("/", substr($dataarray['PG'], 1));
        $SWpnG = explode("/", substr($dataarray['SG'], 1));
        $Hits = explode("/", substr($dataarray['HI'], 1));
        $Fired = explode("/", substr($dataarray['RF'], 1));
        $Acc = explode("/", substr($dataarray['AC'], 1));
        $Killedby = explode("/", substr($dataarray['KB'], 1));
        $JoinedLate = explode("/", substr($dataarray['LA'], 1));
        $TeamScore = explode("/", substr($dataarray['TS'], 1));
        $dataarray['ST'] = $text_servertime . ": " . $dataarray['ST'];
        $RoundsPlayed = explode("/", substr($dataarray['RP'], 1));
        $IsFemale = explode("/", substr($dataarray['GM'], 1));
        $IsPilot = explode("/", substr($dataarray['IP'], 1));
        $joinlink = "protocoltest.php?currentmod=" . $dataarray['L2'] . "&startip=" . $_GET['ip'] . ":" . $dataarray['P1'] . "&bport=" . $_GET['beaconport'];

        if (!isset($dataarray['T1'])) {
            $dataarray['T1'] = "none";
        }

        ?>
        <table border="0" cellspacing="0" class=frame width="<?php print $swidth; ?>">
            <tr>
                <td background="images/<?php print $design[$dset]; ?>_header.gif" colspan=4 class=bigheader align=left>
                    &nbsp;<?php print $text_serverinfo; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php
                    if (isset($dataarray['ST'])) {
                        echo $dataarray['ST'];
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_servername; ?>:&nbsp;&nbsp;</td>
                <td class=rand>&nbsp;<?php print htmlentities($dataarray['I1']); ?></td>
                <td class=rand rowspan=8 align=center><img src="images/<?php print $passwordedpic; ?>"></td>
                <td class=randende rowspan=8 align=center><img src="mapimages/<?php echo $pic; ?>.jpg"></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_adress; ?>:</td>
                <td class=rand>&nbsp;<?php print $_GET['ip'] . ":" . $dataarray['P1']; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_actmap; ?>:</td>
                <td class=rand>&nbsp;<?php print $dataarray['E1']; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_gametype; ?>:</td>
                <td class=rand>&nbsp;<?php print TranslateBeaconGameModeToText($dataarray['F1']); ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_version; ?>:</td>
                <td class=rand>&nbsp;<?php print $dataarray['D2']; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_player; ?>:</td>
                <td class=rand>&nbsp;<?php print $dataarray['B1'] . "/" . $dataarray['A1']; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_rounds; ?>:</td>
                <td class=rand>&nbsp;<?php print $dataarray['Q1'] ?> <?php print $text_rounds; ?>
                    /<?php print $text_map; ?>
                    (<?php
                    print (int)($dataarray['R1'] / 60);
                    ?>:<?php
                    printf("%02u", $dataarray['R1'] - ((int)($dataarray['R1'] / 60) * 60));
                    ?>m:s/<?php
                    print $text_round;
                    ?>)
                </td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_password; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_yn[$dataarray['G1']]; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;Punkbuster:</td>
                <td class=rand>&nbsp;<?php print $text_yn[$dataarray['L3']]; ?></td>
                <td class=randende colspan=2 align=center>
                    <?php
                    if ($dataarray['B1'] <> $dataarray['A1']) {
                        ?>
                        <img src="images/clear.gif" width=10 height=10 name="join"><a class=nav
                                                                                      href="javascript:NewWindow('<?php print $joinlink; ?>','Protocol','200','100','center','front');"
                                                                                      onMouseOver="mi('join')"
                                                                                      onMouseOut="mo('join')"><b><?php print $text_serverjoin; ?>
                        </a>
                        <?php
                    } else {
                        echo $text_serverisfull . "<a class=nav href=\"javascript:NewWindow('" . $joinlink . "','Protocol','200','100','center','front');\"><b>...</a>";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_customgame; ?>:</td>
                <td class=rand>&nbsp;<?php print $dataarray['L2']; ?></td>
                <td colspan=2 class=randende align=center>
                    <?php
                    echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"adm\"><a class=nav href=\"server_admin.php?ip=" . $_GET['ip'] . "&port=" . $_GET['beaconport'] . "\" onMouseOver=\"mi('adm')\" onMouseOut=\"mo('adm')\"><b>Administration</a>";
                    ?>
            </tr>

            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_currentround; ?>:</td>
                <td class=rand>&nbsp;<?php print $dataarray['CR']; ?></td>
                <td class=randende colspan=2 align=center>
                    <?php
                    echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"rest\"><a class=nav href=\"server_restkit.php?ip=" . $_GET['ip'] . "&port=" . $_GET['beaconport'] . "&sname=" . htmlentities($dataarray['I1']) . "\" onMouseOver=\"mi('rest')\" onMouseOut=\"mo('rest')\"><b>" . $text_showrestkit . "</a>";
                    ?>
                </td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_remaintime; ?>:</td>
                <?php
                if ($dataarray['TR'] < 0) {
                    if ($dataarray['TU'] > $dataarray['S1'] or $dataarray['TU'] < -3) {
                        $dataarray['TU'] = $dataarray['S1'];
                    }
                    ?>
                    <td class=rand>&nbsp;<?php print $text_waitforstart ?>:<?php print $dataarray['TU']; ?> s</td>
                    <?php
                } else {
                    ?>
                    <td class=rand>&nbsp;<?php print (int)($dataarray['TR'] / 60); ?>
                        :<?php print sprintf("%02u", $dataarray['TR'] - ((int)($dataarray['TR'] / 60) * 60)); ?>&nbsp;m:s
                    </td>
                    <?php
                }
                echo "<td class=randende colspan=2 align=center>";
                echo "<img src=\"images/clear.gif\" width=10 height=10 name=\"banlist\"><a class=nav href=\"server_banlist.php?ip=" . $_GET['ip'] . "&port=" . $_GET['beaconport'] . "&sname=" . base64_encode($dataarray['I1']) . "\" onMouseOver=\"mi('banlist')\" onMouseOut=\"mo('banlist')\"><b>" . $text_showbanlist . "</a>";
                echo "</td>";
                ?>
        </table>
        <table width="<?php print $swidth; ?>" border=0 cellspacing=0 class=frame>
            <tr>
                <td colspan=4 class=bigheader background="images/<?php print $design[$dset]; ?>_header.gif">
                    &nbsp;<?php print $text_svrsets; ?></td>
                <td colspan=2 align=right class=bigheader background="images/<?php print $design[$dset]; ?>_header.gif">
                    <img
                        src="images/clear.gif" width=10 height=10 name="hideinfo"><a class=nav2
                                                                                     href="server.php?ip=<?php print $_GET['ip'] ?>&beaconport=<?php print $_GET['beaconport']; ?>&hideinfo=<?php print -($_GET['hideinfo'] - 1); ?>"
                                                                                     onMouseOver="mi('hideinfo')"
                                                                                     onMouseOut="mo('hideinfo')"><?php print $text_showhide[$_GET['hideinfo']]; ?>
                        &nbsp;</a></td>
            </tr>
            <tr>
                <?php
                if ($_GET['hideinfo'] == "1")
                {

                ?>
                <td class=randende colspan=6>&nbsp;<?php print $text_motd ?>:&nbsp;<?php print $dataarray['MM']; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_DiffLevel; ?>:</td>
                <td class=randende colspan=5>&nbsp;<?php print $diff[$dataarray['DL']]; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_SpamThreshold; ?>:</td>
                <td class=rand>&nbsp;<?php print $dataarray['SH']; ?></a></td>
                <td class=rand>&nbsp;<?php print $text_ChatLockDuration; ?>:</td>
                <td class=rand>&nbsp;<?php print $dataarray['CL']; ?></td>
                <td class=rand>&nbsp;<?php print $text_VoteBcMaxFreq; ?>:</td>
                <td class=randende>&nbsp;<?php print $dataarray['VF']; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_CamFirstP; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['C1']]; ?></a></td>
                <td class=rand>&nbsp;<?php print $text_Cam3rdP; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['C3']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_CamFree3rdP; ?>:</td>
                <td class=randende>&nbsp;<?php print $text_01[$dataarray['CP']]; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_CamGhost; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['CG']]; ?></a></td>
                <td class=rand>&nbsp;<?php print $text_CamFadeToBlack; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['CF']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_CamTeamOnly; ?>:</td>
                <td class=randende>&nbsp;<?php print $text_01[$dataarray['CT']]; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_ffpw; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['K2']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_ff; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['Y1']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_bombtime; ?>:</td>
                <td class=randende>&nbsp;<?php print $dataarray['T1']; ?> s</td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_autoteam; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['Z1']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_radar; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['B2']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_tbr; ?>:</td>
                <td class=randende>&nbsp;<?php print $dataarray['S1']; ?> s</td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_penalty; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['A2']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_teamnames; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['W1']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_terrorcount; ?>:</td>
                <td class=randende>&nbsp;<?php print $dataarray['H2']; ?></td>
            </tr>
            <tr>
                <td class=rand>&nbsp;<?php print $text_rotatemap; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['J2']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_aiback; ?>:</td>
                <td class=rand>&nbsp;<?php print $text_01[$dataarray['I2']]; ?></td>
                <td class=rand>&nbsp;<?php print $text_dedi; ?>:</td>
                <td class=randende>&nbsp;<?php print $text_yn[$dataarray['H1']]; ?></td>
            </tr>
            <?php
            if ($dataarray['EV'] >= 11) {
                echo "<td class=header background=\"images/$design[$dset]_middle.gif\" align=center colspan=6>";
                echo "<b>Messenger:&nbsp;" . $text_yn[$dataarray['ME']] . "</b></td></tr><tr>";
                if ($dataarray['ME'] == 1) {
                    echo "<td class=randende colspan=6 align=center><font size=1>" . $dataarray['TA'] . "<br>" . $dataarray['TB'] . "<br>" . $dataarray['TC'] . "</font></td></tr><tr>";
                }
            }
            echo "</table>";
            }
            ?>
            <table width="<?php print $swidth; ?>" border=0 cellspacing=0 class=frame>
                <tr>
                    <td colspan=2 class=bigheader background="images/<?php print $design[$dset]; ?>_header.gif">
                        &nbsp;<?php print $text_playerinfo; ?></td>
                </tr>
                <?php
                if ($GameModeTranslate[$dataarray['F1']] == "pilot" or $GameModeTranslate[$dataarray['F1']] == "teamsurvival" or $GameModeTranslate[$dataarray['F1']] == "bomb" or $GameModeTranslate[$dataarray['F1']] == "hostage" or $GameModeTranslate[$dataarray['F1']] == "terroristhuntadvmode" or $GameModeTranslate[$dataarray['F1']] == "scatteredhuntadvmode" or $GameModeTranslate[$dataarray['F1']] == "capturetheenemymode" or $GameModeTranslate[$dataarray['F1']] == "countdownmode" or $GameModeTranslate[$dataarray['F1']] == "kamikazemode") {
                    ?>
                    <tr>
                        <td align=center class=green width="50%"><?php print $text_greenscore ?>
                            : <?php print $TeamScore['0']; ?></td>
                        <td align=center class=red width="50%"><?php print $text_redscore ?>
                            : <?php print $TeamScore['1']; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <table width="<?php print $swidth; ?>" border=0 cellspacing=0>
                <tr>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" width="12"></td>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" align="center">
                        <b><?php print $text_playername; ?></td>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" align="center"><b>UBI
                    </td>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" align="center">
                        <b><?php print $text_killedby; ?></td>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" width="10"
                        align="center">
                        <b><?php print $text_kills; ?></td>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" width="10"
                        align="center">
                        <b><?php print $text_deaths; ?></td>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" width="50"
                        align="center">
                        <b><?php print $text_timeos; ?></td>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" width="10"
                        align="center">
                        <b><?php print $text_roundsplayed; ?></td>
                    <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" width="30"
                        align="center">
                        <b><?php print $text_pingts; ?></td>
                    <?php
                    echo "<td class=header background=\"images/$design[$dset]_middle.gif\" width=10 align=center><b>$text_gender</td>";
                    $colset = "10";
                    if ($GameModeTranslate[$dataarray['F1']] == "pilot") {
                        echo "<td class=header background=\"images/$design[$dset]_middle.gif\" width=10 align=center><b>P</td>";
                        $colset = "11";
                    }
                    ?>
                </tr>
                <?php
                if ($PlayerList['0']) {
                    $sortcount = 0;
                    foreach ($PlayerList as $item) {
                        $playersorted[] = $InTeam[$sortcount] . " ¶" . htmlentities($PlayerList[$sortcount]) . " ¶" . $PlayerKills[$sortcount] . " ¶" . $PlayerTime[$sortcount] . " ¶" . $PlayerPing[$sortcount] . " ¶" . $Deaths[$sortcount] . " ¶" . $Alive[$sortcount] . " ¶" . $Ubi[$sortcount] . " ¶" . $PWpn[$sortcount] . " ¶" . $SWpn[$sortcount] . " ¶" . $PWpnG[$sortcount] . " ¶" . $SWpnG[$sortcount] . " ¶" . $Hits[$sortcount] . " ¶" . $Fired[$sortcount] . " ¶" . $Acc[$sortcount] . " ¶" . $JoinedLate[$sortcount] . " ¶" . htmlentities($Killedby[$sortcount]) . " ¶" . $RoundsPlayed[$sortcount] . " ¶" . $IsFemale[$sortcount] . " ¶" . $IsPilot[$sortcount];
                        $sortcount++;
                    }

                    asort($playersorted);
                    foreach ($playersorted as $item) {
                        $ausgabe = explode(" ¶", $item);
                        $color = "randende";

                        if ($ausgabe['0'] == "4") {
                            $color = "spec";
                            $ausgabe['6'] = 3;
                        }
                        if ($ausgabe['0'] == "0") {
                            $color = "black";
                            $ausgabe['6'] = 3;
                        }
                        if ($GameModeTranslate[$dataarray['F1']] == "pilot" or $GameModeTranslate[$dataarray['F1']] == "teamsurvival" or $GameModeTranslate[$dataarray['F1']] == "bomb" or $GameModeTranslate[$dataarray['F1']] == "hostage" or $GameModeTranslate[$dataarray['F1']] == "terroristhuntadvmode" or $GameModeTranslate[$dataarray['F1']] == "scatteredhuntadvmode" or $GameModeTranslate[$dataarray['F1']] == "capturetheenemymode" or $GameModeTranslate[$dataarray['F1']] == "countdownmode" or $GameModeTranslate[$dataarray['F1']] == "kamikazemode") {
                            if ($ausgabe['0'] == "3") {
                                $color = "red";
                            }
                            if ($ausgabe['0'] == "2") {
                                $color = "green";
                            }
                        }
                        $playerlink = "playerdetail.php?nick=" . base64_encode($ausgabe['1']) . "&Ubi=" . base64_encode($ausgabe['7']) . "&PWpn=" . $ausgabe['8'] . "&SWpn=" . $ausgabe['9'] . "&PWpnG=" . $ausgabe['10'] . "&SWpnG=" . $ausgabe['11'] . "&Hits=" . $ausgabe['12'] . "&Fired=" . $ausgabe['13'] . "&Kills=" . $ausgabe['2'] . "&Deaths=" . $ausgabe['5'] . "&Acc=" . $ausgabe['14'];
                        $ladderlink = "ubiladder.php?ubi=$ausgabe[7]";
                        if ($ausgabe['15'] == 1) {
                            $ausgabe['6'] = 3;
                        }
                        $tonserver = explode(':', $ausgabe['3']);
                        $tonserver['2'] = (int)($tonserver['0'] / 60);
                        $tonserver['0'] = $tonserver['0'] - ($tonserver['2'] * 60);
                        ?>
                        <tr>
                            <td class=<?php print $color ?>><img src="images/<?php print $pic_Alive[$ausgabe[6]]; ?>"
                                                                 title="<?php print $alivetext[$ausgabe[6]] . $ausgabe[16]; ?>">
                            </td>
                            <td class=<?php print $color; ?>>&nbsp;<a class=nav
                                                                      href="javascript:NewWindow('<?php print $playerlink; ?>','Playerdetails','271','278','center','front');"
                                                                      title="<?php print $text_playerdetailstitle; ?>"><img
                                        src="images/wps.gif" border="0">&nbsp;<?php print $ausgabe['1']; ?></a></td>
                            <td class=<?php print $color; ?> align=left>&nbsp;<a class=nav
                                                                                 href="javascript:NewWindow('<?php print $ladderlink; ?>','Ubiladder','352','350','center','front');"
                                                                                 title="<?php print $text_ubiladdertitle; ?>"><img
                                        src="images/ubi.gif" border="0">&nbsp;<?php print $ausgabe['7']; ?></a></td>
                            <td class=<?php print $color ?> align=left>&nbsp;<i><?php print $ausgabe['16']; ?></td>
                            <td class=<?php print $color ?> align=right><?php print $ausgabe['2']; ?></td>
                            <td class=<?php print $color ?> align=right><?php print $ausgabe['5']; ?></td>
                            <td class=<?php print $color ?> width="50" align=right><?php print $tonserver['2']; ?>
                                :<?php sprintf("%02u", $tonserver['0']) ?>
                                :<?php sprintf("%02u", $tonserver['1']); ?></td>
                            <td class=<?php print $color ?> align=right><?php print $ausgabe['17']; ?></td>
                            <td class=<?php print $color ?> width="30" align=right><?php print $ausgabe['4']; ?></td>
                            <?php
                            echo "<td class=$color width=10 align=left><img src=\"images/";
                            if ($ausgabe['18'] == "0") {
                                echo "male";
                            } else {
                                echo "female";
                            }
                            echo ".gif\"></td>";
                            if ($GameModeTranslate[$dataarray['F1']] == "pilot") {
                                echo "<td class=$color width=10 align=left><img src=\"images/";
                                if ($ausgabe['19'] == "0") {
                                    echo "clear";
                                } else {
                                    echo "pilot";
                                }
                                echo ".gif\"></td>";
                            }
                            ?>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td class=randende colspan=$colset align=center>$text_npo</td></tr>";
                }
                echo "</table>";

                ?>
                <table width="<?php print $swidth; ?>" border=0 cellspacing=0 class=frame>
                    <tr>
                        <td colspan=2 class=bigheader background="images/<?php print $design[$dset]; ?>_header.gif">
                            &nbsp;<?php print $text_maplist; ?></td>
                        <td colspan=2 align=right class=bigheader
                            background="images/<?php print $design[$dset]; ?>_header.gif">
                            <img src="images/clear.gif" width=10 height=10 name="hidemaps"><a class=nav2
                                                                                              href="server.php?ip=<?php print $_GET['ip']; ?>&beaconport=<?php print $_GET['beaconport']; ?>&hidemaps=<?php print -($hidemaps - 1); ?>"
                                                                                              onMouseOver="mi('hidemaps')"
                                                                                              onMouseOut="mo('hidemaps')"><?php print $text_showhide[$hidemaps]; ?>
                                &nbsp;</a></td>
                    </tr>
                    <?php
                    if ($hidemaps == "1") {
                        ?>
                        <tr>
                            <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" width="1%">
                                &nbsp;<b><?php print $text_number; ?></b></td>
                            <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif">
                                &nbsp;<b><?php print $text_map; ?></td>
                            <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif" align=center
                                width="15%">&nbsp;<b><?php print $text_download; ?></td>
                            <td class=header background="images/<?php print $design[$dset]; ?>_middle.gif">
                                &nbsp;<b><?php print $text_gametype; ?></td>
                        </tr>
                        <?php
                        $counter = 0;
                        foreach ($MapCycle as $item) {
                            if ($counter <> 0) {
                                echo "<tr><td class=rand>&nbsp;" . $counter . "</td><td class=rand>";
                                if ($dataarray['EV'] >= 11 and $counter == ($dataarray['MN'] + 1)) {
                                    echo "<i><b>";
                                }
                                echo "&nbsp;" . $item . "</td>";
                                if (isset($maplink[$item])) {
                                    echo "<td class=rand align=center><img src=\"images/clear.gif\" width=10 height=10 name=\"link" . $counter . "\"><a class=nav href=\"$maplink[$item]\" target=\"_blank\" onMouseOver=\"mi('link" . $counter . "')\" onMouseOut=\"mo('link" . $counter . "')\"><b>" . $text_link . "</a></td>";
                                } else {
                                    echo "<td class=rand>&nbsp;</td>";
                                }
                                echo "<td class=randende>&nbsp;" . TranslateBeaconGameModeToText($GameTypelist[$counter]) . "</td></tr>";
                            }
                            $counter = $counter + 1;
                        }
                    }
                    }
                    else {
                        echo $text_offline['0'] . $_GET['ip'] . $text_offline['1'] . $_GET['beaconport'] . $text_offline['2'];
                        echo "<br><br><br><a class=nav href=\"javascript:location.reload()\">" . $text_refresh . "</a><br><br>";
                    }

                    ?>
                </table>
            </table>
            <br><font class="normal"><a class=nav href="serverliste.php"><?php print $text_backtolist; ?></a>
    <?php print Copyrightext(); ?></center>
</body></html>