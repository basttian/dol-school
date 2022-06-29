<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *	\file       college/collegeindex.php
 *	\ingroup    college
 *	\brief      Home page of college top menu
 */

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


if (! ((GETPOST('testmenuhider','int') || ! empty($conf->global->MAIN_TESTMENUHIDER)) && empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)))
{
  $conf->dol_hide_leftmenu = 1; // Force hide of left menu.
}

$action = GETPOST('action', 'aZ09');

// Load translation files required by the page
$langs->loadLangs(array("college@college"));
llxHeader("
<link rel='stylesheet' type='text/css' href='./css/spectrecss/spectre.min.css'>
<link rel='stylesheet' type='text/css' href='./css/spectrecss/spectre-exp.min.css'>
<link rel='stylesheet' type='text/css' href='./css/spectrecss/spectre-icons.min.css'>
");
print load_fiche_titre($langs->trans(""), '', 'college.png@college');

?>

<div class="section section-updates bg-gray">
<div class="container grid-xl">
<div class="columns">
<?php if ($user->rights->college->notes->read){ ;?>
  <div class="column col-4 col-xs-12 p-10">
    <div class="card">
      <div class="card-image m-10">
        <i class="fa fa-address-book-o fa-5x" aria-hidden="true"></i>
      </div>
      <div class="card-header">
        <div class="card-title h5"><?php echo $langs->trans("card-header-a-notas") ;?></div>
        <div class="card-subtitle text-gray"><?php echo $langs->trans("card-header-b-notas") ;?></div>
      </div>
      <div class="card-body">
        <?php echo $langs->trans("card-body-notas") ;?>
      </div>
      <div class="card-footer">
        <a href="<?php echo DOL_URL_ROOT."/custom/college/notes_list.php"; ?>" class="float-left btn btn-primary"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
        
        <?php if ($user->rights->college->notes->write){ ;?>
        <a href="<?php echo DOL_URL_ROOT."/custom/college/notes_card.php?action=create"; ?>" class="mrl-10 float-right btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i></a>
        <a href="<?php echo DOL_URL_ROOT."/custom/college/notes_list_add.php?action=createlist"; ?>" class="float-right btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i><i class="fa fa-bars" aria-hidden="true"></i></a>
        <?php } ;?>
      </div>
    </div>
  </div>
<?php } ;?>
<?php if($user->rights->college->inscriptions->read){ ;?>
  <div class="column col-4 col-xs-12 p-10">
    <div class="card">
      <div class="card-image m-10">
        <i class="fa fa-university fa-5x" aria-hidden="true"></i>
      </div>
      <div class="card-header">
        <div class="card-title h5"><?php echo $langs->trans("card-header-a-inscriptions") ;?></div>
        <div class="card-subtitle text-gray"><?php echo $langs->trans("card-header-b-inscriptions") ;?></div>
      </div>
      <div class="card-body">
        <?php echo $langs->trans("card-body-inscriptions") ;?>
      </div>
      <div class="card-footer">
        <a href="<?php echo DOL_URL_ROOT."/custom/college/inscriptions_list.php"; ?>" class="float-left btn btn-primary"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
        <?php if($user->rights->college->inscriptions->write){  ;?>
        <a href="<?php echo DOL_URL_ROOT."/custom/college/inscriptions_card.php?action=create"; ?>" class="m10 float-right btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i></a>
        <?php }  ;?>
      </div>
    </div>
  </div>
<?php } ;?>
<?php if($user->rights->college->students->read){  ;?>
  <div class="column col-4 col-xs-12 p-10">
    <div class="card">
      <div class="card-image m-10">
        <i class="fa fa-graduation-cap fa-5x" aria-hidden="true"></i>
      </div>
      <div class="card-header">
        <div class="card-title h5"><?php echo $langs->trans("card-header-a-students") ;?></div>
        <div class="card-subtitle text-gray"><?php echo $langs->trans("card-header-b-students") ;?></i></div>
      </div>
      <div class="card-body">
        <?php echo $langs->trans("card-body-students") ;?>
      </div>
      <div class="card-footer">
        <a href="<?php echo DOL_URL_ROOT."/custom/college/students_list.php"; ?>" class="float-left btn btn-primary"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
        <?php if($user->rights->college->students->write){ ;?>
        <a href="<?php echo DOL_URL_ROOT."/custom/college/students_card.php?action=create"; ?>" class="m10 float-right btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i></a>
        <?php } ;?>
      </div>
    </div>
  </div>
<?php } ;?>
<?php if($user->rights->college->subject->read){ ;?>
<div class="column col-4 col-xs-12 p-10">
  <div class="card">
    <div class="card-image m-10">
      <i class="fa fa-book fa-5x" aria-hidden="true"></i>
    </div>
    <div class="card-header">
      <div class="card-title h5"><?php echo $langs->trans("card-header-a-subject") ;?></div>
      <div class="card-subtitle text-gray"><?php echo $langs->trans("card-header-b-subject") ;?></i></div>
    </div>
    <div class="card-body">
      <?php echo $langs->trans("card-body-subject") ;?>
    </div>
      <div class="card-footer">
        <a href="<?php echo DOL_URL_ROOT."/custom/college/subject_list.php"; ?>" class="float-left btn btn-primary"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
        <?php if($user->rights->college->subject->write) { ;?>
        
        <a href="<?php echo DOL_URL_ROOT."/custom/college/subject_card.php?action=create"; ?>" class="mrl-10 float-right btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i></a>
        <a href="<?php echo DOL_URL_ROOT."/custom/college/subject_card.php?action=createadds"; ?>" class="float-right btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i><i class="fa fa-bars" aria-hidden="true"></i></a>
        <?php } ;?>
      </div>
    </div>
  </div>
<?php } ;?>     
<?php if($user->rights->college->classrooms->read){ ;?>
<div class="column col-4 col-xs-12 p-10">
  <div class="card">
    <div class="card-image m-10">
      <i class="fa fa-cubes fa-5x" aria-hidden="true"></i>
    </div>
    <div class="card-header">
      <div class="card-title h5"><?php echo $langs->trans("card-header-a-class") ;?></div>
      <div class="card-subtitle text-gray"><?php echo $langs->trans("card-header-b-class") ;?></i></div>
    </div>
    <div class="card-body">
      <?php echo $langs->trans("card-body-class") ;?>
    </div>
      <div class="card-footer">
        <a href="<?php echo DOL_URL_ROOT."/custom/college/classrooms_list.php"; ?>" class="float-left btn btn-primary"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
        <?php if($user->rights->college->classrooms->write){ ;?>
        <a href="<?php echo DOL_URL_ROOT."/custom/college/classrooms_card.php?action=create"; ?>" class="m10 float-right btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i></a>
        <?php } ;?> 
      </div>
    </div>
  </div>
<?php } ;?> 
<?php if($user->rights->college->periods->read){ ;?>
<div class="column col-4 col-xs-12 p-10">
  <div class="card">
    <div class="card-image m-10">
      <i class="fa fa-calendar fa-5x" aria-hidden="true"></i>
    </div>
    <div class="card-header">
      <div class="card-title h5"><?php echo $langs->trans("card-header-a-periods") ;?></div>
      <div class="card-subtitle text-gray"><?php echo $langs->trans("card-header-b-periods") ;?></i></div>
    </div>
    <div class="card-body">
      <?php echo $langs->trans("card-body-periods") ;?>
    </div>
      <div class="card-footer">
        <a href="<?php echo DOL_URL_ROOT."/custom/college/periods_list.php"; ?>" class="float-left btn btn-primary"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
        <?php if($user->rights->college->periods->write){ ;?>
        <a href="<?php echo DOL_URL_ROOT."/custom/college/periods_card.php?action=create"; ?>" class="m10 float-right btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i></a>
        <?php } ;?> 
      </div>
    </div>
  </div>
<?php } ;?> 
  
</div>      
</div>
</div>

<?php if(!$conf->global->COLLEGE_HIDE_FOOTER_MSJ){ ;?>
<footer class="section section-footer m-10">
  <div class="docs-footer container grid-lg" id="copyright">
    <p><a href="https://github.com/basttian" target="_blank">GitHub</a> · <a href="https://www.patreon.com/disejo" target="_blank">Patreon Sponsor</a></p>
    <p>Designed and built with <span class="text-error">♥</span> by Jofre Sebastian. Licensed under the <a href="#" target="_blank">MIT License</a>.</p>
  </div>
</footer>
<?php };?>


<?php
// End of page
llxFooter();
$db->close();
