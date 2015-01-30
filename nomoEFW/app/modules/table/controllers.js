'use strict';
//TODO: Ha valaki 10-es leosztásban nézi mondjuk a 30. elemet, és 100-as leosztásra kapcsol, akkor onnan kell neki mutatni
//TODO: nomoPagination-nek át kell adni a max elemszámot

angular.module('nomoEFW.table')
	.controller('nomoTableController',[ '$scope', '$timeout', 'nomoTableFactory', function ($scope,$timeout, nomoTableFactory) {
		nomoTableFactory.setTableScopeByAttributes($scope);

		nomoTableFactory.getInitialSetup($scope.table).then(
			function(table){

				$scope.table=table;

				//testing custom partials in cells
				//$scope.table.definition.fields[1].templateUrl="/admin/app/partials/nomoTableCustomCell_0.partial.html";

				$scope.$watch('table.rowsPerPage',function(newval,oldval){
					if(newval !== oldval)
						$scope.table.getPage();
				});
				$scope.$watch('table.page',function(newval,oldval){
					if(newval !== oldval)
						$scope.table.getPage();
				});

				$scope.table.getPage();
			}
		);

		$scope.table.getMasterSelectorStatus = function(){
			//STATUS: 0 none, 1 all, 2 mixed
			if(!$scope.table.rows) return 1;

			var status;
			var checkedRows = 0;

			for( var i = 0; i<$scope.table.rows.length; i++){
				if($scope.table.selection[$scope.table.rows[i].rowid])
					checkedRows++;
			}

			if(checkedRows === 0)
				return 0;
			else if(checkedRows === $scope.table.rows.length)
				return 1;
			else
				return 2;
		};

		$scope.table.onMasterSelectorClick = function(param) {
			var currentStatus = $scope.table.getMasterSelectorStatus();
			for(var i = 0; i<$scope.table.rows.length; i++){
				$scope.table.selection[$scope.table.rows[i].rowid] = (currentStatus == 1)?false: true;
			}
		};

		$scope.table.onSelectorClick = function(param){
			if($scope.table.selection[param.rowid])
				delete $scope.table.selection[param.rowid];
			else
			$scope.table.selection[param.rowid] = true;
		};

		$scope.table.onColumnVisibleClick = function(){
			$scope.table.getPage();
		};

		$scope.table.onSortClick = function (column,direction){
			$scope.table.activeSortColumn = {name:column,direction:direction};
			$scope.table.getPage();
		};

		$scope.table.getPage = function(){
			var visibleFields = ['rowid'];
			for( var i = 0; i<$scope.table.definition.fields.length; i++){
				if($scope.table.definition.fields[i].columnVisible)
					visibleFields.push($scope.table.definition.fields[i].name);
			}
			nomoTableFactory.getRows({
				class: $scope.table.class,
				params:{
					numberOfRows: $scope.table.rowsPerPage.value,
					offset: $scope.table.rowsPerPage.value*$scope.table.page,
					fields: visibleFields,
					orderby: {
						field: $scope.table.activeSortColumn.name,
						direction: ($scope.table.activeSortColumn.direction === "desc")
					}
				}
			}).then(
				function(response){
					$scope.table.rows=response;
				}
			);
		}
	}]);
