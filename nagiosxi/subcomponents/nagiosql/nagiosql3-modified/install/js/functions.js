///////////////////////////////////////////////////////////////////////////////
//
// NagiosQL
//
///////////////////////////////////////////////////////////////////////////////
//
// (c) 2008, 2009 by Martin Willisegger
//
// Project  : NagiosQL
// Component: Installer Javascript Functions
// Website  : http://www.nagiosql.org
// Date     : $LastChangedDate: 2009-05-14 10:49:01 +0200 (Do, 14. Mai 2009) $
// Author   : $LastChangedBy: rouven $
// Version  : 3.0.3
// Revision : $LastChangedRevision: 715 $
// SVN-ID   : $Id: functions.js 715 2009-05-14 08:49:01Z rouven $
//
///////////////////////////////////////////////////////////////////////////////

// Hide/Show +/- content elements
function Klappen(Id) {
	var KlappText = document.getElementById('SwTxt'+Id);
	var KlappBild = document.getElementById('SwPic'+Id);
	var KlappMinus="images/minus.png", KlappPlus="images/plus.png";
	if (KlappText.style.display == 'none') {
		KlappText.style.display = 'block';
		KlappBild.src = KlappMinus;
	} else {
		KlappText.style.display = 'none';
		KlappBild.src = KlappPlus;
	}
}
