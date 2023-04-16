<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *   	\file       notes_card.php
 *		\ingroup    college
 *		\brief      Page to create/edit/view notes
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token).
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
dol_include_once('/college/class/notes.class.php');
dol_include_once('/college/lib/college_notes.lib.php');

require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';

// Load translation files required by the page
$langs->loadLangs(array("college@college", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'notescard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');

//ID CLASE PARA FILTRO
$idClass = GETPOST('idClass', 'int');
$arrClass = array();

//VARIABLES XLSX
$idClasse = GETPOST('idClasse', 'int');

// Initialize technical objects
$object = new Notes($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->college->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('notescard', 'globalcard')); // Note that conf->hooks_modules contains array

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
	$permissiontoread = $user->rights->college->notes->read;
	$permissiontoadd = $user->rights->college->notes->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->college->notes->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->college->notes->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->college->notes->write; // Used by the include of actions_dellink.inc.php
	$permissiongeneratexlsx = $user->rights->college->notes->generate_xlsx;
	$permissiongenerateallxlsx = $user->rights->college->notes->generate_all_xlsx;
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
	$permissiongeneratexlsx = 1;
	$permissiongenerateallxlsx = 1;
}

$upload_dir = $conf->college->multidir_output[isset($object->entity) ? $object->entity : 1].'/notes';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->college->enabled)) accessforbidden();
if (!$user->rights->college->notes->read) accessforbidden();

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

	$backurlforlist = dol_buildpath('/college/notes_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/college/notes_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'COLLEGE_NOTES_MODIFY'; // Name of trigger action code to execute when we modify record

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
	$triggersendname = 'COLLEGE_NOTES_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_NOTES_TO';
	$trackid = 'notes'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


    if ($action  == 'getFilterStudent' ){
        $resql=$db->query("SELECT fk_student from ".MAIN_DB_PREFIX."college_inscriptions
         WHERE status = 1 and school_year = ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO." 
         AND fk_class = ".(int)$idClass." ");
        if ($resql)
        {
		$num = $db->num_rows($resql);
        
        $i = 0;
        if ($num > 0)
        {
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                if ($obj)
                {  
                	$arrClass[$i] = $obj->fk_student;      
                }
                $i++;
            }
            echo json_encode($arrClass);
            exit;
         }else{
         	echo json_encode(array(0));
         	exit;
         }
       
        }
    }
    
    if ($action  == 'getFilterSubject' ){
        $resql = $db->query("SELECT rowid, label from ".MAIN_DB_PREFIX."college_subject 
        WHERE fk_class = ".(int)$idClass." AND status = 1 and school_year = ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO." 
        AND fk_user = ".$user->id." ");
        if ($resql)
        {
		$num = $db->num_rows($resql);
        
        $i = 0;
        if ($num > 0)
        {
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                if ($obj)
                {  
                	$arrClass[] = (object)array('ides'=>$obj->rowid);      
                }
                $i++;
            }
            echo json_encode($arrClass);
            exit;
         }else{
         	echo json_encode(array(0));
         	exit;
         }
       
        }
    }

/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new formfile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Notes");
$help_url = '';
llxHeader('', $title, $help_url);

?>
<script type="text/javascript">
// Example : Adding jquery code
 jQuery(document).ready(function() {
	 
	/** 
   * Ocultar el teclado del telefono cuando se hace click en el select
  
  $('#select_class').on('select2:open', function() {
    $('.select2-search__field').prop('focus', true);
  });
  $('#select_subject').on('select2:open', function() {
    $('.select2-search__field').prop('focus', true);
  });*/
	 $('#select_class').on('select2:open', function() {
    $('.select2-search__field').prop('readonly', true);
  });
	 $('#select_subject').on('select2:open', function() {
    $('.select2-search__field').prop('readonly', true);
  });
	 
 	/*function init_myfunc()
 	{
 		jQuery("#myid").removeAttr(\'disabled\');
 		jQuery("#myid").attr(\'disabled\',\'disabled\');
 	}
 	init_myfunc();
 	jQuery("#mybutton").click(function() {
 		init_myfunc();
 	});*/
    
    //$("#fk_user").attr('disabled','disabled');
    

    /*FILTRAR POR CLASE Y ASIGNATURA*/ 
    $("#fk_class").change(function(){
       $('[data-select2-id="fk_student"]').val(null).change();
       $('[data-select2-id="fk_subject"]').val(null).change();
       var option = $(this).find("option:selected");
       var idclass = option.val();
       var students = [];
       var subjects = [];
       $.getJSON( "./notes_card.php?action=getFilterStudent",{ idClass: idclass , token: '<?php echo newToken();?>' }, function(jsonresp) {
       		$.each(jsonresp, function(index,element) {
		        students.push({'id':Number(element)})
			}); 
            matchCustomStu = function(params,data){
                params.term = $.map(students, function (obj) {
                    obj.id = obj.id || obj.id;
                    return obj;
                });
                if ($.trim(params.term[0].id) <= 0) {
                  return data;
                }
                for(var i=0; i < params.term.length; i++){
                    if (data.id.indexOf(params.term[i].id) > -1) {
                        var modifiedData = $.extend({}, data, true);
                        modifiedData.text += '';
                        return modifiedData;
                    } 
                }
                return null;
            }
       }).always(function() {
            $('[data-select2-id="fk_student"]').select2({
                placeholder: {
                    id: '-1',
                    text: 'Select an option'
                },
                allowClear: true,
            	matcher: matchCustomStu
            	
            });
       });
       /*FILTER SUBJECT*/
       $.getJSON( "./notes_card.php?action=getFilterSubject",{ idClass: idclass , token: '<?php echo newToken();?>' }, function(jsonresp) {
       		
            matchCustomSubj = function(params,data){
                for(i in jsonresp){
                  if (data.id.indexOf(jsonresp[i].ides) > -1) {
                        return data;
                    }
                }
                return null;
            }
       }).always(function() {
            $('[data-select2-id="fk_subject"]').select2({
                placeholder: {
                    id: '-1',
                    text: 'Select an option'
                },
                allowClear: true,
            	matcher: matchCustomSubj
            	
            });
       });
     });

    
 });
</script>

<script type="text/javascript">
jQuery(document).ready(function() {
  
  $('.btn_create').attr('disabled','disabled');
  function init_btn_button(){
    if($('#select_class').val() != -1 && $('#select_subject').val() != -1 ){
      $('.btn_create').removeAttr("disabled");
    }else{
      $('.btn_create').prop('disabled',true);
    }
  }
  
  /*ONLY ORDER*/
  var selectedValuesClass;
  var selectedValuesSubject;
  
  $('#select_class').on('change', function(e) {
    $('#loader').show();
    init_btn_button();
    var optionSelected = $('option:selected', this);
    selectedValuesClass = optionSelected.val();
    $('[data-select2-id="select_subject"]').find('option').not(':first').remove();
    $('[data-select2-id="select_subject"]').val('-1').trigger("change");
  });
  
   $('#select_subject').on('change', function(e) {
      $('#loader').show();
      $('[data-toggle]').val(null);
      init_btn_button();
      var optionSelected = $('option:selected', this);
      selectedValuesSubject = optionSelected.val();
      //console.log(selectedValuesSubject);
      $.getJSON( "./ajax.php?action=getnotes",
        { idClass:selectedValuesClass, idSubjejct:selectedValuesSubject , token:"<?php echo newToken() ;?>" }, function(datanotas) {
        //console.log(datanotas);
      }).done(function(datanotas){
          Object.keys(datanotas).forEach(function(key,index,arr) {
            $("[data-toggle='"+datanotas[key].data+"']").val(datanotas[key].nota);
            
          });
          $('#loader').hide();
      }).fail(function(){$('#loader').hide();});
  });
  
  
  
  /*Get note from data-toggle*/
  $('#select_subject').on('change', function(e) {
    e.stopPropagation();
    $('input[data-toggle]').on('change', function(e){
      e.stopPropagation();
      var arrayAlumnoPeriodo = $(this).data('toggle');
      //console.log(arrayAlumnoPeriodo);
      updateOrInsertNote(arrayAlumnoPeriodo);
    });
  });//fin change select_subject


  function updateOrInsertNote(arr){
    $('#loader').show();
    var datavalue = $("[data-toggle='"+arr+"']").val();
    //console.log(datavalue)
    $.post( "./ajax.php?action=senddata&token=<?php echo newToken() ;?>" , 
    { 
      //idNota: datanotas[key].rowid,
      idClass: selectedValuesClass,
      idSubjejct: selectedValuesSubject,
      arralumnoperiodo: arr,
      notavalue: datavalue, 
    }).done(function(dataresponse){
      if(dataresponse){
		console.log(dataresponse);
		$('#loader').hide();
		/*jsondata = JSON.parse(dataresponse);
		$.post('<?php echo $_SERVER['PHP_SELF'] ?>?id='+jsondata.numline+'&action=confirm_validate&confirm=yes&token=<?php echo newToken() ;?>',function(resp){
			console.log("Registro insertado/actualizado con exito.");
			$('#loader').hide();
		}).fail(function() {
			console.log("Error in confirm validation in notes_card file");
    		$('#loader').hide();
  		});*/
      }else{
        console.log("ERROR:NO INSERT OR UPDATE RECORD");
        $('#loader').hide()
      }
    }).fail(function(){$('#loader').hide();});
  }


  
  $('#select_class').on('change',function(){
    var option = $(this).find("option:selected");
    var idclass = option.val();
    $.getJSON( "./ajax.php?action=getsubject",{idClass:idclass, token:"<?php echo newToken() ;?>"}, function(dataobj) {
    }).done(function(dataobj){
      $('[data-select2-id="select_subject"]').select2({
        placeholder: {
            id: '-1',
            text: 'Select an option'
        },
        allowClear: true,
    	  data: dataobj
      })
    }).always(function(){
      $.getJSON( "./ajax.php?action=getstudent",{idClass:idclass, token:"<?php echo newToken() ;?>"}, function(datastudent) {
        //console.log(datastudent);
        $("#tableDataNotes>thead").find("tr").remove();
        $("#tableDataNotes>tbody").find("tr").remove();
      }).done(function(datastudent){
        if(datastudent.length===0){
          $("#tableDataNotes>tbody").append("<tr class='liste_titre'><th><?php echo $langs->trans("nodatafound") ;?></th></tr>");
        }else{
          $.getJSON( "./ajax.php?action=getperiods",{ token:"<?php echo newToken() ;?>" }, function(dataperiods) {
          $("#tableDataNotes>thead").append("<tr class='liste_titre'><th>ID</th><th>Name</th></tr>");
          Object.keys(dataperiods).forEach(function(key,index,arr) { 
            $("#tableDataNotes>thead").find("tr:eq(0)").append("<th>"+dataperiods[key].label+"</th>");
          });
          Object.keys(datastudent).forEach(function(key,index,arr) {  
            $("#tableDataNotes>tbody").append("<tr class='pair'><td>"+datastudent[key].fk_student+"</td><td>"+datastudent[key].label+"</td></tr>")
            Object.keys(dataperiods).forEach(function(k,i,a){
              $("#tableDataNotes>tbody").find("tr:eq(\'"+key+"'\)").append("<td><input data-toggle=\'"+datastudent[key].fk_student+","+dataperiods[k].rowid+"\' step='0.5' min='1' max='10' type='number' /></td>");
            });
          });
          });
          $('#loader').hide();
        }
      });
    });
    
  }); 

  /*CREATE LIST OF STUDENT SIN USO*/
  $('#formucreate').on('submit',function(e){
    e.preventDefault();
    //console.log(selectedValuesClass);
    //console.log(selectedValuesSubject);
  });

  /**
   * BOTONES DE MIS NOTAS
   */
  $("[name=idbtn_generate_xlsx]").on("click",function(e){
	e.preventDefault();
	$('#loader').show();
	$('#donwloadlink').hide();
	$('#simbolicbtn').hide();
	var a_url = this.href;
	$.ajax({
		url: a_url,
		context: document.body,
		dataType: "html"
	}).done(function(data, status) {
		$('#loader').hide();
		if(status == 'success'){
			setTimeout(function () {
				$('#donwloadlink').show();
			}, 100);
		}
	});
  })

  $('#id_generate_xlsx').on("click",function(e){
	e.preventDefault();
	$('#loaderb').show();
	$('#id_generate_xlsx').hide();
	var b_url = this.href;
	$.ajax({
		url: b_url,
		context: document.body,
		dataType: "html"
	}).done(function(data, status) {
		$('#loaderb').hide();
		if(status == 'success'){
			setTimeout(function () {
				$('#id_generate_xlsx').hide();
				$('#btndownloadallnotes').show();
				$('#donwloadlinkb').show();
			}, 100);
		}
	});
  });
  $('#btndownloadallnotes').on('click',function(e){
	setTimeout(function () {
		$('#btndownloadallnotes').hide();
		$('#id_generate_xlsx').show();
		e.preventDefault();
	}, 5000);
  });


});
</script>
<?php

/**
 * Create xlsx from my notes page
 */
if($action == 'createxlsx'){
	if (empty($permissiontoread) && empty($permissiontoadd) && empty($permissiongeneratexlsx) ) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}
	/**  Create spreadsheeet in file note.class */
	$createSheet = $object->createSpreadsheet($user->id, $idClasse);
	if($createSheet){
		exit;
	}
}
/**
 * 
 * MIS NOTAS PAGE
 * 
 */
if ($action == 'mynotes') {
	if (empty($permissiongeneratexlsx) && empty($permissiongenerateallxlsx)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	$arraynotesclass = new $object($db);
	$data = $arraynotesclass->getClassForUser($user->id);

	print load_fiche_titre($langs->trans("MyNotes"), '', 'object_'.$object->picto);
	print dol_get_fiche_head(array(), '');
	
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noshadow" width="100%">';
	print '<tr class="nohover"><td>';
	if(empty($data)){
		print '<tr><th>'.$langs->trans("nodatafound").'</th></tr>';
	}
	print'<div class="refid"><i class="fa fa-list-alt" aria-hidden="true"></i> ';
	print $form->textwithpicto($langs->trans("notestext"),$langs->trans("noteshelp"));
	print '</div>'."<br>";
	$total_de_notas = 0;
	$total_de_alumnos = 0;
	foreach($data as $values){
	$total_de_notas += $values['qtytotal'];
	$total_de_alumnos += $values['qtynotes'];
	print '<a href="'.$_SERVER["PHP_SELF"]."?idClasse=".$values['idclass']."&action=createxlsx&token=".newToken().' " name="idbtn_generate_xlsx" class="boxstatsindicator thumbstat nobold nounderline">';
	print '<div class="boxstats">';
	print '<span class="boxstatstext">'.$values['class'].'</span><br>';
	print '<span class="boxstatsindicator"><span class="fa fa-graduation-cap inline-block" style=" color: #263C5C;"></span> '.$values['qtynotes'].'</span>';
	print '</div>';
	print '</a>';
	}
	/**LINK A TODAS MIS NOTAS 
	print '<a href="'.$_SERVER["PHP_SELF"]."?action=createxlsx&token=".newToken().' " name="idbtn_generate_xlsx" class="boxstatsindicator thumbstat nobold nounderline">';
	print '<div class="boxstats">';
	print '<span class="boxstatstext">'.$langs->trans('lbltodolink').'</span><br>';
	print '<span class="boxstatsindicator"><span class="fa fa-graduation-cap inline-block" style=" color: #2D757A;"></span> '.$total_de_notas.'</span>';
	print '</div>';
	print '</a>';*/
	print '</td>';
	print '</tr>';
	print '</table>';
    print '</div>';
	
	print dol_get_fiche_end();

	print '<div class="fichecenter">';
	/************************************************************************************************************************ */
	print '<div class="fichethirdleft">';
	
	print'<table class="noborder boxtable" width="100%"><tbody><tr class="liste_titre box_titre">';
	print '<th>';
	print '<div class="tdoverflowmax400 maxwidth250onsmartphone float">'.$langs->trans('tbl1titlehead').'</div>';
	print '<div class="nocellnopadd boxclose floatright nowraponall"><span class="fa fa-file-excel-o opacitymedium marginleftonly"></span></div>';
	print'</th></tr><tr><td class="tdoverflowmax150 maxwidth150onsmartphone">';
	/*Loading*/
	print '<img style="display: none;" id="loader" class="m-10" src="../../custom/college/img/loading.gif" height="20px">';
	print '<div style="display: none;" id="donwloadlink">';
	/** DOWNLOAD NOTES */
	print dolGetButtonTitle("", 
		$helpText = '', 
		'fa fa-download', 
		$url = ''.DOL_URL_ROOT.'/document.php?modulepart=college&file='.$object->element."/".$user->id.'.xlsx&token='.newToken().'', 
		$id = '',
		$status = $permissiongeneratexlsx && $permissiontoread && $permissiontoadd,
		$params = array()
	);
	print '</div>';
	print '<div id="simbolicbtn"><span class="btnTitle refused classfortooltip" title="'.$langs->trans('refusedbtntootip').'"><span class="fa fa-download valignmiddle btnTitle-icon"></span></span></div>';
	print '</td></tr></tbody></table>';
	
	print '</div>';
	/************************************************************************************************************************ */
	print '<div class="fichetwothirdright">';
	print'<table class="noborder boxtable" width="100%"><tbody><tr class="liste_titre box_titre">';
	print '<th>';

	print '<div class="tdoverflowmax400 maxwidth250onsmartphone float">'.$langs->trans('tbl2titlehead').'</div>';
	print '<div class="nocellnopadd boxclose floatright nowraponall"><span class="fa fa-check-square-o marginleftonly"></span>&nbsp;('.$total_de_notas.')&nbsp;<i class="fa fa-child" aria-hidden="true"></i>('.$total_de_alumnos.')&nbsp;<span class="fa fa-file-excel-o opacitymedium marginleftonly"></span></div>';

	print'</th></tr><tr><td class="tdoverflowmax150 maxwidth150onsmartphone">';
	/*Loading b*/
	print '<img style="display: none;" id="loaderb" class="m-10" src="../../custom/college/img/loading.gif" height="20px">';
	/** DOWNLOAD ALL NOTES #563*/
	print dolGetButtonTitle(
		"",
		$helpText = $langs->trans('lbltodolinkcreate'),
		'fa fa-repeat',
		$url = !empty($permissiongenerateallxlsx)?$_SERVER["PHP_SELF"]."?action=createxlsx&token=".newToken():'#',
		$id = 'id_generate_xlsx',
		$status = $permissiongeneratexlsx && $permissiongenerateallxlsx && $permissiontoread && $permissiontoadd && $total_de_notas > 0 ? 1 : 0,
		$params = array()
	);
	print '<div style="display: none;" id="donwloadlinkb">';
	print dolGetButtonTitle(
		"",
		$helpText = $langs->trans('lbltodolinktootltip'), 
		'fa fa-download',
		$url = ''.DOL_URL_ROOT.'/document.php?modulepart=college&file='.$object->element."/".$user->id.'.xlsx&token='.newToken().'', 
		$id = 'btndownloadallnotes',
		$status = $permissiongeneratexlsx && $permissiongenerateallxlsx && $permissiontoread && $permissiontoadd && $total_de_notas > 0 ? 1 : 0,
		$params = array()
	);
	print '</div>';
	print '</td></tr></tbody></table>';
	print '</div>';
	print '</div>';
	
	/**DEBBUG */
	//print dol_syslog('antion mynotes',4);
}
  
// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Notes")), '', 'object_'.$object->picto);

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

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("create");

	print '</form>';

	//dol_set_focus('input[name="trimestre"]');
  
  print '<hr>';
  dol_include_once('/college/notes_list_student.php' );
  
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Notes"), '', 'object_'.$object->picto);

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
/*CREATELIST VERIFICAR ACTION*/
require_once __DIR__.'/class/classrooms.class.php';

if($action == 'createlist') {
  if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}
  print load_fiche_titre($langs->trans("CollegeAreaTitle_a"), '', 'object_notes@college');
  print '<span>'.$langs->trans("CollegeAreaTitle_b").', '.$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO.'</span>';
  print dol_get_fiche_head(array(), '');
  // Initialize technical objects
  $object = new Notes($db);
  $objectClass = new ClassroomsLine($db);
  
  print '<div class="div-table-responsive-no-min">';
	print '<table class="centpercent">'."\n";

  /*
  print $form->selectArrayFilter(
    'select_class', 
    $objectClass->getClass(),
    $id = 'id',
  	$moreparam = '',
  	$disableFiltering = 0,
  	$disabled = 0,
  	$minimumInputLength = 1,
  	$morecss = 'flat maxwidth500 widthcentpercentminusxx',
  	$callurlonselect = 0,
  	$placeholder = 'Search option class',
  	$acceptdelayedhtml = 0 
  );
  print '<p></p>';
  print $form->selectArrayAjax(
    'select_subject', 
    $url = DOL_URL_ROOT.'/custom/college/ajax.php?action=getsubject', 
    $id = '', 
    $moreparam = '', 
    $moreparamtourl = '', 
    $disabled = 0, 
    $minimumInputLength = 1, 
    $morecss = 'flat maxwidth500 widthcentpercentminusxx', 
    $callurlonselect = 0, 
    $placeholder = 'Search option subjects', 
    $acceptdelayedhtml = 0
 	);
  */
  print '<tr>';
  print '<td class="hideonsmartphoneimp ">';
  print $form->textwithpicto($langs->trans("CollegeSelect_Class"),$langs->trans("CollegeSelect_ClassInfo"));
  print '</td>';
  print '<td>';
  print $form->selectarray(
    'select_class',
    $objectClass->getClass(),
    $id = '-1',
  	$show_empty = 1,
  	$key_in_label = 0,
  	$value_as_key = 0,
  	$moreparam = '',
  	$translate = 0,
  	$maxlen = 0,
  	$disabled = 0,
  	$sort = '',
  	$morecss = 'flat maxwidth750 minwidth500imp',
  	$addjscombo = 1,
  	$moreparamonempty = '',
  	$disablebademail = 0,
  	$nohtmlescape = 0 
  );
  print '</td><td rowspan="2">';
    /*Loading*/
  print '<img style="display: none;" id="loader" class="m-10" src="../../custom/college/img/spinner.gif" height="20px">';
  print '</td>';
  print '</tr><tr>';
  print '<td class="hideonsmartphoneimp ">';
  print $form->textwithpicto($langs->trans("CollegeSelect_Subject"),$langs->trans("CollegeSelect_SubjectInfo"));
  print '</td>';
  print '<td>';
  print $form->selectarray(
    'select_subject', 
    '',
    $id = '',
  	$show_empty = 1,
  	$key_in_label = 0,
  	$value_as_key = 0,
  	$moreparam = '',
  	$translate = 0,
  	$maxlen = 0,
  	$disabled = 0,
  	$sort = 'ASC',
  	$morecss = 'flat maxwidth750 minwidth500imp',
  	$addjscombo = 1,
  	$moreparamonempty = '',
  	$disablebademail = 0,
  	$nohtmlescape = 0 
  );
  print '</td>';
  print '</tr>';
  /*print $form->buttonsSaveCancel(
    $save_label = 'Create List',
    $cancel_label = '',
    $morebuttons = array(),
    $withoutdiv = 1,
    $morecss = 'flat maxwidth500 widthcentpercentminusxx btn_create',
    $dol_openinpopup = ''
  );*/
	
  print '</table>'."\n";
  print '</div>';
  print '<p></p>';
  ?>
    <div class="div-table-responsive">
    <table id="tableDataNotes" class="border centpercent tableforfieldcreate tagtable nobottomiftotal liste">
    <caption></caption>
      <thead style="background-color: #F8F8F8;">
        <tr class="liste_titre"><th><?php echo $langs->trans("nodatafound") ;?></th></tr>
      </thead>
      <tbody>
      </tbody>
      <tfoot>
        <tr></tr>
      </tfoot>
    </table>
    </div>
  <?php
  print dol_get_fiche_end();
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = notesPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Notes"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteNotes'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
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

	// Confirmation of action xxxx
	if ($action == 'xxx') {
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
	$linkback = '<a href="'.dol_buildpath('/college/notes_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
	 // Ref customer
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->projet->enabled)) {
	 $langs->load("projects");
	 $morehtmlref .= '<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd) {
	 //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
	 $morehtmlref .= ' : ';
	 if ($action == 'classify') {
	 //$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref .= '<input type="hidden" name="action" value="classin">';
	 $morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref .= '</form>';
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


	dol_banner_tab($object, 'ref', $linkback, 0, 'ref', 'ref', $morehtmlref);


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

			/** añadir otra nota desde edit page  */
			print '<div class="pull-left valignmiddle">';
			print dolGetButtonAction('<span class="fa fa-plus-circle btnTitle-icon"></span>&nbsp;'.$langs->trans('addothernote'), '', 'default', $_SERVER["PHP_SELF"].'?action=create&token='.newToken(), '', $permissiontoadd,array('class' => 'valignmiddle'));
			/*print dolGetButtonTitle($langs->trans('New'), '', 'fa fa-plus-circle', dol_buildpath('/college/notes_card.php', 1).'?action=create&backtopage='.urlencode($_SERVER['PHP_SELF']), '', $permissiontoadd);*/
			print '</div>';

			// Send
			if (empty($user->socid)) {
				print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init&token='.newToken().'#formmailbeforetitle');
			}

			// Back to draft
			if ($object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Validate
			if ($object->status == $object::STATUS_DRAFT) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
				}
			}

			// Clone
			//print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.(!empty($object->socid)?'&socid='.$object->socid:'').'&action=clone&token='.newToken(), '', $permissiontoadd);

			/*
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ENABLED) {
					print dolGetButtonAction($langs->trans('Disable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Enable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
				}
			}
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_VALIDATED) {
					print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Re-Open'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
				}
			}
			*/

			// Delete (need delete permission, or if draft, just need create/modify permission)
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoadd));
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend') {
		/*print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 0;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->college->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('college:Notes', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('notes'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

		print '</div><div class="fichehalfright">';

		$MAXEVENT = 10;

		$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-list-alt imgforviewmode', dol_buildpath('/college/notes_agenda.php', 1).'?id='.$object->id);

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);

		print '</div></div>';*/
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'notes';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->college->dir_output;
	$trackid = 'notes'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
    
}
// End of page
llxFooter();
$db->close();
