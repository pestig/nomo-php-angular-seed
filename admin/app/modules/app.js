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
		$routeProvider.when('/admin/:type/:id', {templateUrl: '/nomoEFW/app/modules/form/view.html',controller:'nomoFormCtrl'});
		//$routeProvider.when('/admin/:type', {templateUrl: '/nomoEFW/app/modules/table/view.html',controller:'nomoTableCtrl'});
		$routeProvider.when('/admin/:type', {template: '<div nomo-table nomo-table-class="routeParams.type"></div>'});
		//$routeProvider.when('/admin/:type', {template: '<div nomo-grid></div>',reloadOnSearch: false});
		$routeProvider.when('/admin', {redirectTo : '/admin/User'});
		$routeProvider.otherwise({redirectTo: '/admin'});
	}])


