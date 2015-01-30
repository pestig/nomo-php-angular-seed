'use strict';

angular.module('nomoEFW.grid')
		.directive('nomoDataTable', function ($timeout, nomoGrid,version) {
        return {
            restrict: 'A',
            controller: 'NomoGridDataTableCtrl',
            //templateUrl: function(element,attrs){return (attrs.dgvcDataTableTemplateUrl || '/nomoEFW/app/modules//view.html?ver=' + version);},
            scope: true,
            link: function ($scope, element, attrs) {
                console.log('init directive',$scope.grid)
								$(element).dataTable($scope.grid.dataTableOptions);
            }
        }
    })
