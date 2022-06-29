<?php
require_once"./../../main.inc.php";

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/college/class/students.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/college/class/subject.class.php';

global $langs, $db, $user;

$langs->loadLangs(array("college@college", "list_student"));

$action = GETPOST('action', 'aZ09');
$idStudent = GETPOST('idStudent', 'int') ? GETPOST('idStudent', 'int') : 0;
/**
 * 
 * Action 
 * 
 */


$estudiante = new Students($db);
$asignatura = new Subject($db);
 
if($action == 'filterStudent' && !empty($idStudent)){
  
$dataArrStudent = array();
  
$resql = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."college_notes 
WHERE status = 1 AND school_year = ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO." 
AND fk_student =  ".(int)$idStudent." AND fk_user = ".$user->id." ORDER BY fk_subject ASC, trimestre DESC "); //
 if ($resql)
 {
   $num = $db->num_rows($resql);
   $i = 0;
   if ($num)
   {
     while ($i < $num)
     {
       $obj = $db->fetch_object($resql);
       if ($obj)
       {
           $dataArrStudent[] = array(
           'rowid' => $obj->rowid,
           'trimestre' => $obj->trimestre,
           'fk_subject' => $asignatura->fetch($obj->fk_subject)?$asignatura->label:$obj->fk_subject,
           'nota' => $obj->nota,
           );
       }
       $i++;
     }
     echo json_encode($dataArrStudent);
     exit;
   }
   else
   {
  	 echo json_encode([]);
  	 exit;
   }
 }
}
print '<p></p>';
?>
<script type="text/javascript">
jQuery(document).ready(function() {
  
var estudiante = 0;
$('#fk_student').on('change', function(e) {
  estudiante = $('#fk_student').select2("val");
  $.getJSON( "./notes_list_student.php?action=filterStudent",
  { idStudent: estudiante, token:'<?php echo newToken(); ?>' }, function(jsonresp) {
     console.log("success"); 
     $("#tableDataNotes>tbody").find("tr").remove();
  }).done(function(jsonresp){
      //console.log(jsonresp.length)
      if(jsonresp.length==0){
        $("#tableDataNotes>tbody").append("<tr><td colspan='3' class='liste_titre'><?php echo $langs->trans("nodatafound") ;?></td></tr>");
      }else{
        Object.keys(jsonresp).forEach(function(key,index,arr) {  
          $("#tableDataNotes>tbody").append("<tr class='pair'><td>"+jsonresp[key].trimestre+"&nbsp;<?php echo $langs->trans("tableheader1") ;?>"+"</td><td>"+jsonresp[key].fk_subject+"</td><td>"+jsonresp[key].nota+"</td></tr>")
          //console.log(jsonresp[key].nota);  
        });
      }  
  }).fail(function(e){
    console.log(e);
  }).always(function(){
    
  });
});





});
</script>

<div class="div-table-responsive">
<table id="tableDataNotes" class="tagtable nobottomiftotal liste">
<caption></caption>
  <thead>
    <tr class="liste_titre">
      <th><?php echo $langs->trans("tableheader1") ;?></th>
      <th><?php echo $langs->trans("tableheader2") ;?></th>
      <th><?php echo $langs->trans("tableheader3") ;?></th>
    </tr>
  </thead>
  <tbody>
    <tr class="pair">
      <td colspan="3" class="liste_titre"><?php echo $langs->trans("nodatafound") ;?></td>
    </tr>
  </tbody>
  <tfoot>
    <tr><td colspan="3" class="liste_titre"></td></tr>
  </tfoot>
</table>
</div>
<?php

?>