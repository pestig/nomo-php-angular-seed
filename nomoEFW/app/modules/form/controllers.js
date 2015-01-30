'use strict';

angular.module('nomoEFW.form')
	.controller('nomoFormCtrl', function ($scope,$timeout, nomoForm, nomoAPI) {
	  //console.log('ctrl',$scope.form.type);
		nomoForm.get($scope.form).then(function(form){
			$timeout(function(){
				$scope.form=form;
			})
		});
	})
