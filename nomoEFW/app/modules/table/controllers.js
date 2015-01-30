'use strict';
//TODO: Ha valaki 10-es leosztásban nézi mondjuk a 30. elemet, és 100-as leosztásra kapcsol, akkor onnan kell neki mutatni
//TODO: nomoPagination-nek át kell adni a max elemszámot

angular.module('nomoEFW.table')
	.controller('nomoTableController',[ '$scope', '$timeout', 'nomoTableFactory', function ($scope,$timeout, nomoTableFactory) {
		nomoTableFactory.setTableScopeByAttributes($scope);

		nomoTableFactory.getInitialSetup($scope.table).then(
			function(table){

				$scope.table=table;


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
				if($scope.table.selection.indexOf($scope.table.rows[i].rowid) !== -1)
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
				var index=$scope.table.selection.indexOf($scope.table.rows[i].rowid);
				if(currentStatus == 1){
					if(index !== -1){
						$scope.table.selection.splice(index, 1);
						if($scope.table.activerow && $scope.table.activerow.rowid==$scope.table.rows[i].rowid)
							delete $scope.table.activerow;
					}
				}else{
					if(index === -1){
						$scope.table.selection.push($scope.table.rows[i].rowid);
					}
				}
			}
		};

		$scope.table.onSelectorClick = function(param){
			var index=$scope.table.selection.indexOf(param.rowid);
			if(index === -1)
				$scope.table.selection.push(param.rowid);
			else{
				$scope.table.selection.splice(index, 1);
				if($scope.table.activerow.rowid==param.rowid)
					delete $scope.table.activerow;
			}
		};

		$scope.table.onRowClick = function(param){
			$scope.table.selection=[];
			if($scope.table.selection.indexOf(param.rowid) === -1)
				$scope.table.selection.push(param.rowid);

			$scope.table.activerow=param;
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
