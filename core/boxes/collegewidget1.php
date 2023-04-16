<?php
/* Copyright (C) 2004-2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021  Frédéric France     <frederic.france@netlogic.fr>
 * Copyright (C) 2023 SuperAdmin
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
 * \file    college/core/boxes/collegewidget1.php
 * \ingroup college
 * \brief   Widget provided by College
 *
 * Put detailed description here.
 */

include_once DOL_DOCUMENT_ROOT."/core/boxes/modules_boxes.php";

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
	public $boxcode = "collegebox";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "college@college";

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
	
	
	public $contents_rows = array();
	public $getDataWidgets = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	public function __construct(DoliDB $db, $param = '')
	{
		global $user, $conf, $langs;
		// Translations
		$langs->loadLangs(array("boxes", "college@college"));

		parent::__construct($db, $param);

		$this->boxlabel = $langs->transnoentitiesnoconv("CollegeMyWidgetA");

		$this->param = $param;

		//$this->enabled = $conf->global->FEATURES_LEVEL > 0;         // Condition when module is enabled or not
		//$this->hidden = ! ($user->rights->college->myobject->read);   // Condition when module is visible by user (test on permission)
		$this->hidden = ! ($user->rights->college->inscriptions->read);
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $db,$langs;

		// Use configuration value for max lines count
		$this->max = $max;

		//dol_include_once("/college/class/college.class.php");
		dol_include_once("/college/class/inscriptions.class.php");
		$objectinscriptions = new Inscriptions($db);

		// Populate the head at runtime
		$text = $langs->trans("CollegeMyWidgetBoxADescription", $max);
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link
			'sublink' => DOL_MAIN_URL_ROOT.'/custom/college/collegeindex.php',
			// Sublink icon placed after the text
			'subpicto' => 'object_college@college',
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
		
		/*
		$this->info_box_contents = array(
			0 => array( // First line
				0 => array( // First Column
					//  HTML properties of the TR element. Only available on the first column.
					'tr' => 'class="left"',
					// HTML properties of the TD element
					'td' => '',

					// Main text for content of cell
					'text' => 'First cell of first line',
					// Link on 'text' and 'logo' elements
					'url' => 'http://example.com',
					// Link's target HTML property
					'target' => '_blank',
					// Fist line logo (deprecated. Include instead logo html code into text or text2, and set asis property to true to avoid HTML cleaning)
					//'logo' => 'monmodule@monmodule',
					// Unformatted text, added after text. Usefull to add/load javascript code
					'textnoformat' => '',

					// Main text for content of cell (other method)
					//'text2' => '<p><strong>Another text</strong></p>',

					// Truncates 'text' element to the specified character length, 0 = disabled
					'maxlength' => 0,
					// Prevents HTML cleaning (and truncation)
					'asis' => false,
					// Same for 'text2'
					'asis2' => true
				),
				1 => array( // Another column
					// No TR for n≠0
					'td' => '',
					'text' => 'Second cell',
				)
			),
			1 => array( // Another line
				0 => array( // TR
					'tr' => 'class="left"',
					'text' => 'Another line'
				),
				1 => array( // TR
					'tr' => 'class="left"',
					'text' => ''
				)
			),
			2 => array( // Another line
				0 => array( // TR
					'tr' => 'class="left"',
					'text' => ''
				),
				1 => array( // TR
					'tr' => 'class="left"',
					'text' => ''
				)
			),
		);
		*/


		$i = 0;
		if(!empty($this->getDataWidgets()))
		{
			while ($i < count($this->getDataWidgets()))
			{
				$this->contents_rows[] = array(
					0 => array( // TR
						'tr' => 'class="left"',
						'text' => $this->getDataWidgets()[$i]['clase'],
						'url' => DOL_MAIN_URL_ROOT.'/custom/college/inscriptions_list.php?action=list&search_fk_class='.$this->getDataWidgets()[$i]['idclase'].' ',
						'target' => '_blank',
						'logo' => 'object_students@college',
					),
					1 => array( // TR
						'tr' => 'class="left"',
						'text' => $langs->transnoentitiesnoconv("CollegeColsRows").'('.$this->getDataWidgets()[$i]['cantalumnosporclase'].')',
					)
				);
				$i++;   
			}
				$this->info_box_contents = $this->contents_rows;
		}
		else
		{
			$this->info_box_contents = array(
				0 => array(
					0 => array(
						'tr' => 'class="left"',
						'td' => '',
						'text' => ' No hay inscripciones.',
						'url' => DOL_MAIN_URL_ROOT.'/custom/college/collegeindex.php',
						'target' => '_self',
						'logo' => 'object_college@college',
						)
					)
			);
		}

	}
	
	
	public function getDataWidgets(){
	    global $db, $conf;
		$rowarray = array();
        $sql = "SELECT c.fk_class AS idclass, c.school_year , d.label AS labelclass, COUNT(c.fk_student) AS cant ";
        $sql.= " FROM ".MAIN_DB_PREFIX."college_inscriptions c";
        $sql.= " INNER JOIN ".MAIN_DB_PREFIX."college_classrooms d";
        $sql.= " ON d.rowid = c.fk_class";
    	if(empty($conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO)){
    		$sql.= " GROUP BY c.fk_class HAVING c.school_year = ".date("Y")." ";
    	}else{
    		$sql.= " GROUP BY c.fk_class HAVING c.school_year = ".$conf->global->COLLEGE_MYPARAM_CICLO_LECTIVO." ";
    	}
    	$resql= $db->query($sql);
    	if ($resql){
    	    $num = $db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $db->fetch_object($resql);
					if ($obj)
					{
						$rowarray[] = array('clase'=>$obj->labelclass,'idclase'=>$obj->idclass,'cantalumnosporclase'=>$obj->cant );
					}
					$i++;
				}
				return $rowarray;
			}
    	}else{
    	    return $rowarray;
    	}
    	
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
