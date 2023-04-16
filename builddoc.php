<?php
$action = GETPOST('action', 'aZ09');

if ($action == 'builddoc') {
	if (is_numeric(GETPOST('model', 'alpha'))) {
		$error = $langs->trans("ErrorFieldRequired", $langs->transnoentities("Model"));
	} else {
	    $outputlangs = $langs;
	    $newlang = '';
	    
	    if (!empty($conf->global->MAIN_MULTILANGS) && empty($newlang) && GETPOST('lang_id', 'aZ09')) {
	        $newlang = GETPOST('lang_id', 'aZ09');
	    }
	    if (!empty($conf->global->MAIN_MULTILANGS) && empty($newlang) && isset($object->thirdparty->default_lang)) {
	        $newlang = $object->thirdparty->default_lang; // for proposal, order, invoice, ...
	    }
	    if (!empty($conf->global->MAIN_MULTILANGS) && empty($newlang) && isset($object->default_lang)) {
	        $newlang = $object->default_lang; // for thirdparty
	    }
	    if (!empty($newlang)) {
	        $outputlangs = new Translate("", $conf);
	        $outputlangs->setDefaultLang($newlang);
	    }

	    if (empty($hidedetails)) {
	        $hidedetails = 0;
	    }
	    if (empty($hidedesc)) {
	        $hidedesc = 0;
	    }
	    if (empty($hideref)) {
	        $hideref = 0;
	    }
	    if (empty($moreparams)) {
	        $moreparams = null;
	    }
	    
	    $result = $object->generateDocument($object->model_pdf, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		} else {
			if (empty($donotredirect)) {
			     setEventMessages($langs->trans("FileGenerated"), null);
    			 $urltoredirect = $_SERVER['REQUEST_URI'];
    			 $urltoredirect = preg_replace('/#builddoc$/', '', $urltoredirect);
    			 $urltoredirect = preg_replace('/action=builddoc&?/', '', $urltoredirect);
    			 
    			 header('Location: '.$urltoredirect.'#builddoc');
    			 exit;
			}
		}
	}
}