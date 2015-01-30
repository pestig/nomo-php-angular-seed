'use strict';

angular.module('nomoAdmin.login')
	.controller('nomoAdminLoginFormCtrl',['$scope','nomo','nomoSession','nomoModalFactory', function($scope,nomo,nomoSession,nomoModalFactory) {
		$scope.doLogin=function(){
            nomoSession.login($scope).then(function(result){
				//skip
			},function(error){
				nomoModalFactory.alert(error.message)
			})
		}
	}])
