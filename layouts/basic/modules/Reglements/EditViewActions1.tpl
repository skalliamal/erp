{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="tpl-EditViewActions c-form__action-panel">
	{*	{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}*}
		<button class="btn btn-success u-mr-5px" type="submit" name="mode" value="updateBordereauEdicomBanque">
			<span class="fas fa-check u-mr-5px"></span>
			<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
		</button>
	{*	<button class="btn btn-success" type="submit" name="mode" value="genererPDF" formtarget="_blank">
			<span class="fa fa-print"></span>
			<strong>Generer PDF</strong>
		</button>*}
		{*<button id="update">update</button>*}

	</div>

{/strip}
