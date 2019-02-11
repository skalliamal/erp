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
	{include file=\App\Layout::getTemplatePath('Header.tpl', $MODULE)}
	<div class="bodyContents">
		<div class="mainContainer">
			<div class="o-breadcrumb js-breadcrumb widget_header mb-2 d-flex justify-content-between px-2"
				 data-js="container">
				<div class="o-breadcrumb__container">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				</div>
				<a class="btn btn-outline-dark d-md-none my-auto o-breadcrumb__actions-btn js-breadcrumb__actions-btn" href="#" data-js="click" role="button"
				   aria-expanded="false" aria-controls="o-view-actions__container">
							<span class="fas fa-ellipsis-h fa-fw"
								  title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
				</a>
				<div class="my-auto o-breadcrumb__actions js-breadcrumb__actions" id="o-view-actions__container">
					<div class="float-right btn-toolbar btn-group">
						{foreach item=LINK from=$HEADER_LINKS['LIST_VIEW_HEADER']}
							{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewHeader' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
						{/foreach}
					</div>
				</div>
			</div>
			<div class="contentsDiv">
				{include file=\App\Layout::getTemplatePath('ListViewHeader.tpl', $MODULE)}
			{/strip}
