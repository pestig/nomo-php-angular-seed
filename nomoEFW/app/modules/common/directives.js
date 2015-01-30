'use strict';

angular.module('nomoEFW.common')
	.directive('nomoTabs', function(){
		var link=function($scope,element,attrs){
			var init=function(){
				$('.nomo-tabs-tablist li a',element).each(function(index){
					$(this).data('tabid',index);
					$(this).click(function (e) {
						e.preventDefault()
						$('.nomo-tabs-tablist li',element).removeClass('active')
						$(this).parent().addClass('active')
						$('.nomo-tabs-content .tab-pane',element).hide()
						$('.nomo-tabs-content .tab-pane',element).eq($(this).data('tabid')).show()
					})
				})
			}
			init();
		}
		return {
			restrict:'A',
			link:link
		}
	})
	.directive("autofill", function () {
			return {
					require: "ngModel",
					link: function (scope, element, attrs, ngModel) {
							scope.$on("autofill:update", function() {
									ngModel.$setViewValue(element.val());
							});
					}
			}
	})
