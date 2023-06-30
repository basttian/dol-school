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
require_once __DIR__.'/class/surveytype.class.php';
require_once __DIR__.'/class/report.class.php';

global $db, $user, $langs, $conf; 

$action     = GETPOST('action', 'aZ09');
$idClass    = GETPOST('idClass', 'int');
$idSubjejct = GETPOST('idSubjejct', 'int');
$idPeriods = GETPOST('idPeriods', 'int');
$idStudent = GETPOST('idStudent', 'int');


$estudiante = new Students($db);
$asignatura = new Subject($db);
$periodos   = new Periods($db);

/** Questions */
$idQuestions = GETPOST('idQuestions', 'int');
$idOption    = GETPOST('idOption', 'int');
$lblQuestion = GETPOST('lblQuestion', 'alphanohtml');
$idSurveytype = GETPOST('idSurveytype','int');

/** Response */
$idResponse    = GETPOST('idResponse', 'int');
$dataResponse    = GETPOST('dataResponse', 'alphanohtml');

$currentYear = $conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO;

/**Obtener notas del estudiante */
if($action == 'getStudentGrades' && !empty($idStudent) ){

    $asignatura = new Subject($db);
    $periodos   = new Periods($db);     

    $rows = array();
    $arrnotas=array();
    $rownotas=array();

    $sql = "SELECT fk_student, fk_subject,`status`,";
    for($i=1;$i<=$periodos->getCountRecord();$i++){
      $sql .= "SUM(CASE WHEN trimestre = ".$i." THEN nota ELSE 0 END) AS nota".$i.",";
      $arrnotas[]="nota".$i;
    }
    $sql.= " CAST(AVG(nota) AS DECIMAL(11,2)) AS `prom1`,";
    $sql.= " CAST(AVG(NULLIF(notarecover,0)) AS DECIMAL(11,2)) AS `prom2`";
    $sql.= " FROM ".MAIN_DB_PREFIX."college_notes";
    $sql.= " GROUP BY fk_student, fk_subject, school_year";
    $sql.= " HAVING fk_student=".(int)$idStudent." AND school_year =".(int)$currentYear." AND `status`=1";

    $resql=$db->query($sql);
    if ($resql)
    {
    $num = $db->num_rows($resql);
    $i = 0;
        if ($num)
        {
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                $array = array($obj->prom1,$obj->prom2);
                $asignatura->fetch((int)$obj->fk_subject);
               
                $rows[] = array( 
                    'asignaturas' =>$asignatura->label,
                    'promedioG'   =>number_format($obj->prom1,2),
                    'promedioR'   =>number_format($obj->prom2,2),
                    'promedioF'   =>max($array)
                );

                foreach($arrnotas as $row) {
                    $rownotas[] = array(
                        'notas' => number_format($obj->$row,2),
                    );
                }
                $i++;
            }
            echo json_encode(array('queryrows'=>$rows));
            exit;
        }
    }else{
            echo json_encode(0);
            exit;
      }
}


/**Obtener el total de notas del estudiante por trimestre */
if($action == 'getPeriodGrades' && !empty($idStudent) ){
    $rows = array();

     $sql = "SELECT p.label, AVG(nota) AS prom";
     $sql .=" FROM ".MAIN_DB_PREFIX."college_notes AS n";
     $sql .=" INNER JOIN ".MAIN_DB_PREFIX."college_periods AS p";
     $sql .=" ON n.trimestre = p.rowid";
     $sql .=" WHERE n.fk_student = ".(int)$idStudent." AND n.school_year = ".(int)$currentYear." AND n.status= 1";
     $sql .=" GROUP BY n.trimestre";

    $resql=$db->query($sql);
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
                        'trim'=>$obj->label,
                        'prom'=>$obj->prom,
                    );
                }
                $i++;
            }
            echo json_encode($rows);
            exit;
        }
    }else{
        echo json_encode(0);
        exit;
    }

}

/**
 * Obtener todas las preguntas
 */

if($action == 'getAllQuestions'){
    $rows = array();
    $sql = "SELECT q.rowid, q.ref, q.label FROM ".MAIN_DB_PREFIX."college_questions AS q WHERE `status`= 1 ";
    $resql=$db->query($sql);
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
                        'id'=>$obj->rowid,
                        'ref'=>$obj->ref,
                        'label'=>$obj->label,
                    );
                }
                $i++;
            }
            echo json_encode($rows);
            exit;
        }
    }else{
        echo json_encode(0);
        exit;
    }
}