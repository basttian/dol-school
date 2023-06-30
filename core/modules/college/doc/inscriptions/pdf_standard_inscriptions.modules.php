<?php
use Sabre\VObject\Property\VCard\LanguageTag;
/* Copyright (C) 2004-2014  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2008       Raphael Bertrand        <raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2014  Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2012       Christophe Battarel     <christophe.battarel@altairis.fr>
 * Copyright (C) 2012       Cédric Salvador         <csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014  Raphaël Doursenaud      <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2015       Marcos García           <marcosgdf@gmail.com>
 * Copyright (C) 2017       Ferran Marcet           <fmarcet@2byte.es>
 * Copyright (C) 2018       Frédéric France         <frederic.france@netlogic.fr>
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
 * or see https://www.gnu.org/
 */

/**
 *  \file       core/modules/college/doc/pdf_standard.modules.php
 *  \ingroup    college
 *  \brief      File of class to generate document from standard template
 */

dol_include_once('/college/core/modules/college/modules_inscriptions.php');
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/college/class/subject.class.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/college/class/classrooms.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/college/class/inscriptions.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/college/class/notes.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/college/class/students.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/college/class/subject.class.php';


/**
 *	Class to manage PDF template standard_inscriptions
 */
class pdf_standard_inscriptions extends ModelePDFInscriptions
{
	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var string model name
	 */
	public $name;

	/**
	 * @var string model description (short text)
	 */
	public $description;

	/**
	 * @var int     Save the name of generated file as the main doc when generating a doc with this template
	 */
	public $update_main_doc_field;

	/**
	 * @var string document type
	 */
	public $type;

	/**
	 * @var array Minimum version of PHP required by module.
	 * e.g.: PHP ≥ 5.6 = array(5, 6)
	 */
	public $phpmin = array(5, 6);

	/**
	 * Dolibarr version of the loaded document
	 * @var string
	 */
	public $version = 'dolibarr';

	/**
	 * @var int page_largeur
	 */
	public $page_largeur;

	/**
	 * @var int page_hauteur
	 */
	public $page_hauteur;

	/**
	 * @var array format
	 */
	public $format;

	/**
	 * @var int marge_gauche
	 */
	public $marge_gauche;

	/**
	 * @var int marge_droite
	 */
	public $marge_droite;

	/**
	 * @var int marge_haute
	 */
	public $marge_haute;

	/**
	 * @var int marge_basse
	 */
	public $marge_basse;

	/**
	 * Issuer
	 * @var Societe Object that emits
	 */
	public $emetteur;

	/**
	 * @var bool Situation invoice type
	 */
	public $situationinvoice;


	/**
	 * @var array of document table columns
	 */
	public $cols;

	
	/**
	 * 
	 * @var int $clase
	 * @var int $estudiante
	 * @var int $tutor
	 */
	public $clase,$estudiante,$tutor;
	

	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs, $mysoc;

		// Translations
		$langs->loadLangs(array("main", "bills"));

		$this->db = $db;
		$this->name = "standard";
		$this->description = $langs->trans('DocumentModelStandardPDF');
		$this->update_main_doc_field = 1; // Save the name of generated file as the main doc when generating a doc with this template

		// Dimension page
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur, $this->page_hauteur);
		$this->marge_gauche = isset($conf->global->MAIN_PDF_MARGIN_LEFT) ? $conf->global->MAIN_PDF_MARGIN_LEFT : 10;
		$this->marge_droite = isset($conf->global->MAIN_PDF_MARGIN_RIGHT) ? $conf->global->MAIN_PDF_MARGIN_RIGHT : 10;
		$this->marge_haute = isset($conf->global->MAIN_PDF_MARGIN_TOP) ? $conf->global->MAIN_PDF_MARGIN_TOP : 10;
		$this->marge_basse = isset($conf->global->MAIN_PDF_MARGIN_BOTTOM) ? $conf->global->MAIN_PDF_MARGIN_BOTTOM : 10;

		// Get source company
		$this->emetteur = $mysoc;
		if (empty($this->emetteur->country_code)) {
			$this->emetteur->country_code = substr($langs->defaultlang, -2); // By default, if was not defined
		}

		// Define position of columns
		$this->posxdesc = $this->marge_gauche + 1; // used for notes ans other stuff


		$this->tabTitleHeight = 5; // default height

		//  Use new system for position of columns, view  $this->defineColumnField()
        
		$this->situationinvoice = false;
		
		
		
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Function to build pdf onto disk
	 *
	 *  @param		Object		$object				Object to generate
	 *  @param		Translate	$outputlangs		Lang output object
	 *  @param		string		$srctemplatepath	Full path of source filename for generator using a template file
	 *  @param		int			$hidedetails		Do not show line details
	 *  @param		int			$hidedesc			Do not show desc
	 *  @param		int			$hideref			Do not show ref
	 *  @return     int         	    			1=OK, 0=KO
	 */
	public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
		// phpcs:enable
		global $user, $langs, $conf, $mysoc, $db, $hookmanager, $nblines;

		dol_syslog("write_file outputlangs->defaultlang=".(is_object($outputlangs) ? $outputlangs->defaultlang : 'null'));

		if (!is_object($outputlangs)) {
			$outputlangs = $langs;
		}
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!empty($conf->global->MAIN_USE_FPDF)) {
			$outputlangs->charset_output = 'ISO-8859-1';
		}

		// Load translation files required by the page
		$outputlangs->loadLangs(array("main", "bills", "products", "dict", "companies","college"));

		if (!empty($conf->global->PDF_USE_ALSO_LANGUAGE_CODE) && $outputlangs->defaultlang != $conf->global->PDF_USE_ALSO_LANGUAGE_CODE) {
			global $outputlangsbis;
			$outputlangsbis = new Translate('', $conf);
			$outputlangsbis->setDefaultLang($conf->global->PDF_USE_ALSO_LANGUAGE_CODE);
			$outputlangsbis->loadLangs(array("college"));
		}

		$nblines = (is_array($object->lines) ? count($object->lines) : 0);

		$hidetop = 0;
		if (!empty($conf->global->MAIN_PDF_DISABLE_COL_HEAD_TITLE)) {
			$hidetop = $conf->global->MAIN_PDF_DISABLE_COL_HEAD_TITLE;
		}

		// Loop on each lines to detect if there is at least one image to show
		$realpatharray = array();
		$this->atleastonephoto = false;


		if ($conf->college->dir_output.'/inscriptions') {
			$object->fetch_thirdparty();

			// Definition of $dir and $file
			if ($object->specimen) {
				$dir = $conf->college->dir_output.'/inscriptions';
				$file = $dir."/SPECIMEN.pdf";
			} else {
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->college->dir_output.'/inscriptions/'.$objectref;
				$file = $dir."/".$objectref.".pdf";
			}
			if (!file_exists($dir)) {
				if (dol_mkdir($dir) < 0) {
					$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
					return 0;
				}
			}

			if (file_exists($dir)) {

				// Add pdfgeneration hook
				if (!is_object($hookmanager)) {
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager = new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file'=>$file, 'object'=>$object, 'outputlangs'=>$outputlangs);
				global $action;
				$reshook = $hookmanager->executeHooks('beforePDFCreation', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

				// Set nblines with the new facture lines content after hook
				$nblines = (is_array($object->lines) ? count($object->lines) : 0);

				// Create pdf instance
				$pdf = pdf_getInstance($this->format);
				$default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
				$pdf->SetAutoPageBreak(1, 0);

				$heightforinfotot = 50; // Height reserved to output the info and total part and payment part
				$heightforfreetext = (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT) ? $conf->global->MAIN_PDF_FREETEXT_HEIGHT : 5); // Height reserved to output the free text on last page
				$heightforfooter = $this->marge_basse + (empty($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS) ? 12 : 22); // Height reserved to output the footer (value include bottom margin)

				if (class_exists('TCPDF')) {
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));

				// Set path to the background PDF File
				if (!empty($conf->global->MAIN_ADD_PDF_BACKGROUND)) {
					$pagecount = $pdf->setSourceFile($conf->mycompany->multidir_output[$object->entity].'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $pdf->importPage(1);
				}

				$pdf->Open();
				$pagenb = 0;
				$pdf->SetDrawColor(128, 128, 128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("pdftitleinscriptions"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("pdftitleinscriptions")." ".$outputlangs->convToOutputCharset($object->thirdparty->name));
				if (!empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) {
					$pdf->SetCompression(false);
				}

				// Set certificate
				$cert = empty($user->conf->CERTIFICATE_CRT) ? '' : $user->conf->CERTIFICATE_CRT;
				// If user has no certificate, we try to take the company one
				if (!$cert) {
					$cert = empty($conf->global->CERTIFICATE_CRT) ? '' : $conf->global->CERTIFICATE_CRT;
				}
				// If a certificate is found
				if ($cert) {
					$info = array(
						'Name' => $this->emetteur->name,
						'Location' => getCountry($this->emetteur->country_code, 0),
						'Reason' => 'INSCRIPTIONS',
						'ContactInfo' => $this->emetteur->email
					);
					$pdf->setSignature($cert, $cert, $this->emetteur->name, '', 2, $info);
				}

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right

				// New page
				$pdf->AddPage();
				if (!empty($tplidx)) {
					$pdf->useTemplate($tplidx);
				}
				$pagenb++;

				$top_shift = $this->_pagehead($pdf, $object, 1, $outputlangs, $outputlangsbis);
				$pdf->SetFont('', '', $default_font_size - 1);
				$pdf->MultiCell(0, 3, ''); // Set interline to 3
				$pdf->SetTextColor(0, 0, 0);

				$tab_top = 90 + $top_shift;
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD) ? 42 + $top_shift : 10);
				$tab_height = 130 - $top_shift;
				$tab_height_newpage = 150;
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) {
					$tab_height_newpage -= $top_shift;
				}
                
    /****************************************************************************************************/
				/*CABECERA*/
        $pdf->SetTextColor(128, 0, 0);
        $pdf->SetFont('times', 'B', 16);
        $pdf->SetFillColor(233, 234, 237);
        $pdf->Cell(0, 10,$langs->transnoentities("pdfcabecerainscriptions"), 0, 1, 'C', 1,'',1,1,'','M');
        $pdf->MultiCell(0, 3, ''); // Set interline to 3
        /*LINE - LYRICS*/
        $pdf->SetFont('times', 'BI', 16);
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins(1, 1, 1, 1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(38, 60, 92);
        $pdf->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2, 'color' => array(38, 60, 92)));
        /*DATE*/
	      $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionsdate"), 0, 'L', 1, 0, '', '', true);
    		$pdf->MultiCell(82, 5, dol_print_date($object->date_creation, "day", false, ''), '', 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $html = "<hr>\n";
        $pdf->writeHTML($html, true, false, false, false, '');
        /*ANIO*/
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionsyear"), 0, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5, $object->school_year, 0, 'R', 1, 0, '', '', true);
				$pdf->Ln();
        $html = "<hr>\n";
        $pdf->writeHTML($html, true, false, false, false, '');
        /*CURSO*/
				$clase = new Classrooms($db);
				$clase->fetch($object->fk_class);
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionscurso"), 0, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  $clase->label, 0, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $html = "<hr>\n";
        $pdf->writeHTML($html, true, false, false, false, '');
        
        /*DATOS ALUMNO O ESTUDIANTE*/
        $pdf->SetFont('times', 'BI', 16);
        $pdf->Write(0, $langs->transnoentities("pdftitlealumno"), '', 0, 'L', true, 0, false, false, 0);
        
        $pdf->SetLineStyle(array('width' => 0, 'cap' => '', 'join' => '', 'dash' => 0, 'color' => array(255, 255, 255))); 
         /*ALUMNO*/
        $pdf->SetFont('times', 'B', 12);
        $estudiante = new Students($db);
        $estudiante->fetch($object->fk_student);
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionsalumnodni"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($estudiante->dni)?$estudiante->dni:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionsalumnonombre"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($estudiante->label)?$estudiante->label:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionsalumnodireccion"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($estudiante->direccion)?$estudiante->direccion:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionsalumnotelefono"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($estudiante->telefono)?$estudiante->telefono:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionsalumnoemail"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($estudiante->email)?$estudiante->email:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        
        /*TUTOR O RESPONSABLE*/
        $pdf->SetFont('times', 'BI', 16);
        $pdf->Write(0, $langs->transnoentities("pdftitletutor"), '', 0, 'L', true, 0, false, false, 0);
        /*TUTOR*/
        $pdf->SetFont('times', 'B', 12);
        $tutor = new Societe($db);
        $tutor->fetch($object->fk_tutor);
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionstutornombre"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($tutor->nom)?$tutor->nom:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionstutordireccion"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($tutor->address)?$tutor->address:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionstutorciudadcp"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($tutor->town)?$tutor->town:'-'.' / '.$tutor->zip, 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionstutorphone"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($tutor->phone)?$tutor->phone:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        $pdf->MultiCell(102, 5, $langs->transnoentities("pdfinscriptionstutoremail"), 1, 'L', 1, 0, '', '', true);
        $pdf->MultiCell(82, 5,  !empty($tutor->email)?$tutor->email:'-', 1, 'R', 1, 0, '', '', true);
        $pdf->Ln();
        
/*************************************************************************************************************/
              
        $pdf->SetXY(5, 250);
		    $pdf->SetTextColor(0, 0, 60); 
        $pdf->Write(0, '....................................................................', '', 0, 'R', true, 0, false, false, 0);
        $pdf->Ln();   
              
/*************************************************************************************************************/
				$nexY = $tab_top - 1;
				$pagenb = $pdf->getPage();
				
				// Pagefoot
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) {
					$pdf->AliasNbPages();
				}

				$pdf->Close();

				$pdf->Output($file, 'F');

				if (!empty($conf->global->MAIN_UMASK)) {
					@chmod($file, octdec($conf->global->MAIN_UMASK));
				}

					$this->result = array('fullpath'=>$file);

					return 1; // No error
			} else {
				$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		} else {
			$this->error = $langs->transnoentities("ErrorConstantNotDefined", "FAC_OUTPUTDIR");
			return 0;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return list of active generation modules
	 *
	 *  @param	DoliDB	$db     			Database handler
	 *  @param  integer	$maxfilenamelength  Max length of value to show
	 *  @return	array						List of templates
	 */
	public static function liste_modeles($db, $maxfilenamelength = 0)
	{
		// phpcs:enable
		return parent::liste_modeles($db, $maxfilenamelength); // TODO: Change the autogenerated stub
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *   Show table for lines
	 *
	 *   @param		tcpdf			$pdf     		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y (not used)
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @param		string		$currency		Currency code
	 *   @param		Translate	$outputlangsbis	Langs object bis
	 *   @return	void
	 */
	protected function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop = 0, $hidebottom = 0, $currency = '', $outputlangsbis = null)
	{	
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show top header of page.
	 *
	 *  @param	Tcpdf			$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @param  Translate	$outputlangsbis	Object lang for output bis
	 *  @return	void
	 */
	protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $outputlangsbis = null)
	{
		global $conf, $langs, $db;
    
		// Load traductions files required by page
		$outputlangs->loadLangs(array("main", "bills", "propal", "companies", "college"));

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);

		// Show Draft Watermark
		if ($object->statut == $object::STATUS_DRAFT && (!empty($conf->global->FACTURE_DRAFT_WATERMARK))) {
			  pdf_watermark($pdf, $outputlangs, $this->page_hauteur, $this->page_largeur, 'mm', $conf->global->FACTURE_DRAFT_WATERMARK);
		}

		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);

		$w = 110;

		$posy = $this->marge_haute;
		$posx = $this->page_largeur - $this->marge_droite - $w;

		$pdf->SetXY($this->marge_gauche, $posy);

		// Logo
		if (empty($conf->global->PDF_DISABLE_MYCOMPANY_LOGO)) {
			if ($this->emetteur->logo) {
				$logodir = $conf->mycompany->dir_output;
				if (!empty($conf->mycompany->multidir_output[$object->entity])) {
					$logodir = $conf->mycompany->multidir_output[$object->entity];
				}
				if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO)) {
					$logo = $logodir.'/logos/thumbs/'.$this->emetteur->logo_small;
				} else {
					$logo = $logodir.'/logos/'.$this->emetteur->logo;
				}
				if (is_readable($logo)) {
					$height = pdf_getHeightForLogo($logo);
					$pdf->Image($logo, $this->marge_gauche, $posy, 0, $height); // width=0 (auto)
				} else {
					$pdf->SetTextColor(200, 0, 0);
					$pdf->SetFont('', 'B', $default_font_size - 2);
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
				}
			} else {
				$text = $this->emetteur->name;
				$pdf->MultiCell($w, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
			}
		}

    /*CENTRE INFO */
    $pdf->SetXY($posx, $posy);
    $pdf->SetTextColor(0, 0, 60);
    $pdf->SetFont('', 'BI', 8);
	$pdf->MultiCell(0, 3, '', '', 'C');//$conf->global->MAIN_APPLICATION_TITLE
    $pdf->MultiCell(0, 3, $conf->global->MAIN_INFO_SOCIETE_ADDRESS, '', 'C');
    $pdf->MultiCell(0, 3, $conf->global->MAIN_INFO_SOCIETE_TOWN .' '.$conf->global->MAIN_INFO_SOCIETE_ZIP , '', 'C');
    $pdf->MultiCell(0, 3, $conf->global->MAIN_INFO_SOCIETE_TEL, '', 'C');
    $pdf->MultiCell(0, 3, $conf->global->MAIN_INFO_SOCIETE_MAIL, '', 'C');


		$pdf->SetFont('', 'B', $default_font_size + 3);
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$title = $outputlangs->transnoentities("pdftitleinscriptions");
		if (!empty($conf->global->PDF_USE_ALSO_LANGUAGE_CODE) && is_object($outputlangsbis)) {
			$title .= ' - ';
			$title .= $outputlangsbis->transnoentities("pdftitleinscriptions");
		}
		$pdf->MultiCell($w, 3, $title, '', 'R');

		$pdf->SetFont('', 'B', $default_font_size);

		$posy += 5;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$textref = $outputlangs->transnoentities("Ref")." : ".$outputlangs->convToOutputCharset($object->ref);
		if ($object->statut == $object::STATUS_DRAFT) {
			$pdf->SetTextColor(128, 0, 0);
			//$textref .= ' - '.$outputlangs->transnoentities("NotValidated");
		}
		$pdf->MultiCell($w, 4, $textref, '', 'R');
    $pdf->MultiCell(0, 8, ''); // Set interline to 8
		$posy += 1;

		$pdf->SetTextColor(0, 0, 0);
		return $top_shift;
	}
	

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *   	Show footer of page. Need this->emetteur object
	 *
	 *   	@param	TCPDF		$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
	{
		global $conf;
		$showdetails = empty($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS) ? 0 : $conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_pagefoot($pdf, $outputlangs, '', '$this->emetteur', $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext);
	}

	/**
	 *  Define Array Column Field
	 *
	 *  @param	object			$object    		common object
	 *  @param	Translate		$outputlangs    langs
	 *  @param	int			   $hidedetails		Do not show line details
	 *  @param	int			   $hidedesc		Do not show desc
	 *  @param	int			   $hideref			Do not show ref
	 *  @return	null
	 */
	public function defineColumnField($object, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{	
	}
}
