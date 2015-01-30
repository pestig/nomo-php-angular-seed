angular.module('nomoEFW.advanced_filter')
.controller('NomoAdvancedFilterCtrl', function($scope,$location,nomoGridService,nomoAPI){


    $scope.advanced_filter= $scope.advanced_filter || {};
	  $scope.advanced_filter.filters=[];
  	$scope.advanced_filter.getFilterOperator=function(controltype){
  		if(!controltype) return;
  		if(controltype.match(/(select|checkbox)/gi))
  			return [
  				{"value":"=","label":"="},
  				{"value":"!=","label":"<>"}
  			]
  		else if(controltype.match(/(date|time)/gi))
  			return [
  				{"value":"=","label":"="},
  				{"value":"<","label":"<"},
  				{"value":">","label":">"},
  				{"value":"<=","label":"<="},
  				{"value":">=","label":">="}
  			]
  		else
  			return [
  				{"value":"=","label":"="},
  				{"value":"!=","label":"<>"},
  				{"value":"<","label":"<"},
  				{"value":">","label":">"},
          {"value":"%like%","label":"tartalmazza ezt a kifejezést:"},
  				{"value":"like%","label":"ezzel a kifejezéssel kezdődik:"},
  				{"value":"%like","label":"erre a kifejezésre végződik:"}
  			]
  	}


    $scope.advanced_filter.addFilter=function(){
      var filter={
        data:{
          field:$scope.advanced_filter.definition.fields[0].name,
          operator:null,
          value:''
        },
        definition:[]
      }
      $scope.advanced_filter.filterFieldChanged(filter);
      $scope.advanced_filter.filters.push(filter);
      //$scope.setGridFilter($scope.advancedFilter);
    }

    $scope.advanced_filter.filterFieldChanged=function(filterItem){

			console.log(filterItem);
      setFilterItemDefinition(filterItem);
      var operators=$scope.advanced_filter.getFilterOperator(filterItem.definition[0].controltype);

      //ha jó az operátor akkor végeztünk a filter beállítással
      for(var i=0;i<operators.length;i++){
        if(filterItem.data.operator===operators[i].value)
          return;
      }

      //ha érvényetlen az operátor beállítjuk a default értéket
      if(operators.length>4 && operators[4].value=="%like%")
        filterItem.data.operator=operators[4].value;
      else
        filterItem.data.operator=operators[0].value;
    }


  	$scope.advanced_filter.removeAllFilters=function(){
  		$scope.advanced_filter.filters=[];
  	}

  	$scope.advanced_filter.removeFilter=function(index){
  		$scope.advanced_filter.filters.splice(index, 1);
  		if($scope.advanced_filter.filters.length==0) $scope.advanced_filter.removeAllFilters();

      //$scope.setGridFilter($scope.advancedFilter);
  	}

		/*
    $scope.advanced_filter.setGridFilter=function(_filter){
      $scope.advanced_filter.filters=_filter;
      $scope.advanced_filter.visibility=$scope.advanced_filter.filters.length!=0;

      var advancedFilters = [];
      for (var i = 0; i < $scope.advanced_filter.filters.length; i++) {
        advancedFilters.push($scope.advanced_filter.filters[i].data);
      }
      $scope.grid.filters.advanced=angular.copy(advancedFilters);
      //$scope.grid.filters.advanced=$scope.advancedFilter;   //ha minden karakter leütésre szeretnénk hoyg frissüljön

      // SET FILTER INTO URL PARAMS
      $location.search('f', nomoGridService.filterToSearchParam($scope.grid.filters.advanced));
    }

  	$scope.advanced_filter.filterGrid=function(){
      $scope.advanced_filter.setGridFilter($scope.advancedFilter);
      $scope.advanced_filter.grid.refreshGrid();
  	}

    $scope.advanced_filter.searchParamToFilter=function(_param){
       var filters=[];
       if(_param){
         var param=angular.fromJson(LZString.decompressFromBase64(_param));
         for (var i=0;i<param.length;i++){
           var filter = {
             data:param[i],
             definition:[]
           }
           $scope.advanced_filter.setFilterItemDefinition(filter);
           filters.push(filter);
         }
       }
       return filters;
    }

    $scope.advanced_filter.toggleAdvancedFilter=function(){
      $scope.advancedFilterVisibility=!$scope.advancedFilterVisibility;
      if($scope.advancedFilterVisibility){
        $scope.addFilter();
      }else{
        $scope.removeAllFilters();
      }
  	}

  	var init = function() {
      $scope.$watch('grid.filters.advanced',function(newval,oldval){
        if(!angular.equals(newval,oldval)){
          $scope.grid.refreshGrid();
        }
      },true);

      $scope.$on('$routeUpdate', function(){
        $scope.setGridFilter($scope.searchParamToFilter($location.search().f));
      });

      $scope.setGridFilter($scope.searchParamToFilter($location.search().f));
    }
*/

		var getFieldByName=function(name,definiton){
        if(definiton && definiton.fields){
						for(var i=0;i<definiton.fields.length;i++){
							if(definiton.fields[i].name==name)
                    return definiton.fields[i];
            }
        }
    }

		var setFilterItemDefinition=function(filterItem){
      var fieldDefintion=angular.copy( getFieldByName(filterItem.data.field,$scope.advanced_filter.definition));
  		fieldDefintion.name='value';
  		filterItem.definition=[fieldDefintion];
  	}

		var searchParamToFilter=function(_param){
       var filters=[];
       if(_param){
         var param=angular.fromJson(LZString.decompressFromBase64(_param));
         for (var i=0;i<param.length;i++){
           var filter = {
             data:param[i],
             definition:[]
           }
           setFilterItemDefinition(filter);
           filters.push(filter);
         }
       }
       return filters;
    }

		$scope.advanced_filter.setOutFilter=function(){
      $scope.advanced_filter.visibility=$scope.advanced_filter.filters.length!=0;

      var outfilter = [];
      for (var i = 0; i < $scope.advanced_filter.filters.length; i++) {
        outfilter.push($scope.advanced_filter.filters[i].data);
      }
      $scope.advanced_filter.outfilter=angular.copy(outfilter);

      // SET FILTER INTO URL PARAMS
      $location.search('f', nomoGridService.filterToSearchParam(outfilter));

		}

	  nomoAPI.getDefinition({"name":$scope.advanced_filter.type}).then(function(success){
			$scope.advanced_filter.definition=success;

			$scope.$on('$routeUpdate', function(){
				$scope.advanced_filter.filters=searchParamToFilter($location.search().f);
        $scope.advanced_filter.setOutFilter();
      });
			$scope.advanced_filter.filters=searchParamToFilter($location.search().f);
			$scope.advanced_filter.setOutFilter();
			//$scope.advanced_filter.addFilter();

		})


  })
