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
dol_include_once('/custom/college/class/questions.class.php');

// Load translation files required by the page
$langs->loadLangs(array("college@college"));

$action = GETPOST('action', 'aZ09');
$datos  = GETPOST('datos', 'alphanohtml');
$tipodegrafico = GETPOST('tipodegrafico', 'int');

global $db;
$objquestion = new Questions($db);
$queryquestions = $objquestion->getAllQuestions();

if($action == 'drawchara' && !empty($datos) ){

	if($tipodegrafico==0){//ancla
		?>
		<script>

			var obj = <?php echo json_encode($datos);?>;
			// Obtener los valores y nombres del objeto de datos
			var valuesCount = {};
			var names = [];

			var resultarrquestions = [];
			var resultarranswer = [];

			// Iterar sobre los objetos de datos
			for (const item of obj) {
				var parsedData = JSON.parse(item.data);

				// Iterar sobre los datos de cada objeto
				for (const d of parsedData) {
					var name = d.name;
					var values = d.values;
					
					// Verificar si el nombre ya existe en el objeto valuesCount
					if (valuesCount[name]) {
						// Iterar sobre los valores y aumentar su contador correspondiente
						for (const value of values) {
							valuesCount[name][value] = (valuesCount[name][value] || 0) + 1;
						}
					} else {
						// Agregar el nombre al objeto valuesCount y crear el contador correspondiente
						valuesCount[name] = {};
						for (const value of values) {
							valuesCount[name][value] = 1;
						}
					}
					// Agregar el nombre a la lista de nombres
					names.push(name);
				}
			}

			// Obtener los valores y nombres únicos
			var uniqueNames = [...new Set(names)];
			var uniqueValues = [...new Set(names.map(name => Object.keys(valuesCount[name])).flat())];

			$.getJSON("./ajaxchart.php?action=getAllQuestions&token=<?php echo newToken();?>",function(json){
				for (let index = 0; index < uniqueNames.length; index++) {
					var result = json.filter(word => word.id == uniqueNames[index]);
					resultarrquestions.push(result[0].label)
				}

				for (let index = 0; index < uniqueValues.length; index++) {
					var resultvalue = json.filter(word => word.id == uniqueValues[index]);
					if (resultvalue[0]===undefined) {
						resultarranswer.push(uniqueValues[index])
					}
					if (resultvalue[0]!==undefined) {
						resultarranswer.push(resultvalue[0].label)
					}
				}

				// Crear el gráfico
				const ctx = document.getElementById('chart').getContext('2d');
				new Chart(ctx, {
					type: 'bar',
					data: {
						labels: resultarrquestions, // Nombres en el eje x // resultarrquestions
						datasets: uniqueValues.map((value,i) => ({
							barThickness: 'flex',
							label: resultarranswer[i],
							data: uniqueNames.map(name => valuesCount[name][value] || 0), // Valores en el eje y
							backgroundColor: `rgba(${getRandomValue()}, ${getRandomValue()}, ${getRandomValue()}, 0.7)`, // Color de las barras
							borderColor: `rgba(${getRandomValue()}, ${getRandomValue()}, ${getRandomValue()}, 1)`, // Color del borde de las barras
							borderWidth: 1 // Ancho del borde de las barras
						}))
					},
					options: {
						responsive:true,
						maintainAspectRatio:true,
						onResize:2,
						indexAxis: 'y', // Mostrar el eje y como el eje de índice
						scales: {
							x: {
								beginAtZero:true, // Iniciar el eje x en cero
							},
							y: {
								display:false,
								beginAtZero: true,
							},
						},
						plugins:{
							legend:{
								position:'left',
								align:'center',
							},
							title:{
								display:true,
								text:'Total de respuestas por pregunta. Todas las asignaturas.'
							},
							subtitle: {
								display: true,
								text: 'Total de opciones escogidas por consulta.'
							},
						},
					},
				});
			
			});

			// Función para generar un valor aleatorio entre 0 y 255
			function getRandomValue() {
				return Math.floor(Math.random() * 256);
			}

		</script>
    <canvas id="chart" ></canvas>
		
		<?php
	}elseif($tipodegrafico==1){//ancla
		?>
		<script>

			var dataquestion = <?php echo json_encode($queryquestions);?>; //Este deberia ser revisado para ser reutilizado
			var obj = <?php echo json_encode($datos);?>;
			// Obtener las preguntas y respuestas agrupadas por preguntas
			var preguntas = [];
			var respuestas = [];
			
			obj.forEach(item => {
				var preguntasRespuestas = JSON.parse(item.data);
				preguntasRespuestas.forEach(preguntaRespuesta => {
					var pregunta = preguntaRespuesta.name;
					var respuesta = preguntaRespuesta.values;
					var preguntaIndex = preguntas.indexOf(pregunta);
					if (preguntaIndex === -1) {
						preguntas.push(pregunta);
						respuestas.push(respuesta);
					} else {
						respuestas[preguntaIndex] = respuestas[preguntaIndex].concat(respuesta);
					}
				});
			});

			var resultarranswer = [];
			
			// Crear un gráfico de tipo pie para cada pregunta
			preguntas.forEach((pregunta, index) => {

				var labelquestions = dataquestion.filter((q)=>q.id == pregunta).map(x=>x.label);


				if (document.getElementById((index + 1)) !== null ) {
					destroy()//Destruyo los divisores que son contenedores de los graficos por cada pregunta.
				}else {
					create(index);//creo los divisores que tendran los graficos por cada pregunta.
					//replaceit(index);//reemplazamos los canvas con la nueva informacion. //sin uso
				}

				var uniqueRespuestas = Array.from(new Set(respuestas[index])); // Eliminar respuestas duplicadas
				var countRespuestas = uniqueRespuestas.map(respuesta => respuestas[index].filter(r => r === respuesta).length);
				var totalRespuestas = respuestas[index].length;
				var porcentajesRespuestas = countRespuestas.map(c => ((c / totalRespuestas) * 100).toFixed(2));

				var ctx = document.getElementById((index + 1)).getContext('2d');
				// Crear el gráfico
				new Chart(ctx, {
					type: 'pie',
					data: {
						labels: uniqueRespuestas.map((respuesta, index) => {
							var resultvalue = dataquestion.filter((q)=>q.id == respuesta);
							if (resultvalue[0]===undefined) {
								return uniqueRespuestas[index];
							}
							if (resultvalue[0]!==undefined) {
								return resultvalue[0].label;
							}
						}),
						datasets: [{
							data: porcentajesRespuestas,
							backgroundColor: getRandomColors(porcentajesRespuestas.length),
							borderColor: '#ffffff',
							borderWidth: 1
						}]
					},
					options: {
						responsive: false,
						plugins: {
							title: {
								display: true,
								text: labelquestions,
								font: {
                  family: 'Arial, Helvetica, Verdana',
                  size: 20,
                  weight: 'bold',
                  lineHeight: 1.2,
                },
                padding: {top: 20, left: 0, right: 0, bottom: 20},
							},
							legend: {
								position: 'bottom',
							},
						},
					},
				});
				

			});

			function create(indexid){
				const cv = document.createElement("canvas");
				cv.id = indexid + 1;
				cv.classList.add('item-chart');
				document.getElementById("dynamicgrafs").appendChild(cv);
			}
			/*function replaceit(indexid){
				var theoriginal = document.getElementById('dynamicgrafs');
				var thereplacement = document.createElement('canvas');
				thereplacement.id = indexid + 1;
				theoriginal.replaceChild(thereplacement, theoriginal.lastChild);
			}*/
			function destroy(){
				var olddata=document.getElementById("dynamicgrafs").lastChild;
				document.getElementById("dynamicgrafs").removeChild(olddata);
			}

			// Función para generar colores aleatorios
			function getRandomColors(numColors) {
				var colors = [];
				for (var i = 0; i < numColors; i++) {
					var color = '#' + Math.floor(Math.random() * 16777215).toString(16);
					colors.push(color);
				}
				return colors;
			}

		</script>
		<style>
			.chart-container {
        display: inline-block;
        margin: 10px;
      }
      .item-chart{
        width: 500px;
        margin: 10px;
      }
    </style>
 	  <div id="dynamicgrafs" class="chart-container" ></div>
		
		<?php

	}elseif($tipodegrafico==2){//ancla
		?>
		<script>
		var dataquestion = <?php echo json_encode($queryquestions);?>;
		var obj = <?php echo json_encode($datos);?>;
		// Crear un objeto para almacenar los datos agrupados
		var groupedData = {};
		var objAnswers = {};

		// Iterar sobre los datos y agrupar por asignatura y preguntas
		obj.forEach(item => {
		var subject = item.label_subject;
		var parsedData = JSON.parse(item.data);

		

		parsedData.forEach((obj,i) => {
			var question = obj.name;
			var values = obj.values.length;

			if (!groupedData[subject]) {
			groupedData[subject] = {};
			objAnswers[subject] = {};
			}
			
			if (groupedData[subject][question] && objAnswers[subject][question]) {
			groupedData[subject][question] += values;
			objAnswers[subject][question] += obj.values
			} else {
			groupedData[subject][question] = values;
			objAnswers[subject][question] = obj.values
			}
		});

		});

		var valoresDeRespuestas = Object.values(objAnswers);
		//console.log(valoresDeRespuestas)

		// Obtener los datos necesarios para el gráfico
		var subjects = Object.keys(groupedData);
		var questions = Array.from(new Set(obj.flatMap(item => JSON.parse(item.data)).map(obj => obj.name)));

		var datasets = [];

		subjects.forEach(subject => {
		var values = questions.map(question => groupedData[subject][question] || 0);
		datasets.push({
			label: subject,
			data: values,
			backgroundColor: getRandomColors(questions.length),
			borderWidth: 1
		});
		});
		
		//Valores para el tooltip
		var tooltip = [];
		subjects.forEach(subject => {
			const values = questions.map(question => objAnswers[subject][question] || 0);
			tooltip.push(values)
		});
		

		// Función para generar colores aleatorios
		function getRandomColors(numColors) {
		const colors = [];
		for (let i = 0; i < numColors; i++) {
			var color = `rgba(${Math.floor(Math.random() * 256)}, ${Math.floor(Math.random() * 256)}, ${Math.floor(Math.random() * 256)}, 0.8)`;
			colors.push(color);
		}
		return colors;
		}

		var labelquestions = [];

		
	
		// Crear el gráfico de doughnut agrupado
		new Chart(document.getElementById('groupedSubjectChart'), {
		type: 'bar',
		data: {
			labels: questions.map((z,i)=> {
				var d = dataquestion.filter((q)=>q.id==z).map(x=>x.label);
				return d;
			}),
			datasets: datasets,
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			//indexAxis: 'y',
			plugins:{
			tooltip:{
				enabled: true,
				position: 'nearest',
				//external: externalTooltipHandler, 
				callbacks: {
				label: function(context) {
					let label = context.dataset.label || '';
					if (label) {
								label += ': ';
							}
					if (context.parsed.y !== null) {
						label += tooltip[context.datasetIndex][context.dataIndex].map((respuesta,index) => {
							var resultvalue = dataquestion.filter((q)=>q.id == respuesta).map(x=>x.label);
							if (resultvalue[0]===undefined) {
								return tooltip[context.datasetIndex][context.dataIndex][index];
							}
							if (resultvalue!==undefined) {
								return resultvalue;
							}
						});
					}
					return label;
				}
				},
			},
			},
			scales: {
				x: {
					display:false,
				},
				y: {
					display:true,
				},
			},
		}
		});
		</script>
    <canvas id="groupedSubjectChart"></canvas>
		<?php
	}else{}//Para otro graf


}