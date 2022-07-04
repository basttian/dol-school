<?php
/* Copyright (C) 2004-2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021  Frédéric France     <frederic.france@netlogic.fr>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    htdocs/modulebuilder/template/core/boxes/mymodulewidget1.php
 * \ingroup mymodule
 * \brief   Widget provided by MyModule
 *
 * Put detailed description here.
 */

include_once DOL_DOCUMENT_ROOT."/core/boxes/modules_boxes.php";
require_once DOL_DOCUMENT_ROOT."/custom/college/class/classrooms.class.php";
require_once DOL_DOCUMENT_ROOT."/custom/college/class/inscriptions.class.php";
require_once DOL_DOCUMENT_ROOT."/custom/college/class/notes.class.php";
require_once DOL_DOCUMENT_ROOT."/custom/college/class/periods.class.php";
require_once DOL_DOCUMENT_ROOT."/custom/college/class/students.class.php";
require_once DOL_DOCUMENT_ROOT."/custom/college/class/subject.class.php";

/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class collegewidget1 extends ModeleBoxes
{
	/**
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "college";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "object_students@college";

	/**
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 * @var string[] Module dependencies
	 */
	public $depends = array('college');

	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var mixed More parameters
	 */
	public $param;

	/**
	 * @var array Header informations. Usually created at runtime by loadBox().
	 */
	public $info_box_head = array();

	/**
	 * @var array Contents informations. Usually created at runtime by loadBox().
	 */
	public $info_box_contents = array();

	/**
	 * @var string 	Widget type ('graph' means the widget is a graph widget)
	 */
	public $widgettype = 'graph';


  
  public $objectinscriptions;
  

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	public function __construct(DoliDB $db, $param = '')
	{
		global $user, $conf, $langs, $db;
		// Translations
		$langs->loadLangs(array("boxes", "college@college"));

		parent::__construct($db, $param);

		$this->boxlabel = $langs->transnoentitiesnoconv("CollegeMyWidgetA");

		$this->param = $param;
    
    //$this->objectclassrooms = new Classrooms($db);
    //$this->objectinscriptions = new Inscriptions($db);
    //$this->objectstudents = new Students($db);


		//$this->enabled = $conf->global->FEATURES_LEVEL > 0;         // Condition when module is enabled or not
		//$this->hidden = ! ($user->rights->college->students->read);   // Condition when module is visible by user (test on permission)
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $db, $conf, $user, $langs;

		// Use configuration value for max lines count
		$this->max = $max;
    $objectinscriptions = new Inscriptions($db);
		//dol_include_once("/mymodule/class/mymodule.class.php");
    
		// Populate the head at runtime
		$text = $langs->trans("CollegeMyWidgetBoxADescription").' '.$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO ;
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link
			'sublink' => './custom/college/collegeindex.php',
			// Sublink icon placed after the text
			'subpicto' => 'object_students@college',
			// Sublink icon HTML alt text
			'subtext' => '',
			// Sublink HTML target
			'target' => '',
			// HTML class attached to the picto and link
			'subclass' => 'center',
			// Limit and truncate with "…" the displayed text lenght, 0 = disabled
			'limit' => 0,
			// Adds translated " (Graph)" to a hidden form value's input (?)
			'graph' => false
		);

		// Populate the contents at runtime
		$this->info_box_contents = $objectinscriptions->getWidgetData();
	}

	/**
	 * Method to show box. Called by Dolibarr eatch time it wants to display the box.
	 *
	 * @param array $head       Array with properties of box title
	 * @param array $contents   Array with properties of box lines
	 * @param int   $nooutput   No print, only return string
	 * @return string
	 */
	public function showBox($head = null, $contents = null, $nooutput = 0)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		return parent::showBox($this->info_box_head, $this->info_box_contents, $nooutput);
	}
}
