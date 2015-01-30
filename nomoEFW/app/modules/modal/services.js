'use strict';

angular.module('nomoEFW.modal')
.factory('nomoModalFactory', function($modal,$rootScope) {
    var alert=function(message){
        var alertScope = $rootScope.$new(true);
        alertScope.message = message;
        alertScope.close = function(){
            alertScope.modalInstance.close(alertScope);
        };
        alertScope.modalInstance = $modal.open({
            templateUrl: '/nomoEFW/app/modules/modal/alert.html',
            scope:alertScope,
            size: 'sm'
        });
    }
    return {
        alert:alert
    }
})
