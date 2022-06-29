<?php
require_once"./../../main.inc.php";

// load college libraries
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php.';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
// load college libraries
require_once __DIR__.'/class/notes.class.php';
require_once __DIR__.'/class/classrooms.class.php';
require_once __DIR__.'/class/inscriptions.class.php';
require_once __DIR__.'/class/students.class.php';
require_once __DIR__.'/class/subject.class.php';
/**/
$action = GETPOST('action', 'aZ09');


// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->college->notes->read;
	$permissiontoadd = $user->rights->college->notes->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->college->notes->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->college->notes->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->college->notes->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}
$upload_dir = $conf->college->multidir_output[isset($object->entity) ? $object->entity : 1].'/notes';
//if (empty($conf->college->enabled)) accessforbidden();
if (!$user->rights->college->notes->write) accessforbidden();



// Load translation files required by the page
$langs->loadLangs(array("college@college" , "list_create_tudent"));
llxHeader("", $langs->trans("CollegeAreaTitle"));

/*
 * Actions
 */


$form = new Form($db);
$formfile = new FormFile($db);

// Initialize technical objects
$object = new Notes($db);
$objectClass = new ClassroomsLine($db);

/*
 * View
 */

?>
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
    }).done(function(response){
      if(response==1 ){
        //console.log(response);
        $('#loader').hide();
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

});
</script>
<?php

print load_fiche_titre($langs->trans("CollegeAreaTitle_a"), '', 'object_college_list_add@college');
print '<span>'.$langs->trans("CollegeAreaTitle_b").', '.$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO.'</span>';
// Part to create
if ($action == 'createlist') { 
  
	print '<form id="formucreate" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="search">';

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";

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
 
  print '<p></p>';
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
  	$sort = 'ASC',
  	$morecss = 'flat maxwidth500 widthcentpercentminusxx',
  	$addjscombo = 1,
  	$moreparamonempty = '',
  	$disablebademail = 0,
  	$nohtmlescape = 0 
  );
  print '<p></p>';
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
  	$morecss = 'flat maxwidth500 widthcentpercentminusxx',
  	$addjscombo = 1,
  	$moreparamonempty = '',
  	$disablebademail = 0,
  	$nohtmlescape = 0 
  );
  print '<p></p>';
  /*print $form->buttonsSaveCancel(
    $save_label = 'Create List',
    $cancel_label = '',
    $morebuttons = array(),
    $withoutdiv = 1,
    $morecss = 'flat maxwidth500 widthcentpercentminusxx btn_create',
    $dol_openinpopup = ''
  );*/
	
  print '</table>'."\n";
	print dol_get_fiche_end();

	print '</form>';

}

/*TABLA DE DATOS*/
print '<img style="display: none;" id="loader" class="m-10" src="../../custom/college/img/spinner.gif" height="15px">';
?>

<div class="div-table-responsive">
<table id="tableDataNotes" class="tagtable nobottomiftotal liste">
<caption></caption>
  <thead>
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
// End of page
llxFooter();
$db->close();






























