<?php
// Script (C)opyright 2003 by =TSAF=Muschel
// Released under GNU GENERAL PUBLIC LICENSE
// www.tsaf.de , muschel@tsaf.de
error_reporting(2047);
if (!isset($_GET['nick']) or
    !isset($_GET['Ubi']) or
    !isset($_GET['PWpn']) or
    !isset($_GET['SWpn']) or
    !isset($_GET['SWpnG']) or
    !isset($_GET['Hits']) or
    !isset($_GET['Fired']) or
    !isset($_GET['Kills']) or
    !isset($_GET['Deaths']) or
    !isset($_GET['Acc'])) {
    die ("missing value in url error!");
}
require("config.inc.php");
$db = ConnectTheDBandGetDefaults();
require('language/' . $customlanguage . '.inc.php');
if (!$_GET['PWpn']) {
    $_GET['PWpn'] = "non";
}
if (!$_GET['SWpn']) {
    $_GET['SWpn'] = "non";
}
if (!$PWpnG) {
    $PWpnG = "non";
}
if (!$_GET['SWpnG']) {
    $_GET['SWpnG'] = "non";
}
if ($_GET['Acc'] > 100) {
    $_GET['Acc'] = 100;
}
?>
<html>
<title><?php print $text_pd; ?></title>
<LINK rel='stylesheet' HREF="<?php print $css; ?>" TYPE='text/css'>
<body class=body>
<table border="0" align="center" cellspacing="0" width="268">
    <tr>
        <td colspan=2 align=center class=bigheader background="images/<?php print $design[$dset]; ?>_header.gif">
            <b><?php print $text_pd; ?></b></td>
    </tr>
    <tr>
    <tr>
        <td colspan=2 align=center class=randende>
            <?php print ShowFlags("playerdetail.php", "&nick=" . $_GET['nick'] . "&Ubi=" . $_GET['Ubi'] . "&PWpn=" . $_GET['PWpn'] . "&SWpn=" . $_GET['SWpn'] . "&PWpnG=" . $PWpnG . "&SWpnG=" . $_GET['SWpnG'] . "&Hits=" . $_GET['Hits'] . "&Fired=" . $_GET['Fired'] . "&Kills=" . $_GET['Kills'] . "&Deaths=" . $_GET['Deaths'] . "&Acc=" . $_GET['Acc']); ?>
        </td>
    </tr>
    <td class=rand align=left width="20%">&nbsp;<?php print $text_pdnick; ?>:</td>
    <td class=randende align=left>&nbsp;<?php print base64_decode($_GET['nick']); ?></td>
    </tr>
    <tr>
        <td class=rand align=left>&nbsp;UBI:</td>
        <td class=randende align=left>&nbsp;<?php print base64_decode($_GET['Ubi']); ?></td>
    </tr>
    <tr>
        <td class=rand align=left>&nbsp;<?php print $text_pdkills; ?>:</td>
        <td class=randende align=left>&nbsp;<img width="<?php print $_GET['Kills']; ?>%" height="8" border="0" src="images/hitgreen.gif">&nbsp;<?php print $_GET['Kills']; ?>
        </td>
    </tr>
    <tr>
        <td class=rand align=left>&nbsp;<?php print $text_pddeaths; ?>:</td>
        <td class=randende align=left>&nbsp;<img width="<?php print $_GET['Deaths']; ?>%" height="8" border="0" src="images/hitred.gif">&nbsp;<?php print $_GET['Deaths']; ?>
        </td>
    </tr>
    </tr>
    <tr>
        <td colspan=2 align="center" class=randende><?php print $_GET['Fired']; ?> <?php print $text_pdbfired; ?>
            <br><?php print $_GET['Hits']; ?> <?php print $text_pdbhits; ?>
            <br><?php print $text_pdacc; ?> <?php print $_GET['Acc']; ?>%<br>
            <img width="<?php print $_GET['Acc'] - 1; ?>%" height="8" border="0" src="images/hitgreen.gif"><img width="<?php print 99 - $_GET['Acc']; ?>%"
                                                                                               height="8" border="0"
                                                                                               src="images/hitred.gif">
        </td>
    </tr>
    <tr>
        <td class=randende align=left valign=top colspan=2 height=127 background="images/wpnback.gif">
            <img width="30" height="60" border="0" src="images/clear.gif"><img border="0"
                                                                               src="weaponicons/<?php print $_GET['PWpn']; ?>.gif"
                                                                               title="<?php print $_GET['PWpn']; ?>"><img width="20"
                                                                                                         height="1"
                                                                                                         border="0"
                                                                                                         src="images/clear.gif"><img
                border="0" src="gadgeticons/<?php print $PWpnG; ?>.gif" title="<?php print $PWpnG; ?>"><br>
            <img width="70" height="50" border="0" src="images/clear.gif"><img border="0"
                                                                               src="weaponicons/<?php print $_GET['SWpn']; ?>.gif"
                                                                               title="<?php print $_GET['SWpn']; ?>"> <img width="45"
                                                                                                          height="1"
                                                                                                          border="0"
                                                                                                          src="images/clear.gif"><img
                border="0" src="gadgeticons/<?php print $_GET['SWpnG']; ?>.gif" title="<?php print $_GET['SWpnG']; ?>">
        </td>
    </tr>
</table>
</body>
</html>
