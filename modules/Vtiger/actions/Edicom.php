<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Edicom_Action extends App\Controller\Action
{
	public function checkPermission(\App\Request $request) {
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(\App\Request $request) {
		$method = $request->get('method');
		if($method == 'getProducts')
			$this->getProducts($request);
		else if($method == 'getUser')
			$this->getUser($request);
		else if($method == 'saveAttributions')
			$this->saveAttributions($request);
		else if($method == 'getProductInfo')
			$this->getProductInfo($request);
		else if($method == 'checkBat')
			$this->checkBat($request);
		else if($method == 'sendBat')
			$this->sendBat($request);
		else if($method == 'getStatutBat')
			$this->getStatutBat($request);
		else if($method == 'getOrderAllowedAmount')
			$this->getOrderAllowedAmount($request);
		else if($method == 'getInvoiceAllowedAmount')
			$this->getInvoiceAllowedAmount($request);
	}

	public function getProducts($request){
		$record = $request->getInteger('record');
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT p.name, s.code_produit, s.nom_produit, r.rubrique, p.value17, p.int1, p.picklist1, p.int20, s.pscategory FROM u_yf_ssingleorders o, u_yf_ssingleorders_inventory p, vtiger_service s, u_yf_rubriques r WHERE o.ssingleordersid = p.id AND p.ref = r.rubriquesid AND s.serviceid = p.name AND p.id = ".$record." AND s.code_produit NOT IN (SELECT format FROM u_yf_atribution where ordre = p.id)");
		$products = $query->fetch_all();
		$mysqli->close();
		$response = new Vtiger_Response();
		$response->setResult($products);
		$response->emit();
	}

	public function getUser($request){
		$id = \App\User::getCurrentUserId();
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT first_name, last_name FROM vtiger_users WHERE id = ".$id);
		$user = $query->fetch_assoc();
		$mysqli->close();
		$response = new Vtiger_Response();
		$response->setResult($user);
		$response->emit();
	}

	public function saveAttributions($request){
		$ordre = $request->getInteger('record');
		$operateur = $request->getInteger('operateur');
		$produits = explode(",", $request->get('produits'));		

		$formattedProducts = '(';
		foreach ($produits as $key => $produit) {
			$formattedProducts .= $produit.',';
		}
		$formattedProducts = substr($formattedProducts, 0, -1).')';
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT s.code_produit, s.nom_produit, r.rubrique, p.value17, p.int1, p.picklist1, p.int20, s.pscategory, o.firme FROM u_yf_ssingleorders_inventory p, vtiger_service s, u_yf_rubriques r, u_yf_ssingleorders o WHERE p.ref = r.rubriquesid AND s.serviceid = p.name AND o.ssingleordersid = p.id AND p.id = ".$ordre." AND p.name IN ".$formattedProducts);
		$produits = $query->fetch_all();
		$mysqli->close();
		$rows = [];
		foreach ($produits as $key => $produit) {
			$db = \App\Db::getInstance();
			$vtiger_crmentity=$this->getEntityDataForSave('Atribution');
			$db->createCommand()->insert("vtiger_crmentity", $vtiger_crmentity)->execute();
			$crmId = $db->getLastInsertID('vtiger_crmentity_crmid_seq');
			$row = array( 
				"atributionid"=> $crmId,
				"ordre"=> $ordre,
				"operateur"=> $operateur, 
				"format"=> $produit[0], 
				"libelle"=> $produit[1],
				"rubrique"=> $produit[2],
				"duree_affichage"=> $produit[4].' '. $produit[5],
				"nombre_affichage"=> $produit[6],
				"firme" => $produit[8]
			);
			$db->createCommand()->insert("u_yf_atribution", $row)->execute();
		}
		$response = new Vtiger_Response();
		$response->setResult($produits);
		$response->emit();
	}

	public function getEntityDataForSave($module) {
		$row = [];
		$time = date('Y-m-d H:i:s');
		$row['setype'] = $module;
		$row['users'] = ',' . \App\User::getCurrentUserId() . ',';
		$row['smcreatorid'] =  \App\User::getCurrentUserRealId();
		$row['smownerid'] =  \App\User::getCurrentUserRealId();
		$row['createdtime'] = $time;
		$row['modifiedtime'] =  $time;
		$row['modifiedby'] = \App\User::getCurrentUserRealId();
		return $row;
	}

	public function getProductInfo($request){
		$record = $request->getInteger('record');
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT * FROM vtiger_service WHERE serviceid = ".$record);
		$product = $query->fetch_assoc();
		$mysqli->close();
		$response = new Vtiger_Response();
		$response->setResult($product);
		$response->emit();
	}

	public function getStatutBat($request){
		$record = $request->getInteger('record');
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT * FROM u_yf_suivibat s, vtiger_crmentityrel r WHERE r.relmodule='SuiviBat' AND s.suivibatid = r.relcrmid  AND r.crmid = ".$record." ORDER BY s.suivibatid DESC ");
		$results = $query->fetch_assoc();
		$mysqli->close();
		$response = new Vtiger_Response();
		$response->setResult($results);
		$response->emit();
	}

	public function checkBat($request){
		$record = $request->getInteger('record');
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT count(*) as count FROM vtiger_crmentityrel r, u_yf_suivibat s WHERE r.relmodule='SuiviBat' AND s.suivibatid = r.relcrmid  AND r.crmid = ".$record);
		$results = $query->fetch_assoc();
		$mysqli->close();
		$response = new Vtiger_Response();
		$response->setResult($results);
		$response->emit();
	}

	public function sendBat($request){
		$record = $request->getInteger('record');
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT * FROM u_yf_atribution a, vtiger_account f, u_yf_ssingleorders o, u_yf_operateurs op, s_yf_companies s WHERE a.firme = f.accountid AND o.ssingleordersid = a.ordre AND s.id = 1 AND op.operateursid = a.operateur AND a.atributionid = ".$record);
		$data = $query->fetch_assoc();
		$mysqli->close();

		$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
		try {
		    $mail->isSMTP();
		    $mail->Host = 'smtp.gmail.com';
		    $mail->SMTPAuth = true;
		    $mail->Username = 'lemowsky@gmail.com';
		    $mail->Password = 'lemowsky123*';
		    $mail->SMTPSecure = 'tls';
		    $mail->Port = 587;

		    $mail->setFrom($data["email"], $data["name"]);
		    $mail->addAddress('abdelhak.ayyoub@gmail.com', $data["nom_contact_fabrication"]);
		    $mail->addReplyTo($data["email"], $data["name"]);

		    foreach (json_decode($data["photos"]) as $key => $attachment) {
		    	$mail->addAttachment($attachment->path, $attachment->name);
		    }
		    foreach (json_decode($data["logo"]) as $key => $attachment) {
		    	$mail->addAttachment($attachment->path, $attachment->name);
		    }

		    $mail->isHTML(true);
		    $mail->Subject = 'Here is the subject';
		    $mail->Body    = '<div class="Section1"><span><span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><b><span style="font-size:22pt;"><span style="font-family:Arial, \'sans-serif\';"><span style="color:#FF0000;"><span style="font-size:24px;">URGENT</span></span></span></span><br>
								<span style="font-size:28px;"><span style="font-family:Arial, \'sans-serif\';"><span style="color:#FF0000;">Bon à tirer</span></span></span><br><br>
								<span style="font-size:48px;"><span style="color:#005ca7;">'.$data["support"].'</span></span></b></span></span></span></span></span></span><br>
								'. $data["street"].'<br>
								<strong>Téléphone</strong>: '.$data["phone"].' - <strong>Fax</strong>: '.$data["fax"].'<br>
								<strong>Email</strong>: '.$data["email"].'<br>
								<br>
								<br>
								<br>
								<span style="color:#005ca7;"><strong><span style="font-size:16px;">'.$data["raison_sociale"].'                             '.$data["firme"].'</span></strong></span><br>
								<br>
								À L\'aimable Attention de '.$data["nom_commercial"].'
								<div class="Section1"><span><span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"> </span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">Madame, Monsieur,</span></span></span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"> </span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"> </span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">Nous avons le plaisir de vous soumettre en attache le modèle de votre annonce pour laquelle vous nous avez passé ordre pour la prochaine édition de '.$data["support"].'. </span></span></span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"> </span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">Nous vous serions reconnaissant de bien vouloir nous retourner  votre bon à tirer, visé avec vos éventuelles corrections </span></span></span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">soit par fax au <span style="color:#0000FF;">0</span><span style="color:#000080;">5 </span><span style="color:#0000FF;">22 25 64 48 </span><span style="color:#000080;">ou au </span><span style="color:#0000FF;">0</span><span style="color:#000080;">5 </span><span style="color:#0000FF;">22 25 28 84</span>  soit par <span style="color:#0000FF;">Email</span><span style="color:#FF0000;"> dans les 48 HEURES.</span></span></span></span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">Passé ce délai nous le considérons accepté.</span></span></span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"> </span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">Veuillez croire Madame, Monsieur,</span></span> <span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">en l\'assurance de nos sentiments dévoués.</span></span></span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"> </span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">Bonne réception.</span></span></span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"> </span></span></span></span></span><br>
								<span style="font-size:11pt;"><span style="line-height:normal;"><span><span style="line-height:115%;"><span style="font-family:Calibri, sans-serif;"><span style="font-size:10pt;"><span style="font-family:Arial, \'sans-serif\';">Sce Production</span></span></span></span></span></span></span></span></div>
							</div>';
		    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		    $mail->send();
		    //echo 'Message has been sent';
		} catch (Exception $e) {
		    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
		    die();
		}

		$db = \App\Db::getInstance();
		$vtiger_crmentity = $this->getEntityDataForSave('SuiviBat');
		$db->createCommand()->insert("vtiger_crmentity", $vtiger_crmentity)->execute();
		$crmId = $db->getLastInsertID('vtiger_crmentity_crmid_seq');
		$db->createCommand()->insert("u_yf_suivibat", ['suivibatid' => $crmId, 'date_envoi' => date('Y-m-d'), 'envoi' => 'Email'])->execute();
		$db->createCommand()->insert("vtiger_crmentityrel", ['crmid' => $record, 'module' =>'Atribution', 'relcrmid' => $crmId, 'relmodule' => 'SuiviBat', 'rel_created_user' =>\App\User::getCurrentUserId(), 'rel_created_time' => date('Y-m-d H:i:s')])->execute();

		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}

	public function getOrderAllowedAmount($request){
		$record = $request->getInteger('record');
		$relatedModule = $request->get('relatedModule');
		$table = $relatedModule == 'FirmesAfacturer'? 'u_yf_firmesafacturer' : 'u_yf_reglementsprevisionnels';
		$tableId = $relatedModule == 'FirmesAfacturer'? 'firmesafacturerid' : 'reglementsprevisionnelsid';
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT sum(reltable.montant) as sum, o.sum_gross as grossAmount FROM u_yf_ssingleorders o, vtiger_crmentityrel rel, ".$table." reltable WHERE  reltable.".$tableId." = rel.relcrmid AND o.ssingleordersid = rel.crmid AND rel.relmodule = '".$relatedModule."' AND rel.crmid = ".$record);
		$results = $query->fetch_assoc();
		$mysqli->close();
		$response = new Vtiger_Response();
		$response->setResult($results);
		$response->emit();
	}

	public function getInvoiceAllowedAmount($request){
		$record = $request->getInteger('record');
		$relatedModule = $request->get('relatedModule');
		$table = $relatedModule == 'Reglements'? 'u_yf_reglements' : 'u_yf_avoirs';
		$montant = $relatedModule == 'Reglements'? 'montant' : 'montant_ttc';
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
		$query = $mysqli->query("SELECT sum(reltable.".$montant.") as sum, f.sum_gross as grossAmount FROM u_yf_finvoice f, ".$table." reltable WHERE f.finvoiceid = reltable.facture AND f.finvoiceid = ".$record);
		$results = $query->fetch_assoc();
		$mysqli->close();
		$response = new Vtiger_Response();
		$response->setResult($results);
		$response->emit();
	}

	/*public function getRubriques($request){
		$results = ["results" => [['id' => 1, 'text' => 'Option 1'],['id' => 2, 'text' => 'Option 2']], 'pagination' => ['more' => true]];
		//$results = json_encode($results);
		$response = new Vtiger_Response();
		$response->setResult($results);
		$response->emit();
	}*/
}
