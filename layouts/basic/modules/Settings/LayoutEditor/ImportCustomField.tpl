{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}

	<form class="form-horizontal createCustomFieldForm"  method="POST">
		<div class="modal-body">
			<div class="form-group">
				<div class="row">
					<div class="col-md-2">
						Action d'importation :
					</div>
					<div class="col-md-9 controls">
						<div class="row">
							<div class="col-md-3 text-right">
								<input type="checkbox" name="action_import[]" value="add"> 
								<strong style="margin-left:5px;">Ajouter</strong>
							</div>
							<div class="col-md-3 text-right">
								<input type="checkbox" name="action_import[]" value="update"> 
								<strong style="margin-left:5px;">Modifier</strong>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_IMPORT'}
	</form>
{/strip}