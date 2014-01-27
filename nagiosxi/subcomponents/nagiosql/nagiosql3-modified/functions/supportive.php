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
// Component : Supportive functions
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-02-02 11:24:55 +0100 (Mo, 02 Feb 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 654 $
// SVN-ID    : $Id: supportive.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////

// Replacement for builtin parse_ini_file
function parseIniFile($iIniFile) {
  $aResult  =
  $aMatches = array();
  $a = &$aResult;
  $s = '\s*([[:alnum:]_\- \*]+?)\s*'; preg_match_all('#^\s*((\['.$s.'\])|(("?)'.$s.'\\5\s*=\s*("?)(.*?)\\7))\s*(;[^\n]*?)?$#ms', @file_get_contents($iIniFile), $aMatches, PREG_SET_ORDER);
  foreach ($aMatches as $aMatch) {
    if (empty($aMatch[2]))
        $a [$aMatch[6]] = $aMatch[8];
      else  $a = &$aResult [$aMatch[3]];
  }
  return $aResult;
}
?>