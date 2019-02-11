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
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" id="listMaxEntriesMassEdit" value="{\AppConfig::main('listMaxEntriesMassEdit')}" />
	<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">

	{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $MODULE_NAME)}
	<div class="clearfix"></div>
	<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="selectAllMsg" href="#">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}&nbsp;{\App\Language::translate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
	</div>
	<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="deSelectAllMsg" href="#">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
	</div>
	<div class="listViewEntriesDiv u-overflow-scroll-xs-down">
		<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
		<div class="listViewLoadingImageBlock d-none modal noprint" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</div>
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<table class="table tableBorderHeadBody listViewEntriesTable {$WIDTHTYPE} {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if}">
			<thead>
				<th class="noWrap p-2">N° ordre</th>
				<th class="noWrap p-2">N° bon de commande</th>
				<th class="noWrap p-2">Support</th>
				<th class="noWrap p-2">Type ordre</th>
				<th class="noWrap p-2">Date ordre</th>
				<th class="noWrap p-2">Édition</th>
				<th class="noWrap p-2">Raison sociale</th>
				<th class="noWrap p-2">Commercial</th>
				<th class="noWrap p-2">Signataire</th>
			</thead>
			<tbody>
				{foreach item=ROWS from=$LISTVIEW_ENTRIES name=listview}
					<tr class="listViewEntries" onclick="goToAffectation({$ROWS[0]}, {$ROWS[1]})">
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[1]}
						</td>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[2]}
						</td>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[3]}
						</td>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[4]}
						</td>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[5]}
						</td>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[6]}
						</td>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[7]}
						</td>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[8]}
						</td>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{$ROWS[9]}
						</td>
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
							Aucun ordre à affecter
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
{/strip}

<script type="text/javascript">
$(document).ready(function() {

});

function goToAffectation(ordreId, numOrdre){
	window.location.href="index.php?module=Atribution&view=Edit&ordreId="+ordreId+"&numOrdre="+numOrdre;
}
</script>