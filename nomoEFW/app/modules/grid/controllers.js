'use strict';

angular.module('nomoEFW.grid')
	.controller('NomoGridCtrl', function ($scope,$timeout, nomoGrid, nomoAPI) {
		nomoGrid.get().then(function(grid){
			$timeout(function(){
			  $scope.grid=grid;
			})
		});
  })
	.controller('NomoGridDataTableCtrl', function ($scope,$timeout, nomoGrid, nomoAPI) {

  })



