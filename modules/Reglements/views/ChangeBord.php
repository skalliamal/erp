<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
use Spipu\Html2Pdf\Html2Pdf;

class Reglements_ChangeBord_View extends Vtiger_Index_View
{
    public function process(\App\Request $request)
    {
        if ($request->get('mode')) {
            $mode = $request->get('mode');
            if ($mode == 'updateBordereauEdicomBanque') {
                //$pdf =Vtiger_PDF_Action::generateEncaissement($request);

                $this->updateBordereauEdicomBanque($request);
            }
        }
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $viewer->assign('BANQUE', $request->get('banque'));
        $viewer->assign('DATE', $request->get('date_echeance'));
        $viewer->assign('BORDERAU', $request->get('bordereau'));
        $viewer->assign('TYPE', $request->get('type'));
        $viewer->view('ChangeBord.tpl', $qualifiedModuleName);
    }
    public function updateBordereauEdicomBanque($request)
    {

        $bordereauEdicom =  $request->get('bordereauEdicom');
        $bordereauBanque =  $request->get('bordereauBanque');

        $dbconfig = AppConfig::main('dbconfig');
        $mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name'],$dbconfig['db_port']);
        $query = $mysqli->query("UPDATE u_yf_reglements r SET bordereau='$bordereauBanque' WHERE r.bordereau = '$bordereauEdicom'");
        $mysqli->close();


    }
}