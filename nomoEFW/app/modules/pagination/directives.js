'use strict';

angular.module('nomoEFW.pagination')
.directive('nomoPagination', function (version) {

	var link = function($scope,element,attrs){
		var paginationButtonCount = 9;

		$scope.getPaginationArray = function() {
			var paginationMiddle = Math.floor(paginationButtonCount / 2);
			var startPage = $scope.page - paginationMiddle;
			if (startPage < 0) startPage = 0;

			var paginationArray = [];
			for (var i = 0; i < paginationButtonCount; i++) {
				paginationArray.push(startPage + i);
			}
			return paginationArray;
		}

		$scope.setPage = function(page){
			if(page == 'up'){
				$scope.page++;
			} else if(page == 'down'){
				if($scope.page > 0) {
					$scope.page--;
				}
			} else {
				$scope.page = page;
			}
		}
	};

	return {
		restrict: 'A',
		templateUrl: '/nomoEFW/app/modules/pagination/view.html',
		scope:{
			page: "=ngValue",
			rowCount: "&nomoPaginationRowCount",
			rowsPerPage: "&nomoPaginationRowsPerPage"
		},
		link: link
	}

});
