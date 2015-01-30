'use strict';

angular.module('nomoAdmin.common')
	.controller('nomoAdminMainCtrl',['$scope','nomoSession', function($scope,nomoSession) {
        $scope.nomoSession=nomoSession;
	}])
