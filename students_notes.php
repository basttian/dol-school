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
dol_include_once('/custom/college/class/classrooms.class.php');
dol_include_once('/custom/college/class/inscriptions.class.php');
dol_include_once('/custom/college/class/notes.class.php');
dol_include_once('/custom/college/class/subject.class.php');

// Load translation files required by the page
$langs->loadLangs(array("college@college", "notes"));

$id = GETPOST('id', 'int');
// Initialize technical objects
$object = new Students($db);
$object->fetch($id);

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
if (empty($conf->college->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();

$form = new Form($db);

$help_url = '';
llxHeader('
    <link rel="stylesheet" href="./js/print-js/dist/print.css">
    <script type="text/javascript" src="./js/print-js/dist/print.js"></script>
    '
, $langs->trans('StudentsNotes').' | '.$object->label , $help_url);
if ($id > 0 ) {
	$head = studentsPrepareHead($object);
	print dol_get_fiche_head($head, '4', $langs->trans("StudentsNotes"), -1, $object->picto);
	$linkback = '<a href="'.dol_buildpath('/college/students_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

    $morehtmlref = '<div class="refidno">';
    $morehtmlref.='<br>'.$langs->trans('thirdpartytab') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '-------');
    $morehtmlref .= '</div>';

    dol_banner_tab($object, 'id', $linkback, 1, 'rowid', 'ref', $morehtmlref);
    print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';
    global $conf, $langs, $db, $user;
    $asignatura = new Subject($db);
    $periodos = new Periods($db);
    $rownotas=array();
    $year=$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO;
    
    $sql = "SELECT fk_student, fk_subject,`status`,";
    
    for($i=1;$i<=$periodos->getCountRecord();$i++){
      $sql .= "SUM(CASE WHEN trimestre = ".$i." THEN nota ELSE 0 END) AS nota".$i.",";
      $rownotas[]="nota".$i;
    }
    $sql.= " CAST(AVG(nota) AS DECIMAL(11,2)) AS `prom1`,";
    $sql.= " CAST(AVG(NULLIF(notarecover,0)) AS DECIMAL(11,2)) AS `prom2`";
    $sql.= " FROM ".MAIN_DB_PREFIX."college_notes";
    $sql.= " GROUP BY fk_student, fk_subject, school_year";
    $sql.= " HAVING fk_student=".(int)$id." AND school_year =".(int)$year." AND `status`=1";
    
    print '<div id="idprint">';
    print '<div class="div-table-responsive">';
    print '<table id="tableDataNotess" class="tagtable nobottomiftotal liste">';
    
    
    print '<thead>';
    print '<tr class="liste_titre">';
    print '<th>'.$langs->transnoentities("pdfheadtablecols1").'</th>';
    for($i=1;$i<=$periodos->getCountRecord();$i++){
        print '<th>'.$langs->transnoentities("pdfheadtablecolsloop").$i.'</th>';
    }
    print '<th>'.$langs->transnoentities("pdfheadprom1").'</th>';
    print '<th>'.$langs->transnoentities("pdfheadprom2").'</th>';
    print '<th>'.$langs->transnoentities("pdfheadprom3").'</th>';
    print '</tr>';
    print '</thead>';
    print '<tbody>';
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
                print '<tr class="pair">';
                print '<td>'.$asignatura->label.'</td>';
                foreach($rownotas as $row) {
                    print '<td>'.number_format($obj->$row,2).'</td>';
                }
                print '<td bgcolor="#e9eaed">'.number_format($obj->prom1,2).'</td>';
                print '<td>'.number_format($obj->prom2,2).'</td>';
                print '<td bgcolor="#e9eaed">'.max($array).'</td>';
                print '</tr>';
                $i++;
            }
        }
        else
        {
            print '<tr class="pair"><td colspan="20">'.$langs->transnoentities("pdftablerownodata").'</td></tr>';
        }
    }
    print '</tbody>';
    print '<tfoot>';
    print '</tfoot>';
    print '</table>';
    print '</div>';
    print '</div>';
	print '</div>';
    print '<hr>';
    print '<a class="butAction" href="#" onclick="printDiv()" ><i class="fa fa-print" aria-hidden="true"></i></a>';

    ?>
    <script>
    function printDiv() {
        printJS({ 
			printable: "idprint", 
			type: "html", 
			header: "<h3><?php echo $object->label;?></h3>",
			documentTitle:"<?php echo $object->label;?>",
            style:"table, th, td {border-bottom: 1px solid #ddd;} tr:nth-child(even) {background-color: #f2f2f2;}",
			repeatTableHeader:false
		})
	 }
    </script>
    <?php
}
// End of page
llxFooter();
$db->close();

