<?php
// Script (C)opyright 2003 by =TSAF=Muschel
// Released under GNU GENERAL PUBLIC LICENSE
// www.tsaf.de , muschel@tsaf.de
error_reporting(2047);

if (!isset($_GET['ip']) or !isset($_GET['port'])) {
    die ("No IP or ServerBeaconPort in URL !!");
}
require("config.inc.php");
$db = db_connect($dbHost, $dbUser, $dbPass) or die ("<CENTER>Connect-Error to MySQL! Check $dbHost, $dbUser and $dbPass in config.inc.php!");
db_select_db($dbDatabase, $db) or die ("<CENTER>Connect-Error to Database! Check $dbDatabase in config.inc.php!");

$res = db_query($db, "SELECT * FROM " . $dbtable2 . " WHERE id=?", 'i', 1);
$num = db_num_rows($res);
for ($q = 1; $q < $num + 1; $q++) {
    $dbrow = db_fetch_array($res);
    $lset = $dbrow['language'];
    $dset = $dbrow['css'];
}
$css = "css/" . $design[$dset] . "_css.css";
if (isset($customlanguage)) {
    setcookie("RVScustomlanguage", $customlanguage);
} else {
    if (isset($_COOKIE["RVScustomlanguage"])) {
        $customlanguage = $_COOKIE["RVScustomlanguage"];
    }
}
if (!isset($customlanguage)) {
    $customlanguage = $language[$lset];
}
require('language/' . $customlanguage . '.inc.php');;

$fp = fsockopen("udp://" . $_GET['ip'], $_GET['port'], $error, $error2);
if (is_resource($fp)) {

    socket_set_timeout($fp, $socket_timeout);
    fwrite($fp, "BANLIST", 7);
    if ($socket_blocking_use == True) {
        socket_set_blocking($fp, True);
    }
    $serverrepeat = fread($fp, 1);
    $bytes_left = socket_get_status($fp);
    if ($bytes_left['unread_bytes'] > 0) {
        $serverrepeat .= fread($fp, $bytes_left['unread_bytes']);
    }

    $numbercheck = explode(" ¶", $serverrepeat);
    foreach ($numbercheck as $item) {
        $numbercheckarray[substr($item, 0, 2)] = substr($item, 3, strlen($item) - 3);
    }

    if (isset($numbercheckarray['NP'])) {
        for ($i = 1; $i < $numbercheckarray['NP']; $i++) {
            $serverrepeat .= fread($fp, 1);
            $bytes_left = socket_get_status($fp);
            if ($bytes_left['unread_bytes'] > 0) {
                $serverrepeat .= fread($fp, $bytes_left['unread_bytes']);
            }
        }
    }
    fclose($fp);
}
if (isset($serverrepeat)) {
    $dataarray = explode(" ¶", $serverrepeat);
    foreach ($dataarray as $item) {
        if (substr($item, 0, 2) == "BL") {
            $spchr = strpos($item, " ");
            $ausgabe[substr($item, 2, $spchr - 2)] = substr($item, $spchr, strlen($item) - $spchr);
        }
    }
}
require("header.php");
?>
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
<link rel="stylesheet" type="text/css" href="<?php print $css; ?>">
<body>
<center>
    <table border=0 cellspacing=0 width="<?php print $swidth; ?>">
        <tr>
            <td align=left class=oben background="images/<?php print $design[$dset]; ?>_header.gif">
                <?php
                echo "&nbsp;";
                foreach ($language as $item) {
                    echo "<a href=\"server_banlist.php?customlanguage=" . $item . "&port=" . $_GET['port'] . "&ip=" . $_GET['ip'] . "&sname=" . $sname . "\"><img border=0 src=\"language/flags/" . $item . ".gif\"></a>&nbsp;";

                }
                ?>
                <?php print $text_serverbanlist; ?> / <?php print base64_decode($sname); ?></td>
            <td align=right class=oben background="images/<?php print $design[$dset]; ?>_header.gif"><img src="images/clear.gif"
                                                                                                 width=10 height=10
                                                                                                 name="back"><a
                    class=nav2 href="server.php?beaconport=<?php print $_GET['port']; ?>&ip=<?php print $_GET['ip']; ?>" onMouseOver="mi('back')"
                    onMouseOut="mo('back')"><?php print $text_back; ?>&nbsp;</a></td>
        </tr>
        </tr>
        <tr>
            <td colspan=2>
                <hr>
            </td>
        </tr>
    </table>
    <table border=0 cellspacing=0 class=frame width="<?php print $swidth; ?>">
        <tr>
            <td align=left class=oben background="images/<?php print $design[$dset]; ?>_header.gif" width=20>
                &nbsp;<b><?php print $text_number; ?></td>
            <td align=center class=oben background="images/<?php print $design[$dset]; ?>_header.gif">Banned-ID</td>
        </tr>

        <?php
        if (isset($ausgabe)) {
            sort($ausgabe);
            foreach ($ausgabe as $bannr => $item) {
                echo "<tr><td align=left class=rand>&nbsp;" . $bannr . "</td>";
                echo "<td align=center class=randende>" . $item . "</td></tr>";
            }
        } else {
            echo "<tr><td align=center colspan=2 class=randende>offline or empty!</td></tr>";
            echo "</table><br><br><br><a class=nav href=\"javascript:location.reload()\">Refresh</a><br><br>";
        }
        ?>
    </table>
    <center><br><font class="normal"><a class=nav
                                        href="server.php?ip=<?php print $_GET['ip']; ?>&beaconport=<?php print $_GET['port']; ?>"><?php print $text_back; ?></a>
        <?php print Copyrightext(); ?></center>
</body></html>
