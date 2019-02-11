<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

class Vendors extends CRMEntity
{
	public $table_name = 'vtiger_vendor';
	public $table_index = 'vendorid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_vendor', 'vtiger_vendoraddress', 'vtiger_vendorcf', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_vendor' => 'vendorid', 'vtiger_vendoraddress' => 'vendorid', 'vtiger_vendorcf' => 'vendorid', 'vtiger_entity_stats' => 'crmid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_vendorcf', 'vendorid'];
	public $column_fields = [];
	public $related_tables = [
		'vtiger_vendorcf' => ['vendorid', 'vtiger_vendor', 'vendorid'],
		'vtiger_vendoraddress' => ['vendorid', 'vtiger_vendor', 'vendorid'],
	];
	//Pavani: Assign value to entity_table
	public $entity_table = 'vtiger_crmentity';
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = [
		'Vendor Name' => ['vendor' => 'vendorname'],
		'Phone' => ['vendor' => 'phone'],
		'Email' => ['vendor' => 'email'],
		'Category' => ['vendor' => 'category'],
	];
	public $list_fields_name = [
		'Vendor Name' => 'vendorname',
		'Phone' => 'phone',
		'Email' => 'email',
		'Category' => 'category',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['vendorname', 'phone', 'email', 'category'];
	public $list_link_field = 'vendorname';
	public $search_fields = [
		'Vendor Name' => ['vendor' => 'vendorname'],
		'Phone' => ['vendor' => 'phone'],
	];
	public $search_fields_name = [
		'Vendor Name' => 'vendorname',
		'Phone' => 'phone',
	];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'vendorname', 'assigned_user_id'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'vendorname';

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

		$rel_table_arr = ['Products' => 'vtiger_products', 'Contacts' => 'vtiger_vendorcontactrel', 'Campaigns' => 'vtiger_campaign_records'];

		$tbl_field_arr = ['vtiger_products' => 'productid', 'vtiger_vendorcontactrel' => 'contactid', 'vtiger_campaign_records' => 'campaignid'];

		$entity_tbl_field_arr = ['vtiger_products' => 'vendor_id', 'vtiger_vendorcontactrel' => 'vendorid', 'vtiger_campaign_records' => 'crmid'];

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
						$adb->update($rel_table, [$entity_id_field => $entityId], $entity_id_field . ' = ? and ' . $id_field . ' = ?', [$transferId, $id_field_value]);
					}
				}
			}
		}
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
			'Products' => ['vtiger_products' => ['vendor_id', 'productid'], 'vtiger_vendor' => 'vendorid'],
			'Contacts' => ['vtiger_vendorcontactrel' => ['vendorid', 'contactid'], 'vtiger_vendor' => 'vendorid'],
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_vendor' => 'vendorid'],
		];
		if ($secModule === false) {
			return $relTables;
		}
		return $relTables[$secModule];
	}

	public function saveRelatedModule($module, $crmid, $with_module, $with_crmids, $relatedName = false)
	{
		if (!is_array($with_crmids)) {
			$with_crmids = [$with_crmids];
		}
		if (!in_array($with_module, ['Contacts', 'Products', 'Campaigns'])) {
			parent::saveRelatedModule($module, $crmid, $with_module, $with_crmids, $relatedName);
		} else {
			foreach ($with_crmids as $with_crmid) {
				if ($with_module === 'Contacts') {
					App\Db::getInstance()->createCommand()->insert('vtiger_vendorcontactrel', [
						'vendorid' => $crmid,
						'contactid' => $with_crmid,
					])->execute();
				} elseif ($with_module === 'Products') {
					App\Db::getInstance()->createCommand()->update('vtiger_products', ['vendor_id' => $crmid], ['productid' => $with_crmid])->execute();
				} elseif ($with_module === 'Campaigns') {
					App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
						'campaignid' => $with_crmid,
						'crmid' => $crmid,
						'campaignrelstatusid' => 0,
					])->execute();
				}
			}
		}
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		if (empty($return_module) || empty($return_id)) {
			return;
		}
		if ($return_module == 'Campaigns') {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['crmid' => $id, 'campaignid' => $return_id])->execute();
		} elseif ($return_module == 'Contacts') {
			App\Db::getInstance()->createCommand()->delete('vtiger_vendorcontactrel', ['vendorid' => $id, 'contactid' => $return_id])->execute();
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}
}
