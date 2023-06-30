<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2022 SuperAdmin
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
 * 	\defgroup   college     Module College
 *  \brief      College module descriptor.
 *
 *  \file       htdocs/college/core/modules/modCollege.class.php
 *  \ingroup    college
 *  \brief      Description and activation file for module College
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module College
 */
class modCollege extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 444074; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'college';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "hr";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleCollegeName' not found (College is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description, used if translation string 'ModuleCollegeDesc' not found (College is name of module).
		$this->description = "CollegeDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "CollegeDescription";

		// Author
		$this->editor_name = 'Jofre Diego Sebastian';
		$this->editor_url = '';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.1.5';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where COLLEGE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'college_front@college';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				'/college/css/college.css.php',
                //'/college/css/spectrecss/spectre.min.css',
                //'/college/css/spectrecss/spectre-exp.min.css',
                //'/college/css/spectrecss/spectre-icons.min.css',
                '/college/css/colorbox.css',
                //'/college/css/select2.min.css',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				'/college/js/college.js.php',
                '/college/js/moment.js',
                '/college/js/locale/es.js',
                '/college/js/jquery.colorbox-min.js',
                //'/college/js/select2.min.js',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				   'data' => array(
			           //'somecontext1', 
                       //'somecontext2'
				   ),
				   'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/college/temp","/college/subdir");
		$this->dirs = array("/college/temp");

		// Config pages. Put here list of php page, stored into college/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@college");

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array(
    'always1'=>'modSociete',
    'always2'=>'modFacture',
    'always3'=>'modImport',
    'always4'=>'modExport',
    'always5'=>'modFckeditor',
    'always6'=>'modBookmark',
    ); 
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

		// The language file dedicated to your module
		$this->langfiles = array("college@college");

		// Prerequisites
		$this->phpmin = array(5, 6); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'CollegeWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('COLLEGE_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('COLLEGE_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->college) || !isset($conf->college->enabled)) {
			$conf->college = new stdClass();
			$conf->college->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
        $this->tabs[] = array('data'=>'thirdparty:+tabname:StudentThirdTab:college@college:1:/college/students_notes_list.php?id=__ID__'); 
		$this->tabs[] = array('data'=>'students@college:+tabname:StudentNotesTab:college@college:1:/college/students_notes.php?id=__ID__');
		$this->tabs[] = array('data'=>'students@college:+tabname:StudentFilterNotesYearTab:college@college:1:/college/students_filter_notes_year.php?id=__ID__');
		$this->tabs[] = array('data'=>'students@college:+tabname:StudentPerformanceTab:college@college:1:/college/students_performance.php?id=__ID__');
		$this->tabs[] = array('data'=>'subject@college:+tabname:SubjectNotesTab:college@college:1:/college/subject_notes.php?id=__ID__');

		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@college:$user->rights->college->read:/college/mynewtab1.php?id=__ID__'); // To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@college:$user->rights->othermodule->read:/college/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		$this->dictionaries = array();
		/* Example:
		$this->dictionaries=array(
			'langs'=>'college@college',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."table1", MAIN_DB_PREFIX."table2", MAIN_DB_PREFIX."table3"),
			// Label of tables
			'tablib'=>array("Table1", "Table2", "Table3"),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),
			// Sort order
			'tabsqlsort'=>array("label ASC", "label ASC", "label ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("code,label", "code,label", "code,label"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("code,label", "code,label", "code,label"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label", "code,label", "code,label"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid", "rowid", "rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->college->enabled, $conf->college->enabled, $conf->college->enabled)
		);
		*/

		// Boxes/Widgets
		// Add here list of php file(s) stored in college/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			0 => array(
			    'file' => 'collegewidget1.php@college',
			    'note' => 'Widget provided by College',
			    'enabledbydefaulton' => 'Home',
			),
			1 => array(
				'file' => 'collegewidget2.php@college',
				'note' => 'Widget provided by College',
				'enabledbydefaulton' => 'Home',
			),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/college/class/student.class.php',
			//      'objectname' => 'Student',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->college->enabled',
			//      'priority' => 50,
			//  ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->college->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->college->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		
		//NOTES
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Notes'; // Permission label
		$this->rights[$r][4] = 'notes';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->notes->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Notes'; // Permission label
		$this->rights[$r][4] = 'notes';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->notes->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Notes'; // Permission label
		$this->rights[$r][4] = 'notes';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->notes->delete)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Generate note file in xlsx'; // Permission label
		$this->rights[$r][4] = 'notes';
		$this->rights[$r][5] = 'generate_xlsx'; // In php code, permission will be checked by test if ($user->rights->college->notes->generate_xlsx)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Generate xlsx file of all my notes'; // Permission label
		$this->rights[$r][4] = 'notes';
		$this->rights[$r][5] = 'generate_all_xlsx'; // In php code, permission will be checked by test if ($user->rights->college->notes->generate_all_xlsx)
		$r++;
		
		//INSCRIPTIONS
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Inscriptions'; // Permission label
		$this->rights[$r][4] = 'inscriptions';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->inscriptions->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Inscriptions'; // Permission label
		$this->rights[$r][4] = 'inscriptions';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->inscriptions->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Inscriptions'; // Permission label
		$this->rights[$r][4] = 'inscriptions';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->inscriptions->delete)
		$r++;
    
    //STUDENT
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Student'; // Permission label
		$this->rights[$r][4] = 'students';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->students->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Student'; // Permission label
		$this->rights[$r][4] = 'students';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->students->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Student'; // Permission label
		$this->rights[$r][4] = 'students';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->students->delete)
		$r++;
    
    //SUBJECT
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Subject'; // Permission label
		$this->rights[$r][4] = 'subject';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->subject->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Subject'; // Permission label
		$this->rights[$r][4] = 'subject';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->subject->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Subject'; // Permission label
		$this->rights[$r][4] = 'subject';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->subject->delete)
		$r++;
    
    //CLASSROOMS
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Classrooms'; // Permission label
		$this->rights[$r][4] = 'classrooms';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->classrooms->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Classrooms'; // Permission label
		$this->rights[$r][4] = 'classrooms';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->classrooms->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Classrooms'; // Permission label
		$this->rights[$r][4] = 'classrooms';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->classrooms->delete)
		$r++;
    
    //PERIODS
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Periods'; // Permission label
		$this->rights[$r][4] = 'periods';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->periods->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Periods'; // Permission label
		$this->rights[$r][4] = 'periods';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->periods->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Periods'; // Permission label
		$this->rights[$r][4] = 'periods';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->periods->delete)
		$r++;
    
    //ASSYS
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Assys'; // Permission label
		$this->rights[$r][4] = 'assys';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->assys->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Assys'; // Permission label
		$this->rights[$r][4] = 'assys';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->assys->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Assys'; // Permission label
		$this->rights[$r][4] = 'assys';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->assys->delete)
		$r++;
        //SHOW WIDGET
        $this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Show widget objects'; // Permission label
		$this->rights[$r][4] = 'inscriptions';
		$this->rights[$r][5] = 'widget'; // In php code, permission will be checked by test if ($user->rights->college->inscriptions->widget)
		$r++;
        //MSJPRINCIPALPAGE
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Write messaje in dashboard'; // Permission label
		$this->rights[$r][4] = 'msjpagetop';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->msjpagetop->write)
		$r++;
		//ALL LISTS
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'View all data in all lists'; // Permission label
		$this->rights[$r][4] = 'readalllist';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->readalllist->read)
		$r++;
		//UPLOAD FILE
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'File upload permission'; // Permission label
		$this->rights[$r][4] = 'uploadfile';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->uploadfile->write)
		$r++;
		//QUESTIONS questions
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Questions'; // Permission label
		$this->rights[$r][4] = 'questions';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->questions->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Write objects of Questions'; // Permission label
		$this->rights[$r][4] = 'questions';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->questions->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Questions'; // Permission label
		$this->rights[$r][4] = 'questions';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->questions->delete)
		$r++;
		//REPORT questions
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Report'; // Permission label
		$this->rights[$r][4] = 'report';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->report->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Write objects of Report'; // Permission label
		$this->rights[$r][4] = 'report';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->report->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Report'; // Permission label
		$this->rights[$r][4] = 'report';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->report->delete)
		$r++;
		//SURVEY TYPE
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of Survey Type'; // Permission label
		$this->rights[$r][4] = 'survey';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->college->survey->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of Survey Type'; // Permission label
		$this->rights[$r][4] = 'survey';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->college->survey->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of Survey Type'; // Permission label
		$this->rights[$r][4] = 'survey';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->college->survey->delete)
		$r++;

    
		/* END MODULEBUILDER PERMISSIONS */
    
		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
    
		$this->menu[$r++] = array(
			'fk_menu'=>'', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'top', // This is a Top menu entry
			'titre'=>'ModuleCollegeName',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'college',
			'leftmenu'=>'',
			'url'=>'/college/collegeindex.php',
			'langs'=>'college@college', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->college->enabled', // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled.
			'perms'=>'1', // Use 'perms'=>'$user->rights->college->student->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		/* END MODULEBUILDER TOPMENU */
		/* BEGIN MODULEBUILDER LEFTMENU STUDENT
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=college',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Left menu entry
			'titre'=>'Student',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'college',
			'leftmenu'=>'student',
			'url'=>'/college/collegeindex.php',
			'langs'=>'college@college',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->college->enabled',  // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->college->student->read',			                // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=student',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'List_Student',
			'mainmenu'=>'college',
			'leftmenu'=>'college_student_list',
			'url'=>'/college/student_list.php',
			'langs'=>'college@college',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->college->enabled',  // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->rights->college->student->read',			                // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=student',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'New_Student',
			'mainmenu'=>'college',
			'leftmenu'=>'college_student_new',
			'url'=>'/college/student_card.php?action=create',
			'langs'=>'college@college',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->college->enabled',  // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->rights->college->student->write',			                // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		*/

	/*HOME MENU*/
	$this->menu[$r++]=array(
		'fk_menu'=>'fk_mainmenu=home',
		'type'=>'left',
		'titre'=>'ModuleCollegeName',
	    'prefix' => img_picto('', 'fa-th-large', 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'college',
		'leftmenu'=>'ModuleCollegeName',
		'url'=>'/college/collegeindex.php',
		'langs'=>'college@college',
		'position'=>1100+$r,
		'enabled'=>'$conf->college->enabled',
		'user'=>0,
	);
	$this->menu[$r++]=array(
		'fk_menu'=>'fk_mainmenu=home',
		'type'=>'left',
		'titre'=>'ListMenuHomeNotes',
		'prefix' => img_picto('', 'fa-table', 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'college',
		'leftmenu'=>'college_notes',
		'url'=>'/college/notes_list.php',
		'langs'=>'college@college',
		'position'=>1100+$r,
		'enabled'=>'$conf->college->enabled',
		'perms'=>'$user->rights->college->notes->read',
		'user'=>0,
	);
	$this->menu[$r++]=array(
		'fk_menu'=>'fk_mainmenu=home',
		'type'=>'left',
		'titre'=>'menuhomesurvey',
		'prefix' => img_picto('', 'fa-signal', 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'college',
		'leftmenu'=>'college_survey',
		'url'=>'/college/collegesurvey.php',
		'langs'=>'college@college',
		'position'=>1100+$r,
		'enabled'=>'$conf->college->enabled',
		'perms'=>'$user->rights->college->survey->write || $user->rights->college->survey->read || $user->rights->college->questions->write || $user->rights->college->questions->read || $user->rights->college->report->write || $user->rights->college->report->read',
		'user'=>0,
	);
	
	
    /*INSCRIPTION MENU*/
		$this->menu[$r++]=array(
		    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		    'fk_menu'=>'fk_mainmenu=college',
		    // This is a Left menu entry
		    'type'=>'left',
		    'titre'=>'ListInscriptions',
        	'prefix' => img_picto('', 'object_inscriptions@college', 'class="paddingright mrl-10 pictofixedwidth valignmiddle"'),
		    'mainmenu'=>'college',
		    'leftmenu'=>'college_inscriptions',
		    'url'=>'/college/inscriptions_list.php',
		    // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		    'langs'=>'college@college',
		    'position'=>1100+$r,
		    // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
		    'enabled'=>'$conf->college->enabled',
		    // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
		    'perms'=> '$user->rights->college->inscriptions->read',
		    'target'=>'',
		    // 0=Menu for internal users, 1=external users, 2=both
		    'user'=>2,
		);
		$this->menu[$r++]=array(
		    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		    'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_inscriptions',
		    // This is a Left menu entry
		    'type'=>'left',
		    'titre'=>'NewInscriptions',
        	//'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		    'mainmenu'=>'college',
		    'leftmenu'=>'college_inscriptions',
		    'url'=>'/college/inscriptions_card.php?action=create',
		    // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		    'langs'=>'college@college',
		    'position'=>1100+$r,
		    // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
		    'enabled'=>'$conf->college->enabled',
		    // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
		    'perms'=>'$user->rights->college->inscriptions->write',
		    'target'=>'',
		    // 0=Menu for internal users, 1=external users, 2=both
		    'user'=>2
		);
    
		
		/*PERIODS MENU*/
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'ListPeriods',
            'prefix' => img_picto('', 'object_periods@college', 'class="paddingright mrl-10 pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_periods',
            'url'=>'/college/periods_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->periods->read',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_periods',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewPeriods',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_periods',
            'url'=>'/college/periods_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->periods->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );
		
        /*STUDENT MENU*/
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'ListStudent',
            'prefix' => img_picto('', 'object_students@college', 'class="paddingright mrl-10 pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_students',
            'url'=>'/college/students_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->students->read',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_students',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewStudent',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_students',
            'url'=>'/college/students_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->students->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );
        
        
        /*MATERIAS MENU*/
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'ListSubject',
            'prefix' => img_picto('', 'object_subject@college', 'class="paddingright mrl-10 pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_subject',
            'url'=>'/college/subject_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->subject->read',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_subject',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewSubject',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_subject_create',
            'url'=>'/college/subject_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->subject->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_subject',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewAddsSubject',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_subject_create',
            'url'=>'/college/subject_card.php?action=createadds',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->subject->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );
        
        /*CLASSROOOM MENU*/
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'ListClassrooms',
            'prefix' => img_picto('', 'object_classrooms@college', 'class="paddingright mrl-10 pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_classrooms',
            'url'=>'/college/classrooms_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->classrooms->read',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_classrooms',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewClassrooms',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_classrooms',
            'url'=>'/college/classrooms_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->classrooms->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );
        
        /*NOTES MENU*/
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'ListNotes',
            'prefix' => img_picto('', 'object_notes@college', 'class="paddingright mrl-10 pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_notes',
            'url'=>'/college/notes_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->notes->read',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_notes',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewNotesFromList',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_notes_list',
            'url'=>'/college/notes_card.php?action=createlist',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->notes->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_notes',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewNote',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_notes_list',
            'url'=>'/college/notes_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->notes->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );
		/**NOTES XLSX */
		$this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_notes',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'MyNotes',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_notes_list',
            'url'=>'/college/notes_card.php?action=mynotes',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->notes->generate_xlsx',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );


      /*ASSYS MENU*/
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'ListAssys',
            'prefix' => img_picto('', 'object_assys@college', 'class="paddingright mrl-10 pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_assys',
            'url'=>'/college/assys_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->assys->read',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_assys',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewAssys',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_assys',
            'url'=>'/college/assys_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'college@college',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->college->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->college->enabled',
            // Use 'perms'=>'$user->rights->college->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->college->assys->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );

		/*INFORME MENU*/
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=college',
			'type'=>'left',
			'titre'=>'menuhomesurvey',
			'prefix' => img_picto('', 'fa-signal', 'class="paddingright mrl-10 pictofixedwidth valignmiddle"'),
			'mainmenu'=>'college',
			'leftmenu'=>'college_survey',
			'url'=>'/college/collegesurvey.php',
			'langs'=>'college@college',
			'position'=>1100+$r,
			'enabled'=>'$conf->college->enabled',
			'perms'=>'$user->rights->college->survey->write || $user->rights->college->survey->read || $user->rights->college->questions->write || $user->rights->college->questions->read || $user->rights->college->report->write || $user->rights->college->report->read',
			'user'=>0,
		);
		/** REPORT CREATE 
		$this->menu[$r++]=array(
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_survey',
            'type'=>'left',
            'titre'=>'menucreatenewreport',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_report_create',
            'url'=>'/college/report_card.php?action=create',
            'langs'=>'college@college',
            'position'=>1100+$r,
            'enabled'=>'$conf->college->enabled',
            'perms'=>'$user->rights->college->report->write',
            'target'=>'',
            'user'=>0
        );*/
		/** REPORT LIST */
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_survey',
			'type'=>'left',
			'titre'=>'menulistreport',
			//'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'college',
			'leftmenu'=>'college_report_list',
			'url'=>'/college/report_list.php',
			'langs'=>'college@college',
			'position'=>1100+$r,
			'enabled'=>'$conf->college->enabled',
			'perms'=>'$user->rights->college->report->read',
			'target'=>'',
			'user'=>0
		);
		/** SURVEY CREATE 
		$this->menu[$r++]=array(
            'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_survey',
            'type'=>'left',
            'titre'=>'menucreatenewsurvey',
            //'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
            'mainmenu'=>'college',
            'leftmenu'=>'college_survey_create',
            'url'=>'/college/surveytype_card.php?action=create',
            'langs'=>'college@college',
            'position'=>1100+$r,
            'enabled'=>'$conf->college->enabled',
            'perms'=>'$user->rights->college->survey->write',
            'target'=>'',
            'user'=>0
        );*/
		/** SURVEY LIST */
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_survey',
			'type'=>'left',
			'titre'=>'menulistsurvey',
			//'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'college',
			'leftmenu'=>'college_survey_list',
			'url'=>'/college/surveytype_list.php',
			'langs'=>'college@college',
			'position'=>1100+$r,
			'enabled'=>'$conf->college->enabled',
			'perms'=>'$user->rights->college->survey->read',
			'target'=>'',
			'user'=>0
		);
		/** QUESTION CREATE
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_survey',
			'type'=>'left',
			'titre'=>'menucreatenewquestions',
			//'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'college',
			'leftmenu'=>'college_survey_question_create',
			'url'=>'/college/questions_card.php?action=create',
			'langs'=>'college@college',
			'position'=>1100+$r,
			'enabled'=>'$conf->college->enabled',
			'perms'=>'$user->rights->college->questions->write',
			'target'=>'',
			'user'=>0
		); */
		/** QUESTION LIST */
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=college,fk_leftmenu=college_survey',
			'type'=>'left',
			'titre'=>'menulistquestions',
			//'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'college',
			'leftmenu'=>'college_survey_questions_list',
			'url'=>'/college/questions_list.php',
			'langs'=>'college@college',
			'position'=>1100+$r,
			'enabled'=>'$conf->college->enabled',
			'perms'=>'$user->rights->college->questions->read',
			'target'=>'',
			'user'=>0
		);


		/* END MODULEBUILDER LEFTMENU STUDENT */
		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT STUDENT */
		
		$langs->load("college@college");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='StudentLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='students@college';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'Students'; $keyforclassfile='/college/class/students.class.php'; $keyforelement='students@college';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'StudentLine'; $keyforclassfile='/college/class/student.class.php'; $keyforelement='studentline@college'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='students'; $keyforaliasextra='extra'; $keyforelement='students@college';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='studentline'; $keyforaliasextra='extraline'; $keyforelement='studentline@college';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('studentline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'college_students as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'student_line as tl ON tl.fk_student = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		//$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('student').')';
		$r++;
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/*EXPORT NOTES*/
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='StudentNotes';	
		$this->export_icon[$r]='notes@college';
		//$this->export_permission[$r] = array(array("notes"));
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$this->export_fields_array[$r] = array(
			't.rowid'=>"ID",
			't.ref'=>"Referencia",
			't.school_year'=>"Ciclo lectivo",
			'cc.label'=>"Clase",
			'stu.dni'=>"DNI",
			'stu.label'=>"Estudiante",
			'sub.label'=>"Asignatura",
			't.trimestre'=>"Trimestre",
			't.nota'=>"Nota",
			't.notarecover'=>"Recuperatorio",
			't.description'=>"Descripcion",
			't.note_public'=>"Nota Publica",
			't.note_private'=>"Nota Privada",
			'fku.firstname'=>"Nombre Profesor",
			'fku.lastname'=>"Apellido Profesor",
			't.tms'=>"Fecha",
			't.status'=>"Estado",
		);
		$this->export_TypeFields_array[$r] = array(
			't.rowid'=>"Numeric",
			't.ref'=>"Text",
			't.school_year'=>"Numeric",
			'cc.label'=>"Text",
			'stu.dni'=>"Numeric",
			'stu.label'=>"Text",
			'sub.label'=>"Text",
			't.trimestre'=>"Numeric",
			't.nota'=>"Numeric",
			't.notarecover'=>"Numeric",
			't.description'=>"Text",
			't.note_public'=>"Text",
			't.note_private'=>"Text",
			'fku.firstname'=>"Text",
			'fku.lastname'=>'Text',
			't.tms'=>"Date",
			't.status'=>"Numeric",
		);
		$this->export_entities_array[$r] = array(
			't.rowid'=>'notes@college',
			't.ref'=>"notes@college",
			't.school_year'=>'notes@college',
			'cc.label'=>"classrooms@college",
			'stu.dni'=>"students@college",
			'stu.label'=>"students@college",
			'sub.label'=>"subject@college",
			't.trimestre'=>"notes@college",
			't.nota'=>"notes@college",
			't.notarecover'=>"notes@college",
			't.description'=>"notes@college",
			't.note_public'=>"notes@college",
			't.note_private'=>"notes@college",
			'fku.firstname'=>"user",
			'fku.lastname'=>"user",
			't.tms'=>"notes@college",
			't.status'=>"notes@college",
		);
		
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r] =' FROM '.MAIN_DB_PREFIX.'college_notes as t';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'college_classrooms as cc ON t.fk_class=cc.rowid ';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'user as fku ON t.fk_user=fku.rowid ';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'college_subject as sub ON t.fk_subject=sub.rowid ';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'college_students as stu ON t.fk_student=stu.rowid ';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$r++; 
		/*END EXPORT NOTES*/
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Imports profiles provided by this module 
		$r = 0;
		/* BEGIN MODULEBUILDER IMPORT STUDENT */
		/*************************************/
		$r++;
		 $langs->load("college@college");
		 $this->import_code[$r]=$this->rights_class.'_'.$r;
		 $this->import_label[$r]='StudentLines';
		 $this->import_icon[$r]='students@college';
		 $this->import_entities_array[$r] = array();
     $this->import_tables_array[$r] = array(
      't' => MAIN_DB_PREFIX.'college_students',
     );
     $this->import_fields_array[$r] = array(
      't.ref' => 'ref*',
      't.label' => 'label*',
     );
     $this->import_fieldshidden_array[$r] = array(
		't.fk_user_creat'=>'user->id',
		't.date_creation'=>'const-'.dol_print_date(dol_now(), 'standard')
	);
     //$this->import_regex_array[$r] = array('t.fk_user_creat'=>'rowid@'.MAIN_DB_PREFIX.'user');
     $this->import_examplevalues_array[$r] = array('t.ref'=>'ref', 't.label'=>'label');
     $this->import_updatekeys_array[$r] = array('t.ref'=>'Ref', 't.label'=>'Label');
		/* END MODULEBUILDER IMPORT STUDENT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		//$result = $this->_load_tables('/install/mysql/tables/', 'college');
		$result = $this->_load_tables('/college/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		//$result1=$extrafields->addExtraField('college_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'college@college', '$conf->college->enabled');
		//$result2=$extrafields->addExtraField('college_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'college@college', '$conf->college->enabled');
		//$result3=$extrafields->addExtraField('college_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'college@college', '$conf->college->enabled');
		//$result4=$extrafields->addExtraField('college_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'college@college', '$conf->college->enabled');
		//$result5=$extrafields->addExtraField('college_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'college@college', '$conf->college->enabled');
		//$result1=$extrafields->addExtraField('collegestudent', "Representado", 'int', 1,  3, 'thirdparty', 0, 0, '', '', 1, '', 1, 0, '($stu = new Student($db) && $stu->fetchthird($object->id))?$stu:0', '', 'college@college', '1',0,0);
		
		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = dol_sanitizeFileName('college');
		$myTmpObjects = array();
		$myTmpObjects['Student'] = array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'Student') {
				continue;
			}
			if ($myTmpObjectArray['includerefgeneration']) {
				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/'.$moduledir.'/template_students.odt';
				$dirodt = DOL_DATA_ROOT.'/doctemplates/'.$moduledir;
				$dest = $dirodt.'/template_students.odt';

				if (file_exists($src) && !file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result = dol_copy($src, $dest, 0, 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")"
				));
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array(
      //"TRUNCATE TABLE ".MAIN_DB_PREFIX."college_notes",
      //"TRUNCATE TABLE ".MAIN_DB_PREFIX."college_subject",
      //"TRUNCATE TABLE ".MAIN_DB_PREFIX."college_assys",
      //"TRUNCATE TABLE ".MAIN_DB_PREFIX."college_inscriptions",
      //"DELETE FROM ".MAIN_DB_PREFIX."const WHERE name = 'COLLEGE_MYPARAM_CICLO_LECTIVO'",
    );
		return $this->_remove($sql, $options);
	}
}
