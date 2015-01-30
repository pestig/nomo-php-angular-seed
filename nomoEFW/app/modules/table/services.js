'use strict';

angular.module('nomoEFW.table')
    .factory('nomoTableFactory', ['$rootScope', '$q', 'nomoAPI', 'nomoModalFactory', function ($rootScope, $q, nomoAPI, nomoModalFactory) {

        var defaultSetup = {
            rowsPerPageOptions: [{value:10,label:'10'},{value:50,label:'50'},{value:100,label:'100'}],
            rowsPerPage: null,
            rowsCount: null,
            page: 0
        };
        var setTableScopeByAttributes=function(scope){
            if (angular.isUndefined(scope.table))
                scope.table = {};

            //find attrsClass, attrsMethod, etc. variables in scope, and puts them under table.class, table.method, etc.
            for(var scopeKey in scope){
                var propertyMatch = scopeKey.match(/^attr(\w+)/);
                if(propertyMatch) {
                    var property = propertyMatch[1].charAt(0).toLowerCase() + propertyMatch[1].slice(1);
                    if (property) {
                         scope.table[property] = scope[scopeKey];
                        delete scope[scopeKey];
                    }
                }
            }

        }

        var getInitialSetup = function (table) {
            if (angular.isUndefined(table))
                table = {};

            for (var key in defaultSetup) {
                if (angular.isUndefined(table[key])) {
                    table[key] = defaultSetup[key];
                }
            }
            table.selection = {};
            table.visibleColumns = [];
            table.activeSortColumn = {name: "rowid", direction: "asc"};

            table.rowsPerPage = table.rowsPerPageOptions[0];

            return getUserData().then(
                function (success) {
                    table.userdata = success;
                    return getDefinition(table);
                }
            ).then(
                function (success) {
                    table.definition = success;
                    return table;
                }
            )
        };

        var getRows = function (params) {
            params.className = params.class;
            params.method = (params.method || 'select');
            params.params.resultType = params.params.resultType || 'grid';

            return nomoAPI.execute(params).then(
                function(success){
                    return success.data;
                }
            )
        };

        var getDefinition = function (table) {
            return nomoAPI.getDefinition({
                "name": table.class,
                "resultType": table.resultType || 'grid'
            }).then(
                function (success) {
                    for(var i=0;i<success.fields.length; i++){
                        var shouldBeVisible = true;
                        if(success.fields[i].visible){
                            shouldBeVisible = success.fields[i].visible.grid || success.fields[i].visible.default;
                        }
                        success.fields[i].columnVisible = shouldBeVisible;
                    }
                    return success;
                }
            )
        }

        var getUserData = function () {
            return nomoAPI.getUserData();
        };


        return {
            getRows: getRows,
            setTableScopeByAttributes: setTableScopeByAttributes,
            getInitialSetup: getInitialSetup,
            getUserData: getUserData,
            getDefinition: getDefinition
        }


    }])
