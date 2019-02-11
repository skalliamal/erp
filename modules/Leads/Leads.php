<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

class Leads extends CRMEntity
{
	public $table_name = 'vtiger_leaddetails';
	public $table_index = 'leadid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_leaddetails', 'vtiger_leadsubdetails', 'vtiger_leadaddress', 'vtiger_leadscf', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_leaddetails' => 'leadid', 'vtiger_leadsubdetails' => 'leadsubscriptionid', 'vtiger_leadaddress' => 'leadaddressid', 'vtiger_leadscf' => 'leadid', 'vtiger_entity_stats' => 'crmid'];
	public $entity_table = 'vtiger_crmentity';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_leadscf', 'leadid'];
	//construct this from database;
	public $column_fields = [];
	// This is used to retrieve related vtiger_fields from form posts.
	public $additional_column_fields = ['smcreatorid', 'smownerid', 'contactid', 'crmid'];
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = [
		'Company' => ['leaddetails' => 'company'],
		'Phone' => ['leadaddress' => 'phone'],
		'Website' => ['leadsubdetails' => 'website'],
		'Email' => ['leaddetails' => 'email'],
		'Assigned To' => ['crmentity' => 'smownerid'],
	];
	public $list_fields_name = [
		'Company' => 'company',
		'Phone' => 'phone',
		'Website' => 'website',
		'Email' => 'email',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['company', 'phone', 'website', 'email', 'assigned_user_id'];
	public $list_link_field = 'company';
	public $search_fields = [
		'Company' => ['leaddetails' => 'company'],
	];
	public $search_fields_name = [
		'Company' => 'company',
	];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime'];
	//Default Fields for Email Templates -- Pavani
	public $emailTemplate_defaultFields = ['leadsource', 'leadstatus', 'rating', 'industry', 'secondaryemail', 'email', 'annualrevenue', 'designation', 'salutation'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// For Alphabetical search
	public $def_basicsearch_col = 'company';

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string This module name
	 * @param array List of Entity Id's from which related records need to be transfered
	 * @param int Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = ['Documents' => 'vtiger_senotesrel', 'Attachments' => 'vtiger_seattachmentsrel',
			'Products' => 'vtiger_seproductsrel', 'Campaigns' => 'vtiger_campaign_records', ];

		$tbl_field_arr = ['vtiger_senotesrel' => 'notesid', 'vtiger_seattachmentsrel' => 'attachmentsid',
			'vtiger_seproductsrel' => 'productid', 'vtiger_campaign_records' => 'campaignid', ];

		$entity_tbl_field_arr = ['vtiger_senotesrel' => 'crmid', 'vtiger_seattachmentsrel' => 'crmid',
			'vtiger_seproductsrel' => 'crmid', 'vtiger_campaign_records' => 'crmid', ];

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", [$transferId, $entityId]);
				$res_cnt = $adb->numRows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; ++$i) {
						$id_field_value = $adb->queryResult($sel_result, $i, $id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", [$entityId, $transferId, $id_field_value]);
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace('Exiting transferRelatedRecords...');
	}

	/**
	 * Function to get the relation tables for related modules.
	 *
	 * @param bool|string $secModule secondary module name
	 *
	 * @return array with table names and fieldnames storing relations between module and this module
	 */
	public function setRelationTables($secModule = false)
	{
		$relTables = [
			'Products' => ['vtiger_seproductsrel' => ['crmid', 'productid'], 'vtiger_leaddetails' => 'leadid'],
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_leaddetails' => 'leadid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_leaddetails' => 'leadid'],
			'Services' => ['vtiger_crmentityrel' => ['crmid', 'relcrmid'], 'vtiger_leaddetails' => 'leadid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_leaddetails' => 'leadid'],
		];
		if ($secModule === false) {
			return $relTables;
		}
		return $relTables[$secModule];
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		if (empty($return_module) || empty($return_id)) {
			return;
		}

		if ($return_module === 'Campaigns') {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['crmid' => $id, 'campaignid' => $return_id])->execute();
		} elseif ($return_module === 'Products') {
			App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['crmid' => $id, 'productid' => $return_id])->execute();
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		if (!is_array($withCrmids)) {
			$withCrmids = [$withCrmids];
		}
		foreach ($withCrmids as $withCrmid) {
			if ($withModule === 'Products') {
				App\Db::getInstance()->createCommand()->insert('vtiger_seproductsrel', [
					'crmid' => $crmid,
					'productid' => $withCrmid,
					'setype' => $module,
					'rel_created_user' => App\User::getCurrentUserId(),
					'rel_created_time' => date('Y-m-d H:i:s'),
				])->execute();
			} elseif ($withModule === 'Campaigns') {
				App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
					'campaignid' => $withCrmid,
					'crmid' => $crmid,
					'campaignrelstatusid' => 0,
				])->execute();
			} else {
				parent::saveRelatedModule($module, $crmid, $withModule, $withCrmid, $relatedName);
			}
		}
	}
}
