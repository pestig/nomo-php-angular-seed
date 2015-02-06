'use strict';

angular.module('nomoAdmin', [
	'nomoAdmin.login',
	'nomoAdmin.common',
	'nomoAdmin.index'
])
	.config(['$routeProvider','$locationProvider', function($routeProvider,$locationProvider) {
		$locationProvider.html5Mode(true);
		$routeProvider.when('/admin/report/:type', {templateUrl: '/nomoEFW/app/modules/report/view.html',reloadOnSearch: false});
		$routeProvider.when('/admin/:type/:id', {template: '<div nomo-form nomo-form-class="routeParams.type" nomo-form-id="routeParams.id"></div>',reloadOnSearch: false});
		$routeProvider.when('/admin/:type', {template: '<div nomo-table nomo-table-class="routeParams.type" nomo-table-template-url="/admin/app/modules/table/view.html"></div>',reloadOnSearch: false});
		$routeProvider.when('/admin', {redirectTo : '/admin/Peldatabla'});
		$routeProvider.otherwise({redirectTo: '/admin'});
	}])


