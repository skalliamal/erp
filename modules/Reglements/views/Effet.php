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

class Reglements_Effet_View extends Vtiger_Index_View
{
    public function process(\App\Request $request)
    {
        $print_pdf=true;
        if($request->get('mode') ){
            $mode = $request->get('mode');
            if($mode == 'getEncaissement')
            {
                $reglements=$this->getEncaissement($request);
                echo $reglements; die;
            }

            else if($mode == 'updateBordereau')
            {
                $this->updateBordereau($request);
            }
            else if($mode == 'genererPDF')
            {
                //$pdf =Vtiger_PDF_Action::generateEncaissement($request);
                $print_pdf=false;
                $this->generer($request);
            }
        }
        if($print_pdf) {
            $viewer = $this->getViewer($request);
            $moduleName = $request->getModule();
            $qualifiedModuleName = $request->getModule(false);
            $viewer->assign('BANQUE', $request->get('banque'));
            $viewer->assign('DATE', $request->get('date_echeance'));
            $viewer->assign('BORDERAU', $request->get('bordereau'));
            $viewer->assign('TYPE', $request->get('type'));
            $viewer->view('Effet.tpl', $qualifiedModuleName);
        }
    }
    public function getEncaissement($request)
    {
        $date_echeance=$request->get('date_echeance');

        if($request->get('banque')=='BP')
            $banque="Banque Populaire";
        else  if($request->get('banque')=='SG')
            $banque="Attijariwafa bank";

        if($request->get('type')==1)
            $type="Effet";
        else
            if($request->get('type')==2)
            $type="Cheque sur place";
        else
            if($request->get('type')==3)
            $type="Cheque hors place";
        /*$db = \App\Db::getInstance();
        $count_reglements=$db->select(['count(*)'])->from('u_yf_reglements')->where(['date_valeur' => $date_echeance,"banque"=> $banque,"type"=>$type])->createCommand()->execute();
        var_dump($count_reglements); die;*/
        /*$date_echeance= date('Y-m-d', strtotime(str_replace('/', '-', $date_echeance)));
        */
        $date_echeance= date('Y-m-d',strtotime(str_replace('/', '-', $date_echeance)));
        $d=date('Y-m-d');
      /*  echo "SELECT  inv.number,acc.accountname,acc.ville,r.date_valeur,r.reg_a_recevoir,r.bordereau,r.type,r.banque  FROM u_yf_reglements r
    	left outer join u_yf_finvoice inv on inv.finvoiceid=r.facture
    	left outer join vtiger_account acc on inv.accountid=acc.accountid
     WHERE r.banque = '$banque' AND r.type = '$type' AND date_valeur = '$date_echeance' "; die;*/
        $dbconfig = AppConfig::main('dbconfig');
        /* var_dump($date_echeance);*/
        /*$response = new Vtiger_Response();
       $response->setResult($date_echeance);
       $response->emit();
       die();*/
        $mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name'],$dbconfig['db_port']);
        $query = $mysqli->query("SELECT count(*) as cnt FROM u_yf_reglements r WHERE r.banque = '$banque' AND r.type = '$type' AND date_valeur = '$date_echeance' ");/*CURDATE()*/
        $reglements = $query->fetch_assoc()["cnt"];
        $mysqli->close();
        return $reglements;
    }

    public function updateBordereau($request)
    {
        $date_echeance =  $request->get('date_echeance');
        /*$date_echeance= date('Y-m-d', strtotime(str_replace('/', '-', $date_echeance)));*/
        $date_echeance= date('Y-m-d',strtotime(str_replace('/', '-', $date_echeance)));
        $bordereau =  $request->get('bordereau');
        if($request->get('banque')=='BP')
            $banque="Banque Populaire";
        else  if($request->get('banque')=='SG')
            $banque="Attijariwafa bank";

        if($request->get('type')==1)
            $type="Effet";
        else
            if($request->get('type')==2)
                $type="Cheque sur place";
            else
                if($request->get('type')==3)
                    $type="Cheque hors place";
        $dbconfig = AppConfig::main('dbconfig');
        $mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name'],$dbconfig['db_port']);
        $query = $mysqli->query("UPDATE u_yf_reglements r SET bordereau='$bordereau' WHERE r.banque = '$banque' AND r.type like '$type' AND date_valeur = '$date_echeance' ");
        $mysqli->close();
    }
    public function generer($request)
    {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);

        $moduleName = $request->getModule();

        $date_echeance =  $request->get('date_echeance');

        $date_echeance= date('Y-m-d',strtotime(str_replace('/', '-', $date_echeance)));
        if($request->get('banque')=='BP')
            $banque="Banque Populaire";
        else  if($request->get('banque')=='SG')
            $banque="Attijariwafa bank";
        if($request->get('type')==1)
            $type="Effet";
        else
            if($request->get('type')==2)
                $type="Cheque sur place";
            else
                if($request->get('type')==3)
                    $type="Cheque hors place";

        $encaissements=(new \App\Db\Query())->select("inv.number,acc.accountname,acc.ville,r.date_valeur,r.reg_a_recevoir,r.bordereau,r.type,r.banque,r.montant")
            ->leftJoin('u_yf_finvoice inv','inv.finvoiceid=r.facture')
            ->leftJoin('vtiger_account acc','inv.accountid=acc.accountid')
            ->where(["banque"=>$banque,"type"=>$type,"date_valeur"=>$date_echeance])
            ->from("u_yf_reglements r")->all();

        $sommes=(new \App\Db\Query())->select("sum(r.montant) as somme")
            ->where(["banque"=>$banque,"type"=>$type,"date_valeur"=>$date_echeance])
            ->from("u_yf_reglements r")->all();
        $viewer->assign('ENCAISSEMENTS', $encaissements);
        $viewer->assign('SOMMES', $sommes);
        $viewer->view('genererPDF.tpl', $qualifiedModuleName);

        $html2pdf = new Html2Pdf('P','A4','fr');
        $html2pdf->pdf->SetAuthor('Encaissement');
        $html2pdf->pdf->SetTitle('Encaissement');
        if($request->get('banque')=='BP')
        {
            $html2pdf->writeHTML('<br><img src="http://100.1.1.8/ERP/erptest/public_html/layouts/resources/Logo/bp.png" /> <h3>Edicom SA</h3>
                            <strong style="margin-left: 80px;font-weight: 400">AGENCE Al Moukaouama</strong> <br><br>
                            <strong style="margin-left: 80px;font-weight: 400">BANQUE POPULAIRE</strong> :<br> <br>
                            <strong style="margin-left: 80px;">BORDEREAU DES REMISE DES VALEURS</strong> <strong style="margin-left: 200px"> N° </strong> <br><br>
                            <strong style="margin-left: 80px;font-weight: 500">EFFETS</strong>
                            ');
        }
        else  if($request->get('banque')=='SG')
        {
            $html2pdf->writeHTML('<br><img src="http://100.1.1.8/ERP/erptest/public_html/layouts/resources/Logo/societe-generale.png" style="width: 150px;height: 140px;margin-top: -40px" /> <h3>Edicom SA</h3>
                            <strong style="margin-left: 80px;font-weight: 400">AGENCE BIR ANZARANE</strong> <br><br>
                            <strong style="margin-left: 80px;font-weight: 400">SGMB</strong> :<br> <br>
                            <strong style="margin-left: 80px;">BORDEREAU DES REMISE DES VALEURS</strong> <strong style="margin-left: 200px"> N° </strong><br><br>
                            <strong style="margin-left: 80px;font-weight: 500">EFFETS</strong>
                            ');
        }
        /*  $html2pdf->writeHTML('<br><img src="http://100.1.1.8/ERP/erptest/public_html/layouts/resources/Logo/bp.png" />
                                <h3>Edicom SA</h3>
                               <strong style="margin-left: 80px;font-weight: 400">AGENCE Al Moukaouama</strong> <br><br>
                               <strong style="margin-left: 80px;font-weight: 400">'.$encaissements.["banque"].' </strong> :<br> <br>
                               <strong style="margin-left: 80px;">BORDEREAU DES REMISE DES VALEURS</strong> <strong style="margin-left: 200px"> N° '.$encaissements.["bordereau"].'</strong><br>');*/
        ob_end_clean();
        $html2pdf->output();
    }


    public function getFooterScripts(\App\Request $request)
    {
        $headerScriptInstances = parent::getFooterScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.$moduleName.resources.Encaissement",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
