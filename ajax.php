<?php

if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', '1');
}
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', '1');
}
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}
if (!defined('NOBROWSERNOTIF')) {
	define('NOBROWSERNOTIF', '1');
}

require '../../main.inc.php'; 
// load college libraries
require_once __DIR__.'/class/notes.class.php';
require_once __DIR__.'/class/classrooms.class.php';
require_once __DIR__.'/class/student.class.php';
require_once __DIR__.'/class/inscriptions.class.php';
require_once __DIR__.'/class/subject.class.php';


global $db, $user, $langs; 

$action = GETPOST('action', 'aZ09');
$idClass = GETPOST('idClass', 'int');
$idSubjejct = GETPOST('idSubjejct', 'int');

$arralumnoperiodo = GETPOST('arralumnoperiodo', 'intcomma');
$notavalue = GETPOST('notavalue', 'aZ09');
$idNota = GETPOST('idNota', 'int');


$msj = GETPOST('msj', 'alphanohtml');
$arrclases = GETPOST('arrclases', 'array');
$subject = GETPOST('subject', 'alphanohtml');
$arrteacher = GETPOST('arrteacher', 'int');


$estudiante = new Student($db);

if($action == 'getsubject' && !empty($idClass) ){
//SELECT rowid, label, fk_class FROM llx_college_subject WHERE fk_class = 2 - WHERE fk_class = ".(int)$idClass."  , JSON_UNESCAPED_UNICODE
 $rows = array();
 $resql=$db->query("SELECT rowid, label FROM ".MAIN_DB_PREFIX."college_subject WHERE fk_class = ".(int)$idClass." ");
  if ($resql) {
      $num = $db->num_rows($resql);
         $i = 0;
         if ($num)
         {
           while ($i < $num)
           {
             $obj = $db->fetch_object($resql);
             if ($obj)
             {
              $rows[] = array('id' => $obj->rowid,'text' => $obj->label);
             }
             $i++;
           }
         }
      echo json_encode($rows, JSON_UNESCAPED_UNICODE);
      exit;
  }else{
      echo json_encode(0);
      exit;
  }
}

if($action == 'getstudent' && !empty($idClass) ){
  $rows = array();
  $resql = $db->query( "SELECT i.fk_student, s.label 
  FROM ".MAIN_DB_PREFIX."college_inscriptions AS i 
  INNER JOIN ".MAIN_DB_PREFIX."college_student AS s ON i.fk_student=s.rowid 
  WHERE i.fk_class =  ".(int)$idClass."  AND i.`status` = 1 AND i.school_year = ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO." ORDER BY s.label ASC ");
 
  if ($resql) {
      $num = $db->num_rows($resql);
         $i = 0;
         if ($num)
         {
           while ($i < $num)
           {
             $obj = $db->fetch_object($resql);
             if ($obj)
             {
              $rows[] = array(
                'fk_student' => $obj->fk_student,
                'label' => $obj->label
              );
             }
             $i++;
           }
         }
      echo json_encode($rows, JSON_UNESCAPED_UNICODE);
      exit;
  }else{
      echo json_encode(0);
      exit;
  }
}

if($action == 'getperiods'){
  $rows = array();
  $resql = $db->query("SELECT rowid, label FROM ".MAIN_DB_PREFIX."college_periods WHERE `status` = 1  ");
    if ($resql) {
      $num = $db->num_rows($resql);
         $i = 0;
         if ($num)
         {
           while ($i < $num)
           {
             $obj = $db->fetch_object($resql);
             if ($obj)
             {
              $rows[] = array(
                'rowid' => $obj->rowid,
                'label' => $obj->label
              );
             }
             $i++;
           }
         }
      echo json_encode($rows, JSON_UNESCAPED_UNICODE);
      exit;
  }else{
      echo json_encode(0);
      exit;
  }
  
}


if($action == 'getnotes' && !empty($idClass)  && !empty($idSubjejct) ){
  $rows = array();
  $sql = $db->query("SELECT rowid, fk_student, trimestre, nota FROM llx_college_notes WHERE fk_class = ".(int)$idClass." AND fk_subject = ".(int)$idSubjejct." ");
  if ($sql) {
  $num = $db->num_rows($sql);
     $i = 0;
     if ($num)
     {
       while ($i < $num)
       {
         $obj = $db->fetch_object($sql);
         if ($obj)
         {
          $rows[] = array(
            'rowid'=> (int)$obj->rowid,
            'data' => array((int)$obj->fk_student, (int)$obj->trimestre),
            'nota' => (float)$obj->nota
          );
         }
         $i++;
       }
     }
  echo json_encode($rows, JSON_UNESCAPED_UNICODE);
  exit;
  }else{
      echo json_encode(0);
      exit;
  }

}



if($action == 'senddata' && !empty($idClass)  && $idSubjejct!= -1  && !empty($arralumnoperiodo)  && !empty($notavalue)  ){
  
  $data = explode(',',$arralumnoperiodo);
  
    /*NINJA*/
    $query = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."college_notes WHERE fk_subject = ".(int)$idSubjejct." AND fk_student = ".(int)$data[0]." AND trimestre = ".(int)$data[1]."  ");
    $obj = $db->fetch_object($query);
  if($query){
      
    $db->begin();
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."college_notes";
    $sql .="(rowid ,fk_user_creat,school_year,status,fk_class,fk_user,fk_subject,trimestre,fk_student,nota)";
    $sql .=" VALUES(".(int)$obj->rowid.", ".$user->id.", ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO.",1,".(int)$idClass.",".(int)$user->id.",".(int)$idSubjejct.",".(int)$data[1].",".(int)$data[0].",".(float)$notavalue.")";
    $sql .=" ON DUPLICATE KEY UPDATE nota=".(float)$notavalue." ";
    $resql = $db->query($sql);
    if($resql){
      $db->commit();
      echo json_encode(1);
      exit;
    }else{
      $db->rollback();
      echo json_encode(0);
      dol_print_error($db);
      exit;
    }
  
  }
  
}

/**
 *  ADD SUBJECT
 *  $arrclases
 *  $subject
 *  $arrteacher
 */

if($action == 'savesubjectinlotes'){
  $langs->loadLangs(array("college@college"));
  $error = 0;
  if (empty($arrclases)) {
    setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("classrequired")), null, 'warnings');
    $error++;
  }
  if (empty($subject)) {
    setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("subjectrequired")), null, 'warnings');
    $error++;
  }
  if($error==0){
    $db->begin();
    for($i = 0; $i < count($arrclases); $i++) {
     for($j=0; $j < count($arrclases[$i]); $j++){
      $sql = "INSERT INTO ".MAIN_DB_PREFIX."college_subject";
      $sql.= "(ref,label,fk_user_creat,status,fk_class,fk_user,school_year)";
      $sql.= "values('".$subject."','".$subject."','".$user->id."',1,'".$arrclases[$i][$j]."','".$arrteacher."','".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO."'  )";
      $resql = $db->query($sql);
     }
    }
    if($resql){
      $db->commit();
      setEventMessages($msj, null ,'mesgs','');
      exit;
    }else{
      $db->rollback();
      dol_print_error($db);
      exit;
    } 
  }
  
}


















