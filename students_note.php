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
 *  \file       students_note.php
 *  \ingroup    college
 *  \brief      Tab for notes on Students
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

dol_include_once('/college/class/students.class.php');
dol_include_once('/college/lib/college_students.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("college@college", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object = new Students($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->college->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('studentsnote', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) {
	$upload_dir = $conf->college->multidir_output[!empty($object->entity) ? $object->entity : $conf->entity]."/".$object->id;
}


// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->college->students->read;
	$permissiontoadd = $user->rights->college->students->write;
	$permissionnote = $user->rights->college->students->write; // Used by the include of actions_setnotes.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
	$permissionnote = 1;
}

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->college->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}
if (empty($reshook)) {
	include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php'; // Must be include, not include_once
}


/*
 * View
 */

$form = new Form($db);

//$help_url='EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes';
$help_url = '';
llxHeader('', $langs->trans('Students').' | '.$object->label , $help_url);

if ($id > 0 || !empty($ref)) {
	$object->fetch_thirdparty();

	$head = studentsPrepareHead($object);

	print dol_get_fiche_head($head, 'note', $langs->trans("Students"), -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/college/students_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	
	 // Ref customer
	 //$morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 //$morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 /*if (! empty($conf->projet->enabled))
	 {
	 $langs->load("projects");
	 $morehtmlref.='<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd)
	 {
	 if ($action != 'classify')
	 //$morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
	 $morehtmlref.=' : ';
	 if ($action == 'classify') {
	 //$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref.='<input type="hidden" name="action" value="classin">';
	 $morehtmlref.='<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref.='</form>';
	 } else {
	 $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	 }
	 } else {
	 if (! empty($object->fk_project)) {
	 $proj = new Project($db);
	 $proj->fetch($object->fk_project);
	 $morehtmlref .= ': '.$proj->getNomUrl();
	 } else {
	 $morehtmlref .= '';
	 }
	 }
	 }*/
	 $morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';


	$cssclass = "titlefield";
	include DOL_DOCUMENT_ROOT.'/core/tpl/notes.tpl.php';

	print '</div>';
  ?>
  <script type="text/javascript">
  jQuery(document).ready(function() {
    var txtyear;
    var msj = '<?php echo $langs->trans("nodatafound") ;?>';
    $("#tableDataNotess>tbody").append("<tr class='pair'><td colspan='3'>"+msj+"</td></tr>");
    
    $('#loader').hide();
    $('#txtsearch').on('keyup',function(){
      var idarrays=[];
      if ($(this).val().length > 3) {
        txtyear = $(this).val();
        $('#loader').show();
        $.getJSON( "./ajax.php?action=getnotesforstudent",{
          yeartab:$(this).val(),
          studenttab:<?php echo $object->id ;?>,
          token:"<?php echo newToken() ;?>"}, function() {
          $("#tableDataNotess>tbody").find("tr").remove();
        }).done(function(datanotestudent){
          //console.log(datanotestudent);
          if (datanotestudent != 0) {
            Object.keys(datanotestudent).forEach(function(key,index,arr) {
              $("#tableDataNotess>tbody").append(
              "<tr class='pair'><td>"+datanotestudent[key].asignatura+
              "</td><td>"+datanotestudent[key].promedio+
              "</td><td>"+((datanotestudent[key].noterecovery==0)?'-':datanotestudent[key].noterecovery)+
              "</td></tr><tr><td colspan='3'><table id='tablerecord'><thead><tr></tr></thead><tbody><tr id="+key+"></tr></tbody></table></td></tr>");
            });
            $.getJSON( "./ajax.php?action=getperiods",{ token:"<?php echo newToken() ;?>" }, function(dataperiods) {
            Object.keys(dataperiods).forEach(function(kk,index,arr) {
          	$("#tableDataNotess>tbody>tr").find("table>thead>tr:eq(0)").append("<th>"+dataperiods[kk].label+"</th><th></th>");
            });}).done(function(dataperiods){ 
          	Object.keys(datanotestudent).forEach(function(key,index,arr) {
          	  Object.keys(dataperiods).forEach(function(k,i,a){
          	  $("#tableDataNotess>tbody>tr>").find("table>tbody>tr#"+key+" ").append(
          	  "<td>"+datanotestudent[key].data[dataperiods[k].rowid]+"</td></td><td>");
          	  });
          	});
            $('#loader').hide();
            });
          }else{
            $("#tableDataNotess>tbody").append("<tr class='pair'><td colspan='3'>"+msj+"</td></tr>");
            $('#loader').hide();
          }
        }) 
      }else{
        if($(this).val().length < 4 ){
          $("#tableDataNotess>tbody").find("tr").remove();
        }
        $("#tableDataNotess>tbody").append("<tr class='pair'><td colspan='3'>"+msj+"</td></tr>");
        $('#loader').hide();
      }  
    })
  
  });
  </script>
  <div class="centpercent">
  <div style="vertical-align: middle">
  
  <div class="pagination paginationref" style="padding-top: 2em;">
  <div class="right">
    <img style="display: none;" class="valignmiddle" id="loader" src="../../custom/college/img/spinner.gif" height="20px" />
    <input class="flat minwidth300" type="number" id="txtsearch" placeholder="<?php echo $langs->trans("inputfilterplaceholder") ;?>"/>
  </div>
  </div>
  
  
  <div class="inline-block floatleft">
  <div class="maxwidth750 refid">
    <h1><?php echo $langs->trans("titlenotas") ;?></h1>
  </div>
  </div>
  </div>
  </div>
  <div id="idprint">
  <div class="div-table-responsive">
  <table id="tableDataNotess" class="tagtable nobottomiftotal liste">
  <caption class="right"><em><b><?php echo $object->label ;?></b></em></caption>
    <thead>
      <tr class="liste_titre">
        <th><?php echo $langs->trans("tablenotesheader1") ;?></th>
        <th><?php echo $form->textwithpicto($langs->trans("tablenotesheader2"),$langs->trans("tablenotesheader2Tooltip"));?></th>
        <th><?php echo $form->textwithpicto($langs->trans("tablenotesheader3"),$langs->trans("tablenotesheader3Tooltip"));?></th>
      </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
      <tr><td colspan="3" class="liste_titre">
      <a href="#" onclick="printDiv()" class="prl-10" style="float: right;"><i class="fa fa-print fa-2x"></i></a>
      </td></tr>
    </tfoot>
  </table>
  </div>
  </div>
  <script type="text/javascript">
  
  function printDiv() {
     var divName= "idprint";
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;
     document.body.innerHTML = printContents;
     window.print();
     document.body.innerHTML = originalContents;
   }
  
  </script>
  
  
  
  <?php
	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
















