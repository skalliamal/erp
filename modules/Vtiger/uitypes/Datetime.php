<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * Uitype: 80.
 */
class Vtiger_Datetime_UIType extends Vtiger_Date_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (isset($this->validate[$value]) || empty($value)) {
			return;
		}
		$arrayDateTime = explode(' ', $value, 2);
		$cnt = count($arrayDateTime);
		if ($cnt === 1) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
		} elseif ($cnt === 2) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
			(new Vtiger_Time_UIType())->validate($arrayDateTime[1], $isUserFormat); //Time
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		switch ($this->getFieldModel()->getUIType()) {
			case 79:
				return App\Fields\DateTime::formatToDb($value);
			default:
				return parent::getDBValue($value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		switch ($this->getFieldModel()->getUIType()) {
			case 80:
				return $rawText ? Vtiger_Util_Helper::formatDateDiffInStrings($value) : '<span title="' . App\Fields\DateTime::formatToDisplay($value) . '">' . Vtiger_Util_Helper::formatDateDiffInStrings($value) . '</span>';
			default:
				return App\Fields\DateTime::formatToDisplay($value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		switch ($this->getFieldModel()->getUIType()) {
			case 80:
				return $rawText ? \App\Fields\DateTime::formatToViewDate($value) : '<span title="' . App\Fields\DateTime::formatToDisplay($value) . '">' . \App\Fields\DateTime::formatToViewDate($value) . '</span>';
		}
		return \App\TextParser::textTruncate($this->getDisplayValue($value, $record, $recordModel, $rawText), $this->getFieldModel()->get('maxlengthtext'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		switch ($this->getFieldModel()->getUIType()) {
			case 79:
				return 'Edit/Field/DateTimeField.tpl';
			default:
				return 'Edit/Field/DateTime.tpl';
		}
	}
}
