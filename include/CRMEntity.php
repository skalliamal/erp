<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/vtigercrm/data/CRMEntity.php,v 1.16 2005/04/29 04:21:31 mickie Exp $
 * Description:  Defines the base class for all data entities used throughout the
 * application.  The base class including its methods and variables is designed to
 * be overloaded with module-specific methods and variables particular to the
 * module's base entity class.
 * ****************************************************************************** */
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';

class CRMEntity
{
	public $db;
	public $ownedby;

	/** 	Constructor which will set the column_fields in this object
	 */
	public function __construct()
	{
		$this->db = PearDatabase::getInstance();
		$this->column_fields = vtlib\Deprecated::getColumnFields(get_class($this));
	}

	public static function getInstance($module)
	{
		$modName = $module;
		if (is_numeric($module)) {
			$modName = App\Module::getModuleName($module);
		}
		if ($module === 'Calendar' || $module === 'Events') {
			$module = 'Calendar';
			$modName = 'Activity';
		}
		if (\App\Cache::staticHas('CRMEntity', $module)) {
			return clone \App\Cache::staticGet('CRMEntity', $module);
		}

		// File access security check
		if (!class_exists($modName)) {
			if (AppConfig::performance('LOAD_CUSTOM_FILES') && file_exists("custom/modules/$module/$modName.php")) {
				\vtlib\Deprecated::checkFileAccessForInclusion("custom/modules/$module/$modName.php");
				require_once "custom/modules/$module/$modName.php";
			} else {
				\vtlib\Deprecated::checkFileAccessForInclusion("modules/$module/$modName.php");
				require_once "modules/$module/$modName.php";
			}
		}
		$focus = new $modName();
		$focus->moduleName = $module;
		\App\Cache::staticSave('CRMEntity', $module, clone $focus);

		return $focus;
	}

	/**
	 * Save the inventory data.
	 */
	public function saveInventoryData($moduleName)
	{
		$db = App\Db::getInstance();

		\App\Log::trace('Entering ' . __METHOD__);

		$inventory = Vtiger_InventoryField_Model::getInstance($moduleName);
		$table = $inventory->getTableName('data');

		$db->createCommand()->delete($table, ['id' => $this->id])->execute();
		if (is_array($this->inventoryData)) {
			foreach ($this->inventoryData as $insertData) {
				$insertData['id'] = $this->id;
				$db->createCommand()->insert($table, $insertData)->execute();
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/** Function to delete a record in the specifed table
	 * @param string $tableName -- table name:: Type varchar
	 *                          The function will delete a record. The id is obtained from the class variable $this->id and the columnname got from $this->tab_name_index[$table_name]
	 */
	public function deleteRelation($tableName)
	{
		if ((new App\Db\Query())->from($tableName)->where([$this->tab_name_index[$tableName] => $this->id])->exists()) {
			\App\Db::getInstance()->createCommand()->delete($tableName, [$this->tab_name_index[$tableName] => $this->id])->execute();
		}
	}

	/**
	 * Function returns the column alias for a field.
	 *
	 * @param <Array> $fieldinfo - field information
	 *
	 * @return string field value
	 */
	protected function createColumnAliasForField($fieldinfo)
	{
		return strtolower($fieldinfo['tablename'] . $fieldinfo['fieldname']);
	}

	/**
	 * Retrieve record information of the module.
	 *
	 * @param int    $record - crmid of record
	 * @param string $module - module name
	 */
	public function retrieveEntityInfo($record, $module)
	{
		if (!isset($record)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
		}

		// Tables which has multiple rows for the same record
		// will be skipped in record retrieve - need to be taken care separately.
		$multiRowTables = null;
		if (isset($this->multirow_tables)) {
			$multiRowTables = $this->multirow_tables;
		} else {
			$multiRowTables = [
				'vtiger_attachments',
			];
		}

		// Lookup module field cache
		if ($module == 'Calendar' || $module == 'Events') {
			vtlib\Deprecated::getColumnFields('Calendar');
			if (VTCacheUtils::lookupFieldInfoModule('Events')) {
				$cachedEventsFields = VTCacheUtils::lookupFieldInfoModule('Events');
			} else {
				$cachedEventsFields = [];
			}
			$cachedCalendarFields = VTCacheUtils::lookupFieldInfoModule('Calendar');
			$cachedModuleFields = array_merge($cachedEventsFields, $cachedCalendarFields);
			$module = 'Calendar';
		} else {
			$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);
		}
		if ($cachedModuleFields === false) {
			// Pull fields and cache for further use
			$tabid = \App\Module::getModuleId($module);
			$query = (new \App\Db\Query())
				->select(['fieldname', 'fieldid', 'fieldlabel', 'columnname', 'tablename', 'uitype', 'typeofdata', 'presence'])
				->from('vtiger_field')
				->where(['tabid' => $tabid]);
			$dataReader = $query->createCommand()->query();
			if ($dataReader->count()) {
				while ($row = $dataReader->read()) {
					// Update cache
					VTCacheUtils::updateFieldInfo(
						(int) $tabid, $row['fieldname'], (int) $row['fieldid'], $row['fieldlabel'], $row['columnname'], $row['tablename'], (int) $row['uitype'], $row['typeofdata'], (int) $row['presence']
					);
				}
				// Get only active field information
				$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);
			}
			$dataReader->close();
		}

		if ($cachedModuleFields) {
			$query = new \App\Db\Query();
			$columnClause = [];
			$requiredTables = $this->tab_name_index; // copies-on-write

			foreach ($cachedModuleFields as $fieldInfo) {
				if (in_array($fieldInfo['tablename'], $multiRowTables)) {
					continue;
				}
				// Alias prefixed with tablename+fieldname to avoid duplicate column name across tables
				// fieldname are always assumed to be unique for a module
				$columnClause[] = $fieldInfo['tablename'] . '.' . $fieldInfo['columnname'] . ' AS ' . $this->createColumnAliasForField($fieldInfo);
			}
			$columnClause[] = 'vtiger_crmentity.deleted';
			$query->select($columnClause);
			if (isset($requiredTables['vtiger_crmentity'])) {
				$query->from('vtiger_crmentity');
				unset($requiredTables['vtiger_crmentity']);
				foreach ($requiredTables as $tableName => $tableIndex) {
					if (in_array($tableName, $multiRowTables)) {
						// Avoid multirow table joins.
						continue;
					}
					$query->leftJoin($tableName, "vtiger_crmentity.crmid = $tableName.$tableIndex");
				}
			}
			$query->where(['vtiger_crmentity.crmid' => $record]);
			if ($module != '') {
				$query->andWhere(['vtiger_crmentity.setype' => $module]);
			}
			$resultRow = $query->one();
			if (empty($resultRow)) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
			} else {
				foreach ($cachedModuleFields as $fieldInfo) {
					$fieldvalue = '';
					$fieldkey = $this->createColumnAliasForField($fieldInfo);
					//Note : value is retrieved with a tablename+fieldname as we are using alias while building query
					if (isset($resultRow[$fieldkey])) {
						$fieldvalue = $resultRow[$fieldkey];
					}
					if ($fieldInfo['uitype'] === 120) {
						$query = (new \App\Db\Query())->select('userid')->from('u_#__crmentity_showners')->where(['crmid' => $record])->distinct();
						$fieldvalue = $query->column();
						if (is_array($fieldvalue)) {
							$fieldvalue = implode(',', $fieldvalue);
						}
					}
					$this->column_fields[$fieldInfo['fieldname']] = $fieldvalue;
				}
			}
		}
		$this->column_fields['record_id'] = $record;
		$this->column_fields['record_module'] = $module;
	}

	/**
	 * Function invoked during export of module record value.
	 */
	public function transformExportValue($key, $value)
	{
		// NOTE: The sub-class can override this function as required.
		return $value;
	}

	/**
	 * Function to unlink an entity with given Id from another entity.
	 *
	 * @param int    $id
	 * @param string $returnModule
	 * @param int    $returnId
	 * @param bool   $relatedName
	 */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		switch ($relatedName) {
			case 'getManyToMany':
				$this->deleteRelatedM2M($id, $returnModule, $returnId);
				break;
			case 'getDependentsList':
				$this->deleteRelatedDependent($id, $returnModule, $returnId);
				break;
			case 'getRelatedList':
				$this->deleteRelatedFromDB($id, $returnModule, $returnId);
				break;
			default:
				$this->deleteRelatedDependent($id, $returnModule, $returnId);
				$this->deleteRelatedFromDB($id, $returnModule, $returnId);
				break;
		}
	}

	public function deleteRelatedDependent($crmid, $withModule, $withCrmid)
	{
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.fieldname'])
			->from('vtiger_field')
			->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $this->moduleName, 'relmodule' => $withModule])])
			->createCommand()->query();
		$recordModel = \Vtiger_Record_Model::getInstanceById($crmid, $this->moduleName);
		while ($row = $dataReader->read()) {
			if ((int) $recordModel->get($row['fieldname']) === (int) $withCrmid) {
				$recordModel->set($row['fieldname'], 0);
			}
		}
		$dataReader->close();
		$recordModel->save();
	}

	/**
	 * Function to remove relation M2M - for relation many to many.
	 *
	 * @param string $module
	 * @param int    $crmid
	 * @param string $withModule
	 * @param int    $withCrmid
	 */
	public function deleteRelatedM2M($crmid, $withModule, $withCrmid)
	{
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($this->moduleName, $withModule);
		\App\Db::getInstance()->createCommand()->delete($referenceInfo['table'], [$referenceInfo['base'] => $withCrmid, $referenceInfo['rel'] => $crmid])->execute();
	}

	/**
	 * @param int    $crmid
	 * @param string $withModule
	 * @param int    $withCrmid
	 */
	public function deleteRelatedFromDB($crmid, $withModule, $withCrmid)
	{
		App\Db::getInstance()->createCommand()->delete('vtiger_crmentityrel', ['or',
			[
				'crmid' => $crmid,
				'relmodule' => $withModule,
				'relcrmid' => $withCrmid,
			],
			[
				'relcrmid' => $crmid,
				'module' => $withModule,
				'crmid' => $withCrmid,
			],
		])->execute();
	}

	public function updateMissingSeqNumber($module)
	{
		\App\Log::trace('Entered updateMissingSeqNumber function');
		\VtlibUtils::vtlibSetupModulevars($module, $this);
		$tabid = \App\Module::getModuleId($module);
		if (!\App\Fields\RecordNumber::isModuleSequenceConfigured($tabid)) {
			return;
		}

		$fieldinfo = (new App\Db\Query())->from('vtiger_field')
			->where(['tabid' => $tabid, 'uitype' => 4])->one();
		$returninfo = [];

		if ($fieldinfo) {
			$fieldTable = $fieldinfo['tablename'];
			$fieldColumn = $fieldinfo['columnname'];
			if ($fieldTable === $this->table_name) {
				$dataReader = (new App\Db\Query())->select(['recordid' => $this->table_index])
					->from($this->table_name)
					->where(['or', [$fieldColumn => ''], [$fieldColumn => null]])
					->createCommand()->query();
				$totalCount = $dataReader->count();
				if ($totalCount) {
					$returninfo['totalrecords'] = $totalCount;
					$returninfo['updatedrecords'] = 0;
					$moduleData = \App\Fields\RecordNumber::getNumber($tabid);
					$sequenceNumber = $moduleData['sequenceNumber'];
					$prefix = $moduleData['prefix'];
					$postfix = $moduleData['postfix'];
					$oldNumber = $sequenceNumber;
					while ($recordinfo = $dataReader->read()) {
						$recordNumber = \App\Fields\RecordNumber::parse($prefix . $sequenceNumber . $postfix);
						App\Db::getInstance()->createCommand()
							->update($fieldTable, [$fieldColumn => $recordNumber], [$this->table_index => $recordinfo['recordid']])
							->execute();
						$sequenceNumber += 1;
						$returninfo['updatedrecords'] = $returninfo['updatedrecords'] + 1;
					}
					$dataReader->close();
					if ($oldNumber != $sequenceNumber) {
						\App\Fields\RecordNumber::updateNumber($sequenceNumber, $tabid);
					}
				}
			} else {
				\App\Log::error('Updating Missing Sequence Number FAILED! REASON: Field table and module table mismatching.');
			}
		}
		return $returninfo;
	}

	/**
	 * Save the related module record information. Triggered from CRMEntity->saveentity method or updateRelations.php.
	 *
	 * @param string This module name
	 * @param int This module record number
	 * @param string Related module name
	 * @param mixed Integer or Array of related module record number
	 * @param string function name
	 */
	public function saveRelatedModule($module, $crmid, $withModule, $withCrmid, $relatedName = false)
	{
		if (!is_array($withCrmid)) {
			$withCrmid = [$withCrmid];
		}
		switch ($relatedName) {
			case 'getManyToMany':
				$this->saveRelatedM2M($module, $crmid, $withModule, $withCrmid);
				break;
			case 'getDependentsList':
				break;
			case 'getActivities':
				break;
			default:
				$this->saveRelatedToDB($module, $crmid, $withModule, $withCrmid);
				break;
		}
	}

	/**
	 * Function to save relation between records in relation many to many.
	 *
	 * @param string $module
	 * @param int    $crmid
	 * @param string $withModule
	 * @param int[]  $withCrmid
	 */
	public function saveRelatedM2M($module, $crmid, $withModule, $withCrmid)
	{
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($module, $withModule);
		foreach ($withCrmid as $relcrmid) {
			// Relation already exists? No need to add again
			if ((new App\Db\Query())->from($referenceInfo['table'])
				->where([$referenceInfo['base'] => $relcrmid, $referenceInfo['rel'] => $crmid])
				->exists()) {
				continue;
			}
			\App\Db::getInstance()->createCommand()->insert($referenceInfo['table'], [
				$referenceInfo['base'] => $relcrmid,
				$referenceInfo['rel'] => $crmid,
			])->execute();
		}
	}

	/**
	 * Function add info about relations.
	 *
	 * @param string $module
	 * @param int    $crmid
	 * @param string $withModule
	 * @param int[]  $withCrmid
	 */
	public function saveRelatedToDB($module, $crmid, $withModule, $withCrmid)
	{
		foreach ($withCrmid as $relcrmid) {
			if ($withModule === 'Documents') {
				$checkpresence = (new \App\Db\Query())->select(['crmid'])->from('vtiger_senotesrel')->where(['crmid' => $crmid, 'notesid' => $relcrmid])->exists();
				if ($checkpresence) {
					continue;
				}
				\App\Db::getInstance()->createCommand()->insert('vtiger_senotesrel', [
					'crmid' => $crmid,
					'notesid' => $relcrmid,
				])->execute();
			} else {
				$checkpresence = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentityrel')->where(['crmid' => $crmid, 'module' => $module, 'relcrmid' => $relcrmid, 'relmodule' => $withModule])->exists();
				if ($checkpresence) {
					continue;
				}
				\App\Db::getInstance()->createCommand()->insert('vtiger_crmentityrel', [
					'crmid' => $crmid,
					'module' => $module,
					'relcrmid' => $relcrmid,
					'relmodule' => $withModule,
					'rel_created_user' => \App\User::getCurrentUserId(),
					'rel_created_time' => date('Y-m-d H:i:s'),
				])->execute();
			}
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string This module name
	 * @param array List of Entity Id's from which related records need to be transfered
	 * @param int Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$db = PearDatabase::getInstance();

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$relTables = $this->setRelationTables();
		if (array_key_exists('Documents', $relTables)) {
			$relTables['Attachments'] = ['vtiger_seattachmentsrel' => ['crmid', 'attachmentsid']];
		}
		foreach ($transferEntityIds as &$transferId) {
			// Pick the records related to the entity to be transfered, but do not pick the once which are already related to the current entity.
			$relatedRecords = $db->pquery('SELECT relcrmid, relmodule FROM vtiger_crmentityrel WHERE crmid=? && module=?' .
				' && relcrmid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid=? && module=?)', [$transferId, $module, $entityId, $module]);
			while ($row = $db->getRow($relatedRecords)) {
				$where = 'relcrmid = ? && relmodule = ? && crmid = ? && module = ?';
				$params = [$row['relcrmid'], $row['relmodule'], $transferId, $module];
				$db->update('vtiger_crmentityrel', ['crmid' => $entityId], $where, $params);
			}
			// Pick the records to which the entity to be transfered is related, but do not pick the once to which current entity is already related.
			$parentRecords = $db->pquery('SELECT crmid, module FROM vtiger_crmentityrel WHERE relcrmid=? && relmodule=?' .
				' && crmid NOT IN (SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid=? && relmodule=?)', [$transferId, $module, $entityId, $module]);
			while ($row = $db->getRow($parentRecords)) {
				$where = 'crmid = ? && module = ? && relcrmid = ? && relmodule = ?';
				$params = [$row['crmid'], $row['module'], $transferId, $module];
				$db->update('vtiger_crmentityrel', ['relcrmid' => $entityId], $where, $params);
			}

			$db->update('vtiger_modtracker_basic', ['crmid' => $entityId], 'crmid = ? && status <> ?', [$transferId, 7]);
			foreach ($relTables as &$relTable) {
				$idField = current($relTable)[1];
				$entityIdField = current($relTable)[0];
				$relTableName = key($relTable);
				// IN clause to avoid duplicate entries
				$sql = "SELECT $idField FROM $relTableName WHERE $entityIdField = ? " .
					" && $idField NOT IN ( SELECT $idField FROM $relTableName WHERE $entityIdField = ? )";
				$selResult = $db->pquery($sql, [$transferId, $entityId]);
				if ($db->getRowCount($selResult) > 0) {
					while (($idFieldValue = $db->getSingleValue($selResult)) !== false) {
						$db->update($relTableName, [
							$entityIdField => $entityId,
							], "$entityIdField = ? and $idField = ?", [$transferId, $idFieldValue]
						);
					}
				}
			}
			$fields = App\Field::getRelatedFieldForModule(false, $module);
			foreach ($fields as &$field) {
				$columnName = $field['columnname'];
				$db->update($field['tablename'], [
					$columnName => $entityId,
					], "$columnName = ?", [$transferId]
				);
			}
		}
		\App\Log::trace('Exiting transferRelatedRecords...');
	}

	/**
	 * To keep track of action of field filtering and avoiding doing more than once.
	 *
	 * @var array
	 */
	protected $__inactive_fields_filtered = false;

	/**
	 * Filter in-active fields based on type.
	 *
	 * @param string $module
	 */
	public function filterInactiveFields($module)
	{
		if ($this->__inactive_fields_filtered) {
			return;
		}
		// Look for fields that has presence value NOT IN (0,2)
		$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module, ['1']);
		if ($cachedModuleFields === false) {
			// Initialize the fields calling suitable API
			vtlib\Deprecated::getColumnFields($module);
			$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module, ['1']);
		}

		$hiddenFields = [];

		if ($cachedModuleFields) {
			foreach ($cachedModuleFields as $fieldinfo) {
				$fieldLabel = $fieldinfo['fieldlabel'];
				// NOTE: We should not translate the label to enable field diff based on it down
				$fieldName = $fieldinfo['fieldname'];
				$tableName = str_replace('vtiger_', '', $fieldinfo['tablename']);
				$hiddenFields[$fieldLabel] = [$tableName => $fieldName];
			}
		}

		if (isset($this->list_fields)) {
			$this->list_fields = array_diff_assoc($this->list_fields, $hiddenFields);
		}

		if (isset($this->search_fields)) {
			$this->search_fields = array_diff_assoc($this->search_fields, $hiddenFields);
		}

		// To avoid re-initializing everytime.
		$this->__inactive_fields_filtered = true;
	}

	/**
	 * @param string $tableName
	 *
	 * @return string
	 */
	public function getJoinClause($tableName)
	{
		if (strripos($tableName, 'rel') === (strlen($tableName) - 3)) {
			return 'LEFT JOIN';
		} elseif ($tableName == 'vtiger_entity_stats' || $tableName == 'u_yf_openstreetmap') {
			return 'LEFT JOIN';
		}
		return 'INNER JOIN';
	}

	/**
	 * @param <type> $module
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	public function getNonAdminAccessQuery($module, $parentRole, $userGroups)
	{
		$query = $this->getNonAdminUserAccessQuery($parentRole, $userGroups);
		if (!empty($module)) {
			$moduleAccessQuery = $this->getNonAdminModuleAccessQuery($module);
			if (!empty($moduleAccessQuery)) {
				$query .= " UNION $moduleAccessQuery";
			}
		}
		return $query;
	}

	/**
	 * The function retrieves access to queries for users without administrator rights.
	 *
	 * @param Users  $user
	 * @param string $parentRole
	 * @param array  $userGroups
	 *
	 * @return string
	 */
	public function getNonAdminUserAccessQuery($parentRole, $userGroups)
	{
		$userId = \App\User::getCurrentUserId();
		$query = "(SELECT $userId as id) UNION (SELECT vtiger_user2role.userid AS userid FROM " .
			'vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid ' .
			'INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE ' .
			"vtiger_role.parentrole like '$parentRole::%')";
		if (count($userGroups) > 0) {
			$query .= ' UNION (SELECT groupid FROM vtiger_groups where' .
				' groupid in (' . implode(',', $userGroups) . '))';
		}
		return $query;
	}

	/**
	 * This function takes access to the module for users without administrator privileges.
	 *
	 * @param string $module
	 * @param Users  $user
	 *
	 * @return string
	 */
	public function getNonAdminModuleAccessQuery($module)
	{
		$userId = \App\User::getCurrentUserId();
		require 'user_privileges/sharing_privileges_' . $userId . '.php';
		$tabId = \App\Module::getModuleId($module);
		$sharingRuleInfoVariable = $module . '_share_read_permission';
		$sharingRuleInfo = $$sharingRuleInfoVariable;
		$query = '';
		if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
			count($sharingRuleInfo['GROUP']) > 0)) {
			$query = ' (SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per ' .
				"WHERE userid=$userId && tabid=$tabId) UNION (SELECT " .
				'vtiger_tmp_read_group_sharing_per.sharedgroupid FROM ' .
				"vtiger_tmp_read_group_sharing_per WHERE userid=$userId && tabid=$tabId)";
		}
		return $query;
	}

	/**
	 * Returns the terms of non-administrator changes.
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	public function listQueryNonAdminChange($query)
	{
		//make the module base table as left hand side table for the joins,
		//as mysql query optimizer puts crmentity on the left side and considerably slow down
		$query = preg_replace('/\s+/', ' ', $query);
		if (strripos($query, ' WHERE ') !== false) {
			\VtlibUtils::vtlibSetupModulevars($this->moduleName, $this);
			$query = str_ireplace(' WHERE ', " WHERE $this->table_name.$this->table_index > 0  AND ", $query);
		}
		return $query;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param string $secModule - $secmodule secondary module name
	 * @return array returns the array with table names and fieldnames storing relations
	 * between module and this module
	 */

	public function setRelationTables($secModule = false)
	{
		$relTables = [
			'Documents' => [
				'vtiger_senotesrel' => ['crmid', 'notesid'],
				$this->table_name => $this->table_index,
			],
			'OSSMailView' => [
				'vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'],
				$this->table_name => $this->table_index,
			],
		];
		if ($secModule === false) {
			return $relTables;
		}
		return $relTables[$secModule];
	}

	/**
	 * Function to track when a new record is linked to a given record.
	 */
	public static function trackLinkedInfo($crmId)
	{
		$currentTime = date('Y-m-d H:i:s');
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => \App\User::getCurrentUserId()], ['crmid' => $crmId])->execute();
	}

	/**
	 * Function to track when a record is unlinked to a given record.
	 *
	 * @param int $crmId
	 */
	public function trackUnLinkedInfo($crmId)
	{
		$currentTime = date('Y-m-d H:i:s');
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => \App\User::getCurrentUserId()], ['crmid' => $crmId])->execute();
	}

	public function getLockFields()
	{
		if (isset($this->lockFields)) {
			return $this->lockFields;
		}
		return false;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($moduleName && $eventType === 'module.postinstall') {
		} elseif ($eventType === 'module.disabled') {
		} elseif ($eventType === 'module.preuninstall') {
		} elseif ($eventType === 'module.preupdate') {
		} elseif ($eventType === 'module.postupdate') {
		}
	}
}
