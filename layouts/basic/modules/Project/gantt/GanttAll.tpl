{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="tpl-Project-gantt-GanttAll">
	<div class="noprint mb-2">
		<div class="row">
			<div class="col-lg-5 col-12">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS=buttonTextHolder}
			</div>
			<div class="col-lg-7 col-12 d-flex flex-wrap flex-sm-nowrap flex-md-nowrap flex-lg-nowrap justify-content-center justify-content-md-end justify-content-lg-end">
				<div class="customFilterMainSpan">
					{if $CUSTOM_VIEWS|@count gt 0}
					<select id="customFilter" class="form-control select2" title="{\App\Language::translate('LBL_CUSTOM_FILTER')}">
						{foreach item="CUSTOM_VIEW" from=$CUSTOM_VIEWS}
							<option value="{$CUSTOM_VIEW->get('cvid')}"{/strip} {strip}data-id="{$CUSTOM_VIEW->get('cvid')}" {if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected" {elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected" {/if} class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE)}</option>
						{/foreach}
					</select>
					<span class="fas fa-filter filterImage mr-2" style="display:none;"></span>
					{else}
					<input type="hidden" value="0" id="customFilter"/>
					{/if}
				</div>
			</div>
		</div>
	</div>
	{include file=\App\Layout::getTemplatePath('gantt/GanttContents.tpl', $MODULE)}
</div>
{/strip}
