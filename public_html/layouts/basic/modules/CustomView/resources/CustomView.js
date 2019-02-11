/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

var Vtiger_CustomView_Js;
Vtiger_CustomView_Js = {
	init() {
		return this;
	},
	contentsCotainer: false,
	columnListSelect2Element: false,
	advanceFilterInstance: false,
	//This will store the columns selection container
	columnSelectElement: false,
	//This will store the input hidden selectedColumnsList element
	selectedColumnsList: false,
	loadFilterView: function (url) {
		var progressIndicatorElement = $.progressIndicator();
		AppConnector.request(url).done(function (data) {
			app.hideModalWindow();
			var contents = $(".contentsDiv").html(data);
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			Vtiger_CustomView_Js.registerEvents();
			Vtiger_CustomView_Js.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance($('.filterContainer', contents));
		});
	},
	loadDateFilterValues: function () {
		var selectedDateFilter = $('#standardDateFilter option:selected');
		var currentDate = selectedDateFilter.data('currentdate');
		var endDate = selectedDateFilter.data('enddate');
		$("#standardFilterCurrentDate").val(currentDate);
		$("#standardFilterEndDate").val(endDate);
	},
	/**
	 * Function to get the contents container
	 * @return : jQuery object of contents container
	 */
	getContentsContainer: function () {
		if (Vtiger_CustomView_Js.contentsCotainer == false) {
			Vtiger_CustomView_Js.contentsCotainer = $('div.contentsDiv');
		}
		return Vtiger_CustomView_Js.contentsCotainer;
	},
	getColumnListSelect2Element: function () {
		return Vtiger_CustomView_Js.columnListSelect2Element;
	},
	/**
	 * Function to get the view columns selection element
	 * @return : jQuery object of view columns selection element
	 */
	getColumnSelectElement: function () {
		if (Vtiger_CustomView_Js.columnSelectElement == false) {
			Vtiger_CustomView_Js.columnSelectElement = $('#viewColumnsSelect');
		}
		return Vtiger_CustomView_Js.columnSelectElement;
	},
	/**
	 * Function to get the selected columns list
	 * @return : jQuery object of selectedColumnsList
	 */
	getSelectedColumnsList: function () {
		if (Vtiger_CustomView_Js.selectedColumnsList == false) {
			Vtiger_CustomView_Js.selectedColumnsList = $('#selectedColumnsList');
		}
		return Vtiger_CustomView_Js.selectedColumnsList;
	},
	/**
	 * Function which will get the selected columns
	 * @return : array of selected values
	 */
	getSelectedColumns: function () {
		var columnListSelectElement = Vtiger_CustomView_Js.getColumnSelectElement();
		return columnListSelectElement.val();
	},
	saveFilter: function () {
		var aDeferred = $.Deferred();
		var formData = $("#CustomView").serializeFormData();
		AppConnector.request(formData, true).done(function (data) {
			aDeferred.resolve(data);
		}).fail(function (error) {
			aDeferred.reject(error);
		});
		return aDeferred.promise();
	},
	saveAndViewFilter: function () {
		Vtiger_CustomView_Js.saveFilter().done(function (response) {
			if (response.success) {
				var url;
				if (app.getParentModuleName() == 'Settings') {
					url = 'index.php?module=CustomView&parent=Settings&view=Index&sourceModule=' + $('#sourceModule').val();
				} else {
					url = response['result']['listviewurl'];
				}
				window.location.href = url;
			} else {
				$.unblockUI();
				var params = {
					title: app.vtranslate('JS_DUPLICATE_RECORD'),
					text: response.error['message']
				};
				Vtiger_Helper_Js.showPnotify(params);
			}
		});
	},
	registerIconEvents: function () {
		var container = this.getContentsContainer();
		container.on('change', '.iconPreferences input', function (e) {
			var currentTarget = $(e.currentTarget);
			var buttonElement = currentTarget.closest('.btn');
			var iconElement = currentTarget.next();
			if (currentTarget.prop('checked')) {
				buttonElement.removeClass('btn-default').addClass('btn-primary');
				iconElement.removeClass(iconElement.data('unchecked')).addClass(iconElement.data('check'));
			} else {
				buttonElement.removeClass('btn-primary').addClass('btn-default');
				iconElement.removeClass(iconElement.data('check')).addClass(iconElement.data('unchecked'));
			}
		});
		container.find('.iconPreferences input').each(function (e) {
			$(this).trigger('change');
		});
	},
	registerBlockToggleEvent: function () {
		var container = this.getContentsContainer();
		container.on('click', '.blockHeader', function (e) {
			var blockHeader = $(e.currentTarget);
			var blockContents = blockHeader.next();
			var iconToggle = blockHeader.find('.iconToggle');
			if (blockContents.hasClass('d-none')) {
				blockContents.removeClass('d-none');
				iconToggle.removeClass(iconToggle.data('hide')).addClass(iconToggle.data('show'));
			} else {
				blockContents.addClass('d-none');
				iconToggle.removeClass(iconToggle.data('show')).addClass(iconToggle.data('hide'));
			}
		});
	},
	registerColorEvent: function () {
		var container = this.getContentsContainer();
		var field = container.find('.colorPicker');
		var addon = field.parent().find('.input-group-addon');
		field.colorpicker().on('changeColor', function (e) {
			var color = e.color.toHex();
			addon.css('background-color', color);
		});
	},
	registerEvents: function () {
		this.registerIconEvents();
		new App.Fields.Text.Editor(this.getContentsContainer().find('.js-editor'));
		this.registerBlockToggleEvent();
		this.registerColorEvent();
		let select2Element = App.Fields.Picklist.showSelect2ElementView(Vtiger_CustomView_Js.getColumnSelectElement());
		$('.stndrdFilterDateSelect').datepicker();
		$('.chzn-select').chosen();
		$("#standardDateFilter").on('change', function () {
			Vtiger_CustomView_Js.loadDateFilterValues();
		});

		$("#CustomView").on('submit', function (e) {
			var selectElement = Vtiger_CustomView_Js.getColumnSelectElement();
			if ($('#viewname').val().length > 40) {
				var params = {
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('JS_VIEWNAME_ALERT')
				};
				Vtiger_Helper_Js.showPnotify(params);
				e.preventDefault();
				return;
			}

			//Mandatory Fields selection validation
			//Any one Mandatory Field should select while creating custom view.
			var mandatoryFieldsList = JSON.parse($('#mandatoryFieldsList').val());
			var selectedOptions = selectElement.val();
			var mandatoryFieldsMissing = true;
			if (selectedOptions) {
				for (var i = 0; i < selectedOptions.length; i++) {
					if ($.inArray(selectedOptions[i], mandatoryFieldsList) >= 0) {
						mandatoryFieldsMissing = false;
						break;
					}
				}
			}
			if (mandatoryFieldsMissing) {
				var message = app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_MANDATORY_FIELD');
				select2Element.validationEngine('showPrompt', message, 'error', 'topLeft', true);
				e.preventDefault();
				return;
			} else {
				select2Element.validationEngine('hide');
			}
			//Mandatory Fields validation ends
			var result = $(e.currentTarget).validationEngine('validate');
			if (result == true) {
				//handled standard filters saved values.
				var stdfilterlist = {};

				if (($('#standardFilterCurrentDate').val() != '') && ($('#standardFilterEndDate').val() != '') && ($('select.standardFilterColumn option:selected').val() != 'none')) {
					stdfilterlist['columnname'] = $('select.standardFilterColumn option:selected').val();
					stdfilterlist['stdfilter'] = $('select#standardDateFilter option:selected').val();
					stdfilterlist['startdate'] = $('#standardFilterCurrentDate').val();
					stdfilterlist['enddate'] = $('#standardFilterEndDate').val();
					$('#stdfilterlist').val(JSON.stringify(stdfilterlist));
				}

				//handled advanced filters saved values.
				var advfilterlist = Vtiger_CustomView_Js.advanceFilterInstance.getValues();
				$('#advfilterlist').val(JSON.stringify(advfilterlist));
				$('input[name="columnslist"]', Vtiger_CustomView_Js.getContentsContainer()).val(JSON.stringify(Vtiger_CustomView_Js.getSelectedColumns()));
				Vtiger_CustomView_Js.saveAndViewFilter();
				return false;
			} else {
				app.formAlignmentAfterValidation($(e.currentTarget));
			}
		});
		$('#CustomView').validationEngine(app.validationEngineOptions);
	}
};
$(document).ready(function () {
	Vtiger_CustomView_Js.init();
})