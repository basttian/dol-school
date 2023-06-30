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
require_once __DIR__.'/class/students.class.php';
require_once __DIR__.'/class/inscriptions.class.php';
require_once __DIR__.'/class/subject.class.php';
require_once __DIR__.'/class/periods.class.php';
require_once __DIR__.'/class/questions.class.php';

global $db, $user, $langs; 

$action     = GETPOST('action', 'aZ09');
$idClass    = GETPOST('idClass', 'int');
$idSubjejct = GETPOST('idSubjejct', 'int');
$idPeriods = GETPOST('idPeriods', 'int');
$idStudent = GETPOST('idStudent', 'int');

$arralumnoperiodo = GETPOST('arralumnoperiodo', 'intcomma');
$notavalue        = GETPOST('notavalue', 'aZ09');
$idNota           = GETPOST('idNota', 'int');

$msj        = GETPOST('msj', 'alphanohtml');
$arrclases  = GETPOST('arrclases', 'array');
$subject    = GETPOST('subject', 'alphanohtml');
$arrteacher = GETPOST('arrteacher', 'int');

$yeartab    = GETPOST('yeartab', 'int');
$studenttab = GETPOST('studenttab', 'int');
$subjecttab = GETPOST('subjecttab', 'int');
$periodstab = GETPOST('periodstab', 'int');

$estudiante = new Students($db);
$asignatura = new Subject($db);
$periodos   = new Periods($db);
$questionsearch = new Questions($db);

/** Questions */
$idQuestions = GETPOST('idQuestions', 'int');
$idOption    = GETPOST('idOption', 'int');
$lblQuestion = GETPOST('lblQuestion', 'alphanohtml');
$idSurveytype = GETPOST('idSurveytype','int');

/** Response */
$idResponse    = GETPOST('idResponse', 'int');
$dataResponse    = GETPOST('dataResponse', 'alphanohtml');


if($action == 'getsubject' && !empty($idClass) ){
//SELECT rowid, label, fk_class FROM llx_college_subject WHERE fk_class = 2 - WHERE fk_class = ".(int)$idClass."  , JSON_UNESCAPED_UNICODE
 $rows = array();
 $resql=$db->query("SELECT rowid, label FROM ".MAIN_DB_PREFIX."college_subject WHERE fk_class = ".(int)$idClass." AND fk_user=".$user->id." ");
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
  $resql = $db->query( "SELECT i.fk_student, s.label FROM ".MAIN_DB_PREFIX."college_inscriptions AS i INNER JOIN ".MAIN_DB_PREFIX."college_students AS s ON i.fk_student=s.rowid WHERE i.fk_class =  ".(int)$idClass."  AND i.`status` = 1 AND i.school_year = ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO." ORDER BY s.label ASC ");
 
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
  $sql = $db->query("SELECT rowid, fk_student, trimestre, nota FROM ".MAIN_DB_PREFIX."college_notes WHERE fk_class = ".(int)$idClass." AND fk_subject = ".(int)$idSubjejct." ");
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


/**
 * Notas desde regilla
 * Funcion
 */
if($action == 'senddata' && !empty($idClass)  && $idSubjejct!= -1  && !empty($arralumnoperiodo)  && !empty($notavalue)  ){
  $dateCreation = dol_print_date(dol_now(), 'standard');
  $data = explode(',',$arralumnoperiodo);
  
    /*NINJA*/
    $query = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."college_notes WHERE fk_subject = ".(int)$idSubjejct." AND fk_student = ".(int)$data[0]." AND trimestre = ".(int)$data[1]."  ");
    $obj = $db->fetch_object($query);
  if($query){
    $db->begin();
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."college_notes";
    $sql .="(rowid,ref,fk_user_creat,school_year,status,fk_class,fk_user,fk_subject,trimestre,fk_student,date_creation,notarecover,nota)";
    $sql .=" VALUES(".(int)$obj->rowid.",".(int)$data[0].",".$user->id.", ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO.",1,".(int)$idClass.",".(int)$user->id.",".(int)$idSubjejct.",".(int)$data[1].",".(int)$data[0].",'".$dateCreation."',0,".(float)$notavalue.")";
    $sql .=" ON DUPLICATE KEY UPDATE nota=".(float)$notavalue." ";
    $resql = $db->query($sql);
    $querylastrow = $db->last_insert_id(MAIN_DB_PREFIX."college_notes");
    if($resql){
      $db->commit();
      echo json_encode(array('codResponse'=>1, 'idrow'=>(int)$obj->rowid, 'numline'=>$querylastrow ));
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
  $dateCreation = dol_print_date(dol_now(), 'standard');
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
      $resql=$db->query("SELECT label FROM ".MAIN_DB_PREFIX."college_classrooms WHERE rowid = ".(int)$arrclases[$i][$j]." ");
      $obj = $db->fetch_object($resql);
      $sql = "INSERT INTO ".MAIN_DB_PREFIX."college_subject";
      $sql.= "(ref,label,fk_user_creat,status,fk_class,fk_user,school_year,date_creation)";
      $sql.= "values('".strtoupper($subject)."','".strtoupper($subject).", ".$obj->label."','".$user->id."',1,'".$arrclases[$i][$j]."','".$arrteacher."','".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO."','".$dateCreation."'  )";
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

//notes tab student 
if ($action == 'getnotesforstudent') {
  $rows=array();
  $rownotas=array();
  $sql = "SELECT fk_subject,`status`,";
  for($i=1;$i<=$periodos->getCountRecord();$i++){
  $sql .= "SUM(CASE WHEN trimestre = ".$i." THEN nota ELSE 0 END) AS `".$i."`," ;
  //$sql .="sum(CASE WHEN trimestre = 2 THEN nota ELSE 0 END) AS Tr2,";
  //$sql .="sum(CASE WHEN trimestre = 3 THEN nota ELSE 0 END) AS Tr3,";  
  }
  $sql .=" CAST(AVG(nota) AS DECIMAL(11,2)) AS `prom` ,";
  $sql .=" CAST(AVG(NULLIF(notarecover,0)) AS DECIMAL(11,2)) AS `promnotarecover`";
  $sql .=" FROM ".MAIN_DB_PREFIX."college_notes GROUP BY fk_student, fk_subject, school_year";
  $sql .=" HAVING fk_student=".(int)$studenttab." AND school_year = ".(int)$yeartab." AND `status`=1 ";
  
  $resql = $db->query($sql);
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
         $asignatura->fetch((int)$obj->fk_subject);
         
  			 $rows[] = array(
  				 'asignatura'  => $asignatura->label,
 		       'promedio'    => (float)$obj->prom,
           'idasignatura'=> (int)$obj->fk_subject,
           'data'        => $obj,
           'noterecovery'=> (float)$obj->promnotarecover
  			 );
         
        }
  			$i++;
  		}
      echo json_encode($rows, JSON_UNESCAPED_UNICODE);
      exit;
  	}else{
  	 echo json_encode(0);
     exit;
  	}
  }else{
      $db->rollback();
      dol_print_error($db);
      exit;
  }
}

//filter notes tab ssubject card 
if ($action == 'filternotesfromsubject') {
  $rows = array();
	$query = "SELECT e.label, n.trimestre ,n.nota, notarecover";
	$query .= " FROM ".MAIN_DB_PREFIX."college_notes AS n";
	$query .= " INNER JOIN ".MAIN_DB_PREFIX."college_students AS e";
	$query .= " ON n.fk_student = e.rowid";
	$query .= " WHERE n.fk_subject=".(int)$idSubjejct." AND n.school_year =".(int)$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO ;
  if ((int)$idPeriods > 0) {
    $query .= " AND n.trimestre =".(int)$idPeriods;
  }
	$query .= " ORDER BY e.label, n.trimestre ASC";

	$resql=$db->query($query);
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
					$rows[] = array(
						'label'=>$obj->label,
						'trimestre'=>$obj->trimestre,
						'nota'=>$obj->nota,
            'notarecover'=>$obj->notarecover>0?number_format($obj->notarecover,2):"-",
					);
				}
				$i++;
			}
			echo json_encode($rows, JSON_UNESCAPED_UNICODE);
      exit;
		}else{
      echo json_encode(0);
      exit;
     }
	}
}

/** QUESTIONS */
//CREATE ONLY ROW
if($action == 'newquestion'){
  global $user, $db;
  $dateCreation = dol_print_date(dol_now(), 'standard');
  $sql =  "INSERT INTO ".MAIN_DB_PREFIX."college_questions";
  $sql .= " (label,date_creation,fk_user_creat,status,parent_id,type,survey_id)";
  $sql .= " VALUES('-','".$dateCreation."',".$user->id.",1,".(int)$idQuestions.",-1,".(int)$idSurveytype." )";
  $resql = $db->query($sql);
  if($resql){
    $db->commit();
    echo json_encode(1);
    exit;
  }
  else
  {
    $db->rollback();
    echo json_encode(0);
    dol_print_error($db);
    exit;
  }
}

//UPDATE
if($action == 'updatequestion'){
  global $user, $db;
  $dateCreation = dol_print_date(dol_now(), 'standard');
  $sql =  "UPDATE ".MAIN_DB_PREFIX."college_questions";
  $sql .= " SET label='".$lblQuestion."',date_creation='".$dateCreation."',fk_user_modif=".$user->id."";
  $sql .= " WHERE rowid=".(int)$idOption." ";
  $resql = $db->query($sql);
  if($resql){
    $db->commit();
    echo json_encode(1);
    exit;
  }
  else
  {
    $db->rollback();
    echo json_encode(0);
    dol_print_error($db);
    exit;
  }
}

//DELETE
if($action == 'deleteOption'){
  global $db;
  $sql =  "DELETE FROM ".MAIN_DB_PREFIX."college_questions";
  $sql .= " WHERE rowid =".(int)$idOption;
  $resql = $db->query($sql);
  if($resql){
    $db->commit();
    echo json_encode(1);
    exit;
  }
  else
  {
    $db->rollback();
    echo json_encode(0);
    dol_print_error($db);
    exit;
  }
}


/** REPORT */
//UPDATE //
if($action == 'updateresponse' && !empty($idResponse) && !empty($dataResponse)){
  global $user, $db;

  /*$query = "UPDATE ".MAIN_DB_PREFIX."college_report";
  $query .= " SET data=NULL WHERE rowid=".(int)$idResponse." ";
  $resql = $db->query($query);
  if($resql){}*/

    $data = json_encode($dataResponse,JSON_UNESCAPED_UNICODE);
  
    $dateCreation = dol_print_date(dol_now(), 'standard');
    $sql =  "UPDATE ".MAIN_DB_PREFIX."college_report";
    $sql .= " SET date_creation='".$dateCreation."',fk_user_modif=".$user->id.",data='". $data."' ";
    $sql .= " WHERE rowid=".(int)$idResponse." ";
    $resql = $db->query($sql);
    if($resql){
      $db->commit();
      echo json_encode(array('idresponse'=>$idResponse, 'data'=>$dataResponse));
      exit;
    }
    else
    {
      $db->rollback();
      echo json_encode(0);
      dol_print_error($db);
      exit;
    }
  
}

/** STUDENT FROM TYPE SURVEY */ //ancla
if($action=='getstudentsperreport' && !empty($idSurveytype) || $idStudent == null || $idSubjejct == null || $idPeriods == null ){
  global $user, $db;
  $rows = array();

  $sql = "SELECT r.rowid, r.fk_student, stu.label AS `studentname`, r.data, r.fk_subject, sub.label AS `subjectname`, r.trimestre AS `trimestre` ";
  $sql .= " FROM ".MAIN_DB_PREFIX."college_report AS r";
  $sql .= " INNER JOIN ".MAIN_DB_PREFIX."college_students AS stu";
  $sql .= " ON r.fk_student = stu.rowid";
  $sql .= " INNER JOIN ".MAIN_DB_PREFIX."college_subject AS sub";
  $sql .= " ON r.fk_subject = sub.rowid";
  $sql .= " WHERE r.fk_surveytype = ".(int)$idSurveytype."";
  $sql .= " AND r.status = 1";
  $sql .= " AND r.school_year = ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO."";
  if(!empty($idPeriods)){
    $sql .= " AND r.trimestre = ".(int)$idPeriods."";
  }
  if(!empty($idSubjejct)){
    $sql .= " AND r.fk_subject = ".(int)$idSubjejct."";
  }
  if(!empty($idStudent)){
    $sql .= " AND r.fk_student = ".(int)$idStudent."";
    //$sql .= " GROUP BY r.fk_student";
  }
  $sql .= " ORDER BY stu.label ASC";

  $resql = $db->query($sql);
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
                'rowid_report'  => $obj->rowid,
                'fk_student'    => $obj->fk_student,
                'label_student' => $obj->studentname,
                'data'          => $obj->data,
                'fk_subject'    => $obj->fk_subject,
                'label_subject' => $obj->subjectname,
                'fk_trimestre'  => $obj->trimestre,
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


if($action =='getPeriodForSelect'){
  $rows = array();
  $resql=$db->query("SELECT rowid, label FROM ".MAIN_DB_PREFIX."college_periods  WHERE `status` = 1 ");
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
          $rows[] = array('id'=>$obj->rowid, 'text'=>$obj->label);
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