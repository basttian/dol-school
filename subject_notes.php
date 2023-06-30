<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       subject_document.php
 *  \ingroup    college
 *  \brief      Tab for documents linked to Subject
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/college/class/subject.class.php');
dol_include_once('/college/lib/college_subject.lib.php');
dol_include_once('/college/class/periods.class.php');
dol_include_once('/college/class/classrooms.class.php');

// Load translation files required by the page
$langs->loadLangs(array("college@college", "companies", "other", "mails"));
$id = (GETPOST('socid', 'int') ? GETPOST('socid', 'int') : GETPOST('id', 'int'));

// Initialize technical objects
$object = new Subject($db);
$extrafields = new ExtraFields($db);

$objectClass = new Classrooms($db);
$periods = new Periods($db);
$periodslabel = $periods->getAllPeriods();

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals


$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->college->subject->read;
	$permissiontoadd = $user->rights->college->subject->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->college->subject->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->college->subject->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->college->subject->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}


/*
 * Actions
 */


/*
 * View
 */

$form = new Form($db);

$title = $langs->trans("Subject").' - '.$langs->trans("SubjectNotesTab");
$help_url = '';
//$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('
<link rel="stylesheet" href="./js/print-js/dist/print.css">
<script type="text/javascript" src="./js/print-js/dist/print.js"></script>
', $title, $help_url);
?>
	<script type="text/javascript">
		//Ocultar teclado en telefono
		jQuery(document).ready(function() {
			$('#select_trimestre').on('select2:open', function() {
				$('.select2-search__field').prop('readonly', true);
			});
		});
	</script>
<?php
if ($object->id) {

	$objectClass->fetch($object->fk_class);

	$profesor = new User($db);
	$profesor->fetch($object->fk_user);

	/*
	 * Show tabs
	 */
	$head = subjectPrepareHead($object);
	print dol_get_fiche_head($head, '4', $langs->trans("SubjectNotesTab"), -1, $object->picto);
	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/college/subject_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';
	$morehtmlref = '<div class="refidno">';
	$morehtmlref.= ''.$profesor->lastname.', '.$profesor->firstname.'<br>';
	$morehtmlref.= ''.$objectClass->label.', '.$object->school_year.'';
	$morehtmlref .= '</div>';
	dol_banner_tab($object, 'id', $linkback, $user->rights->college->readalllist->read, 'rowid', 'ref', $morehtmlref);


		/*BLOQUEAR ACCESO*/
		if($object->fk_user != $user->id || $user->admin ){ 
			if(!$user->rights->college->readalllist->read){
			print info_admin( 	  	
				$user->firstname.' '.$user->lastname.'. You have attempted to enter an area to which you do not have access, please do not attempt this action again.',
				$infoonimgalt = 0,
				$nodiv = 0,
				$admin = '0',
				$morecss = 'error',//More CSS ('', 'warning', 'error') 
				$textfordropdown = '403 Forbidden' 
			);
			accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
			exit;
			}
		}



	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	$periods = new Periods($db);
	$periodarr = $periods->getAllPeriods();
	print '<br>';
	print '<div class="fichehalfleft">';
	print '<div class="inline-block">';
	print '<div id="listoneselect">';
	print $form->selectarray(
		'select_trimestre',
		$periodarr,
		$id = '',
		$show_empty = $langs->trans("tblsubjectfilterTrimestre"),
		$key_in_label = 0,
		$value_as_key = 0,
		$moreparam = '',
		$translate = 0,
		$maxlen = 0,
		$disabled = 0,
		$sort = 'ASC',
		$morecss = 'flat maxwidth1000 minwidth500imp',
		$addjscombo = 1,
		$moreparamonempty = '',
		$disablebademail = 0,
		$nohtmlescape = 0
	  );
	print '<img class="valignmiddle" style="display: none; float: right;" id="loader" src="../../custom/college/img/spinner.gif" height="20px">';
	print '</div>';
	print '</div>';
	print '</div>';
	print '<div class="fichehalfright">';
	//Print table Div class: hideonsmartphoneimp
	if (empty($user->socid)) {
		print '<a class="butAction valignmiddle" style="float: right;" href="#" onclick="printDivPdf()" ><i style="color:#FFF;" class="fa fa-print" aria-hidden="true"></i></a>';
		print '<a class="butAction valignmiddle" style="float: right;" href="#" onclick="showone()" ><i class="fa fa-th-list" aria-hidden="true"></i></a>';
		print '<a class="butAction valignmiddle" style="float: right;" href="#" onclick="showtwo()" ><i class="fa fa-list" aria-hidden="true"></i></a>';

	}
	print '</div>';
	print '<br>';
	print '<br>';
	print '<br>';
	print '
	<div id="idprint">
	<div class="div-table-responsive">

	<div id="listone">
	<table id="tableDataPeriodsNotes" class="tagtable nobottomiftotal liste">
		<thead>
		<tr class="liste_titre">
			<th class="wrapcolumntitle liste_titre" align="left">'.$langs->trans("tblsubjectcardEstudiante").'</th>
			<th class="wrapcolumntitle liste_titre" align="left">'.$langs->trans("tblsubjectcardTrimestre").'</th>
			<th class="wrapcolumntitle liste_titre" align="left">'.$langs->trans("tblsubjectcardNota").'</th>
			<th class="wrapcolumntitle liste_titre" align="left">'.$langs->trans("tblsubjectcardNotaRecover").'</th>
		</tr>
		</thead>
		<tbody>
		';
		if(!empty($object->searchNotesFromSubject($object->id))){
		foreach ($object->searchNotesFromSubject($object->id) as $key => $value) {
			print'<tr class="pair">';
			print'<td>';
			print $value['label'];
			print'</td>';
			print'<td>';
			print $value['trimestre'].'º';
			print'</td>';
			print'<td>';
			print number_format($value['nota'],2);
			print'</td>';
			print'<td>';
			print $value['notarecover']==0?"-": number_format($value['notarecover'],2);
			print'</td>';
			print'</tr>';
		}}else{
			print'<tr>';
			print'<td colspan="4">';
			print $langs->trans("nodatafound");
			print'</td>';
			print'</tr>';
		}
		print'
		</tbody>
		<tfoot>
		<tr><td colspan="4" class="liste_titre">
		</td></tr>
		</tfoot>
	</table>
	</div>

	<div id="listtwo">
		<table id="" class="tagtable nobottomiftotal liste">
		<thead>
		<tr class="liste_titre">
				<th class="wrapcolumntitle liste_titre" align="left">'.strtoupper($langs->trans("tblsubjectcardEstudiante")).'</th>';
		foreach ($periodslabel as $value) { 
			print '<th class="wrapcolumntitle liste_titre">'.$value['label'].'</th>';
		}
		print 
		'<th class="wrapcolumntitle liste_titre">'.$langs->trans("pdfheadprom1").'</th>
		<th class="wrapcolumntitle liste_titre">'.$langs->trans("pdfheadprom2").'</th>
		<th class="wrapcolumntitle liste_titre">'.$langs->trans("pdfheadprom3").'</th>
		</tr>
		</thead>
		<tbody>
		';
		if(!empty($object->listNotesFromSubject($object->id))){
		foreach ($object->listNotesFromSubject($object->id) as $key => $value) {
			print'<tr class="pair">';
			print'<td>';
			print $value['label'];
			print'</td>';
			foreach ($periodslabel as $k => $val) { 
				print'<td align="center">';
				print $value['n'][$k];
				print'</td>';
			}
			print'<td bgcolor="#e9eaed" align="center">';
			print $value['promg'];
			print'</td>';
			print'<td align="center">';
			print $value['promr']>0?$value['promr']:'-';
			print'</td>';
			print'<td bgcolor="#e9eaed" align="center">';
			print $value['promf'];
			print'</td>';
			print'</tr>';
		}}else{
			print'<tr>';
			print'<td colspan="10">';
			print $langs->trans("nodatafound");
			print'</td>';
			print'</tr>';
		}
		print'
		</tbody>
		<tfoot>
		<tr><td colspan="10" class="liste_titre">
		</td></tr>
		</tfoot>
		</table>
	</div>

	</div>
	<script type="text/javascript">

	function printDivPdf() {
		printJS({ 
			printable: "idprint", 
			type: "html", 
			header: "<h3>'.$object->label.'</h3><cite>'.$profesor->lastname.', '.$profesor->firstname.'</cite>",
			documentTitle:"'.$object->label.' - '.$profesor->lastname.', '.$profesor->firstname.'",
			style:"table, th, td {border-bottom: 1px solid #ddd;} tr:nth-child(even) {background-color: #f2f2f2;}",
			repeatTableHeader:false
		})
	}

	//PERIODOS
	var periodos = 0;
	$("#select_trimestre").on("change", function(e) {
		e.preventDefault();
		$("#loader").show();
		periodos = $("#select_trimestre :selected").val();
		//console.log(periodos)
		$.getJSON( "./ajax.php?action=filternotesfromsubject",
		{ idSubjejct:"'.$object->id.'", idPeriods:periodos ,token:"'.newToken().'" }, function( jsonresp ) {
			$("#tableDataPeriodsNotes>tbody").find("tr").remove();
			//console.log(jsonresp)
		}).done(function(jsonresp){
			//console.log(jsonresp.length)
			if( jsonresp.length > 0 ){
				Object.keys(jsonresp).forEach(function(key,index,arr) {  
					$("#tableDataPeriodsNotes>tbody").append("<tr class=`pair`><td>"+jsonresp[key].label+"</td><td>"+jsonresp[key].trimestre+"º"+"</td><td>"+Number(jsonresp[key].nota).toFixed(2)+"</td><td>"+jsonresp[key].notarecover+"</td></tr>")
				});
			}else{
				$("#tableDataPeriodsNotes>tbody").append("<tr><td colspan='."4".'>'.$langs->trans("nodatafound").'</td></tr>");
			};
			$("#loader").hide();
		}).fail(function(e){
			$("#loader").hide();
			console.log(e);
		});
	});

	$("#listtwo").hide();
	$("#listone").show();

	function showone(){
		$("#listoneselect").hide();
		$("#listtwo").show();
		$("#listone").hide();
	}
	function showtwo(){
		$("#listoneselect").show();
		$("#listtwo").hide();
		$("#listone").show();
	}
	
	</script>
	';
	print '</div>';

	print '</div>';
	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();