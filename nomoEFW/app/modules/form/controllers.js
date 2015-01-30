'use strict';

angular.module('nomoEFW.form')
	.controller('nomoFormController', function ($scope,$timeout, nomoFormFactory, nomoAPI) {
		nomoFormFactory.setFormScopeByAttributes($scope);

		nomoFormFactory.get($scope.form).then(function(form){
			$timeout(function(){
				$scope.form=form;
			})
		});
	})
