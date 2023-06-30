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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';

dol_include_once('/college/class/surveytype.class.php');
dol_include_once('/college/lib/college_surveytype.lib.php');
dol_include_once('/college/class/notes.class.php');
dol_include_once('/college/lib/college_notes.lib.php');
dol_include_once('/college/class/students.class.php');
dol_include_once('/college/class/subject.class.php');
dol_include_once('/college/class/classrooms.class.php');
dol_include_once('/college/class/periods.class.php');

/** OBJECTS */ //
$form = new Form($db);
$object = new Surveytype($db);
$objsurvey = $object->fetchAllTypes();

$objectperiods = new Periods($db);

// Load translation files required by the page ancla
$langs->loadLangs(array("college@college"));
llxHeader("", 
$langs->trans("ModuleCollegeName"));

$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->hasRight('college', 'survey', 'read');
	$permissiontoadd = $user->hasRight('college', 'survey', 'write');
	$permissiontodelete = $user->hasRight('college', 'survey', 'delete');
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
	$permissiontodelete = 1;
}
if (!isModEnabled("college")) {
	accessforbidden('Module college not enabled');
}
if (empty($permissiontoadd)) {
	print info_admin 	( 	  	
		$langs->trans("msjSurveywarninginfo").'<a style="color: #605f5f;" href="'.DOL_URL_ROOT.'/custom/college/students_list.php"> '.$langs->trans("Studentss").'</a>',
			$infoonimgalt = 0,
			$nodiv = 0,
			$admin = '0',
			$morecss = 'hideonsmartphone error',//More CSS ('', 'warning', 'error') 
			$textfordropdown = '403: Forbidden' 
	);
    accessforbidden('NotEnoughPermissions', 0, 1);
}
$action = GETPOST('action', 'aZ09');
// Load translation files required by the page
$langs->loadLangs(array("college@college"));
print load_fiche_titre($langs->transnoentitiesnoconv("Surveytypeindex"), '', 'object_'.$object->picto);

?>
<style>
.grafsection{
  background-color: #FFF;
  background-image: url('./img/chart-areaspline.svg');
  background-repeat: no-repeat;
  background-position: center;
  background-size: contain;
}
</style>
<div class="fichehalfleft">
<div class="div-table-responsive-no-min">
<table class="centpercent">	
<tr>
<td>
</td>
</tr>
<tr>
<td>
<?php
print '<div class="contenedorloadingtext p-10">'; //ancla
print $form->selectarray(
    'select_chart_type',
    [['key' => '0','label'=>$langs->trans("chart_a").' <i class="fa fa-bar-chart" aria-hidden="true"></i>'],
	['key' => '1','label'=>$langs->trans("chart_b").' <i class="fa fa-pie-chart" aria-hidden="true"></i>'],
	['key' => '2','label'=>$langs->trans("chart_c").' <i class="fa fa-bar-chart" aria-hidden="true"></i>']],
    $id = '-1',
  	$show_empty = $langs->trans("select_chart_type"),
  	$key_in_label = 0,
  	$value_as_key = 0,
  	$moreparam = '',
  	$translate = 0,
  	$maxlen = 0,
  	$disabled = 0,
  	$sort = '',
  	$morecss = 'centpercent',
  	$addjscombo = 1,
  	$moreparamonempty = '',
  	$disablebademail = 0,
  	$nohtmlescape = 0
);
/*Loading chart*/
print '<img style="display: none;" id="loaderchart" class="valignmiddle prl-10" src="../../custom/college/img/spinner.gif" height="20px">';
print '</div>';
?>
</td>
</tr>
</table>
</div>
</div>
<div class="fichehalfright">
<div class="div-table-responsive-no-min">
<table class="centpercent">	
<tr>
<td>
<?php
print '<div class="contenedorloadingtext p-10">';
print $form->selectarray(
    'select_survey_type',
    $objsurvey,
    $id = '-1',
  	$show_empty = $langs->trans("select_survey_type"),
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
/*Loading*/
print '<img style="display: none;" id="loader" class="valignmiddle prl-10" src="../../custom/college/img/spinner.gif" height="20px">';
print '</div>';
?>
</td>
</tr>
<tr>
<td>
<?php
print '<div class="fichecenter">';
print '<div class="contenedorloadingtext p-10">';
print $form->selectarray(
    'select_student',
    '',
    $id = '-1',
  	$show_empty = $langs->trans("select_student"),
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
print '</div>';
?>
</td>
</tr>
<tr>
<td>
<?php
print '<div class="contenedorloadingtext p-10">';
print $form->selectarray(
    'select_subject',
    '',
    $id = '-1',
  	$show_empty = $langs->trans("select_subject"),
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
/*Loading*/
print '<img style="display: none;" id="loader" class="valignmiddle prl-10" src="../../custom/college/img/spinner.gif" height="20px">';
print '</div>';
?>
</td>
</tr>
<tr>
<td>
<?php
print '<div class="contenedorloadingtext p-10">';
print $form->selectarray(
    'select_periods',
    $objectperiods->getAllPeriods(),
    $id = '-1',
  	$show_empty = $langs->trans("select_periods"),
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
print '</div>';
?>
</td>
</tr>
</table>
</div>
</div>
<br>

<?php
print '<div class="underbanner clearboth"></div>';
//SECTION GRAF
print '<div class="fichecenter">';
print '<div class="div-table-responsive-no-min">';
print '<table class="centpercent grafsection">';
print '<tr>';
print '<td>';
print '<div id="graficos"></div>';
print '</td>';
print '</tr>';
print '</table>';
print '</div>';
print '</div>';

// End of page
llxFooter();
$db->close();
?>
<!-- SCRIPT -->
<script>

jQuery(document).ready(function() {});
	
	var idChartType = 0;
	var idSurvey = 0;
	var idStudent = 0;

	var json = [{}];

	/** Seleccionar tipo de grafico */ //ancla
	$('#select_chart_type').on('change',function(e){
		e.stopPropagation();
		var option = $(this).find("option:selected");
		idChartType = option.val();
		$('[data-select2-id="select_student"]').val('-1').trigger("change");
	});

	/** Seleccionamos typo de encuesta y nos filtra los estudiantes que tienen dichas encuesta */
	$('#select_survey_type').on('change',function(e){
		e.stopPropagation();
		$('#loader').show();
		var option = $(this).find("option:selected");
		idSurvey = option.val();
		$.getJSON( "./ajax.php?action=getstudentsperreport",{idSurveytype:idSurvey, token:"<?php echo newToken() ;?>"}, 
			function(dataobj) {
				$('[data-select2-id="select_student"]').find('option').not(':first').remove();
				$('[data-select2-id="select_subject"]').find('option').not(':first').remove();
				//$('[data-select2-id="select_student"]').val('-1').trigger("change");
				//$('[data-select2-id="select_student"]').find('option').not(':first').empty();
				$('[data-select2-id="select_student"]').last();
				$('[data-select2-id="select_periods"]').val('-1').trigger("change");
				
			var filteredArrayStudent = [];
			filteredArrayStudent = Array.from(new Set(dataobj.map(function(item) {
				return item.fk_student + '|' + item.label_student;
			}))).map(function(item) {
				var values = item.split('|');
				return {
					id: values[0],
					text: values[1]
				};
			});
			$('[data-select2-id="select_student"]').select2({
				placeholder: {
					id: '-1',//-1
					text: '<?php echo $langs->trans("select_student")?>'
				},
				tags: true,
				allowClear: true,
				data: filteredArrayStudent
			});

		}).done(function(dataobj){
			 json = dataobj;
		}).always(function(){
			$('#loader').hide();
		});
	});

	$('#select_student').on('change',function(e){
		e.stopPropagation();
		$('#loaderchart').show();
		var option = $(this).find("option:selected");
		idStudent = option.val();
		var result = json.filter(obj => obj.fk_student == idStudent);
		$('[data-select2-id="select_subject"]').find('option').not(':first').remove();
		//$('[data-select2-id="select_subject"]').val('-1').trigger("change");
		//$('[data-select2-id="select_periods"]').val('-1').trigger("change");
		var filteredArraySubject = Array.from(new Set(result.map(function(item) {
			return item.fk_subject + '|' + item.label_subject;
		}))).map(function(item) {
			var values = item.split('|');
			return {
				id: values[0],
				text: values[1]
			};
		});
		$('[data-select2-id="select_subject"]').select2({
			placeholder: {
				id: '-1',//-1
				text: '<?php echo $langs->trans("select_subject")?>'
			},
			tags: true,
			allowClear: false,
			data: filteredArraySubject
		});
		
		//Dibujamos el Grafico
		drawChart(result);
		
	});

	/** Carga las encuestas realizadas por cada asignatura en el select subject */
	$('#select_subject').on('change', function(e){
		e.stopPropagation();
		//$('[data-select2-id="select_periods"]').val('-1').trigger("change");
		var option = $(this).find("option:selected");
		idSubjejct = option.val();
		var result = json.filter(obj => obj.fk_subject == idSubjejct);

		//Dibujamos el Grafico
		drawChart(result);
		
	});

	/** Carga las encuestas realizadas por cada periodos */
	$('#select_periods').on('change', function(e){
		e.stopPropagation();
		idPeriods = $(this).val();
		var result = json.filter(obj => obj.fk_trimestre == idPeriods);

		/*var filteredArrayPeriods = result.map(function(item) {
			return item.fk_trimestre;
		});*/

		//$('[data-select2-id="select_periods"]').val(filteredArrayPeriods[0]).trigger("change");//filteredArrayPeriods[0]

		//Dibujamos el Grafico
		drawChart(result);

	});

	async function drawChart(data){
		let result;
  		try {
			/** CHART */
			result = await $.ajax({
				method: "POST",
				url: "<?php echo DOL_URL_ROOT.'/custom/college/js/graficos.js.php?action=drawchara&token='.newToken();?>",
				data:{datos:data,tipodegrafico:idChartType},
			}).done(function(html) {
				$('#loaderchart').hide();
				$("#graficos").html(" ");
			}).always(function(html){
				$("#graficos").append(html);
				$('#loaderchart').hide();
			});	
			return result;
		}catch (error) {
			console.error(error)
		}
	}
</script>
<?php
