'use strict';

angular.module('nomoAdmin', [
	'nomoAdmin.login',
	'nomoAdmin.common',
	'nomoAdmin.index'
])
	.config(['$routeProvider','$locationProvider', function($routeProvider,$locationProvider) {
		$locationProvider.html5Mode(true);
		//$routeProvider.when('/admin/menu/:id?', {templateUrl: '/nomoEFW/app/modules/admin_menu/menu_panel.html'});
		//$routeProvider.when('/admin/myprofile', {templateUrl: '/nomoEFW/app/modules/admin_menu/profile.html'});
		$routeProvider.when('/admin/report/:type', {templateUrl: '/nomoEFW/app/modules/report/view.html',reloadOnSearch: false});
		$routeProvider.when('/admin/:type/:id', {templateUrl: '/nomoEFW/app/modules/form/view.html',controller:'nomoFormController'});
		//$routeProvider.when('/admin/:type/:id', {templateUrl: '<div nomo-form nomo-form-class="routeParams.type" nomo-form-id="if(routeParams.id==\'new\')?\'new\':routeParams.id"></div>'});
		$routeProvider.when('/admin/:type', {template: '<div nomo-table nomo-table-class="routeParams.type"></div>'});
		$routeProvider.when('/admin', {redirectTo : '/admin/User'});
		$routeProvider.otherwise({redirectTo: '/admin'});
	}])


