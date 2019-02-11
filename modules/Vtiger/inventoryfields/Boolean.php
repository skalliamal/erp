<?php

/**
 * Inventory Boolean Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Boolean_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Boolean';
	protected $defaultLabel = 'LBL_BOOLEAN';
	protected $columnName = 'bool';
	protected $dbType = \yii\db\Schema::TYPE_BOOLEAN;
	protected $onlyOne = false;

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (!in_array($value, [0, 1, '1', '0', 'on'])) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditValue($value)
	{
		return (bool) $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return (bool) $value ? App\Language::translate('LBL_YES') : App\Language::translate('LBL_NO');
	}
}
