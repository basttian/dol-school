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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
$form = new Form($db);

//WYSIWYG Editor
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
// Security check
if (! $user->rights->college->msjpagetop->write) {
    accessforbidden();
}
// Load translation files required by the page
$langs->loadLangs(array("college@college", "Other"));
// load college libraries
require_once __DIR__.'/class/notes.class.php';
require_once __DIR__.'/class/classrooms.class.php';
require_once __DIR__.'/class/students.class.php';
require_once __DIR__.'/class/inscriptions.class.php';
require_once __DIR__.'/class/subject.class.php';
require_once __DIR__.'/class/periods.class.php';
require_once __DIR__.'/class/assys.class.php';

global $db, $user, $langs; 

  ?>
    <script type="text/javascript">
      $( document ).ready(function() {
        //$('.button-save').addClass( "butActionDelete").addClass("butActionRefused");
         $(".button-save").on("click",function(){
            $('#loader').show();
            
            var text = CKEDITOR.instances['motd'].getData();
            $.post( '<?php echo DOL_URL_ROOT."/admin/ihm.php" ;?>',{
              token:'<?php echo newToken() ;?>',
              action: 'update',
              mode: 'dashboard',
              'main_motd': text,},function() {
              $.colorbox.close();
            }).done(function(){
              $('#loader').hide();
              
            });
         })

      });
    </script>
  <?php

if($user->rights->college->msjpagetop->write){
  print info_admin 	( 	  	
        $langs->trans("avisoMsjMainMotd"),
		  	$infoonimgalt = 0,
		  	$nodiv = 0,
		  	$admin = '0',
		  	$morecss = 'hideonsmartphone warning',//More CSS ('', 'warning', 'error') 
		  	$textfordropdown = '' 
	);
  
  $doleditor = new DolEditor('motd', (isset($conf->global->MAIN_MOTD) ? $conf->global->MAIN_MOTD : ''), '', 142, 'dolibarr_notes', 'In', false, true, true, 8, '90%');
	$doleditor->Create();
  
  print '<div class="center">';
  //print '<img style="display: none;" id="loader" class="valignmiddle" src="../../custom/college/img/spinner.gif" height="20px">';
  //print '<input class="button button-save minwidth400 widthcentpercentminusxx" value="' . $langs->trans("SaveConstMainMotd") . '">';
  print '<button class="button button-save widthcentpercentminusxx" >
  ' . $langs->trans("SaveConstMainMotd") . '
  <img style="display: none;" id="loader" class="valignmiddle" src="../../custom/college/img/spinner.gif" height="20px">
  </button>';
  print '</div>';

}

//minwidth500 maxwidth50 widthcentpercentminusxx













































