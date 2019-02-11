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
class Settings_LayoutEditor_importCustomField_View extends Settings_Vtiger_Index_View
{


	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		if(!empty($request->get('action_import'))){
			Settings_LayoutEditor_Field_Action::import_fields(
				in_array ( "add" , $request->get('action_import') ),
				in_array ( "update", $request->get('action_import') )
			);
		}
		$viewer->view('ImportCustomField.tpl', $qualifiedModuleName);
	}
}
