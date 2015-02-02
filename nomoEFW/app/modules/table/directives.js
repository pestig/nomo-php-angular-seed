'use strict';

angular.module('nomoEFW.table')
	.directive('nomoTable', function ($rootScope, version) {
		return {
			restrict: 'A',
			controller: 'nomoTableController',
			templateUrl: function (element, attrs) {
				return (attrs.nomoTableTemplateUrl || '/nomoEFW/app/modules/table/view.html') + '?ver=' + version;
			},
			scope: {
				table: '=?nomoTable',
				attrClass: '=?nomoTableClass'
			}
		}
	})
