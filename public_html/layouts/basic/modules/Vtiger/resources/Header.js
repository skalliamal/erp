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

//Show Alert if user is on a unsupported browser (IE7, IE8, ..etc)
if (/MSIE 6.0/.test(navigator.userAgent) || /MSIE 7.0/.test(navigator.userAgent) || /MSIE 8.0/.test(navigator.userAgent) || /MSIE 9.0/.test(navigator.userAgent)) {
	if (app.getCookie('oldbrowser') != 'true') {
		app.setCookie("oldbrowser", true, 365);
		window.location.href = 'layouts/basic/modules/Vtiger/browsercompatibility/Browser_compatibility.html';
	}
}

$.Class("Vtiger_Header_Js", {
	quickCreateModuleCache: {},
	self: false,
	getInstance: function () {
		if (this.self != false) {
			return this.self;
		}
		this.self = new Vtiger_Header_Js();
		return this.self;
	}
}, {
	menuContainer: false,
	contentContainer: false,
	quickCreateCallBacks: [],
	init: function () {
		this.setContentsContainer('.js-base-container');
	},
	setContentsContainer: function (element) {
		if (element instanceof $) {
			this.contentContainer = element;
		} else {
			this.contentContainer = $(element);
		}
		return this;
	},
	getContentsContainer: function () {
		return this.contentContainer;
	},
	getQuickCreateForm: function (url, moduleName, params) {
		var thisInstance = this;
		var aDeferred = $.Deferred();
		var requestParams;
		if (typeof params === "undefined") {
			params = {};
		}
		if ((!params.noCache) || (typeof (params.noCache) === "undefined")) {
			if (typeof Vtiger_Header_Js.quickCreateModuleCache[moduleName] !== "undefined") {
				aDeferred.resolve(Vtiger_Header_Js.quickCreateModuleCache[moduleName]);
				return aDeferred.promise();
			}
		}
		requestParams = url;
		if (typeof params.data !== "undefined") {
			var requestParams = {};
			requestParams['data'] = params.data;
			requestParams['url'] = url;
		}
		AppConnector.request(requestParams).done(function (data) {
			if ((!params.noCache) || (typeof (params.noCache) === "undefined")) {
				Vtiger_Header_Js.quickCreateModuleCache[moduleName] = data;
			}
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	registerQuickCreateCallBack: function (callBackFunction) {
		if (typeof callBackFunction != 'function') {
			return false;
		}
		this.quickCreateCallBacks.push(callBackFunction);
		return true;
	},
	/**
	 * Function to save the quickcreate module
	 * @param accepts form element as parameter
	 * @return returns deferred promise
	 */
	quickCreateSave: function (form) {
		var aDeferred = $.Deferred();
		var quickCreateSaveUrl = form.serializeFormData();
		AppConnector.request(quickCreateSaveUrl).done(
			function (data) {
				aDeferred.resolve(data);
			},
			function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
	/**
	 * Function to navigate from quickcreate to editView Fullform
	 * @param accepts form element as parameter
	 */
	quickCreateGoToFullForm: function (form, editViewUrl) {
		//As formData contains information about both view and action removed action and directed to view
		form.find('input[name="action"]').remove();
		form.append('<input type="hidden" name="view" value="Edit" />');
		$.each(form.find('[data-validation-engine]'), function (key, data) {
			$(data).removeAttr('data-validation-engine');
		});
		form.addClass('not_validation');
		form.submit();
	},
	showAnnouncement: function () {
		var thisInstance = this;
		var announcementContainer = $('#announcements');
		var announcements = announcementContainer.find('.announcement');
		if (announcements.length > 0) {
			var announcement = announcements.first();
			var aid = announcement.data('id')

			app.showModalWindow(announcement.find('.modal'), function (modal) {
				announcement.remove();
				modal.find('button').on('click', function (e) {
					AppConnector.request({
						module: 'Announcements',
						action: 'BasicAjax',
						mode: 'mark',
						record: aid,
						type: $(this).data('type')
					}).done(function (res) {
						app.hideModalWindow();
						thisInstance.showAnnouncement();
					})
				});
			}, '', {backdrop: 'static'});
		}
	},
	registerAnnouncements: function () {
		var thisInstance = this;
		var announcementContainer = $('#announcements');
		if (announcementContainer.length == 0) {
			return false;
		}
		thisInstance.showAnnouncement();
	},
	registerCalendarButtonClickEvent: function () {
		var element = $('#calendarBtn');
		var dateFormat = element.data('dateFormat');
		var currentDate = element.data('date');
		var vtigerDateFormat = app.convertToDatePickerFormat(dateFormat);
		element.on('click', function (e) {
			e.stopImmediatePropagation();
			element.closest('div.nav').find('div.open').removeClass('open');
			var calendar = $('#' + element.data('datepickerId'));
			if ($(calendar).is(':visible')) {
				element.DatePickerHide();
			} else {
				element.DatePickerShow();
			}
		})
		element.DatePicker({
			format: vtigerDateFormat,
			date: currentDate,
			calendars: 1,
			starts: 1,
			className: 'globalCalendar'
		});
	},
	registerHelpInfo: function (container) {
		if (typeof container === "undefined") {
			container = $('form[name="QuickCreate"]');
		}
		app.showPopoverElementView(container.find('.js-help-info'));
	},
	handleQuickCreateData: function (data, params) {
		if (typeof params === "undefined") {
			params = {};
		}
		var thisInstance = this;
		app.showModalWindow(data, function (data) {
			var quickCreateForm = data.find('form[name="QuickCreate"]');
			var moduleName = quickCreateForm.find('[name="module"]').val();
			var editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
			editViewInstance.registerBasicEvents(quickCreateForm);
			thisInstance.registerChangeNearCalendarEvent(quickCreateForm, moduleName);
			quickCreateForm.validationEngine(app.validationEngineOptions);
			if (typeof params.callbackPostShown !== "undefined") {
				params.callbackPostShown(quickCreateForm);
			}
			thisInstance.registerQuickCreatePostLoadEvents(quickCreateForm, params);
			thisInstance.registerHelpInfo(quickCreateForm);
		});
	},
	isFreeDay: function (dayOfWeek) {

		if (dayOfWeek == 0 || dayOfWeek == 6) {
			return true;
		}
		return false;
	},
	getNearCalendarEvent: function (container, module) {
		var thisInstance = this;
		var dateStartVal = container.find('[name="date_start"]').val();
		if (typeof dateStartVal === "undefined" || dateStartVal === '') {
			return;
		}
		var params = {
			module: module,
			view: 'QuickCreateEvents',
			currentDate: dateStartVal,
			user: container.find('[name="assigned_user_id"]').val(),
		}
		var progressIndicatorElement = $.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true,
				elementToBlock: container.find('.eventsTable')
			}
		});
		AppConnector.request(params).done(function (events) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			container.find('.eventsTable').html(events);
			thisInstance.registerHelpInfo(container);
		});
	},
	registerChangeNearCalendarEvent: function (data, module) {
		var thisInstance = this;
		if (!data || module != 'Calendar' || typeof module === "undefined" || !data.find('.eventsTable').length) {
			return;
		}
		var user = data.find('[name="assigned_user_id"]');
		var dateStartEl = data.find('[name="date_start"]');
		var dateEnd = data.find('[name="due_date"]');
		user.on('change', function (e) {
			var element = $(e.currentTarget);
			var data = element.closest('form');
			thisInstance.getNearCalendarEvent(data, module);
		});
		dateStartEl.on('change', function (e) {
			var element = $(e.currentTarget);
			var data = element.closest('form');
			thisInstance.getNearCalendarEvent(data, module);
		});
		data.find('ul li a').on('click', function (e) {
			var element = $(e.currentTarget);
			var data = element.closest('form');
			data.find('.addedNearCalendarEvent').remove();
			thisInstance.getNearCalendarEvent(data, module);
		});
		data.on('click', '.nextDayBtn', function () {
			var dateStartEl = data.find('[name="date_start"]')
			var startDay = dateStartEl.val();
			var dateStartFormat = dateStartEl.data('date-format');
			startDay = moment(Vtiger_Helper_Js.convertToDateString(startDay, dateStartFormat, '+7', ' ')).format(dateStartFormat.toUpperCase());
			dateStartEl.val(startDay);
			dateEnd.val(startDay);
			thisInstance.getNearCalendarEvent(data, module);
		});
		data.on('click', '.previousDayBtn', function () {
			var dateStartEl = data.find('[name="date_start"]')
			var startDay = dateStartEl.val();
			var dateStartFormat = dateStartEl.data('date-format');
			startDay = moment(Vtiger_Helper_Js.convertToDateString(startDay, dateStartFormat, '-7', ' ')).format(dateStartFormat.toUpperCase());
			dateStartEl.val(startDay);
			dateEnd.val(startDay);
			thisInstance.getNearCalendarEvent(data, module);
		});
		data.on('click', '.dateBtn', function (e) {
			var element = $(e.currentTarget);
			dateStartEl.val(element.data('date'));
			data.find('[name="due_date"]').val(element.data('date'));
			data.find('[name="date_start"]').trigger('change');
		});
		thisInstance.getNearCalendarEvent(data, module);
	},
	registerQuickCreatePostLoadEvents: function (form, params) {
		var thisInstance = this;
		var submitSuccessCallbackFunction = params.callbackFunction;
		var goToFullFormCallBack = params.goToFullFormcallback;
		if (typeof submitSuccessCallbackFunction === "undefined") {
			submitSuccessCallbackFunction = function () {
			};
		}

		form.on('submit', function (e) {
			var form = $(e.currentTarget);
			if (form.hasClass('not_validation')) {
				return true;
			}
			var module = form.find('[name="module"]').val();
			//Form should submit only once for multiple clicks also
			if (typeof form.data('submit') !== "undefined") {
				return false;
			} else {
				var invalidFields = form.data('jqv').InvalidFields;
				if (invalidFields.length > 0) {
					//If validation fails, form should submit again
					form.removeData('submit');
					$.progressIndicator({'mode': 'hide'});
					e.preventDefault();
					return;
				} else {
					//Once the form is submiting add data attribute to that form element
					form.data('submit', 'true');
					$.progressIndicator({'mode': 'hide'});
				}

				var recordPreSaveEvent = $.Event(Vtiger_Edit_Js.recordPreSave);
				form.trigger(recordPreSaveEvent, {
					'value': 'edit',
					'module': module
				});
				if (!(recordPreSaveEvent.isDefaultPrevented())) {
					var targetInstance = thisInstance;
					var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(module);
					if (typeof (moduleInstance.quickCreateSave) === 'function') {
						targetInstance = moduleInstance;
					}
					targetInstance.quickCreateSave(form).done(function (data) {
						app.hideModalWindow();
						var parentModule = app.getModuleName();
						var viewname = app.getViewName();
						if ((module == parentModule) && (viewname == "List")) {
							var listinstance = new Vtiger_List_Js();
							listinstance.getListViewRecords();
						}
						submitSuccessCallbackFunction(data);
						var registeredCallBackList = thisInstance.quickCreateCallBacks;
						for (var index = 0; index < registeredCallBackList.length; index++) {
							var callBack = registeredCallBackList[index];
							callBack({
								'data': data,
								'name': form.find('[name="module"]').val()
							});
						}
						app.event.trigger("QuickCreate.AfterSaveFinal", data, form);
						$.progressIndicator({'mode': 'hide'});
					});
				} else {
					//If validation fails in recordPreSaveEvent, form should submit again
					form.removeData('submit');
					$.progressIndicator({'mode': 'hide'});
				}
				e.preventDefault();
			}
		});

		form.find('#goToFullForm').on('click', function (e) {
			var form = $(e.currentTarget).closest('form');
			var editViewUrl = $(e.currentTarget).data('editViewUrl');
			if (typeof goToFullFormCallBack !== "undefined") {
				goToFullFormCallBack(form);
			}
			thisInstance.quickCreateGoToFullForm(form, editViewUrl);
		});

		this.registerTabEventsInQuickCreate(form);
	},
	registerTabEventsInQuickCreate: function (form) {
		var tabElements = form.find('.nav.nav-pills , .nav.nav-tabs').find('a');
		//This will remove the name attributes and assign it to data-element-name . We are doing this to avoid
		//Multiple element to send as in calendar
		var quickCreateTabOnHide = function (target) {
			var container = $(target);
			container.find('[name]').each(function (index, element) {
				element = $(element);
				element.attr('data-element-name', element.attr('name')).removeAttr('name');
			});
		}
		//This will add the name attributes and get value from data-element-name . We are doing this to avoid
		//Multiple element to send as in calendar
		var quickCreateTabOnShow = function (target) {
			var container = $(target);
			container.find('[data-element-name]').each(function (index, element) {
				element = $(element);
				element.attr('name', element.attr('data-element-name')).removeAttr('data-element-name');
			});
		}
		tabElements.on('click', function (e) {
			quickCreateTabOnHide(tabElements.not('[aria-expanded="false"]').attr('data-target'));
			quickCreateTabOnShow($(this).attr('data-target'));
			//while switching tabs we have to clear the invalid fields list
			form.data('jqv').InvalidFields = [];

		});
		//To show aleady non active element , this we are doing so that on load we can remove name attributes for other fields
		tabElements.filter('a:not(.active)').each(function (e) {
			quickCreateTabOnHide($(this).attr('data-target'));
		});
	},
	basicSearch: function () {
		var thisInstance = this;
		$('.js-global-search__value').on('keypress', function (e) {
			var currentTarget = $(e.currentTarget)
			if (e.which == 13) {
				thisInstance.hideSearchMenu();
				thisInstance.labelSearch(currentTarget);
			}
		});
		$('.js-global-search-operator').on('click', function (e) {
			var currentTarget = $(e.target);
			var block = currentTarget.closest('.js-global-search__input');
			block.find('.js-global-search__value').data('operator', currentTarget.data('operator'));
			block.find('.js-global-search-operator .dropdown-item').removeClass('active');
			currentTarget.closest('.dropdown-item').addClass('active');
		});
		if ($('#gsAutocomplete').val() == 1) {
			$.widget("custom.gsAutocomplete", $.ui.autocomplete, {
				_create: function () {
					this._super();
					this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
				},
				_renderMenu: function (ul, items) {
					var that = this, currentCategory = "";
					$.each(items, function (index, item) {
						var li;
						if (item.category != currentCategory) {
							ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
							currentCategory = item.category;
						}
						that._renderItemData(ul, item);
					});
				},
				_renderItemData: function (ul, item) {
					return this._renderItem(ul, item).data("ui-autocomplete-item", item);
				},
				_renderItem: function (ul, item) {
					var url = 'index.php?module=' + item.module + '&view=Detail&record=' + item.id;
					return $("<li>")
						.data("item.autocomplete", item)
						.append($("<a href='" + url + "'></a>").html(item.label))
						.appendTo(ul);
				},
			});
			$('.js-global-search__value').gsAutocomplete({
				minLength: app.getMainParams('gsMinLength'),
				source: function (request, response) {
					var basicSearch = new Vtiger_BasicSearch_Js();
					basicSearch.reduceNumberResults = app.getMainParams('gsAmountResponse');
					basicSearch.returnHtml = false;
					basicSearch.setMainContainer(this.element.closest('.js-global-search__input'));
					basicSearch.search(request.term).done(function (data) {
						var data = JSON.parse(data);
						var serverDataFormat = data.result;
						var reponseDataList = [];
						for (var id in serverDataFormat) {
							var responseData = serverDataFormat[id];
							reponseDataList.push(responseData);
						}
						response(reponseDataList);
					});
				},
				select: function (event, ui) {
					var selectedItemData = ui.item;
					if (selectedItemData.permitted) {
						var url = 'index.php?module=' + selectedItemData.module + '&view=Detail&record=' + selectedItemData.id;
						window.location.href = url;
					}
					return false;
				},
				close: function (event, ui) {
					//$('.js-global-search__value').val('');
				}
			});
		}
	},
	labelSearch: function (currentTarget) {
		var val = currentTarget.val();
		if (val == '') {
			alert(app.vtranslate('JS_PLEASE_ENTER_SOME_VALUE'));
			currentTarget.focus();
			return false;
		}
		var progress = $.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var basicSearch = new Vtiger_BasicSearch_Js();
		basicSearch.setMainContainer(currentTarget.closest('.js-global-search__input'));
		basicSearch.search(val).done(function (data) {
			basicSearch.showSearchResults(data);
			progress.progressIndicator({
				'mode': 'hide'
			});
		});
	},
	registerHotKeys: function () {
		$(".hotKey").each(function (index) {
			var thisObject = this;
			var key = $(thisObject).data('hotkeys');
			if (key != '') {
				Mousetrap.bind(key, function () {
					thisObject.click();
				});
			}
		});
	},
	quickCreateModule: function (moduleName, params) {
		if (window !== window.parent) {
			window.parent.Vtiger_Header_Js.getInstance().quickCreateModule(moduleName, params);
			return;
		}
		var thisInstance = this;
		if (typeof params === "undefined") {
			params = {};
		}
		if (typeof params.callbackFunction === "undefined") {
			params.callbackFunction = function () {
			};
		}
		var url = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		if ((app.getViewName() === 'Detail' || app.getViewName() === 'Edit') && app.getParentModuleName() != 'Settings') {
			url += '&sourceModule=' + app.getModuleName();
			url += '&sourceRecord=' + app.getRecordId();
		}
		var progress = $.progressIndicator();
		thisInstance.getQuickCreateForm(url, moduleName, params).done(function (data) {
			thisInstance.handleQuickCreateData(data, params);
			app.registerEventForClockPicker();
			progress.progressIndicator({
				'mode': 'hide'
			});
		});
	},
	registerReminderNotice: function () {
		var self = this;
		$('#page').before(`<div class="remindersNoticeContainer" tabindex="-1" role="dialog" aria-label="${app.vtranslate('JS_REMINDER')}" aria-hidden="true"></div>`);
		var block = $('.remindersNoticeContainer');
		var remindersNotice = $('.remindersNotice');
		remindersNotice.on('click', function () {
			if (!remindersNotice.hasClass('autoRefreshing')) {
				Vtiger_Index_Js.requestReminder();
			}
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu()
			block.toggleClass("toggled");
			self.hideReminderNotification();
			app.closeSidebar();
			self.hideSearchMenu();
		});
	},
	registerReminderNotification: function () {
		var self = this;
		$('#page').before('<div class="remindersNotificationContainer" tabindex="-1" role="dialog"></div>');
		var block = $('.remindersNotificationContainer');
		var remindersNotice = $('.notificationsNotice');
		remindersNotice.on('click', function () {
			if (!remindersNotice.hasClass('autoRefreshing')) {
				Vtiger_Index_Js.getNotificationsForReminder();
			}
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu();
			block.toggleClass("toggled");
			self.hideReminderNotice();
			app.closeSidebar();
			self.hideSearchMenu();
		});
	},
	toggleBreadcrumActions(container) {
		if (!container.find('.js-breadcrumb').length) {
			return;
		}
		let breadcrumb = container.find('.js-breadcrumb'),
			actionBtn = breadcrumb.find('.js-breadcrumb__actions-btn'),
			cssActionsTop = {top: breadcrumb.offset().top + breadcrumb.height()};
			breadcrumb.find('.o-breadcrumb__actions').css(cssActionsTop);
		actionBtn.on('click', () => {
			breadcrumb.find('.o-breadcrumb__actions').toggleClass('is-active');
		});
	},
	registerMobileEvents: function () {
		const self = this,
			container = this.getContentsContainer();
		$('.rightHeaderBtnMenu').on('click', function () {
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu();
			self.hideSearchMenu();
			self.hideReminderNotice();
			self.hideReminderNotification();
			$('.mobileLeftPanel ').toggleClass('mobileMenuOn');
		});
		$('.js-quick-action-btn').on('click', function () {
			let currentTarget = $(this);
			app.closeSidebar();
			self.hideBreadcrumbActionMenu();
			self.hideSearchMenu();
			self.hideReminderNotice();
			self.hideReminderNotification();
			$('.actionMenu').toggleClass('actionMenuOn');
			if (currentTarget.hasClass('active')) {
				currentTarget.removeClass('active');
				currentTarget.attr('aria-expanded', 'false');
				currentTarget.popover();
			} else {
				currentTarget.addClass('active');
				currentTarget.attr('aria-expanded', 'true');
				currentTarget.popover('disable');
			}
			$('.quickCreateModules').on('click', function () {
				self.hideActionMenu();
			});
		});
		$('.searchMenuBtn').on('click', function () {
			let currentTarget = $(this);
			app.closeSidebar();
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu();
			self.hideReminderNotice();
			self.hideReminderNotification();
			$('.searchMenu').toggleClass('toogleSearchMenu');
			if (currentTarget.hasClass('active')) {
				currentTarget.removeClass('active');
				$('.searchMenuBtn .c-header__btn').attr('aria-expanded', 'false');
			} else {
				currentTarget.addClass('active');
				$('.searchMenuBtn .c-header__btn').attr('aria-expanded', 'true');
			}
		});
		$('.js-header__btn--mail .dropdown').on('show.bs.dropdown', function () {
			app.closeSidebar();
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu();
			self.hideReminderNotice();
			self.hideReminderNotification();
			self.hideSearchMenu();
		});
		this.toggleBreadcrumActions(container);
	},
	hideMobileMenu: function () {
		$('.mobileLeftPanel ').removeClass('mobileMenuOn');
	},
	hideSearchMenu: function () {
		$('.searchMenu').removeClass('toogleSearchMenu');
	},
	hideActionMenu: function () {
		$('.actionMenu').removeClass('actionMenuOn');
	},
	hideBreadcrumbActionMenu: function () {
		$('.js-breadcrumb__actions').removeClass('is-active');
	},
	hideReminderNotice: function () {
		$('.remindersNoticeContainer').removeClass('toggled');
	},
	hideReminderNotification: function () {
		$('.remindersNotificationContainer').removeClass('toggled');
	},
	showPdfModal: function (url) {
		var params = {};
		if (app.getViewName() == 'List') {
			var selected = Vtiger_List_Js.getSelectedRecordsParams(false, true);
			$.extend(params, selected);
		}
		url += '&' + $.param(params);
		app.showModalWindow(null, url);
	},
	registerFooTable: function () {
		var container = $('.tableRWD');
		container.find('thead tr th:gt(1)').attr('data-hide', 'phone');
		container.find('thead tr th:gt(3)').attr('data-hide', 'tablet,phone');
		container.find('thead tr th:last').attr('data-hide', '');
		var whichColumnEnable = container.find('thead').attr('col-visible-alltime');
		container.find('thead tr th:eq(' + whichColumnEnable + ')').attr('data-hide', '');
		$('.tableRWD, .customTableRWD').footable({
			breakpoints: {
				phone: 768,
				tablet: 1024
			},
			addRowToggle: true,
			toggleSelector: ' > tbody > tr:not(.footable-row-detail)'
		});
		$('.footable-toggle').on('click', function (event) {
			event.stopPropagation();
			$(this).trigger('footable_toggle_row');
		});
		var records = $('.customTableRWD').find('[data-toggle-visible=false]');
		records.find('.footable-toggle').css("display", "none");
	},
	registerShowHideRightPanelEvent: function (container) {
		var thisInstance = this;
		var key = 'ShowHideRightPanel' + app.getModuleName();
		if (app.cacheGet(key) == 'show') {
			thisInstance.showSiteBar(container, container.find('.toggleSiteBarRightButton'));
		}

		if (app.cacheGet(key) == null) {
			if (container.find('.siteBarRight').data('showpanel') == 1) {
				thisInstance.showSiteBar(container, container.find('.toggleSiteBarRightButton'));
			}
		}
		container.find('.toggleSiteBarRightButton').on('click', function (e) {
			var toogleButton = $(this);
			if (toogleButton.closest('.siteBarRight').hasClass('hideSiteBar')) {
				app.cacheSet(key, 'show');
				thisInstance.showSiteBar(container, toogleButton);
			} else {
				app.cacheSet(key, 'hide');
				thisInstance.hideSiteBar(container, toogleButton);
			}
		});
	},
	hideSiteBar: function (container, toogleButton) {
		var key, siteBarRight, content, buttonImage;
		siteBarRight = toogleButton.closest('.siteBarRight');
		content = container.find('.rowContent');
		buttonImage = toogleButton.find('[data-fa-i2svg]');

		siteBarRight.addClass('hideSiteBar');
		content.removeClass('js-sitebar--active');
		buttonImage.removeClass('fa-chevron-right').addClass("fa-chevron-left");
		toogleButton.addClass('hideToggleSiteBarRightButton');
	},
	showSiteBar: function (container, toogleButton) {
		var key, siteBarRight, content, buttonImage;
		siteBarRight = toogleButton.closest('.siteBarRight');
		content = container.find('.rowContent');
		buttonImage = toogleButton.find('[data-fa-i2svg]');

		siteBarRight.removeClass('hideSiteBar');
		content.addClass('js-sitebar--active');
		buttonImage.removeClass('fa-chevron-left').addClass("fa-chevron-right");
		toogleButton.removeClass('hideToggleSiteBarRightButton');
	},
	registerToggleButton: function () {
		$(".buttonTextHolder .dropdown-menu a").on('click', function () {
			$(this).parents('.d-inline-block').find('.dropdown-toggle .textHolder').html($(this).text());
		});
	},
	listenTextAreaChange: function () {
		var thisInstance = this;
		$('textarea').on('keyup', function () {
			var elem = $(this);
			if (!elem.data('has-scroll')) {
				elem.data('has-scroll', true);
				elem.on('scroll keyup', function () {
					thisInstance.resizeTextArea($(this));
				});
			}
			thisInstance.resizeTextArea($(this));
		});
	},
	resizeTextArea: function (elem) {
		elem.height(1);
		elem.scrollTop(0);
		elem.height(elem[0].scrollHeight - elem[0].clientHeight + elem.height());
	},
	registerChat: function () {
		const self = this;
		var modal = $('.chatModal');
		if (modal.length === 0) {
			return;
		}
		var modalBody = modal.find('.modal-body');
		app.showNewScrollbar(modalBody, {wheelPropagation: true});
		$('.headerLinkChat').on('click', function (e) {
			e.stopPropagation();
			var remindersNoticeContainer = $('.remindersNoticeContainer,.remindersNotificationContainer');
			if (remindersNoticeContainer.hasClass('toggled')) {
				remindersNoticeContainer.removeClass('toggled');
			}
			$('.actionMenu').removeClass('actionMenuOn');
			$('.chatModal').modal({backdrop: false});
		});
		var modalDialog = modal.find('.modal-dialog');
		this.registerChatLoadItems(modal.data('timer'));
		modal.find('.addMsg').on('click', function (e) {
			var message = modal.find('.message').val();
			clearTimeout(self.chatTimer);
			AppConnector.request({
				dataType: 'html',
				data: {
					module: 'Chat',
					action: 'Entries',
					mode: 'add',
					message: message,
					cid: $('.chatModal .chatItem').last().data('cid')
				}
			}).done(function (html) {
				$('.chatModal .modal-body').append(html);
				self.registerChatLoadItems(modal.data('timer'));
			});
			modal.find('.message').val('');
		});
		app.animateModal(modal, 'slideInRight', 'slideOutRight');
	},
	registerChatLoadItems: function (timer) {
		const self = this;
		var icon = $('.chatModal .modal-title .fa-comments');
		this.chatTimer = setTimeout(function () {
			icon.css('color', '#00e413');
			self.getChatItems();
			self.registerChatLoadItems(timer);
			icon.css('color', '#000');
		}, timer);
	},
	getChatItems: function () {
		const self = this;
		AppConnector.request({
			module: 'Chat',
			view: 'Entries',
			mode: 'get',
			cid: $('.chatModal .chatItem').last().data('cid')
		}).done(function (html) {
			if (html) {
				$('.chatModal .modal-body').append(html);
			}
		}).fail(function (error, err) {
			clearTimeout(self.chatTimer);
		});
	},
	registerEvents: function () {
		var thisInstance = this;
		const container = thisInstance.getContentsContainer(),
			menuContainer = container.find('.js-menu--scroll'),
			quickCreateModal = container.find('.quickCreateModules');
		app.showNewScrollbarLeft(menuContainer, {suppressScrollX: true});
		app.showNewScrollbar(menuContainer.find('.subMenu').last(), {suppressScrollX: true});
		thisInstance.listenTextAreaChange();
		thisInstance.registerFooTable(); //Enable footable
		thisInstance.registerShowHideRightPanelEvent($('#centerPanel'));
		$('.js-clear-history').on('click', () => {
			app.clearBrowsingHistory();
		});
		$('.globalSearch').on('click', function () {
			var currentTarget = $(this);
			thisInstance.hideSearchMenu();
			var advanceSearchInstance = new Vtiger_AdvanceSearch_Js();
			advanceSearchInstance.setParentContainer(currentTarget.closest('.js-global-search__input'));
			advanceSearchInstance.initiateSearch().done(function () {
				advanceSearchInstance.selectBasicSearchValue();
			});
		});
		$('.searchIcon').on('click', function (e) {
			var currentTarget = $(this).closest('.js-global-search__input').find('.js-global-search__value');
			var pressEvent = $.Event("keypress");
			pressEvent.which = 13;
			currentTarget.trigger(pressEvent);
		});
		thisInstance.registerAnnouncements();
		thisInstance.registerHotKeys();
		thisInstance.registerToggleButton();
		//this.registerCalendarButtonClickEvent();
		//After selecting the global search module, focus the input element to type
		$('.basicSearchModulesList').on('change', function () {
			var value = $(this).closest('.js-global-search__input').find('.js-global-search__value')
			setTimeout(function () {
				value.focus();
			}, 100);
		});
		thisInstance.basicSearch();
		quickCreateModal.on("click", ".quickCreateModule", function (e, params) {
			var moduleName = $(e.currentTarget).data('name');
			quickCreateModal.modal('hide');
			thisInstance.quickCreateModule(moduleName);
		});

		thisInstance.registerMobileEvents();

		if (/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
			$('#basicSearchModulesList_chosen').find('.chzn-results').css({
				'max-height': '350px',
				'overflow-y': 'scroll'
			});
		} else {
			app.showScrollBar($('#basicSearchModulesList_chosen').find('.chzn-results'), {
				height: '450px',
				railVisible: true,
				alwaysVisible: true,
				size: '6px'
			});
			//Added to support standard resolution 1024x768
			if (window.outerWidth <= 1024) {
				//$('.headerLinksContainer').css('margin-right', '8px');
			}
		}
		thisInstance.registerReminderNotice();
		thisInstance.registerReminderNotification();
		thisInstance.registerChat();
	}
});
$(document).ready(function () {
	window.addEventListener('popstate', (event) => {
		if (event.state) {
			window.location.href = event.state;
		}
	});
	Vtiger_Header_Js.getInstance().registerEvents();
});
