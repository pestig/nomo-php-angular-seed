'use strict';

angular.module('nomoEFW.admin_menu')
	.controller('titleCtrl', function ($scope, $routeParams, $location, nomoMenuService, nomoAPI) {
		$scope.$on('$routeChangeSuccess', function (next, current) {
			//handling title, desc, breadcrumbs and view
			$scope.view = {};
			nomoMenuService.getViewLabel().then(function (title) {
				$scope.view.title = title;
			})
			nomoMenuService.getBreadCrumbs(nomoAPI.getTypeByPath(), nomoAPI.getRowidByPath()).then(function (breadCrumbs) {
				$scope.view.breadCrumbs = breadCrumbs;
			});
		});
	})
	.controller('nomoMenuViewCtrl', function ($scope, $routeParams, $location, nomoMenuService) {
		$scope.menuItems = [];

		nomoMenuService.getTree().then(function (tree) {
			var branch = null;
			if ($routeParams.id) {
				for (var i = 0; i < tree.length; i++) {
					if (tree[i].rowid == $routeParams.id) {
						branch = tree[i];
					}
				}
				$scope.menuItems = branch.menuitems;
			} else {
				$scope.menuItems = tree;
			}
		})
	})
	.controller('NomoSidebarMenuCtrl', function ($scope, $location, nomoMenuService) {
		//Event handlers
		$scope.$on('$routeChangeSuccess', function (current, previous) {
			$scope.setActiveMenuItems();
		});
		$scope.init = function () {
			//BEGIN populate MENU
			//console.log(nomoMenuService.getTree());
			nomoMenuService.getTree().then(
				function (result) {
					//successfull call

					$scope.menuitems = result;
					$scope.setActiveMenuItems();
				}
			)
			//END populate MENU
		}

		$scope.setActiveMenuItems = function () {
			function setActiveClassName(menuitems) {
				var hasActive = false;
				for (var i = 0; i < menuitems.length; i++) {
					var menuitem = menuitems[i];
					if (menuitem.menuitems.length > 0) {
						hasActive = setActiveClassName(menuitem.menuitems);
						if (hasActive)
							menuitem.activeClassName = "active";
						else
							menuitem.activeClassName = "";
					} else if (menuitem.outfilter && menuitem.outfilter == $location.path()) {
						menuitem.activeClassName = "active";
						hasActive = true;
					} else {
						menuitem.activeClassName = "";
					}
				}
				return hasActive;
			}
			if ($scope.menuitems) setActiveClassName($scope.menuitems);
		}

		$scope.init();
	})

