'use strict';

/* Controllers */
angular.module('nomoEFW.common')
	.controller('NomoMainCtrl', function ($scope, $rootScope, $window, nomoAPI,version) {
	  //$rootScope.version=version;
		$scope.logmeout = function () {
			//console.log('logout click');
			nomoAPI.execute({
				className: "User",
				method: 'logmeout'
			}).then(
				function (resp) { /*success*/
					//console.log(resp);
					//$window.location.href="/login";
					$window.location.reload()
				},
				function () { /*error*/
					bootbox.alert("A kiléptetés nem sikerült.", function () {});
				},
				function () { /*notify*/ }
			)
		}

		var init = function () {
			nomoAPI.getUserData().then(
				function (response) { /*success*/
					$scope.userdata = response;
				}
			)
			if (!$rootScope.basepath) $rootScope.basepath = "/home";
		}
		init();



	})




