/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_PDF_Edit_Js("Settings_PDF_Edit3_Js", {}, {
	step3Container: false,
	advanceFilterInstance: false,
	init: function () {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer: function () {
		return this.step3Container;
	},
	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer: function (element) {
		this.step3Container = element;
		return this;
	},
	/**
	 * Function  to intialize the reports step1
	 */
	initialize: function (container) {
		if (typeof container === "undefined") {
			container = jQuery('#pdf_step3');
		}
		if (container.is('#pdf_step3')) {
			this.setContainer(container);
		} else {
			this.setContainer(jQuery('#pdf_step3'));
		}
	},
	submit: function () {
		for (var instance in CKEDITOR.instances) {
			CKEDITOR.instances[instance].updateElement();
		}
		var aDeferred = jQuery.Deferred();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		var saveData = form.serializeFormData();
		saveData['action'] = 'Save';
		saveData['step'] = 3;
		AppConnector.request(saveData).done(function (data) {
			data = JSON.parse(data);
			if (data.success == true) {
				Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')});

				AppConnector.request(formData).done(function (data) {
					form.hide();
					progressIndicatorElement.progressIndicator({
						'mode': 'hide'
					})
					aDeferred.resolve(data);
				}).fail(function (error, err) {
					app.errorLog(error, err);
				});
			}
		}).fail(function (error, err) {
			app.errorLog(error, err);
		});
		return aDeferred.promise();
	},
	registerCancelStepClickEvent: function (form) {
		jQuery('button.cancelLink', form).on('click', function () {
			window.history.back();
		});
	},
	registerEvents: function () {
		let container = this.getContainer(),
			opts = app.validationEngineOptions;
		// to prevent the page reload after the validation has completed
		opts['onValidationComplete'] = function (form, valid) {
			//returns the valid status
			return valid;
		};
		opts['promptPosition'] = "bottomRight";
		container.validationEngine(opts);
		App.Fields.Picklist.showSelect2ElementView(container.find('select'));
		new App.Fields.Text.Editor('.js-editor', {toolbar: 'Full'});
		this.registerCancelStepClickEvent(container);
	}
});
