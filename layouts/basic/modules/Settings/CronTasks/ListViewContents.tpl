{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o.
********************************************************************************/
-->*}
{strip}
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">

	<div class="tpl-Settings-CronTasks-ListViewContents listViewEntriesDiv u-overflow-scroll-xs-down">
		<span class="listViewLoadingImageBlock d-none modal" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</span>
		{assign var="NAME_FIELDS" value=$MODULE_MODEL->getNameFields()}
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<table class="table tableRWD table-bordered table-sm  listViewEntriesTable">
			<thead col-visible-alltime='2'>
				<tr class="listViewHeaders">
					<th width="1%" class="{$WIDTHTYPE}"></th>
						{assign var=WIDTH value={99/(count($LISTVIEW_HEADERS))}}
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<th  {if $LISTVIEW_HEADER@last}colspan="1" {/if} class="{$WIDTHTYPE}">
							<a  {if !($LISTVIEW_HEADER->has('sort'))} class="listViewHeaderValues u-cursor-pointer" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}" {/if}>{\App\Language::translate($LISTVIEW_HEADER->get('label'), $QUALIFIED_MODULE)}
								{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}&nbsp;&nbsp;<span class="{$SORT_IMAGE}"></span>{/if}</a>
						</th>
					{/foreach}
					<th  class="{$WIDTHTYPE}"></th>
				</tr>
			</thead>
			<tbody>
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
					<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}"{' '}
						{if method_exists($LISTVIEW_ENTRY,'getDetailViewUrl')}data-recordurl="{$LISTVIEW_ENTRY->getDetailViewUrl()}"{/if}
						>
						<td width="1%" nowrap class="{$WIDTHTYPE}">
							<span class="fas fa-ellipsis-v"
								  title="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE))}"></span>
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
							{assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
							{assign var="VALUE" $LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}

							<td class="listViewEntryValue {$WIDTHTYPE}"  >
								&nbsp;
								{if $LISTVIEW_HEADERNAME==='duration'}
									{if $VALUE==='running'}<i class="fa fa-spinner fa-spin" title="{\App\Language::translate('LBL_IS_RUNNING',$QUALIFIED_MODULE)}"></i>
									{elseif $VALUE==='timeout'}<i class="fa fa-exclamation-triangle text-danger" title="{\App\Language::translate('LBL_HAD_TIMEOUT',$QUALIFIED_MODULE)}"></i>
									{else}{$VALUE}
									{/if}
								{else}
									{$VALUE}
								{/if}
								{if $LAST_COLUMN && $LISTVIEW_ENTRY->getRecordLinks()}
								</td><td nowrap class="{$WIDTHTYPE}">
									<div class="float-right actions">
										<span class="actionImages">
											{foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
												{assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
												<a {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};
														if (event.stopPropagation){ldelim}
																	event.stopPropagation();{rdelim} else{ldelim}
																				event.cancelBubble = true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
													<span class="{$RECORD_LINK->getIcon()} alignMiddle" title="{\App\Language::translate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></span>
												</a>
												{if !$RECORD_LINK@last}
													&nbsp;&nbsp;
												{/if}
											{/foreach}
										</span>
									</div>
								</td>
							{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>

		<!--added this div for Temporarily -->
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<table class="emptyRecordsDiv">
				<tbody>
					<tr>
						<td>
							{\App\Language::translate('LBL_NO')} {\App\Language::translate($MODULE, $QUALIFIED_MODULE)} {\App\Language::translate('LBL_FOUND')}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
</div>
{/strip}
