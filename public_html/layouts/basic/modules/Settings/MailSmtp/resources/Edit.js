/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js('Settings_MailSmtp_Edit_Js', {}, {
	registerSubmitForm: function () {
		var form = this.getForm();
		form.on('submit', function (e) {
			if (form.validationEngine('validate') === true) {
				var paramsForm = form.serializeFormData();
				var progressIndicatorElement = jQuery.progressIndicator({
					blockInfo: {'enabled': true}
				});
				app.saveAjax('updateSmtp', paramsForm).done(function (respons) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					if (true == respons.result.success) {
						window.location.href = respons.result.url;
					} else {
						form.find('.alert').removeClass('d-none');
						form.find('.alert p').text(respons.result.message);
					}
				});
				return false;
			} else {
				app.formAlignmentAfterValidation(form);
			}
		})
	},
	/**
	 * Register events to preview password
	 */
	registerPreviewPassword: function () {
		const container = this.getForm();
		const button = container.find('.previewPassword');
		button.on('mousedown', function (e) {
			container.find('[name="' + $(e.currentTarget).data('targetName') + '"]').attr('type', 'text');
		});
		button.on('mouseup', function (e) {
			container.find('[name="' + $(e.currentTarget).data('targetName') + '"]').attr('type', 'password');
		});
		button.on('mouseout', function (e) {
			container.find('[name="' + $(e.currentTarget).data('targetName') + '"]').attr('type', 'password');
		});
	},
	registerEvents: function () {
		var form = this.getForm()
		if (form.length) {
			form.validationEngine(app.validationEngineOptions);
			form.find("[data-inputmask]").inputmask();
		}
		this.registerSubmitForm();
		this.registerPreviewPassword();
		app.showPopoverElementView(form.find('.js-popover-tooltip'));

		form.find(".saveSendMail").on('click', function () {
			if (form.find(".saveMailContent").hasClass("d-none")) {
				form.find(".saveMailContent").removeClass("d-none");
			} else {
				form.find(".saveMailContent").addClass("d-none");
			}
		});
	}
})
