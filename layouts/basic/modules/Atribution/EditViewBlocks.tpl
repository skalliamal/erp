{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
<div class='verticalScroll'>
	<div class='editViewContainer'>
		<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php"
			  enctype="multipart/form-data">
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
			{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
				<input type="hidden" name="picklistDependency"
					   value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}'/>
			{/if}
			{if !empty($MAPPING_RELATED_FIELD)}
				<input type="hidden" name="mappingRelatedField"
					   value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}'/>
			{/if}
			{assign var=QUALIFIED_MODULE_NAME value={$QUALIFIED_MODULE}}
			{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
			{if $PARENT_MODULE neq ''}
				<input type="hidden" name="module" value="{$MODULE}"/>
				<input type="hidden" name="parent" value="{$PARENT_MODULE}"/>
				<input type="hidden" value="{$VIEW}" name="view"/>
			{else}
				<input type="hidden" name="module" value="{$MODULE}"/>
			{/if}
			<input type="hidden" name="action" value="Save"/>
			<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}"/>
			<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}"/>
			<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}"/>
			{if $MODE === 'duplicate'}
				<input type="hidden" name="_isDuplicateRecord" value="true"/>
				<input type="hidden" name="_duplicateRecord" value="{\App\Request::_get('record')}"/>
			{/if}
			{if $IS_RELATION_OPERATION }
				<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}"/>
				<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}"/>
				<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}"/>
			{/if}
			{foreach from=$RECORD->getModule()->getFieldsByDisplayType(9) item=FIELD key=FIELD_NAME}
				<input type="hidden" name="{$FIELD_NAME}"
					   value="{$FIELD->getEditViewDisplayValue($RECORD->get($FIELD_NAME),$RECORD)}"/>
			{/foreach}
			<div class='widget_header row mb-3'>
				<div class="col-md-8">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				</div>
			</div>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
				{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
				{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
				{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
				{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
				{if $BLOCKS_HIDE && ($RECORD_ID || !$RECORD_ID && $BLOCK_LABEL eq "Afféctation")}
					<div class="js-toggle-panel c-panel c-panel--edit row  mx-1 mb-3" data-js="click"
						 data-label="{$BLOCK_LABEL}">
						<div class="blockHeader c-panel__header align-items-center">
							{if $BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION'}
								{assign var=SEARCH_ADDRESS value=TRUE}
							{else}
								{assign var=SEARCH_ADDRESS value=FALSE}
							{/if}
							<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}"
								  data-js="click" data-mode="hide"
								  data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 {if ($IS_HIDDEN)}d-none{/if}"
								  data-js="click" data-mode="show"
								  data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<h5 class="m-0">{\App\Language::translate($BLOCK_LABEL, $QUALIFIED_MODULE_NAME)}</h5>
						</div>
						<div class="c-panel__body c-panel__body--edit blockContent js-block-content {if $IS_HIDDEN}d-none{/if}"
							 data-js="display">
							{if $BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION'}
									<div class="{if !$SEARCH_ADDRESS} {/if} adressAction row py-2 justify-content-center">
										{include file=\App\Layout::getTemplatePath('BlockHeader.tpl', $MODULE)}
									</div>
							{/if}
							<div class="row">
								{assign var=COUNTER value=0}
								{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{if $FIELD_MODEL->getUIType() eq '20' || $FIELD_MODEL->getUIType() eq '19' || $FIELD_MODEL->getUIType() eq '300'}
								{if $COUNTER eq '1'}
								{assign var=COUNTER value=0}
								{/if}
								{/if}
								{if $COUNTER eq 2}
								{assign var=COUNTER value=1}
								{else}
								{assign var=COUNTER value=$COUNTER+1}
								{/if}
								<div class="{if $FIELD_MODEL->get('label') eq "FL_REAPEAT"} col-sm-3
								{elseif $FIELD_MODEL->get('label') eq "FL_RECURRENCE"} col-sm-9
								{elseif $FIELD_MODEL->getUIType() neq "300"}col-sm-4
								{else} col-md-12 m-auto{/if} fieldRow row form-group align-items-center my-1">
									{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
									{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
									<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right u-text-small-bold">
										{if $FIELD_MODEL->isMandatory() eq true}<span class="redColor">*</span>{/if}
										{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
											<a href="#" class="js-help-info float-right" title="" data-placement="top" data-content="{\App\Language::translate($HELPINFO_LABEL, 'HelpInfo')}" data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"><span class="fas fa-info-circle"></span></a>
										{/if}
										{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE_NAME)}
									</label>
									<div class="{$WIDTHTYPE} w-100 {if $FIELD_MODEL->getUIType() neq "300"} col-lg-12 col-xl-9 {/if} fieldValue" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1}{elseif $FIELD_MODEL->getUIType() eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>

										{if $FIELD_NAME == "rubriques" }
											<select class="col-xl-12" id="rubriquesSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "prestations" }
											<select class="col-xl-12" id="prestationsSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "villes" }
											<select class="col-xl-12" id="villesSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "regions" }
											<select class="col-xl-12" id="regionsSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "prof_lib_specialites" }
											<select class="col-xl-12" id="prof_lib_specialitesSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "prof_lib_services" }
											<select class="col-xl-12" id="prof_lib_servicesSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "prof_lib_reseaux_sociaux" }
											<select class="col-xl-12" id="prof_lib_reseaux_sociauxSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "vignette_thematique_rubriques" }
											<select class="col-xl-12" id="vignette_thematique_rubriquesSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "vignette_localite_villes" }
											<select class="col-xl-12" id="vignette_localite_villesSelect2" multiple="multiple"></select>
										{/if}
										{if $FIELD_NAME == "vignette_regions_regions" }
											<select class="col-xl-12" id="vignette_regions_regionsSelect2" multiple="multiple"></select>
										{/if}

										{if $FIELD_MODEL->getUIType() eq "300"}
											<label class="u-text-small-bold">{if $FIELD_MODEL->isMandatory() eq true}
													<span class="redColor">*</span>
												{/if}{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
											</label>
										{/if}
										{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
									</div>
								</div>
								{/foreach}
							</div>
							
						</div>

					</div>
					{if $BLOCK_LABEL eq "Afféctation" }
						<div id="productsDiv" class="js-toggle-panel c-panel c-panel--edit row  mx-1 mb-3" data-js="click" data-label="Block">
							<div class="blockHeader c-panel__header align-items-center">
								<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}"
									  data-js="click" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
								<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 {if ($IS_HIDDEN)}d-none{/if}" data-js="click" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
								<h5 class="m-0">Produits</h5>
							</div>
							<div class="c-panel__body c-panel__body--edit blockContent js-block-content {if $IS_HIDDEN}d-none{/if}" data-js="display">
								<div class="row">
									<div class="col-xl-12">
										<span id="produitsOrdre"></span>
									</div>
								</div>
							</div>
						</div>
					{/if}
				{/if}
			{/foreach}
			{/strip}
			
<script type="text/javascript">

let blocks = {
	C: $('[data-label="elements_fabrication_catalogue"]'),
	ML: $('[data-label="elements_fabrication_module_logo"]'),
	VB: $('[data-label="elements_fabrication_video"]'),
	PDJ: $('[data-label="elements_fabrication_professionnel_du_jour"]'),
	EP: $('[data-label="elements_fabrication_espace_promo"]'),
	PL: $('[data-label="elements_fabrication_profession_lib"]'),
	VG: $('[data-label="elements_fabrication_video_graphique"]'),
	VT: $('[data-label="elements_fabrication_vignette_thematique"]'),
	VL: $('[data-label="elements_fabrication_vignette_localite"]'),
	VR: $('[data-label="elements_fabrication_vignette_regions"]'),
}
let select2Fields = {
	1: 'rubriques',
	2: 'villes',
	3: 'regions',
	4: 'prestations',
	5: 'prof_lib_specialites',
	6: 'prof_lib_services',
	7: 'prof_lib_reseaux_sociaux',
	8: 'vignette_thematique_rubriques',
	9: 'vignette_localite_villes',
	10: 'vignette_regions_regions',
};
let blockMandatoryFields = {
	elements_fabrication_catalogue: ['photos'],
	elements_fabrication_module_logo: ['description', 'logo', 'rubriques', 'prestations', 'villes', 'regions'],
	elements_fabrication_video: ['video','video2'],
	elements_fabrication_professionnel_du_jour: ['professionnel_description', 'professionnel_image', 'professionnel_lien'],
	elements_fabrication_espace_promo: ['espace_promo_titre', 'espace_promo_description', 'espace_promo_image1', 'espace_promo_image2'],
	elements_fabrication_profession_lib: ['prof_lib_specialites', 'prof_lib_parcours', 'prof_lib_services', 'prof_lib_video', 'prof_lib_images', 'prof_lib_reseaux_sociaux'],
	elements_fabrication_video_graphique: ['video_graphique1', 'video_graphique2'],
	elements_fabrication_vignette_thematique: ['vignette_thematique_rubriques'],
	elements_fabrication_vignette_localite: ['vignette_localite_villes'],
	elements_fabrication_vignette_regions: ['vignette_regions_regions'],
}

let imageInputs = ['photos', 'logo', 'professionnel_image', 'espace_promo_image1', 'espace_promo_image2', 'prof_lib_images'];

let recordId = getQueryVariable('record');
let ordreId = getQueryVariable('ordreId');
let numOrdre = getQueryVariable('numOrdre');
let format = $('#Atribution_editView_fieldName_format').val();

if(recordId){
	$('#productsDiv').hide();
	$('#Atribution_editView_fieldName_operateur_clear').parent().hide();
	$('#Atribution_editView_fieldName_operateur_select').parent().hide();

	$('#Atribution_editView_fieldName_ordre_clear').parent().hide();
	$('#Atribution_editView_fieldName_ordre_select').parent().hide();

	for (let key in select2Fields) {
		let fieldName = select2Fields[key];
		$('#Atribution_editView_fieldName_'+fieldName).hide();
	}

	for (let key in blocks) {
		blocks[key].hide();
	}
	blocks[format].show();
}
else
	$('#Atribution_editView_fieldName_statut').parent().parent().hide();

if(ordreId){
	$('#Atribution_editView_fieldName_ordre_clear').parent().hide();
	$('#Atribution_editView_fieldName_ordre_select').parent().hide();
	$('[name="ordre"]').val(ordreId);
	$('[name="ordre_display"]').val(numOrdre);
	$('[name="ordre_display"]').prop("readonly", true);
}
$(document).ready(function() {

	/*$('#mySelect2').select2({
	  ajax: {
	    url: 'index.php?action=Edicom&record=' + recordId + '&method=getRubriques',
	    data: function (params) {
	      let query = {
	        search: params.term,
	        type: 'public'
	      }

	      // Query parameters will be ?search=[term]&type=public
	      return query;
	    }
	  }
	});*/
	for (let key in select2Fields) {
		let fieldName = select2Fields[key];
		$('#'+fieldName+'Select2').select2({
		    tags: true,
		    tokenSeparators: [],
		    createTag: function (params) {
		    	if(fieldName == 'prestations'){
		    		let rubriquesLength = $('#rubriquesSelect2').select2('data').length;
			    	let prestationsLength = $('#prestationsSelect2').select2('data').length;
				    if(rubriquesLength*3 <= prestationsLength)
				    	return null;
		    	}
			    if(params.term.trim() == '')
			    	return null;
			    return {
					id: params.term,
					text: params.term
			    }
			}
		});
		$('#'+fieldName+'Select2').on('change.select2', function(){
			let data = $('#'+fieldName+'Select2').select2('data');
			let html = '';
			for (let key in data) {
				let text = data[key].text;
				html += text + '\n';
			}
			html = html.slice(0, -1);
			$('#Atribution_editView_fieldName_'+fieldName).val(html);
		});
	}

	if(recordId){
		for (let key in select2Fields) {
			let fieldName = select2Fields[key];
			let fieldValues = $('#Atribution_editView_fieldName_'+fieldName).val().split('\n');
			for (let key in fieldValues) {
				let fieldValue = fieldValues[key];
				if(fieldValue !=  '')
					$('#'+fieldName+'Select2').append(new Option(fieldValue, fieldValue, false, true)).trigger('change');
			}
		}
		let url = "index.php?action=Edicom&record=" + getQueryVariable('record') + "&method=getStatutBat";
		AppConnector.request(url).done(function (data) {
			let statut = data.result == null? 'BAT non envoyé' : data.result.statut;
			$('#Atribution_editView_fieldName_statut').val(statut);
		}).fail(function (error) {
			console.log(error);
		});
	}

	if(ordreId){
		getProducts(ordreId);
	}

	$('#Atribution_editView_fieldName_valide').change(function(){
		if($(this).prop('checked')){
			if(checkMandatoryFields()){
				let url = "index.php?action=Edicom&record=" + getQueryVariable('record') + "&method=getUser";
				AppConnector.request(url).done(function (data) {
					$('#Atribution_editView_fieldName_valide_par').val(data.result.first_name + ' ' + data.result.last_name);
					$('#Atribution_editView_fieldName_date_validation').val(new Date().toLocaleString().slice(0, 10));
				}).fail(function (error) {
					console.log(error);
				});
			}
			else{
				$(this).prop('checked', false);
				Vtiger_Helper_Js.showPnotify("Veuillez remplir tous les éléments de fabrication");
			}						
		}
		else{
			$('#Atribution_editView_fieldName_valide_par').val("");
			$('#Atribution_editView_fieldName_date_validation').val("");
		}
	});

	$('#Atribution_editView_fieldName_realise').change(function(){
		if($(this).prop('checked')){
			if(checkMandatoryFields()){
				$('#Atribution_editView_fieldName_date_realisation').val(new Date().toLocaleString().slice(0, 10));
			}
			else{
				$(this).prop('checked', false);
				Vtiger_Helper_Js.showPnotify("Veuillez remplir tous les éléments de fabrication");
			}						
		}
		else
			$('#Atribution_editView_fieldName_date_realisation').val("");
	});

	$(document).on('change', '#productCheckAll', function(){
		$('.product-checkbox').prop('checked', $(this).prop('checked'));
	});

	$('#Atribution_editView_fieldName_mis_en_ligne').change(function(){
		if($(this).prop('checked')){
			let url = "index.php?action=Edicom&record=" + getQueryVariable('record') + "&method=checkBat";
			AppConnector.request(url).done(function (data) {
				if(data.result.count < 1){
					$('#Atribution_editView_fieldName_mis_en_ligne').prop('checked', false);
					Vtiger_Helper_Js.showPnotify("Aucun BAT envoyé!");
				}
				else{
					let url2 = "index.php?action=Edicom&record=" + getQueryVariable('record') + "&method=getUser";
					AppConnector.request(url2).done(function (data) {
						$('#Atribution_editView_fieldName_mis_en_ligne_par').val(data.result.first_name + ' ' + data.result.last_name);
						let nombre = $('#Atribution_editView_fieldName_nombre_affichage').val();
						let duree = Number($('#Atribution_editView_fieldName_duree_affichage').val().split(" ")[0]);
						let type = $('#Atribution_editView_fieldName_duree_affichage').val().split(" ")[1];
						let debut = new Date();
						let fin = debut;
						if(nombre == 0){
							console.log(type + ' ' + duree);
							if (type == 'Jours')
								fin = new Date(new Date(debut).setDate(debut.getDate()+duree));
							else if (type == 'Mois')
								fin = new Date(new Date(debut).setMonth(debut.getMonth()+duree));
						}
						$('#Atribution_editView_fieldName_debut_mise_en_ligne').val(debut.toLocaleString().slice(0, 10));
						$('#Atribution_editView_fieldName_fin_mise_en_ligne').val(fin.toLocaleString().slice(0, 10));
					}).fail(function (error) {
						console.log(error);
					});
				}
			}).fail(function (error) {
				console.log(error);
			});			
		}
		else{
			$('#Atribution_editView_fieldName_mis_en_ligne_par').val("");
			$('#Atribution_editView_fieldName_debut_mise_en_ligne').val("");
			$('#Atribution_editView_fieldName_fin_mise_en_ligne').val("");
		}
	});

	$('#sauvgarderAffectations').click(function() {
		document.progressLoader = $.progressIndicator({
			'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		ordre = $('[name="ordre"]').val();
		operateur = $('[name="operateur"]').val();
		if($('#ordre_display').val() != '' && $('#operateur_display').val() != ''){
			let produits = [];
			$('.product-checkbox').each(function( index ) {
				if($(this).prop('checked'))
					produits.push($('#produit-'+index).html());
			});
			produits = produits.toString();
			let url = "index.php?action=Edicom&record=" + ordre + "&method=saveAttributions&produits=" + produits + "&operateur=" + operateur;
			AppConnector.request(url).done(function (data) {
				document.progressLoader.progressIndicator({ 'mode': 'hide'});
				window.location.href="index.php?module=Atribution&view=List";
			}).fail(function (error) {
				console.log(error);
				document.progressLoader.progressIndicator({ 'mode': 'hide'});
				Vtiger_Helper_Js.showPnotify("Sélectionnez au moins un produit!");
			});
		}
		else{
			document.progressLoader.progressIndicator({ 'mode': 'hide'});
			Vtiger_Helper_Js.showPnotify("L'ordre et l'opérateur sont obligatoires!");
		}
	});
});

function getQueryVariable(variable)
{
	let query = window.location.search.substring(1);
	let vars = query.split("&");
	for (let i=0;i<vars.length;i++) {
        let pair = vars[i].split("=");
        if(pair[0] == variable)
       		return pair[1];
	}
	return null;
}

function checkMandatoryFields(){
	let block = blocks[format].attr('data-label');
	let fields = blockMandatoryFields[block];
	for (let key in fields) {
		let fieldName = fields[key];
		let fieldValue = $('#Atribution_editView_fieldName_'+fieldName).val();
		if((fieldValue && !imageInputs.includes(fieldName) || (fieldValue && imageInputs.includes(fieldName) && fieldValue.length > 2))){
		}
		else
			return false;	
	}
	return true;
}
</script>