/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Settings_SalesProcesses_Index_Js", {}, {
	registerChangeVal: function (content) {
		var thisInstance = this;
		content.on('change', '.configField', function (e) {
			var target = $(e.currentTarget);
			var params = {};
			params['type'] = target.data('type');
			params['param'] = target.attr('name');
			if (target.attr('type') == 'checkbox') {
				params['val'] = this.checked;
			} else {
				params['val'] = target.val() != null ? target.val() : '';
			}
			app.saveAjax('updateConfig', params).done(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
				if (target.attr('type') == 'checkbox') {
					if (params['val']) {
						target.parent().removeClass('btn-light').addClass('btn-success').find('[data-fa-i2svg]').removeClass('fa-square').addClass('fa-check-square');
					} else {
						target.parent().removeClass('btn-success').addClass('btn-light').find('[data-fa-i2svg]').removeClass('fa-check-square').addClass('fa-square');
					}
				}
			});
		});
	},
	registerEvents: function () {
		var content = $('#salesProcessesContainer');
		this.registerChangeVal(content);
	}
});
