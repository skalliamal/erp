<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Accounts_List_View extends Vtiger_List_View
{
	
	public function preProcess(\App\Request $request, $display = true)
	{
		if(!empty($request->get('syncroniser'))){

			$this->syncroniseAccountsAdded();
			$this->syncroniseAccountsUpdated();
		}
		parent::preProcess($request, false);

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$mid = false;
		if ($request->has('mid')) {
			$mid = $request->getInteger('mid');
		}

		$linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->getByType('view', 1)];
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName, $mid));
		$this->viewName = App\CustomView::getInstance($moduleName)->getViewId();
		if ($request->isEmpty('viewname') && App\CustomView::hasViewChanged($moduleName, $this->viewName)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($this->viewName);
			if ($customViewModel) {
				App\CustomView::setDefaultSortOrderBy($moduleName, ['orderBy' => $customViewModel->getSortOrderBy('orderBy'), 'sortOrder' => $customViewModel->getSortOrderBy('sortOrder')]);
			}
			App\CustomView::setCurrentView($moduleName, $this->viewName);
		}
		$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $this->viewName);
		if (isset($_SESSION['lvs'][$moduleName]['entityState'])) {
			$this->listViewModel->set('entityState', $_SESSION['lvs'][$moduleName]['entityState']);
		}
		$viewer->assign('HEADER_LINKS', $this->listViewModel->getHederLinks($linkParams));
		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('VIEWID', $this->viewName);
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}
	public function syncroniseAccountsAdded()
	{
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name_crm']);
		$firmes=$mysqli->query("SELECT f.code_firme,f.rs_comp, v.ville,ld.email,ld.tel_1,
								num_voie,comp_num_voie,lib_voie,comp_voie,arr.arrondissement,q.quartier 
								FROM firmes f
								left join villes v on v.code =f.code_ville 
								left join lien_dirigeant ld on ld.code_firme =f.code_firme 
								left join arrondissements arr on arr.code=f.code_arr
                                left join quartiers q on q.code=f.code_quart
                                left join villes vrc on vrc.code=f.code_ville_rc
								where f.code_firme not in (select account_no from ".$dbconfig['db_name'].".vtiger_account)");
		$tableName="vtiger_crmentity";
		$rows = $firmes->fetch_all();
		$vtiger_crmentity=$this->getEntityDataForSave();
		foreach ($rows as $row) {
			$tableData=array( 
				"account_no"=> $row[0], 
				"accountname"=> $row[1], 
				"ville"=> $row[2],
				"email1"=> $row[3], 
				"phone"=> $row[4],
				"adresse"=> $row[5].$row[6].$row[7].$row[8].$row[9].$row[10]
			);	
			$db = \App\Db::getInstance();
			$db->createCommand()->insert("vtiger_crmentity", $vtiger_crmentity)->execute();
			$LastInsertID=$db->getLastInsertID('vtiger_crmentity_crmid_seq');
			$db->createCommand()->insert("vtiger_account", ["accountid" => $LastInsertID] + $tableData)->execute();
			
		}
		$mysqli->close();
		header('Location: index.php?module=Accounts&view=List&mid=115&parent=114');
	}
	public function getEntityDataForSave()
	{
		$row = [];
		$time = date('Y-m-d H:i:s');
		$row['setype'] = "Accounts";
		$row['users'] = ',' . \App\User::getCurrentUserId() . ',';
		$row['smcreatorid'] =  \App\User::getCurrentUserRealId();
		$row['createdtime'] = $time;
		$row['modifiedtime'] =  $time;
		$row['modifiedby'] = \App\User::getCurrentUserRealId();
		return $row;
	}

	public function syncroniseAccountsUpdated()
	{
		$dbconfig = AppConfig::main('dbconfig');
		$mysqli = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name_crm']);
		$mysqli->query("update  ".$dbconfig['db_name'].".vtiger_account as acc 
						inner join firmes f on acc.account_no =f.code_firme 
						left join villes v on v.code =f.code_ville 
						left join lien_dirigeant ld on ld.code_firme =f.code_firme 
						left join arrondissements arr on arr.code=f.code_arr
                        left join quartiers q on q.code=f.code_quart
                        left join villes vrc on vrc.code=f.code_ville_rc
						set acc.accountname=f.rs_comp, acc.ville=v.ville,acc.email1=ld.email,acc.phone=ld.tel_1
						acc.adresse=concat(num_voie, ' ' ,comp_num_voie, ' ' ,lib_voie, ' ' ,comp_voie, ' ' ,arr.arrondissement, ' ' ,q.quartier)");
		$mysqli->close();
		header('Location: index.php?module=Accounts&view=List&mid=115&parent=114');
	}
}
