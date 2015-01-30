'use strict';

angular.module('nomoEFW.grid')
	.factory('nomoGrid', function ($location,$q,nomoAPI) {
    var _this = this;
    return {
			get: function (grid) {
				var _this = this;
				return _this.init(grid).then(function(grid){
					return nomoAPI.getUserData().then(
						function (success) {
							grid.userdata = success
							return _this.getDefinition(grid).then(function (success) {
                grid.definition=success;
								var deferred=$q.defer();
								deferred.resolve(grid);
								return deferred.promise;
              })
						},
						function (error) {
							bootbox.alert('A useradatok lekérése nem sikerült');
							return null;
						}
					)
				});
			},
			getDefinition: function (grid) {
        return nomoAPI.getDefinition({
          "name": grid.type,
          "resultType": grid.resultType
        })
      },
     	init: function (grid) {
        var _this = this;

				grid = angular.isDefined(grid)?grid:{};
       	grid.type=nomoAPI.getTypeByPath();
        grid.method='select';
        grid.resultType='grid';
        grid.definition={'fields':[]};
				grid.dataTableOptions=_this.getDataTableOptions(grid),
				grid.actions= [];
        grid.buttons= [{
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
				}];
				grid.row_buttons= [{
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
				}];


				var deferred=$q.defer();
				deferred.resolve(grid);
				return deferred.promise;
			},
			getDataTableOptions:function(grid){
				var displayLength=window.localStorage.getItem("dataTables_iDisplayLength") || 25;
				var page=parseInt($location.search().p || 1);
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
					"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
						//rendezés beállítása
						var orders=[];
						for (var i = 0; i < oSettings.aaSorting.length; i++) {
							if(oSettings.aaSorting[i][0])
								orders.push([grid.definition.fields[oSettings.aaSorting[i][0]-1].name,oSettings.aaSorting[i][1]]);
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
          "sAjaxSource": "/api/datatables/" + grid.type,
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
              grid.data = data;
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
            var mainCheckbox = $(grid.dataTable).find('tr th .group-checkable')
            mainCheckbox.uniform();
            mainCheckbox.change(function () {
              var amIChecked = $(this).is(':checked');
              var pageList = []
              $(grid.dataTable).find('tbody .checkboxes').each(function () {
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
            //window.localStorage.setItem(grid.type, angular.toJson(oData));
            var gridStateJson=window.localStorage.getItem(grid.type);
            var gridState=(gridStateJson)?angular.fromJson(gridStateJson):false;

            if(!gridState || !gridState.visibleColumns){
              gridState={"visibleColumns":{}};
              for (var i = 0; i < grid.definition.fields.length; i++) {
                var colVisible = false;
                if (typeof grid.definition.fields[i].visible.grid != 'undefined')
                  colVisible = grid.definition.fields[i].visible.grid;
                else
                  colVisible = grid.definition.fields[i].visible.default;
                gridState.visibleColumns[grid.definition.fields[i].name]=colVisible;
              }
              window.localStorage.setItem(grid.type,angular.toJson(gridState));
            }

            for (var i = 0; i < grid.definition.fields.length; i++) {
              var colVisible=gridState.visibleColumns[grid.definition.fields[i].name];
              if(typeof colVisible === 'undefined') colVisible=false;
              //console.log(grid.definition.fields[i].name,colVisible);
              $('.datatables-colvis-dropdown input').eq(i).attr('checked', colVisible);
              $(grid.dataTable).dataTable().fnSetColumnVis(i + 1, colVisible, false /*(i == grid.definition.fields.length - 1)*/);
            }
            $('.datatables-colvis-dropdown input').uniform();
            $.uniform.update('thead .group-checkable');

            grid.dataTable.dataTable().fnAdjustColumnSizing();
          },
          "fnServerParams": function (aoData) {
            var efwparams = {
              "resultType": "grid",
              "className":grid.type,
              "method":grid.method,
              "fields": [],
              "filters": (new Array().concat(grid.filters.default, grid.filters.advanced, grid.filters.quick) || []),
              "datatables":true
            }
            var fields = [];
            for (var i = 0; i < grid.definition.fields.length; i++) {
              fields.push({
                "name": grid.definition.fields[i]["name"]
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
            for (var i = 0; i < grid.row_buttons.length; i++) {
              var actionBtn = document.createElement('a');
              $(actionBtn)
                .attr('data-nomo-rowid', aData[1])
                .attr('href', 'javascript:;')
              //.attr('ng-click','action_'+actions[i].type+'('+aData[1]+')')
              .html(grid.row_buttons[i].label)
                .addClass('btn default btn-xs ' + grid.row_buttons[i].className)
                .data('action', grid.row_buttons[i].action)
                .data('rowid', aData[1])
                .click(function () {
                  scope.$apply(grid.actions[$(this).data('action')]($(this).data('rowid')));
                });
              $('.row-actions', nRow).append(actionBtn).append(' ');
              $compile(actionBtn)(scope);
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
		}
	})
