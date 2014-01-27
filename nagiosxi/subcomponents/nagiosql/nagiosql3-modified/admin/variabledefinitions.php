<?php
///////////////////////////////////////////////////////////////////////////////
//
// NagiosQL
//
///////////////////////////////////////////////////////////////////////////////
//
// (c) 2008, 2009 by Martin Willisegger
//
// Project   : NagiosQL
// Component : Admin time definition list
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: variabledefinitions.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Vorgabedatei einbinden
// ======================
$preAccess  = 1;
$intSub     = 2;
$preNoMain  = 1;
require("../functions/prepend_adm.php");
//
// Übergabeparameter überprüfen
// ============================
$chkDataId    = isset($_GET['dataId'])  ? $_GET['dataId']   : 0;
$chkMode      = isset($_GET['mode'])    ? $_GET['mode']     : "";
$chkDef       = isset($_GET['def'])     ? $_GET['def']      : "";
$chkRange     = isset($_GET['range'])   ? $_GET['range']    : "";
$chkId        = isset($_GET['id'])      ? $_GET['id']       : "";
$chkVersion   = isset($_GET['version']) ? $_GET['version']  : 0;
$chkLinkTab   = isset($_GET['linktab']) ? $_GET['linktab']  : "";
if (ini_get("magic_quotes_gpc") == 0) {
  $chkDef     = addslashes($chkDef);
  $chkRange   = addslashes($chkRange);
}
//
// Datensätze holen
// ================
if ($chkLinkTab != "") {
  $strSQL    = "SELECT * FROM `tbl_variabledefinition` LEFT JOIN `".$chkLinkTab."` ON `id`=`idSlave` WHERE `idMaster`=$chkDataId ORDER BY `name`";
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
  //
  // Daten in Session abspeichern
  // ============================
  if ($chkMode == "") {
    $_SESSION['variabledefinition'] = "";
    if ($intDataCount != 0) {
      foreach ($arrDataLines AS $elem) {
        $arrTemp['id']          = $elem['id'];
        $arrTemp['definition']  = addslashes($elem['name']);
        $arrTemp['range']       = addslashes($elem['value']);
        $arrTemp['status']      = 0;
        $_SESSION['variabledefinition'][]   = $arrTemp;
      }
    }
  }
}
//
// Modus Add
// =========
if ($chkMode == "add") {
  if (isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition'])) {
    $intCheck = 0;
    foreach ($_SESSION['variabledefinition'] AS $key => $elem) {
      if (($elem['definition'] == $chkDef) && ($elem['status'] == 0)) {
        $_SESSION['variabledefinition'][$key]['definition'] = $chkDef;
        $_SESSION['variabledefinition'][$key]['range'] = $chkRange;
        $intCheck = 1;
      }
    }
    if ($intCheck == 0) {
      $arrTemp['id'] = 0;
      $arrTemp['definition'] = $chkDef;
      $arrTemp['range'] = $chkRange;
      $arrTemp['status'] = 0;
      $_SESSION['variabledefinition'][] = $arrTemp;
    }
  } else {
    $arrTemp['id'] = 0;
    $arrTemp['definition'] = $chkDef;
    $arrTemp['range'] = $chkRange;
    $arrTemp['status'] = 0;
    $_SESSION['variabledefinition'][] = $arrTemp;
  }
}
//
// Modus Del
// =========
if ($chkMode == "del") {
  if (isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition'])) {
    foreach ($_SESSION['variabledefinition'] AS $key => $elem) {
      if (($elem['definition'] == $chkDef) && ($elem['status'] == 0)) {
        $_SESSION['variabledefinition'][$key]['status'] = 1;
      }
    }
  }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>None</title>
<link href="<?php echo $SETS['path']['root']; ?>config/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" language="javascript">
  <!--
  function doEdit(key,range) {
  parent.document.frmDetail.txtVariablename.value = key;
  parent.document.frmDetail.txtVariablevalue.value = range;
  }
  function doDel(key) {
    document.location.href = "<?php echo $SETS['path']['root']; ?>admin/variabledefinitions.php?dataId=<?php echo $chkDataId; ?>&mode=del&def="+key;
  }
  //-->
</script>
</head>
<body style="margin:0">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<?php
  if (isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition']) && (count($_SESSION['variabledefinition']) != 0)) {
    foreach($_SESSION['variabledefinition'] AS $elem) {
      if ($elem['status'] == 0) {
?>
  <tr>
      <td class="tablerow" style="padding-bottom:2px; width:260px"><?php echo htmlspecialchars(stripslashes($elem['definition'])); ?></td>
        <td class="tablerow" style="padding-bottom:2px; width:260px"><?php echo htmlspecialchars(stripslashes($elem['range'])); ?></td>
        <td class="tablerow" style="width:50px" align="right"><img src="<?php echo $SETS['path']['root']; ?>images/edit.gif" width="18" height="18" alt="<?php echo gettext('Modify'); ?>" title="<?php echo gettext('Modify'); ?>" onClick="doEdit('<?php echo $elem['definition']; ?>','<?php echo $elem['range']; ?>')" style="cursor:pointer">&nbsp;<img src="<?php echo $SETS['path']['root']; ?>images/delete.gif" width="18" height="18" alt="<?php echo gettext('Delete'); ?>" title="<?php echo gettext('Delete'); ?>" onClick="doDel('<?php echo $elem['definition']; ?>')" style="cursor:pointer"></td>
    </tr>
<?php
      }
    }
  } else {
?>
  <tr>
      <td class="tablerow"><?php echo gettext('No data'); ?></td>
        <td class="tablerow">&nbsp;</td>
        <td class="tablerow" align="right">&nbsp;</td>
    </tr>
<?php
  }
?>
</table>
</body>
</html>