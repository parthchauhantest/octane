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
// Component : Visualization Class
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: nag_class.php 286 2010-08-17 20:08:34Z egalstad $
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
// Class: General representation functions
//
///////////////////////////////////////////////////////////////////////////////////////////////
// Covers all the features necessary to illustrate the application
// Are
//
// Name: nagvisual
//
// Class variables:
// -----------------
// $ ArrSettings: Multi-dimensional array with the global configuration settings
// $ IntDomainId: Domain Id
// $ MyDBClass: Database class object
//
//  External functions
// ------------------
//
//
///////////////////////////////////////////////////////////////////////////////////////////////
class nagvisual {
    // Declare class variables
    var $arrSettings;         // Is filled in the class
  var $intDomainId;         // Is filled in the class
  var $myDBClass;           // Is defined in the file prepend_adm.php

    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Constructor 
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Activities during class initialization
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function nagvisual() {
    // Global settings read
    $this->arrSettings = $_SESSION['SETS'];
    if (isset($_SESSION['domain'])) $this->intDomainId = $_SESSION['domain'];
  }
    ///////////////////////////////////////////////////////////////////////////////////////////
 // Function: Set Position - uses the menu integers to call which menu item is being selected as the current page 
 //								- menu items are all stored in the tbl_menu, tbl_submenu in mysql 
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Get current position within the menu structure and gives them as
  // Info line back.
  //
  // Parameters: $ intMain ID of the selected main menu item
  // $ IntSub ID of the selected submenu entry (0 if none)
  // $ StrTop The root node as a string (optional)
  //
  // Return value: String Position
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getPosition($intMain,$intSub = 0,$strTop = "") {
    $strPosition = "";
    $strSQLMain = "SELECT `item`, `link` FROM `tbl_mainmenu` WHERE `id` = $intMain";
    $booReturn = $this->myDBClass->getDataArray($strSQLMain,$arrDataMain,$intDataCountMain);
    if (($booReturn != false) && ($intDataCountMain != 0)) {
      $strMainLink = $this->arrSettings['path']['root'].$arrDataMain[0]['link'];
      $strMain = gettext($arrDataMain[0]['item']);
      if ($strTop != "") {
        $strPosition .= "<a href='".$_SESSION['SETS']['path']['root']."admin.php'>".$strTop."</a> -> ";
      }
      $strPosition .= "<a href='".$strMainLink."'>".gettext($strMain)."</a>";
    }
    if ($intSub != 0) {
      $strSQLSub  = "SELECT `item`, `link` FROM `tbl_submenu` WHERE `id_main` = $intMain AND `id` = $intSub";
      $booReturn = $this->myDBClass->getDataArray($strSQLSub,$arrDataSub,$intDataCountSub);
      if (($booReturn != false) && ($intDataCountSub != 0)) {
        $strSubLink = $this->arrSettings['path']['root'].$arrDataSub[0]['link'];
        $strSub = gettext($arrDataSub[0]['item']);
        if ($strSub != "") {
          $strPosition .= " -> <a href='".$strSubLink."'>".gettext($strSub)."</a>";
        }
      }
    }
    return $strPosition;
  }
  
  
    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: Main Menu View
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
 // Returns from the main menu
  //
  // Parameters: $ intMain ID of the selected main menu item
  // $ IntSub ID of the selected submenu entry (0 if no)
  // $ Id intMenu Menugruppe
  //
  // Return value: 0 for success or 1 for failure
  //
   ///////////////////////////////////////////////////////////////////////////////////////////
  function getMenu($intMain,$intSub,$intMenu) {
    //
    // URL for visible / invisible menu change
    // =================================================
    $strQuery = str_replace("menu=visible&","",$_SERVER['QUERY_STRING']);
    $strQuery = str_replace("menu=invisible&","",$strQuery);
    $strQuery = str_replace("menu=visible","",$strQuery);
    $strQuery = str_replace("menu=invisible","",$strQuery);
    if ($strQuery != "") {
      $strURIVisible   = mysql_real_escape_string(htmlentities(str_replace("&","&amp;",$_SERVER['PHP_SELF']."?menu=visible&".$strQuery)));//security fix
      $strURIInvisible = mysql_real_escape_string(htmlentities(str_replace("&","&amp;",$_SERVER['PHP_SELF']."?menu=invisible&".$strQuery)));
    } else {
      $strURIVisible   = mysql_real_escape_string(htmlentities($_SERVER['PHP_SELF']."?menu=visible"));
      $strURIInvisible = mysql_real_escape_string(htmlentities($_SERVER['PHP_SELF']."?menu=invisible"));
    }
    
    
    //
    // Menu items from database to read and store in arrays
    // =========================================================
    $strSQLMain = "SELECT `id`, `item`, `link` FROM `tbl_mainmenu` WHERE `menu_id` = $intMenu ORDER BY `order_id`";
    $strSQLSub  = "SELECT `id`, `item`, `link`, `access_rights` FROM `tbl_submenu` WHERE `id_main` = $intMain ORDER BY `order_id`";
    // Records for the main menu in a numeric array store
    $booReturn = $this->myDBClass->getDataArray($strSQLMain,$arrDataMain,$intDataCountMain);
    if (($booReturn != false) && ($intDataCountMain != 0)) {
      $y=1;
      for ($i=0;$i<$intDataCountMain;$i++) {
        $arrMainLink[$y] = $this->arrSettings['path']['root'].$arrDataMain[$i]['link'];
        $arrMainId[$y]   = $arrDataMain[$i]['id'];
        $arrMain[$y]   = gettext($arrDataMain[$i]['item']);
        $y++;
      }
    } else {
      return(1);
    }
    // Records for the sub-menu in a numeric array store
    $booReturn = $this->myDBClass->getDataArray($strSQLSub,$arrDataSub,$intDataCountSub);
    if (($booReturn != false) && ($intDataCountSub != 0)) {
      $y=1;
      for ($i=0;$i<$intDataCountSub;$i++) {
        // Menu item in the array only transferred if the user has the necessary rights
        if ($this->checkKey($_SESSION['keystring'],$arrDataSub[$i]['access_rights']) == 0) {
          $arrSubLink[$y] = $this->arrSettings['path']['root'].$arrDataSub[$i]['link'];
          $arrSubID[$y]   = $arrDataSub[$i]['id'];
          $arrSub[$y]     = gettext($arrDataSub[$i]['item']);
          $y++;
        }
      }
    }
    //
    // Edition of the complete menu structure
    // ===================================
    //if (!(isset($_SESSION['menu'])) || ($_SESSION['menu'] != "invisible")) {
	// XI MOD - 11/07/09 removed menu 
    if ((isset($_SESSION['menu'])) && ($_SESSION['menu'] == "visible")) {
      // Menu is displayed
      echo "<td width=\"150\" align=\"center\" valign=\"top\">\n";
      echo "<table cellspacing=\"1\" class=\"menutable\">\n";
      // Work off each main menu item
      for ($i=1;$i<=count($arrMain);$i++) {
        echo "<tr>\n";
        if ($arrMainId[$i] == $intMain) {
          echo "<td class=\"menuaktiv\"><a href=\"".$arrMainLink[$i]."\">".$arrMain[$i]."</a></td>\n</tr>\n";
          // If sub-menu item exists
          if (isset($arrSub)) {
            echo "<tr>\n<td class=\"menusub\">\n";
            // Work through each sub menu item
            for ($y=1;$y<=count($arrSub);$y++) {
              if ((isset($arrSubLink[$y])) && ($arrSubLink[$y] != "")) {
                if ($arrSubID[$y] == $intSub) {
                  echo "<a class=\"menulink\" href=\"".$arrSubLink[$y]."\"><b>".$arrSub[$y]."</b></a><br>\n";
                } else {
                  echo "<a class=\"menulink\" href=\"".$arrSubLink[$y]."\">".$arrSub[$y]."</a><br>\n";
                }
              }
            }
            echo "</td>\n</tr>\n";
          }
        } else {
          echo "<td class=\"menuinaktiv\"><a href=\"".$arrMainLink[$i]."\">".$arrMain[$i]."</a></td>\n</tr>\n";
        }
      }
      echo "</table>\n";
      echo "<br><a href=\"$strURIInvisible\" class=\"menulinksmall\">[".gettext('Hide menu')."]</a>\n";
      echo "</td>\n";
    } else {
      // Menu is hidden
      echo "<td valign=\"top\">\n";
      echo "<a href=\"$strURIVisible\"><img src=\"".$this->arrSettings['path']['root']."images/menu.gif\" alt=\"".gettext('Show menu')."\" border=\"0\" ></a>\n";
      echo "</td>\n";
    }
    return(0);
  }
  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: access key convert - processes NagiosQL user permission key 
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  // Converts the string to access keys in an array 
  //
 // Parameters: $ strKey array with the language definitions
  //
  // Return Value: $ arrKey array with the key values 
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getKeyArray($strKey) {
    // Transfer key empty?
    if (($strKey == "") || (strlen($strKey) != 8)) $strKey = "00000000";
    // String key process
    for($i=0;$i<8;$i++) {
      $arrKey[] = substr($strKey,$i,1);
    }
    return($arrKey);
  }

  ///////////////////////////////////////////////////////////////////////////////////////////
 // Function: Process transfer is null 
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Convert the returned value of "zero" in -1 or allows him to
  //
  // Parameters: $ strKey Sring with the transfer value
  //
  // Return Value: Processed Sting 
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function checkNull($strKey) {
    // If the transfer is null
    if (strtoupper($strKey) == "NULL") {
      return("-1");
    }
    return($strKey);
  }

  ///////////////////////////////////////////////////////////////////////////////////////////
 // Function: A "/" at the end of the string append
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Hang a "/" at the end of a string and removes duplicate ("/") from this
  //
  // Parameters: $ strPath Sring with the transfer value
  //
  // Return Value: Processed Sting 
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function addSlash($strPath) {
    if ($strPath == "") return("");
    $strPath = $strPath."/";
    $strPath = str_replace("//","/",$strPath);
    $strPath = str_replace("//","/",$strPath);
    return ($strPath);
  }


  ///////////////////////////////////////////////////////////////////////////////////////////
 // Function: Check Authorization
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Check the permission of the access key
  //
  // Parameters: $ strUserKey access key of the user
  // $ StrAccessKey required access key
  //
  // Return Value: 0 / 1 0 if access ok / 1 if access denied 
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function checkKey($strUserKey,$strAccessKey) {
    // Convert array keys in
    $arrUserKey   = $this->getKeyArray($strUserKey);
    $arrAccessKey = $this->getKeyArray($strAccessKey);
    // Array to compare
    $intReturn = 0;
    for ($i=0;$i<8;$i++) {
      // No key required
      if ($arrAccessKey[$i] == 0) continue;
      // Keys available
      if (($arrAccessKey[$i] == 1) && ($strUserKey[$i] == 1)) continue;
      return(1);
    }
    return(0);
  }
    ///////////////////////////////////////////////////////////////////////////////////////////
 // Function: compose page links for page numbering 
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Creates a string containing the select the links for each page
  //
  // Parameters: $ link strSite aside
  // $ IntCount Number of records
  // $ ChkLimit Current Limit (Page Link Leave bold)
  // $ ChkSelOrderBy OrderBy string (for services page)
  //
  // Return value: string with the next page 
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function buildPageLinks($strSite,$intCount,$chkLimit,$chkSelOrderBy="") {
        $strPages = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n<tr>\n";
    // String defined as part 1
    if ($chkLimit > 0) {
      $intPrev   = mysql_real_escape_string($chkLimit-$this->arrSettings['common']['pagelines']);
      $strPages .= "<td valign=\"middle\" align=\"left\" width=\"25\"><a href=\"".$strSite;
      $strPages .= "?limit=$intPrev\"><img src=\"".$this->arrSettings['path']['root'];
      $strPages .= "images/left.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Prev\" title=\"Prev\">";
      $strPages .= "</a></td><td valign=\"middle\" align=\"center\">".gettext('Pages:')." [ ";
    } else {
      $strPages .= "<td valign=\"middle\" align=\"left\" width=\"25\"><img src=\"".$this->arrSettings['path']['root'];
      $strPages .= "images/pixel.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"-\" title=\"-\">";
      $strPages .= "</td><td valign=\"middle\" align=\"center\">".gettext('Pages:')." [ ";
    }
    // Divide by 15 the records in pages
    $y     = 1;
    $intNext = 0;
    for($i=0;$i<$intCount;$i=$i+$this->arrSettings['common']['pagelines']) {
      // Current page number write bold
        if ($i == $chkLimit) {
        $strNumber = "<b>$y</b>";
        $intNext = mysql_real_escape_string($chkLimit + $this->arrSettings['common']['pagelines']);
      } else {
        $strNumber = $y;
      }
      if ($chkSelOrderBy == "") {
        $strPages .= "<a href=\"".$strSite."?limit=$i\">".$strNumber."</a> ";
      } else {
        $strOrderBy = rawurlencode($chkSelOrderBy);
        $strPages .= "<a href=\"".$strSite."?limit=$i&orderby=$chkSelOrderBy\">".$strNumber."</a> ";
      }
      $y++;
    }
    if ($intNext < $intCount) {
      $strPages .= " ] </td><td valign=\"middle\" align=\"right\" width=\"25\"><a href=\"".$strSite;
      $strPages .= "?limit=$intNext\"><img src=\"".$this->arrSettings['path']['root'];
      $strPages .= "images/right.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Prev\" title=\"Prev\">";
      $strPages .= "</a></td>\n</tr>\n</table>\n";
    } else {
      $strPages .= " ] </td><td valign=\"middle\" align=\"right\" width=\"25\"><img src=\"".$this->arrSettings['path']['root'];
      $strPages .= "images/pixel.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"-\" title=\"-\">";
      $strPages .= "</td>\n</tr>\n</table>\n";
    }
    // Link string return if more than one page is displayed
    if ($y > 2) {
       
      return($strPages);
    } else {
      return("");
    }
  }
    ///////////////////////////////////////////////////////////////////////////////////////////
 // Function: build select box
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Build a selection field within a form to
  //
  // Parameters: $ strTable table name from which the data are damaging
  // $ StrTabField field name of the table from which the data are damaging
  // $ ObjTemplate Template Name
  // $ Template strParseVar key data value [DAT_XXX {}]
  // $ Template strParseGroup group of the selection [$ templ-> parse (xxx)]
  // $ IntDataId record ID (master table)
  // $ StrLinkTable link table name
  // $ IntSelMode ModusId choosing 0 = nothing 1 = 2 =* relations -1 = null
  // If the link table is empty handed, includes $ intSelMode
  // The id of the slave table
  // $ IntModeId 0 = data only, 1 = with empty line, 2 = with empty line and * 3 = *
  // $ Id intSkipId individuals must not be displayed
  // $ IntOption option value for general use
  // $ StrPostKey $ _POST keys to refresh
  //
  // Return value: 0 on success, 1 on failure
  // 
  ///////////////////////////////////////////////////////////////////////////////////////////
  function parseSelect($strTable,$strTabField,$strParseVar,$strParseGroup,&$objTemplate,$intDataId,$strLinkTable,$intSelMode=0,$intModeId=0,$intSkipId=0,$intOption=0,$strPostKey="") {
    // Version set
    $this->myConfigClass->getConfigData("version",$intVersion);
    // Data from the main table load
    if ($intSkipId != 0) {$strWhere = "AND `id` <> $intSkipId";} else {$strWhere = "";}
    // The command definitions differ, or check misc
    if (($strTable == "tbl_command") && (($intOption == 1) || ($intOption == 3))) {
      $strWhere = "AND (`command_type` = 0 OR `command_type` = 1)";
    }
    if (($strTable == "tbl_command") && (($intOption == 2) || ($intOption == 4))) {
      $strWhere = "AND (`command_type` = 0 OR `command_type` = 2)";
    }
    if (($intOption != 7) && ($intOption != 8) && ($intOption != 9) && ($intOption != 10)) {
      $strSQL  = "SELECT `id`, `".$strTabField."` FROM `".$strTable."` WHERE `active`='1' AND `config_id`=".$this->intDomainId." 
	              $strWhere AND `".$strTabField."` <> '' AND `".$strTabField."` IS NOT NULL ORDER BY `".$strTabField."`";
      $booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
    } else {
      if ($intOption == 7) {
        $arrHost    = $_SESSION['servicedependency']['arrHostDepend'];
        $arrHostgroup = $_SESSION['servicedependency']['arrHostgroupDepend'];
      } else if ($intOption == 8) {
        $arrHost    = $_SESSION['servicedependency']['arrHost'];
        $arrHostgroup = $_SESSION['servicedependency']['arrHostgroup'];
      } else if ($intOption == 9) {
        $arrHost    = $_SESSION['serviceescalation']['arrHost'];
        $arrHostgroup = $_SESSION['serviceescalation']['arrHostgroup'];
      } else if ($intOption == 10) {
        $arrHost[]    = $_SESSION['serviceextinfo']['arrHost'];
        $arrHostgroup = "";
      } else {
        return(1);
      }
      if ((is_array($arrHost) && in_array("*",$arrHost)) || (is_array($arrHostgroup) && in_array("*",$arrHostgroup))) {
        if (is_array($arrHost)) {
		  $strSQL  = "SELECT `id` FROM `tbl_host` WHERE `active`='1' AND `config_id`=".$this->intDomainId;
          $booReturn = $this->myDBClass->getDataArray($strSQL,$arrTemp,$intDCTemp);
          foreach($arrTemp AS $elem) {
            $arrTempHost[] = $elem['id'];
          }
          $strSQL  = "SELECT `id`, `".$strTabField."`, count(`idSlave`) AS `counter` FROM `".$strTable."`
                LEFT JOIN `tbl_lnkServiceToHost` ON `id` = `tbl_lnkServiceToHost`.`idMaster`
                WHERE `active`='1'
                  AND `config_id`=".$this->intDomainId."
                  AND `tbl_lnkServiceToHost`.`idSlave` IN (".implode(",",$arrTempHost).")
                  GROUP BY `".$strTabField."`
                  HAVING `counter` = $intDCTemp
                  ORDER BY `".$strTabField."`";
          $booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
        }
        if (is_array($arrHostgroup)) {
          $strSQL  = "SELECT `id` FROM `tbl_hostgroup` WHERE `active`='1' AND `config_id`=".$this->intDomainId;
          $booReturn = $this->myDBClass->getDataArray($strSQL,$arrTemp,$intDCTemp);
          foreach($arrTemp AS $elem) {
            $arrTempHostgroup[] = $elem['id'];
          }
          $strSQL  = "SELECT `id`, `".$strTabField."`, count(`idSlave`) AS `counter` FROM `".$strTable."`
                LEFT JOIN `tbl_lnkServiceToHostgroup` ON `id` = `tbl_lnkServiceToHostgroup`.`idMaster`
                WHERE `active`='1'
                  AND `config_id`=".$this->intDomainId."
                  AND `tbl_lnkServiceToHostgroup`.`idSlave` IN (".implode(",",$arrTempHostgroup).")
                  GROUP BY `".$strTabField."`
                  HAVING `counter` = $intDCTemp
                  ORDER BY `".$strTabField."`";
          $booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
        }
      } else {
		// Service Dependency/Escalation Selection according POST Parameter
        if ($intVersion != 3) {
		  if (is_array($arrHost)) {
            $intCounter1 = count($arrHost);
          } else {
            $intCounter1 = 0;
          }
          if ($intCounter1 != 0) {
            $strSQL  = "SELECT `id`, `".$strTabField."`, count(`idSlave`) AS `counter` FROM `".$strTable."`
                  LEFT JOIN `tbl_lnkServiceToHost` ON `id` = `tbl_lnkServiceToHost`.`idMaster`
                  WHERE `active`='1'
                    AND `config_id`=".$this->intDomainId."
                    AND `tbl_lnkServiceToHost`.`idSlave` IN (".implode(",",$arrHost).")
                    GROUP BY `".$strTabField."`
                    HAVING `counter` = $intCounter1
                    ORDER BY `".$strTabField."`";
            $booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
          } else {
            $booReturn = false;
          }
        } else {
          if (is_array($arrHostgroup)) {
            $intCounter1 = count($arrHostgroup);
          } else {
            $intCounter1 = 0;
          }
          if (is_array($arrHost)) {
            $intCounter2 = count($arrHost);
          } else {
            $intCounter2 = 0;
          }
          if ($intCounter1 != 0) {
            $strSQL = "SELECT DISTINCT `id` FROM `tbl_host`
                   LEFT JOIN `tbl_lnkHostToHostgroup` ON `id` = `tbl_lnkHostToHostgroup`.`idMaster`
                   LEFT JOIN `tbl_lnkHostgroupToHost` ON `id` = `tbl_lnkHostgroupToHost`.`idSlave`
                   WHERE (`tbl_lnkHostgroupToHost`.`idMaster` IN (".implode(",",$arrHostgroup).")
                    OR `tbl_lnkHostToHostgroup`.`idSlave` IN (".implode(",",$arrHostgroup)."))
                   AND `active`='1'
                   AND `config_id`=".$this->intDomainId;
			$booReturn = $this->myDBClass->getDataArray($strSQL,$arrDataHostgroups,$intDCHostgroups);
            $arrDataHg2 = "";
            if ($intDCHostgroups != 0) {
				foreach ($arrDataHostgroups AS $elem) {
				  $arrHostgroupList[] = $elem['id'];
				}
			 } else {
				$arrHostgroupList[] = 0;
			 }
            if ($intCounter2 != 0) {
			  $strSQL = "SELECT `id` FROM `tbl_host` WHERE `active`='1' AND `config_id`=".$this->intDomainId;
              $booReturn = $this->myDBClass->getDataArray($strSQL,$arrDataHost,$intDCHost);
              $arrHostIdList = "";
              foreach ($arrDataHost AS $elem) {
                if ((($arrHostIdList == "") || !in_array($elem['id'],$arrHostIdList)) &&
                  (in_array($elem['id'],$arrHostgroupList) || in_array($elem['id'],$arrHost))) {
                  $arrHostIdList[] = $elem['id'];
                }
              }
            } else {
			  $arrHostIdList = $arrHostgroupList;
            }
			$intCounter = count($arrHostIdList);
            $strSQL  = "SELECT `id`, `".$strTabField."`, count(`idSlave`) AS `counter` FROM `".$strTable."`
                  LEFT JOIN `tbl_lnkServiceToHost` ON `id` = `tbl_lnkServiceToHost`.`idMaster`
                  WHERE `active`='1'
                    AND `config_id`=".$this->intDomainId."
                    AND `tbl_lnkServiceToHost`.`idSlave` IN (".implode(",",$arrHostIdList).")
                    GROUP BY `".$strTabField."`
					HAVING `counter` = $intCounter
				  UNION
				  SELECT `id`, `".$strTabField."`, count(`idSlave`) AS `counter` FROM `".$strTable."`
                    LEFT JOIN `tbl_lnkServiceToHostgroup` ON `id` = `tbl_lnkServiceToHostgroup`.`idMaster`
                    WHERE `active`='1'
                    AND `config_id`=".$this->intDomainId."
                    AND `tbl_lnkServiceToHostgroup`.`idSlave` IN (".implode(",",$arrHostgroup).")
					GROUP BY `".$strTabField."`
                    HAVING `counter` = $intCounter
			      UNION 
			      SELECT `id`, `".$strTabField."`, $intCounter FROM `".$strTable."`
                    WHERE `active`='1'
                    AND `config_id`=".$this->intDomainId."
					AND `".$strTable."`.`hostgroup_name` = 2
			      UNION SELECT `id`, `".$strTabField."`, $intCounter FROM `".$strTable."`
                  WHERE `active`='1'
                    AND `config_id`=".$this->intDomainId."
					AND `".$strTable."`.`host_name` = 2
					GROUP BY 2
                    ORDER BY 2";
			$booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
//            $strSQL  = "SELECT `id`, `".$strTabField."`, count(`idSlave`) AS `counter` FROM `".$strTable."`
//                  LEFT JOIN `tbl_lnkServiceToHostgroup` ON `id` = `tbl_lnkServiceToHostgroup`.`idMaster`
//                  WHERE `active`='1'
//                    AND `config_id`=".$this->intDomainId."
//                    AND `tbl_lnkServiceToHostgroup`.`idSlave` IN (".implode(",",$arrHostgroup).")
//                    GROUP BY `".$strTabField."`
//                    HAVING `counter` = $intCounter
//                    ORDER BY `".$strTabField."`";
//			$booReturn = $this->myDBClass->getDataArray($strSQL,$arrData2,$intDataCount);
//			$arrData = array_merge($arrData,$arrData2);			
          } else if ($intCounter2 != 0) {
            $strSQL  = "SELECT `id`, `".$strTabField."`, count(`idSlave`) AS `counter` FROM `".$strTable."`
                  LEFT JOIN `tbl_lnkServiceToHost` ON `id` = `tbl_lnkServiceToHost`.`idMaster`
                  WHERE `active`='1'
                    AND `config_id`=".$this->intDomainId."
                    AND `tbl_lnkServiceToHost`.`idSlave` IN (".implode(",",$arrHost).")
					GROUP BY `".$strTabField."`
                    HAVING `counter` = $intCounter2
			      UNION SELECT `id`, `".$strTabField."`, $intCounter2 FROM `".$strTable."`
                  WHERE `active`='1'
                    AND `config_id`=".$this->intDomainId."
					AND `".$strTable."`.`host_name` = 2
					GROUP BY `".$strTabField."`
					ORDER BY 2";
			$booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
          } else if ($intOption == 10) {
            $strSQL  = "SELECT `id`, `".$strTabField."` FROM `".$strTable."`
                  LEFT JOIN `tbl_lnkServiceToHost` ON `id` = `tbl_lnkServiceToHost`.`idMaster`
                  WHERE `active`='1'
                    AND `config_id`=".$this->intDomainId."
                    AND `tbl_lnkServiceToHost`.`idSlave` IN ($arrHost)
                    ORDER BY `".$strTabField."`";
			$booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
          } else {
            $booReturn = false;
          }
        }
      }
    }
    if (($booReturn == false) || ($intDataCount == 0)) {
      // HTML validity - an option to write
      if ($intVersion != 3) $objTemplate->setVariable("CLASS_20_MUST_ONLY","class=\"inpmust\"");
      $objTemplate->setVariable($strParseVar,"&nbsp;");
      $objTemplate->setVariable($strParseVar."_ID",0);
      $objTemplate->parse($strParseGroup);
      return(1);
    }
    if (($intSelMode == 1) && ($strLinkTable != "")) {
      // Select selections
      if ($intOption != 6){
        if (($strPostKey == "") || (!isset($_POST[$strPostKey]))) {
          $strSQL   = "SELECT `idSlave` FROM `".$strLinkTable."` WHERE `idMaster`=$intDataId";
          $booReturn  = $this->myDBClass->getDataArray($strSQL,$arrDataSelected,$intDCSelected);
          if ($intDCSelected != 0) {
            foreach($arrDataSelected AS $elem) {
              $arrSelect[] = $elem['idSlave'];
            }
          }
        } else {
          $arrSelect    = $_POST[$strPostKey];
          $intDCSelected  = count($_POST[$strPostKey]);
        }
      } else {
        $strSQL   = "SELECT `idSlaveH`, `idSlaveHG`, `idSlaveS` FROM `".$strLinkTable."` WHERE `idMaster`=$intDataId";
        $booReturn  = $this->myDBClass->getDataArray($strSQL,$arrDataSelected,$intDCSelected);
        if ($intDCSelected != 0) {
          foreach($arrDataSelected AS $elem) {
            $arrSelect[] = $elem['idSlaveH']."::".$elem['idSlaveHG']."::".$elem['idSlaveS'];
          }
        }
      }
    }
    // In mode 1 and 2 blank line insert
    if (($intModeId == 1) || ($intModeId == 2)) {
      $objTemplate->setVariable($strParseVar,"&nbsp;");
      $objTemplate->setVariable($strParseVar."_ID",0);
      if ($intVersion != 3) $objTemplate->setVariable("CLASS_20_MUST_ONLY","class=\"inpmust\"");
      $objTemplate->parse($strParseGroup);
    }
    // In mode 2 and 3 a "*" insert
    if (($intModeId == 2) || ($intModeId == 3)) {
      $objTemplate->setVariable($strParseVar,"*");
      $objTemplate->setVariable($strParseVar."_ID","*");
      if ($intVersion != 3) $objTemplate->setVariable("CLASS_20_MUST_ONLY","class=\"inpmust\"");
      if ($intSelMode == 2) {
        $objTemplate->setVariable($strParseVar."_SEL","selected");
      }
      if (($strPostKey != "") && (isset($_POST[$strPostKey])) && in_array("*",$arrSelect)) {
        $objTemplate->setVariable($strParseVar."_SEL","selected");
      }
      $objTemplate->parse($strParseGroup);
    }
    // Register For special option "null"
    if (($intOption == 3) || ($intOption == 4) || ($intOption == 5)) {
      $objTemplate->setVariable($strParseVar,"null");
      $objTemplate->setVariable($strParseVar."_ID",-1);
      if ($intVersion != 3) $objTemplate->setVariable("CLASS_20_MUST_ONLY","class=\"inpmust\"");
      if ($intSelMode == -1) $objTemplate->setVariable($strParseVar."_SEL","selected");
      $objTemplate->parse($strParseGroup);
    }
    if ($intOption != 6) {
      // Enter records
      foreach ($arrData AS $elem) {
        $objTemplate->setVariable($strParseVar,$elem[$strTabField]);
        $objTemplate->setVariable($strParseVar."_ID",$elem['id']);
        if ($intVersion != 3) $objTemplate->setVariable("CLASS_20_MUST_ONLY","class=\"inpmust\"");
        if (($intSelMode == 1) && ($strLinkTable != "") && ($intDCSelected != 0) && in_array($elem['id'],$arrSelect)) {
          $objTemplate->setVariable($strParseVar."_SEL","selected");
        }
        if (($strLinkTable == "") && ($elem['id'] == $intSelMode) && !isset($_POST[$strPostKey])) {
          $objTemplate->setVariable($strParseVar."_SEL","selected");
        }
        if (($strLinkTable == "") && ($strPostKey != "") && isset($_POST[$strPostKey]) && ($elem['id'] == $_POST[$strPostKey])) {
          $objTemplate->setVariable($strParseVar."_SEL","selected");
        }
        $objTemplate->parse($strParseGroup);
      }
    } else {
      // Records Record (service groups)
      foreach ($arrData AS $elem) {
        // Hostname get
        $strSQL = "SELECT `idSlave`, `host_name` FROM `tbl_lnkServiceToHost` LEFT JOIN `tbl_host` ON `id` = `idSlave` WHERE `idMaster` = ".$elem['id']." ORDER BY `host_name`";
        $booReturn = $this->myDBClass->getDataArray($strSQL,$arrDataHost,$intDCHost);
        if ($intDCHost != "") {
          foreach ($arrDataHost AS $hostdata) {
            $arrTemp[] = array ( "name"  =>  "H:".$hostdata['host_name'].",".$elem[$strTabField],
                       "value" =>  $hostdata['idSlave']."::0::".$elem['id']);
          }
        }
        // Hostgroup get 
        $strSQL = "SELECT `idSlave`, `hostgroup_name` FROM `tbl_lnkServiceToHostgroup` LEFT JOIN `tbl_hostgroup` ON `id` = `idSlave` WHERE `idMaster` = ".$elem['id']." ORDER BY `hostgroup_name`";
        $booReturn = $this->myDBClass->getDataArray($strSQL,$arrDataHostgroup,$intDCHostgroup);
        if ($intDCHostgroup != "") {
          foreach ($arrDataHostgroup AS $hostgroupdata) {
            $arrTemp[] = array ( "name"  =>  "HG:".$hostgroupdata['hostgroup_name'].",".$elem[$strTabField],
                       "value" =>  "0::".$hostgroupdata['idSlave']."::".$elem['id']);
          }
        }
      }
      //var_dump($arrSelect);
	  if (isset($arrTemp) && is_array($arrTemp)) {
        asort($arrTemp);
        foreach ($arrTemp AS $elem) {
          $objTemplate->setVariable($strParseVar,$elem['name']);
          $objTemplate->setVariable($strParseVar."_ID",$elem['value']);
          if ($intVersion != 3) $objTemplate->setVariable("CLASS_20_MUST_ONLY","class=\"inpmust\"");
          if (($intSelMode == 1) && ($strLinkTable != "") && ($intDCSelected != 0) && in_array($elem['value'],$arrSelect)) {
            $objTemplate->setVariable($strParseVar."_SEL","selected");
          }
          $objTemplate->parse($strParseGroup);
        }
      }
    }
    return(0);
  }
}
?>
