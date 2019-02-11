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
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="tpl-QuickCreate modelContainer modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-full mx-auto" role="document">
			<div class="modal-content">
				<form class="form-horizontal recordEditView" id="quickCreate" name="QuickCreate" method="post"
					  action="index.php">
					<div class="modal-header col-12 m-0 align-items-center form-row d-flex justify-content-between pb-1">
						<div class="col-xl-6 col-12">
							<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
								<span class="col-12">
									<span class="fas fa-plus mr-1"></span>
									<strong class="mr-1">{\App\Language::translate('LBL_QUICK_CREATE', $MODULE)}:</strong>
									<strong class="text-uppercase"><span class="userIcon-{$MODULE} mx-1"></span>{\App\Language::translate('LBL_EVENT_OR_TASK', $MODULE)}</strong>
								</span>
							</h5>
						</div>
						<div class="col-xl-6 col-12 text-center text-xl-right">
							{assign var="CALENDAR_MODULE_MODEL" value=$QUICK_CREATE_CONTENTS['Calendar']['moduleModel']}
							{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='quickcreateViewHeader'}
							{/foreach}
							{if $MODULE_NAME eq 'Calendar'}
								{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateTaskRecordUrl()}
							{else}
								{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateEventRecordUrl()}
							{/if}
							<button class="btn btn-outline-secondary mr-0 mr-md-1 mb-2 mb-md-0 col-12 col-md-4 u-text-ellipsis" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button">
								<strong>{\App\Language::translate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong>
							</button>
							<button class="btn btn-success col-12 col-md-1 mb-2 mb-md-0" type="submit" title="{\App\Language::translate('LBL_SAVE', $MODULE)}">
								<strong><span class="fas fa-check"></span></strong>
							</button>
							<button class="cancelLink btn btn-danger col-12 col-md-1 ml-0 ml-md-1" type="reset" aria-hidden="true" data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">
								<span class="fas fa-times"></span>
							</button>
						</div>
					</div>
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}'/>
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}'/>
					{/if}
					<input type="hidden" name="action" value="SaveAjax"/>
					<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}"/>
					<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}"/>
					<input type="hidden" name="userChangedEndDateTime" value="0"/>
					<!-- Random number is used to make specific tab is opened -->
					{assign var="RAND_NUMBER" value=rand()}
					<div class="modal-body m-0 tabbable">
						<ul class="nav nav-pills">
							<li class="nav-item">
								<a class="nav-link active show" href="javascript:void(0);"
								   data-target=".EventsQuikcCreateContents_{$RAND_NUMBER}" data-toggle="tab"
								   data-tab-name="Event">{\App\Language::translate('LBL_EVENT',$MODULE)}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="javascript:void(0);"
								   data-target=".CalendarQuikcCreateContents_{$RAND_NUMBER} " data-toggle="tab"
								   data-tab-name="Task">{\App\Language::translate('LBL_TASK',$MODULE)}</a>
							</li>
						</ul>
						<div class="tab-content overflowVisible">
							{foreach item=MODULE_DETAILS key=MODULE_NAME from=$QUICK_CREATE_CONTENTS}
								<div class="{$MODULE_NAME}QuikcCreateContents_{$RAND_NUMBER} tab-pane {if $MODULE_NAME eq 'Events'} active show {/if}fade"
									 role="tabpanel">
									<input type="hidden" name="module" value="{$MODULE_NAME}"/>
									<input type="hidden" name="mode" value="{if $MODULE_NAME eq 'Calendar'}calendar{else}events{/if}"/>
									{assign var="RECORD_STRUCTURE_MODEL" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['recordStructureModel']}
									{assign var="RECORD_STRUCTURE" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['recordStructure']}
									{assign var="MODULE_MODEL" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['moduleModel']}
									<div class="quickCreateContent">
										<div class="massEditTable border-0 px-1 mx-auto m-0">
											<div class="px-0 m-0 form-row d-flex justify-content-center align-items-center">
												{assign var=COUNTER value=0}
												{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
												{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
												{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
												{assign var="refrenceListCount" value=count($refrenceList)}
												{if $COUNTER eq 2}
											</div>
											<div class="col-12 form-row px-0 m-0">
												{assign var=COUNTER value=1}
												{else}
												{assign var=COUNTER value=$COUNTER+1}
												{/if}
												<div class="col-md-6 py-2 form-row  px-0 m-0 {$WIDTHTYPE}">
													<div class="col-lg-12 col-xl-3 pl-0 text-lg-left text-xl-right u-text-ellipsis">
														{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
														{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
														<label class="muted small font-weight-bold px-2">
															{if $FIELD_MODEL->isMandatory() eq true}
																<span class="redColor">*</span>
															{/if}
															{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
																<a href="#" class="js-help-info float-right" title=""
																   data-placement="top"
																   data-content="{\App\Language::translate($HELPINFO_LABEL, 'HelpInfo')}"
																   data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}'>
																	<span class="fas fa-info-circle"></span>
																</a>
															{/if}
															{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
														</label>
													</div>
													<div class="fieldValue col-lg-12 col-xl-9 px-0 px-sm-1">
														{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
													</div>
												</div>
												{/foreach}
											</div>
										</div>
										<div class="float-right d-flex my-2">
											<div class="btn-group-toggle pr-1" data-toggle="buttons">
												<label class="btn c-btn-checkbox c-btn-outline-done js-btn--mark-as-completed"  data-js="click">
													<strong>
														<span class="far fa-square fa-lg mr-1 c-btn-checkbox--unchecked"></span>
														<span class="far fa-check-square fa-lg mr-1 c-btn-checkbox--checked"></span>
														<input type="checkbox" checked autocomplete="off">{\App\Language::translate('LBL_MARK_AS_HELD', $MODULE)}
													</strong>
												</label>
											</div>
											<button class="btn btn-success" type="submit">
												<strong>
													<span class="fas fa-check mr-1"></span>
													{\App\Language::translate('LBL_SAVE', $MODULE)}
												</strong>
											</button>
										</div>
										{if AppConfig::module($MODULE, 'SHOW_DAYS_QUICKCREATE')}
											<div class="form-row px-0 mx-0 col-12 eventsTable"></div>
										{/if}
									</div>
								</div>
							{/foreach}
							{if $COUNTER eq 1}
								<div class="col-md-6 form-row align-items-center p-1 fieldsLabelValue {$WIDTHTYPE} px-0"></div>
							{/if}
						</div>
					</div>
					{if !empty($SOURCE_RELATED_FIELD)}
						{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
							<input type="hidden" name="{$RELATED_FIELD_NAME}"
								   value="{\App\Purifier::encodeHtml($RELATED_FIELD_MODEL->get('fieldvalue'))}"
								   data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}"/>
						{/foreach}
					{/if}
				</form>
			</div>
		</div>
	</div>
{/strip}
