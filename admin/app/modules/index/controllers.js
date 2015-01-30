'use strict';

angular.module('nomoAdmin.index')
	.controller('nomoAdminIndexCtrl',['$scope','$route','$routeParams','nomoModalFactory','nomoSession', function($scope,$route,$routeParams,nomoModalFactory,nomoSession) {

		$scope.doLogout=function(){
			nomoSession.logout().then(function(result){
				//skip
			},function(error){
				nomoModalFactory.alert(error.message)
			})
		}
		$scope.routeParams=$routeParams;

        $route.reload();
	}])
