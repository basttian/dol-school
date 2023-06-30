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
$langs->loadLangs(array("college@college", "TAB"));

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


$help_url = '';
llxHeader(
	'<script type="text/javascript" src="./js/printThis.js"></script>', $langs->trans('StudentsNotes').' | '.$object->label , $help_url);

if ($id > 0 ) {
	$head = studentsPrepareHead($object);
	print dol_get_fiche_head($head, '6', $langs->trans("StudentsNotes"), -1, $object->picto);
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
	$notas = new Notes($db);
	$notesdata = $notas->fetchAll('ASC','',0,0,array('fk_student'=>$object->id,'status'=>1),'AND');
  
?>
<script>
jQuery(document).ready(function() {	
	
	var colorAleatorio = [];
	function obtenerColorAleatorio() {
		const letrasHexadecimales = '0123456789ABCDEF';
		let color = '#';
		for (let i = 0; i < 6; i++) {
			color += letrasHexadecimales[Math.floor(Math.random() * 16)];
		}
		return color;
	}
	for (let i = 0; i < 25; i++) {
  		colorAleatorio.push(obtenerColorAleatorio());
	}

	/** CHART LEFT */
	$.ajax({
		method: "POST",
		url: "<?php echo DOL_URL_ROOT.'/custom/college/ajaxchart.php?action=getStudentGrades&token='.newToken();?>",
		data:{idStudent:<?php echo $id;?>},
	}).done(function(json_data) {
		const data = JSON.parse(json_data);
		asignaturas = data.queryrows.map(row => row.asignaturas);
		promediosF = data.queryrows.map(row => parseFloat(row.promedioF));

		const ctx1 = document.getElementById('chartleft').getContext('2d');
	
		(async function() {
			const datapie = {
			labels: asignaturas,
			datasets: [{
				data: promediosF,
				backgroundColor: colorAleatorio,
			}]
			};
			new Chart(ctx1,
					{
					type: 'polarArea',
					data: datapie,
					options: {
						responsive: true,
						plugins: {
							legend: {
								position:'right',
								display: true,
								title: {
									display:true,
									text:'<?php echo $langs->trans("piestudentlabel")?>',
								}
							},
						},
						scales:{
							r: {
								max:10,
								min: 0,
								ticks: {
									stepSize: 0.5
								}
							},
						},
					},
					}
				);
			})();
	});

	$.ajax({
		method: "POST",
		url: "<?php echo DOL_URL_ROOT.'/custom/college/ajaxchart.php?action=getPeriodGrades&token='.newToken();?>",
		data:{idStudent:<?php echo $id;?>},
	}).done(function(json_data) {
		const data = JSON.parse(json_data);
		trimestre = data.map(row => row.trim);
		promediototal = data.map(row => parseFloat(row.prom));

		const ctx2 = document.getElementById('chartright').getContext('2d');

		/** CHART RIGHT */
		(async function() {
			const data = {
				labels: trimestre,
				datasets: [{
					type: 'bar',
					label: '<?php echo $langs->trans("barstudentlabel")?>',
					data: promediototal,
					borderColor: 'rgb(255, 99, 132)',
					backgroundColor: 'rgba(255, 99, 132, 0.2)'
				},{
					type: 'line',
					label: '<?php echo $langs->trans("linestudentlabel")?>',
					data: promediototal,
					fill: true,
					borderColor: 'rgb(54, 162, 235)'
				}]
			};

			new Chart(ctx2,
			{
				type: 'scatter',
				data: data,
				options: {
					responsive: true,
					scales: {
					y: {
						beginAtZero: true,
						max:10
					}
					}
				}
			}
		);
		})();
	});

});
</script>
<?php
if(empty($notesdata)){
	print info_admin( 	  	
		$langs->trans("nodatafound"),
		$infoonimgalt = 0,
		$nodiv = 0,
		$admin = '0',
		$morecss = 'warning',
		$textfordropdown = '' 
	);
}



print '<div class="fichecenter"><div class="fichehalfleft">';
print '<div class="clearboth"></div>';

print '<canvas id="chartleft"></canvas>';

print '</div><div class="fichehalfright">';
print '<canvas id="chartright"></canvas>';

print '</div></div>';
print '<div class="underbanner clearboth"></div>';

}
print '</div>';
// End of page
llxFooter();
$db->close();