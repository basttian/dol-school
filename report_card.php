<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 SuperAdmin
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
 *   	\file       report_card.php
 *		\ingroup    college
 *		\brief      Page to create/edit/view report
 */

use Sabre\VObject\Property\ICalendar\Period;

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("MAIN_SECURITY_FORCECSP"))   define('MAIN_SECURITY_FORCECSP', 'none');	// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/college/class/report.class.php');
dol_include_once('/college/lib/college_report.lib.php');
dol_include_once('/college/class/questions.class.php');
dol_include_once('/college/class/subject.class.php');
dol_include_once('/college/class/periods.class.php');

// Load translation files required by the page
$langs->loadLangs(array("college@college", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');


// Initialize technical objects
$object = new Report($db);
$extrafields = new ExtraFields($db);

$objSubject = new Subject($db);
$objPeriods = new Periods($db);


$diroutputmassaction = $conf->college->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('reportcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->hasRight('college', 'report', 'read');
	$permissiontoadd = $user->hasRight('college', 'report', 'write'); // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->hasRight('college', 'report', 'delete') || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->hasRight('college', 'report', 'write'); // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->hasRight('college', 'report', 'write'); // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->college->multidir_output[isset($object->entity) ? $object->entity : 1].'/report';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->module, $object->id, $object->table_element, $object->element, 'fk_soc', 'rowid', $isdraft);
if (!isModEnabled("college")) {
	accessforbidden();
}
if (!$permissiontoread) {
	accessforbidden();
}


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/college/report_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/college/report_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'COLLEGE_REPORT_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'COLLEGE_REPORT_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_REPORT_TO';
	$trackid = 'report'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Report");
$help_url = '';
llxHeader('', $title, $help_url);

?>
<script type="text/javascript">
jQuery(document).ready(function() {

	//ancla

	$("#btngetresponses").on("click",function(e){
		e.preventDefault();
		$('#loader').show();
		$(this).addClass('butActionRefused');
		const groupedObjects = $( "#formresponses" ).serializeArray().reduce((grouped, obj) => {
		
			if (!grouped[obj.name]) {
				grouped[obj.name] = [];
			}
			grouped[obj.name].push(obj.value);
			return grouped;
			}, {});
		const array = Object.entries(groupedObjects).map(([name, values]) => ({
				name,values
			}));
		
		$.post("./ajax.php?action=updateresponse&token=<?php echo newToken() ;?>" ,{
			    contentType: 'application/json',
				'idResponse': "<?php echo $object->id; ?>", 
				'dataResponse': (array)
			})
			.done(function(resp) {
				console.log('sent correctly.')
				console.log(resp);
			})
			.fail(function() {
				$('#btngetresponses').removeClass('butActionRefused');
				$('#loader').hide();
			})
			.always(function() {
				$('#btngetresponses').removeClass('butActionRefused');
				$('#loader').hide();
			});
		
		
	
	});	


	/**
   	* Ocultar el teclado del telefono cuando se hace click en el selec
	*/
	$('#fk_class').on('select2:open', function() {
		$('.select2-search__field').prop('focus', true);
	});
	$('#fk_subject').on('select2:open', function() {
		$('.select2-search__field').prop('focus', true);
	});
	/*$('#fk_student').on('select2:open', function() {
		$('.select2-search__field').prop('readonly', true);
	});*/
	$('#trimestre').on('select2:open', function() {
		$('.select2-search__field').prop('readonly', true);
	});
// 	function init_myfunc()
// 	{
// 		jQuery("#myid").removeAttr(\'disabled\');
// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
// 	}
// 	init_myfunc();
// 	jQuery("#mybutton").click(function() {
// 		init_myfunc();
// 	});
});
</script>'
<?php

// Part to create 
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden('NotEnoughPermissions', 0, 1);
	}

	print load_fiche_titre($langs->transnoentitiesnoconv("Reportnew"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');

	$arrsubjects = $objSubject->fetchAll('ASC','',0,0,array(),'');
	$arrperiods = $objPeriods->fetchAll('ASC','',0,0,array(),'');

	$arrselect_subject = array();
	$arrpselect_periods = array();
	foreach ($arrsubjects as $clave => $valor){
		$arrselect_subject[$clave] = $valor->label;
	}
	foreach ($arrperiods as $clave => $valor){
		$arrpselect_periods[$clave] = $valor->label;
	}

	// Set some default values
	//if ( !GETPOSTISSET('fk_subject')) $_POST['fk_subject'] = '';
	if ( !GETPOSTISSET('trimestre')) $_POST['trimestre'] = '';

	print '<div class="fichehalfleft">';
	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print '</div>';
	print '<div class="fichehalfright">';
	print '<table bgcolor="#f4f7f9" class="border centpercent tableforfieldcreate">'."\n";
	//print '<tr>';
	//print '<td>';
	//print $form->textwithpicto('Asignatura','tooltipcreatesubject',1,'info','',0,3,'',2);
	//print '</td>';
	//print '<td>';
	/*print $form->selectarray(
		'fk_subject',
		$arrselect_subject,
		$id = '',
		$show_empty = 1,
		$key_in_label = 0,
		$value_as_key = 0,
		$moreparam = '',
		$translate = 0,
		$maxlen = 0,
		$disabled = 0,
		$sort = 'ASC',
		$morecss = 'flat maxwidth350 minwidth300imp',
		$addjscombo = 1,
		$moreparamonempty = '',
		$disablebademail = 0,
		$nohtmlescape = 0
	  );*/
	//print '</td>';
	//print '</tr>';
	print '<tr>';
	print '<td>';
	print $form->textwithpicto($langs->trans('createtrimestre'),$langs->trans('tooltipcreatetrimestre'),1,'warning','',0,3,'',2);
	print '</td>';
	print '<td>';
	print $form->selectarray(
		'trimestre',
		$arrpselect_periods,
		$id = '',
		$show_empty = 1,
		$key_in_label = 0,
		$value_as_key = 0,
		$moreparam = '',
		$translate = 0,
		$maxlen = 0,
		$disabled = 0,
		$sort = 'ASC',
		$morecss = 'flat maxwidth350 minwidth300imp',
		$addjscombo = 1,
		$moreparamonempty = '',
		$disablebademail = 0,
		$nohtmlescape = 0
	  );
	print '</td>';
	print '</tr>';
	print '</table>'."\n";
	print '</div>';
	print '<div class="clearboth"></div>';


	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("CreateNewReport");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {

	print load_fiche_titre($langs->trans("Report"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}
	

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';

}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {

	/*ACCESO NO PERMITIDO SI NO CONCUERDA LA NOTA INGRESADA CON EL USUARIO ACTUAL*/
	if($object->fk_user_creat != $user->id || $user->admin){
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



	$head = reportPrepareHead($object);

	print dol_get_fiche_head($head, 'card', $langs->trans("Report"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteReport'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	if ($action == 'xxx') {
		$text = $langs->trans('ConfirmActionReport', $object->ref);
		/*if (isModEnabled('notification'))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('REPORT_CLOSE', $object->socid, $object);
		}*/

		$formquestion = array();

		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/college/report_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
		// Ref customer
		$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string', '', 0, 1);
		$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string'.(isset($conf->global->THIRDPARTY_REF_INPUT_SIZE) ? ':'.$conf->global->THIRDPARTY_REF_INPUT_SIZE : ''), '', null, null, '', 1);
		// Thirdparty
		$morehtmlref .= '<br>'.$object->thirdparty->getNomUrl(1, 'customer');
		if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $object->thirdparty->id > 0) {
			$morehtmlref .= ' (<a href="'.DOL_URL_ROOT.'/commande/list.php?socid='.$object->thirdparty->id.'&search_societe='.urlencode($object->thirdparty->name).'">'.$langs->trans("OtherOrders").'</a>)';
		}
		// Project
		if (isModEnabled('project')) {
			$langs->load("projects");
			$morehtmlref .= '<br>';
			if ($permissiontoadd) {
				$morehtmlref .= img_picto($langs->trans("Project"), 'project', 'class="pictofixedwidth"');
				if ($action != 'classify') {
					$morehtmlref .= '<a class="editfielda" href="'.$_SERVER['PHP_SELF'].'?action=classify&token='.newToken().'&id='.$object->id.'">'.img_edit($langs->transnoentitiesnoconv('SetProject')).'</a> ';
				}
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, $object->fk_project, ($action == 'classify' ? 'projectid' : 'none'), 0, 0, 0, 1, '', 'maxwidth300');
			} else {
				if (!empty($object->fk_project)) {
					$proj = new Project($db);
					$proj->fetch($object->fk_project);
					$morehtmlref .= $proj->getNomUrl(1);
					if ($proj->title) {
						$morehtmlref .= '<span class="opacitymedium"> - '.dol_escape_htmltag($proj->title).'</span>';
					}
				}
			}
		}
	*/
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				if (empty($reshook))
					$object->formAddObjectLine(1, $mysoc, $soc);
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}

	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Send
			if (empty($user->socid)) {
				print dolGetButtonAction('', $langs->trans('SendMail'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&token='.newToken().'&mode=init#formmailbeforetitle');
			}

			// Back to draft
			if ($object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction('', $langs->trans('SetToDraft'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Validate
			if ($object->status == $object::STATUS_DRAFT) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction('', $langs->trans('Validate'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
				}
			}

			// Clone
			if ($permissiontoadd) {
				print dolGetButtonAction('', $langs->trans('ToClone'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.(!empty($object->socid)?'&socid='.$object->socid:'').'&action=clone&token='.newToken(), '', $permissiontoadd);
			}

			/*
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ENABLED) {
					print dolGetButtonAction('', $langs->trans('Disable'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction('', $langs->trans('Enable'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
				}
			}
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_VALIDATED) {
					print dolGetButtonAction('', $langs->trans('Cancel'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction('', $langs->trans('Re-Open'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
				}
			}
			*/

			// Delete
			$params = array();
			print dolGetButtonAction('', $langs->trans("Delete"), 'delete', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken(), 'delete', $permissiontodelete, $params);
		}

		print '</div>'."\n";
	}

	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	//ancla
	if ($action != 'presend') {

		print '<div class="fichecenter">';

		$questions = new Questions($db);
		$questionslist = $questions->fetchAll('ASC','',0,0,array('parent_id' => 0, 'status' => 1,'survey_id'=>(int)$object->fk_surveytype ),'AND' );

		$numberofquestions = count($questionslist);
		$abecedario = range('A', 'Z');
		$arrabecedario = array();
		foreach ($abecedario as $letra) {
			array_push($arrabecedario, $letra);
		};

		if (!empty($object->data)) {
			$arrayObjetos = json_decode($object->data);
			if ($arrayObjetos === null && json_last_error() !== JSON_ERROR_NONE) {
				echo "Error al decodificar la cadena JSON";
			} else {
				$arrayObjetos;
			}
			$preguntas = array_column($arrayObjetos, "name");
			$respuestas = array_column($arrayObjetos, "values");
			/**JQUERY */
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					var questions = <?php echo json_encode($preguntas) ?>;
					var responses = <?php echo json_encode($respuestas) ?>;
					var elementos = $('#formresponses').find(':input, select, textarea');

					for (let i = 0; i < questions.length; i++) {
						const elementname = questions[i];
							elementos.each(function(indice) {
							if ($(this).is(':radio')) {
								$("input[name="+elementname+"][value='"+responses[i]+"']").prop("checked",true);
							}else if ($(this).is(':checkbox')) {
								responses.map((x,index)=> {
									$("input[name="+elementname+"][value='"+responses[i][index]+"']").prop("checked",true);	
								});
							} else if ($(this).is('input[type=text]')) {
								$("input[name="+elementname+"]").val(responses[i]);
							} else if ($(this).is('input[type=number]')) {
								$("input[name="+elementname+"]").val(responses[i]);
							} else if ($(this).is('select')){
								$("select[name="+elementname+"]").val(responses[i]).change();
							} else if ($(this).is('textarea')){
								$("[name="+elementname+"]").val(responses[i]);
							}else {
								console.log('Es otro tipo de elemento.');
							}
							//console.log(elementname)
						});
						//Tomamos el ultimo elemento del array response y lo enviamos al input
						$("#op"+questions[i]+"").val(responses[i].slice(-1));
					}
					
				});	
				</script>
			<?php
		}

		print '<form id="formresponses">';
		//print '<input type="hidden" name="idreport" value="'.$object->id.'">';
		//print '<input type="hidden" name="class" value="'.$object->fk_class.'">';
		//print '<input type="hidden" name="subject" value="'.$object->fk_subject.'">';
		//print '<input type="hidden" name="periods" value="'.$object->trimestre.'">';
		//table
		print '<div class="div-table-responsive">';
		print '<table class="tagtable nobottomiftotal liste">';
		print '<tr class="liste_titre">';
		print '<td colspan="2" class="liste_titre">';
		print $langs->trans("tbltitleheadtha").' ( '.$numberofquestions.' )';
		print '</td>';
		print '<td class="liste_titre">';
		print $form->textwithpicto($langs->trans("tbltitleheadthb"),$langs->trans("tbltitleheadthbtooltip"),1,'info','',0,3,'',2);
		print '</td>';
		print '</tr>';
		print '<tbody>';
		$i=0;
		foreach ($questionslist as $clave => $question){
			//$clave = rowid Questions
			print '<tr class="pair">';
			print '<th><span class="questionindex">'.$arrabecedario[$i++].'</span></th>';
			print '<td>';
			print '<span style="color:#0A1464;">'.$question->label.'</span>';
			$optionsList = $questions->fetchAll('ASC','',0,0,array('parent_id' => $clave ) );
			print '</td>';
			print '<td>';
			
			foreach ($optionsList as $key => $option){
				switch ($question->type) {
					case 0:
						print $option->label.' '.$object->generateInput('radio', $clave, $key, $attributes = array());
						print '<hr>';
						break;
					case 1:
						print $option->label.' '.$object->generateInput('checkbox', $clave, $key, $attributes = array());
						print '<hr>';
						break;
					case 2:
						$options[$clave][$key] = $option->label;
						break;
					case 5:
						print $option->label.' '.$object->generateInput('radio', $clave, $key, $attributes = array());
						print '<hr>';
						break;
					case 6:
						print $option->label.' '.$object->generateInput('checkbox', $clave, $key, $attributes = array());
						print '<hr>';
						break;
				}
			}

			

			if($question->type == 2){ //'Select'
				print $object->generateSelect($clave, $options[$clave], $attributes = array());
				print '<hr>';
			}

			if($question->type == 3){ //'input text text'
				print $object->generateInput('text', $clave, $value = '', $attributes = array('placeholder'=>''));
				print '<hr>';
			}
		
			if($question->type == 4){ //'textarea'
				print $object->generateTextArea('textarea', $clave, $value = '', $attributes = array());
				print '<hr>';
			}

			if($question->type == 5){ //'Radio + Text'
				print $object->generateInput('text', $clave, $value = '', $attributes = array('id'=>'op'.$clave));
				print '<hr>';
			}
			if($question->type == 6){ //'Checkbox + Text'
				print $object->generateInput('text', $clave, $value = '', $attributes = array('id'=>'op'.$clave));
				print '<hr>';
			}
			
			print '</td>';
			print '</tr>';
		}

		print '</tbody>';
		print '</table>';
		print '</div>';

		print '</form>';
		print '</div>';
		/** añadir otra nota desde edit page  
		 * $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=sendResponses&token='.newToken()
		*/
		print '<div class="underbanner clearboth"></div>';
		print '<br>';
		print '<div class="pull-left">';
		print '<div class="contenedorbtntexto">';
		print dolGetButtonAction('<span class="fa fa-plus-circle btnTitle-icon"></span>&nbsp;'.$langs->trans('addotherreportbtn'), '', 'default', '', 'btngetresponses', $permissiontoadd,array('class' => 'valignmiddle'));
		print '<img style="display: none;" id="loader" class="" src="../../custom/college/img/spinner.gif" height="20px">';
		print '</div>';
		print '</div>';
		

		print '<div class="clearboth"></div>';

		print '<div class="fichecenter">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 1;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->college->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('college:Report', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, 0, $object->model_pdf, 1, 0, 0, 28, 0, '', ' ', '', $langs->defaultlang,'',null,0,'','');
		}


		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('report'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

		$MAXEVENT = 10;

		$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-bars imgforviewmode', dol_buildpath('/college/report_agenda.php', 1).'?id='.$object->id);

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);
		print '</div>';
		print dol_get_fiche_end(0);
	}


	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'report';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->college->dir_output;
	$trackid = 'report'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
