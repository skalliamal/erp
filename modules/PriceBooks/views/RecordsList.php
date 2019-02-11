<?php

/**
 * PriceBooks records list view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PriceBooks_RecordsList_View extends Vtiger_RecordsList_View
{
	/**
	 * {@inheritdoc}
	 */
	public function initializeContent(\App\Request $request)
	{
		if ($request->isEmpty('currency_id', true)) {
			$defaultCurrency = vtlib\Functions::getDefaultCurrencyInfo();
			$request->set('currency_id', $defaultCurrency['id']);
		}
		parent::initializeContent($request);
	}
}
