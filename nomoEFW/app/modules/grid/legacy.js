angular.module('nomoEFW.grid')

  //GRID
  .directive('nomoGrid', function (version) {
    var link = function ($scope, element, attrs) {
      if(attrs.nomoGridType) $scope.grid.type=attrs.nomoGridType;
      if(attrs.nomoGridFilter) $scope.grid.filters.default=angular.fromJson(attrs.nomoGridFilter);

      $scope.grid.dataTableConfig=$scope.grid.getDataTableConfig();
      $scope.grid.getDefinition(element).then(
        function(success){
					$scope.grid.dataTableConfig.aaSorting=$scope.grid.dtOrder;
					$scope.grid.dataTableConfig.aoColumnDefs=$scope.getColumnData();
          var dataTableElement=$(element).find('.dataTable');
          //$(dataTableElement).attr('id','SpryMedia_DataTables_test');


          $scope.grid.dataTable=dataTableElement.dataTable($scope.grid.dataTableConfig);

        }
      );
    }
    return {
      restrict: 'A',
      controller: 'nomoGridController',
      templateUrl: function(element,attrs){
        return (attrs.nomoTemplateUrl || '/nomoEFW/app/modules/grid/legacy/grid.partial.html')+'?ver='+version;
      },
      scope: {
        grid: '=?nomoGridScope',
      },
      link: link
  }
  })
  .directive('nomoGridInline', function (version) {
    var link = function ($scope, element, attrs) {
      if(attrs.nomoGridType) $scope.grid.type=attrs.nomoGridType;
      if(attrs.nomoGridFilter) $scope.grid.filters.default=angular.fromJson(attrs.nomoGridFilter);

      $scope.grid.dataTableConfig=$scope.grid.getDataTableConfig();
      $scope.grid.dataTableConfig.aLengthMenu=[ [ -1], [ "Mind"] ]

      $scope.grid.getDefinition(element).then(
        function(success){
					$scope.grid.dataTableConfig.aaSorting=$scope.grid.dtOrder;
          $scope.grid.dataTableConfig.aoColumnDefs=$scope.getColumnData();
          var dataTableElement=$(element).find('.dataTable');
          //$(dataTableElement).attr('id','SpryMedia_DataTables_test');
          $scope.grid.dataTable=dataTableElement.dataTable($scope.grid.dataTableConfig);
        }
      );
    }
    return {
      restrict: 'A',
      controller: 'nomoGridController',
      templateUrl: function(element,attrs){
        return (attrs.nomoTemplateUrl || '/nomoEFW/app/modules/grid/legacy/grid.partial.html')+'?ver='+version;
      },
      scope: {
        grid: '=?nomoGridScope',
      },
      link: link
  }
  })

  //QUICK FILTER
  .directive('nomoGridFilterQuick', function (version) {
    var link = function ($scope, element, attrs) {
    }
    return {
        restrict: 'A',
        templateUrl: function(element,attrs){
            return (attrs.nomoTemplateUrl || '/nomoEFW/app/modules/grid/legacy/grid_filter_quick.partial.html')+'?ver='+version;
        },
        controller: 'nomoQuickFilterCtrl',
        link: link,
        scope: true
    }
  })
  .controller('nomoQuickFilterCtrl', function ($scope) {
    var _this=this;
    $scope.quickFilter=$scope.quickFilter || {};
    $scope.quickFilter.form = {data:{},definition:[]}; //quick filter panel
  })

  //ADVANCED FILTER
  .directive('nomoGridFilterAdvanced', function (version,$location,nomoGridService) {
    var link = function ($scope, element, attrs) {

    }
    return {
      restrict: 'A',
      templateUrl: function(element,attrs){
          return (attrs.nomoTemplateUrl || '/nomoEFW/app/modules/grid/legacy/grid_filter_advanced.partial.html')+'?ver='+version;
      },
      controller: 'nomoGridFilterAdvancedCtrl',
      link: link
    }
  })
  .controller('nomoGridFilterAdvancedCtrl', function($scope,$location,nomoGridService){
    $scope.advancedFilter=[];
  	$scope.getFilterOperator=function(controltype){
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


    $scope.addFilter=function(){
      var filter={
        data:{
          field:$scope.grid.definition.fields[0].name,
          operator:null,
          value:''
        },
        definition:[]
      }
      $scope.filterFieldChanged(filter);
      $scope.advancedFilter.push(filter);
      //$scope.setGridFilter($scope.advancedFilter);
    }

    $scope.filterFieldChanged=function(filterItem){
      $scope.setFilterItemDefinition(filterItem);
      var operators=$scope.getFilterOperator(filterItem.definition[0].controltype);

			filterItem.data.value="";
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

    $scope.setFilterItemDefinition=function(filterItem){
      var fieldDefintion=angular.copy( $scope.grid.getDefinitionItemByName(filterItem.data.field) );
			fieldDefintion.readonly=false;
  		fieldDefintion.name='value';
			if(fieldDefintion.controltype=="radio") fieldDefintion.controltype="select";
			if(fieldDefintion.params && fieldDefintion.params.buttons) fieldDefintion.params.buttons=null;

  		filterItem.definition=[fieldDefintion];
  	}


  	$scope.removeAllFilters=function(){
  		$scope.setGridFilter([]);
  	}
  	$scope.removeFilter=function(index){
  		$scope.advancedFilter.splice(index, 1);
  		if($scope.advancedFilter.length==0) $scope.removeAllFilters();

      //$scope.setGridFilter($scope.advancedFilter);
  	}

    $scope.setGridFilter=function(_filter){
      $scope.advancedFilter=_filter;
      $scope.advancedFilterVisibility=$scope.advancedFilter.length!=0;

      var advancedFilters = [];
      for (var i = 0; i < $scope.advancedFilter.length; i++) {
        advancedFilters.push($scope.advancedFilter[i].data);
      }
      $scope.grid.filters.advanced=angular.copy(advancedFilters);
      //$scope.grid.filters.advanced=$scope.advancedFilter;   //ha minden karakter leütésre szeretnénk hoyg frissüljön

      // SET FILTER INTO URL PARAMS
      $location.search('f', nomoGridService.filterToSearchParam($scope.grid.filters.advanced));
    }

  	$scope.filterGrid=function(){
      $scope.setGridFilter($scope.advancedFilter);
      $scope.grid.refreshGrid();
  	}

    $scope.searchParamToFilter=function(_param){
       var filters=[];
       if(_param){
         var param=angular.fromJson(LZString.decompressFromBase64(_param));
         for (var i=0;i<param.length;i++){
           var filter = {
             data:param[i],
             definition:[]
           }
           $scope.setFilterItemDefinition(filter);
           filters.push(filter);
         }
       }
       return filters;
    }

    $scope.grid.actions.toggleAdvancedFilter=function(){
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

    $scope.$on('DefinitionUpdated',function(){
        init();
    });
    if($scope.grid.definition && $scope.grid.definition.fields && $scope.grid.definition.fields.length>0) init();



  })
  .directive('nomoGridButtons', function (version) {
    var link = function ($scope, element, attrs) {
    }
    return {
      restrict: 'A',
      templateUrl: function(element,attrs){
        return (attrs.nomoTemplateUrl || '/nomoEFW/app/modules/grid/legacy/grid_buttons.partial.html')+'?ver='+version;
      },
      link: link,
      scope: true
    }
  })


  //GRID
  .controller('nomoGridController', function ($scope, $window, $location, $compile,nomoAPI, nomoGridService,$http) {
    var _this=this;

    $scope.grid = angular.copy(nomoGridService.getDefaultConfig);
    $scope.grid.filters.advanced=nomoGridService.searchParamToFilter($location.search().f);

    $scope.grid.type=nomoAPI.getTypeByPath();
    $scope.grid.getDefinition = function (scopeElement) {
      return nomoAPI.getDefinition({
        "name":$scope.grid.type
      }).then(
        function(response){
          $scope.grid.definition=response;

					var orders=nomoGridService.searchParamToFilter($location.search().s);
					var configOrder=[];
					for (var i = 0; i < $scope.grid.definition.fields.length; i++) {
						for (var j = 0; j < orders.length; j++) {
							 if(orders[j][0]==$scope.grid.definition.fields[i]["name"]){
									configOrder.push([i+1,orders[j][1]])
							 }
						}
					}

					$scope.grid.dtOrder=configOrder
          $scope.$broadcast('DefinitionUpdated');
        }
      )
    }

    _this.getSelection=function(rowid){return ($scope.grid.selection[rowid])?true:false}

    $scope.grid.getDefinitionItemByName=function(name){
        if($scope.grid.definition && $scope.grid.definition.fields){
            for(var i=0;i<$scope.grid.definition.fields.length;i++){
                if($scope.grid.definition.fields[i].name==name)
                    return $scope.grid.definition.fields[i];
            }
        }
    }


    $scope.getColumnData=function(){
       //if(console) console.info('init grid');
       var coldata=[]
        //checkbox column
        coldata.push({'bSortable': false,sTitle:'<input class="group-checkable checkboxes" type="checkbox" >',mRender:function(oObj){return '<input class="checkboxes" type="checkbox" >'},"aTargets": [0],"sWidth": "38px"})
        //data columns
        for(var i=0;i<$scope.grid.definition.fields.length;i++){
          if($scope.grid.definition.fields[i].controltype.match(/(date|time)/i)){
            coldata.push({
              sTitle:$scope.grid.definition.fields[i]["label"],
              aTargets: [i+1],
              mRender:function(data, type, row){
                if(parseInt(data)==0)
                  return '';
                else{
                  if(data.match(/[:]/ig)){
                    //ha van benne időpont, kiszedjük a másodperc részt
                    data=data.split(':');
										if(data.length>2)
                      data.pop();
                    return data.join(':');
                  } else {
                    return data
                  }
                }
              }
            })
          }else{
            coldata.push({sTitle:$scope.grid.definition.fields[i]["label"],"aTargets": [i+1],mRender:function(data, type, row){return '<div class="cellwrapper">'+data+'</div>';}})
          }
        }
        //actions column
        coldata.push({'bSortable': false,sTitle:'Műveletek',mRender:function(oObj){return '<div class="row-actions"></div>'},"sWidth": "180px","aTargets": [$scope.grid.definition.fields.length+1]})
        return coldata;
    }

    $scope.grid.colVisClicked=function(iCol){
      var gridStateJson=window.localStorage.getItem($scope.grid.type);
      var gridState=(gridStateJson)?angular.fromJson(gridStateJson):false;
      bVis = !$($scope.grid.dataTable).dataTable().fnSettings().aoColumns[iCol].bVisible;

      var field=$scope.grid.definition.fields[iCol-1];
      gridState.visibleColumns[field.name]=bVis;
      window.localStorage.setItem($scope.grid.type,angular.toJson(gridState));

      $($scope.grid.dataTable).dataTable().fnSetColumnVis(iCol, bVis, false);
      $scope.grid.dataTable.dataTable().fnAdjustColumnSizing();
    }

    var getAAData=function(){
        var aaData=[];
        for(var i=0;i<$scope.grid.data.length;i++){
            aaDataRow=[];
            aaDataRow.push(''); //checkbox row
            for(var key in $scope.grid.data[i]){
                if(angular.isDefined($scope.grid.data[i][key].label))
                    aaDataRow.push($scope.grid.data[i][key].label);
                else
                    aaDataRow.push($scope.grid.data[i][key].value);
            }
            aaDataRow.push(''); //actions row
            aaData.push(aaDataRow);
        }
        return aaData;
    }

    $scope.setSelection = function (sel) {
  		$scope.gridSelection = sel;
  	}

    _this.updateSelection=function(rows,isSelected){
      for(var i=0;i<rows.length;i++){
        if(isSelected){
            $scope.grid.selection[rows[i]]=true;
            $('tr[data-rowid="'+rows[i]+'"]').addClass("active");
        } else {
            delete($scope.grid.selection[rows[i]]);
            $('tr[data-rowid="'+rows[i]+'"]').removeClass("active");
        }
      }
      var selectionArray=[];

      for(var selectedId in $scope.grid.selection){
        selectionArray.push(parseInt(selectedId));
      }

      $scope.$apply($scope.setSelection(selectionArray.sort(function(a,b){return a-b})));
      return;
      /*Main checkbox state update*/

      var countAll=$('tbody .checkboxes',grid).length;
      var countChecked=$('tbody .checkboxes:checked',grid).length;
      if(countAll==countChecked)
        $('thead .group-checkable',grid).attr('checked',true);
      else
        $('thead .group-checkable',grid).attr('checked',false);
      $.uniform.update('thead .group-checkable');
    }

    $scope.grid.refreshGrid=function(){
      $scope.grid.selection={};
      $($scope.grid.dataTable).dataTable().fnDraw();
      return $($scope.grid.dataTable).dataTable();
    }


    var displayLength=window.localStorage.getItem("dataTables_iDisplayLength") || 25;
		var page=parseInt($location.search().p || 1);

    $scope.grid.getDataTableConfig=function(){
      return {
         "iDisplayLength": displayLength,
				 "iDisplayStart":(page!=1)?((page-1)*displayLength):0,
         "oLanguage": {
           "sEmptyTable":     "Nincs rendelkezésre álló adat",
           "sInfo":           "Találatok: _START_ - _END_ Összesen: _TOTAL_",
           "sInfoEmpty":      "Nincs találat",
           //"sInfoFiltered":   "(_MAX_ összes rekord közül szűrve)",
           "sInfoFiltered":   "",
           "sInfoPostFix":    "",
           "sInfoThousands":  " ",
           "sLengthMenu":     "_MENU_ találat oldalanként",
           "sLoadingRecords": "Betöltés...",
           "sProcessing":     "Feldolgozás...",
           "sSearch":         "Keresés:",
           "sZeroRecords":    "Nincs a megjeleníthető találat",
           "oPaginate": {
                   "sFirst":    "Első",
                   "sPrevious": "Előző",
                   "sNext":     "Következő",
                   "sLast":     "Utolsó"
           },
           "oAria": {
                   "sSortAscending":  ": aktiválja a növekvő rendezéshez",
                   "sSortDescending": ": aktiválja a csökkenő rendezéshez"
           }
          },
          "sDom": 'r<"top"l><"clear">t<"bottom"ip><"clear">',
          "bStateSave": false,
          "iCookieDuration": 60 * 60 * 24 * 365,
          "fnStateSaveParams": function (oSettings, oData) {

            //console.log("save_"+$scope.grid.type, oData);//return;
            /*oData.aoSearchCols = null;
            oData.oSearch = null;
            window.localStorage.setItem($scope.grid.type, angular.toJson(oData));*/
          },
          "fnStateLoaded": function (oSettings, oData) {
            //console.log("load_"+$scope.grid.type, oData);//return;
            /*var tableCookie = window.localStorage.getItem($scope.grid.type);
            if (tableCookie) {
              oData = angular.fromJson(tableCookie);
              _this.cookieState = angular.fromJson(tableCookie);
                console.log("loaded_"+$scope.grid.type, oData);//return;
            } else {
              console.warn('Empty cookiestatE!');
            } */
          },
					"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
						//rendezés beállítása
						var orders=[];
						for (var i = 0; i < oSettings.aaSorting.length; i++) {
							if(oSettings.aaSorting[i][0])
								orders.push([$scope.grid.definition.fields[oSettings.aaSorting[i][0]-1].name,oSettings.aaSorting[i][1]]);
						}
						var page=Math.round(iStart/displayLength)+1;
						if(page!=1)
							$location.search('p', page);
					  else
							$location.search('p', null);

						$location.search('s', nomoGridService.filterToSearchParam(orders));
						$location.replace();

  				},
          "bProcessing": true,
          "bServerSide": true,
          "sAjaxSource": "/api/datatables/" + $scope.grid.type,
          "aoColumnDefs": {},
          "fnServerData": function (sSource, aoData, fnCallback, oSettings) {
            //angular ajax request
            oSettings.jqXHR = nomoAPI.http({
              url: sSource,
              method:'POST',
              data: $.param(aoData),
              headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).then(function(result){
              var response=result.data;
              var data = [];
              for (var i = 0; i < response.data.length; i++) {
                var dataItem = {};
                for (var key in response.data[i]) {
                  var keyParts = key.split('_nomoefw_');
                  if (!dataItem[keyParts[0]]) dataItem[keyParts[0]] = {}
                  if (response.data[i][key] === null) response.data[i][key] = "";
                  if (keyParts.length > 1) {
                    //Ha fiktív tábla - pl. parent_nomoefw_label, parent_nomoefw_type
                    dataItem[keyParts[0]][keyParts[1]] = response.data[i][key]
                  } else {
                    dataItem[keyParts[0]]['value'] = response.data[i][key]
                  }
                }
                data.push(dataItem);
              }
              $scope.grid.data = data;
              response.aaData = getAAData();
              fnCallback(response, "success")
              return response;
            }/*,
            function(error){
              fnCallback(error, "error")
             console.log('error',error);
            }*/)

          },
          "fnInitComplete": function (oSettings) {
            var mainCheckbox = $($scope.grid.dataTable).find('tr th .group-checkable')
            mainCheckbox.uniform();
            mainCheckbox.change(function () {
              var amIChecked = $(this).is(':checked');
              var pageList = []
              $($scope.grid.dataTable).find('tbody .checkboxes').each(function () {
                $(this).attr('checked', amIChecked)
                pageList.push($(this).parents('tr').attr('data-rowid'))
              });
              $.uniform.update()
              _this.updateSelection(pageList, amIChecked)
            });

						//kezeljük ha változott a megjelenített sorok száma
						$(oSettings.nTableWrapper).find(".dataTables_length select").change(function () {
					    var dataTables_iDisplayLength=window.localStorage.getItem("dataTables_iDisplayLength");
							if($(this).val()!=dataTables_iDisplayLength){
								 window.localStorage.setItem("dataTables_iDisplayLength",$(this).val());

							}
							displayLength=$(this).val();
							//$.cookie('the_cookie', oContainer.find("select").val(), { expires: 90 });
						});

            //handle visible columns
            //window.localStorage.setItem($scope.grid.type, angular.toJson(oData));
            var gridStateJson=window.localStorage.getItem($scope.grid.type);
            var gridState=(gridStateJson)?angular.fromJson(gridStateJson):false;

            if(!gridState || !gridState.visibleColumns){
              gridState={"visibleColumns":{}};
              for (var i = 0; i < $scope.grid.definition.fields.length; i++) {
                var colVisible = false;
                if (typeof $scope.grid.definition.fields[i].visible.grid != 'undefined')
                  colVisible = $scope.grid.definition.fields[i].visible.grid;
                else
                  colVisible = $scope.grid.definition.fields[i].visible.default;
                gridState.visibleColumns[$scope.grid.definition.fields[i].name]=colVisible;
              }
              window.localStorage.setItem($scope.grid.type,angular.toJson(gridState));
            }

            for (var i = 0; i < $scope.grid.definition.fields.length; i++) {
              var colVisible=gridState.visibleColumns[$scope.grid.definition.fields[i].name];
              if(typeof colVisible === 'undefined') colVisible=false;
              //console.log($scope.grid.definition.fields[i].name,colVisible);
              $('.datatables-colvis-dropdown input').eq(i).attr('checked', colVisible);
              $($scope.grid.dataTable).dataTable().fnSetColumnVis(i + 1, colVisible, false /*(i == $scope.grid.definition.fields.length - 1)*/);
            }
            $('.datatables-colvis-dropdown input').uniform();
            $.uniform.update('thead .group-checkable');

            $scope.grid.dataTable.dataTable().fnAdjustColumnSizing();
          },
          "fnServerParams": function (aoData) {
            var efwparams = {
              "resultType": "grid",
              "className":$scope.grid.type,
              "method":$scope.grid.method,
              "fields": [],
              "filters": (new Array().concat($scope.grid.filters.default, $scope.grid.filters.advanced, $scope.grid.filters.quick) || []),
              "datatables":true
            }
            var fields = [];
            for (var i = 0; i < $scope.grid.definition.fields.length; i++) {
              fields.push({
                "name": $scope.grid.definition.fields[i]["name"]
              });
            }
            efwparams.fields = efwparams.fields.concat([''], fields, ['']);
            aoData.push({
              "name": "json",
              "value": JSON.stringify(efwparams)
            });
          },
          "fnCreatedRow": function (nRow, aData, iDataIndex) {
            $('.checkboxes', nRow).uniform();
            $(nRow).attr('data-rowid', aData[1]);
            $('.checkboxes', nRow).click(function () {
              var tr = $(this).parents('tr');
              if ($(this).is(':checked')) {
                _this.updateSelection([$(tr).data("rowid")], true)
              } else {
                _this.updateSelection([$(tr).data("rowid")], false)
              }
            });
            for (var i = 0; i < $scope.grid.row_buttons.length; i++) {
              var actionBtn = document.createElement('a');
              $(actionBtn)
                .attr('data-nomo-rowid', aData[1])
                .attr('href', 'javascript:;')
              //.attr('ng-click','action_'+actions[i].type+'('+aData[1]+')')
              .html($scope.grid.row_buttons[i].label)
                .addClass('btn default btn-xs ' + $scope.grid.row_buttons[i].className)
                .data('action', $scope.grid.row_buttons[i].action)
                .data('rowid', aData[1])
                .click(function () {
                  $scope.$apply($scope.grid.actions[$(this).data('action')]($(this).data('rowid')));
                });
              $('.row-actions', nRow).append(actionBtn).append(' ');
              $compile(actionBtn)($scope);
            }
            //apply uniform
          },
          "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {

            //after page change, we reload selected checkboxes
            if (_this.getSelection($(nRow).data('rowid'))) {
              //console.log(nRow,_this.getSelection($(nRow).data('rowid')));
              $('.checkboxes', nRow).attr('checked', true);
              $.uniform.update($('.checkboxes', nRow));
              $(nRow).addClass("active");
            }
          }
        }
    }

    /*actions begin*/
    $scope.grid.actions['deleteSelection'] = function () {
      var arrSelection=[];
      for (key in $scope.grid.selection) {
          if ($scope.grid.selection[key]) arrSelection.push(key);
      }
      if (arrSelection.length == 0) {
        bootbox.alert("Nincs kijelölt elem.");
      } else {
        if(angular.equals($scope.grid.selection,{})){
          bootbox.alert("Jelenleg nincsenek elemek kijelölve a listából.");
          return;
        }
        bootbox.confirm("<b>" + arrSelection.length + " db</b> elemet készül törölni.<br><strong>Biztosan törölni akarja?</strong>", function (result) {
          if (result) {
            nomoAPI.execute({
              className: $scope.grid.type,
              method: 'delete',
              params: {
                filters: [
                  {
                    field: 'rowid',
                    value: arrSelection,
                    operator: 'in'
                              }
                          ]
              }
            }).then(
              function () { /*success*/
                $.gritter.add({
                  title: 'Sikeres törlés',
                  text: 'Az elemeket sikeresen töröltük a rendszerből.',
                  sticky: false,
                  time: 3000
                });
                 $scope.grid.refreshGrid();
              },
              function (error) { /*error*/
                bootbox.alert("Az elem törlésébe hiba csúszott:<br><b>" + error.data.message + "</b>", function () {});
              },
              function () { /*notify*/ })
          }
        })
      }
    }
    $scope.grid.actions['delete'] = function (rowid) {
      bootbox.confirm("Biztosan törölni akarja?", function (result) {
        if (result) {
          nomoAPI.execute({
            className: $scope.grid.type,
            method: 'delete',
            params: {
              filters: [
                {
                  field: 'rowid',
                  value: rowid
                }
              ]
            }
          }).then(
            function () { /*success*/
              $.gritter.add({
                title: 'Sikeres törlés',
                text: 'Az elemet sikeresen töröltük a rendszerből.',
                sticky: false,
                time: 3000
              });
              $scope.grid.refreshGrid();
            },
            function (error) { /*error*/
              bootbox.alert("Az elem törlésébe hiba csúszott:<br><b>" + error.data.message, function () {});
            },
            function () { /*notify*/ })
        }
      })
    }
    $scope.grid.actions['new'] = function (_params) {
      if(_params){
        $location.path(nomoAPI.getBasePath() + '/' + $scope.grid.type + '/new/').search({
          params: angular.toJson(_params)
        });
      } else if ($scope.grid.filters.default) {
        $location.path(nomoAPI.getBasePath() + '/' + $scope.grid.type + '/new/').search({
          params: angular.toJson($scope.grid.filters.default)
        });
      } else {
        $location.path(nomoAPI.getBasePath() + '/' + $scope.grid.type + '/new').search({});
      }

      $.gritter.add({
        title: 'Új elem',
        text: 'Az új elem a létrehozás gombbal menthető.',
        class_name: "gritter-light",
        sticky: false,
        time: 3000
      });
    }
    $scope.grid.actions['export'] = function () {
      methodParams = {};

      methodParams.filters =(new Array().concat($scope.grid.filters.default, $scope.grid.filters.advanced, $scope.grid.filters.quick) || []);

			var gridStateJson=window.localStorage.getItem($scope.grid.type);
      var gridState=(gridStateJson)?angular.fromJson(gridStateJson):false;
			var fields = [];
			for (var i = 0; i < $scope.grid.definition.fields.length; i++) {
				var colVisible=gridState.visibleColumns[$scope.grid.definition.fields[i].name];
				if(typeof colVisible === 'undefined') colVisible=false;
				if(colVisible)
					fields.push($scope.grid.definition.fields[i]["name"]);
			}
			methodParams["fields"] = fields;
			var params = {
        "className": $scope.grid.type,
        "method": "export",
        "params": methodParams
      }
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      form.setAttribute("action", "/api?rnd=" + Math.random());
      form.setAttribute("target", "_blank");
      var hiddenField = document.createElement("input");
      hiddenField.setAttribute("type", "hidden");
      hiddenField.setAttribute("name", "json");
      hiddenField.setAttribute("value", JSON.stringify(params));
      form.appendChild(hiddenField);
      document.body.appendChild(form);
      form.submit();
    }
    $scope.grid.actions['open'] = function (rowid, action) {
      $location.path(nomoAPI.getBasePath() + '/' + $scope.grid.type + '/' + rowid).search({});
    }
    /*actions end*/

  })



  .factory('nomoGridService', function (nomoAPI) {
    var _this = this;
      return {
        getDefaultConfig: {
          type: null,
          method: 'select',
          definition: [],
          selection:{},
          dataTable:{},
          resultType: 'grid',
          filters: {
            default: [],
            quick: [],
            advanced: []
          },
          quickFilterForm:{},
          actions: [],
          buttons: [
            {
              action: 'new',
              icon: 'fa fa-plus',
              label: 'Létrehozás',
              className: 'green',
              params:{back:true}
            },{
              action: 'deleteSelection',
              icon: 'fa fa-trash-o',
              label: 'Kijelöltek törlése',
              className: 'red',
              params:{back:true}
            },
            {
              action: 'export',
              icon: 'fa fa-download',
              label: 'Exportálás (CSV)'
              //className: 'blue'
              //params:{back:true}
            }
          ],
          row_buttons: [{
              action: "open",
              label: "Megnyitás",
              className: "blue-stripe"
            }, {
              action: "delete",
              label: "Törlés",
              className: "red-stripe",
              successCallback: function () {
                 $scope.grid.refreshGrid();
              }
          }]
        },
        searchParamToFilter:function(_param){
           if(_param)
             return angular.fromJson(LZString.decompressFromBase64(_param));
           else
             return [];
        },
        filterToSearchParam:function(_filters){
          var  filters=angular.copy(_filters);
          if(filters.length)
            return  LZString.compressToBase64(angular.toJson(filters));
          else
            return  null;
        }
      }
    })
