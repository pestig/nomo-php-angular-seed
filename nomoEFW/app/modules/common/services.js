'use strict';

angular.module('nomoEFW.common')
  .value('version', (typeof (VERSION) != 'undefined')?VERSION:'0.1.0')
  .run(function($rootScope,version){
    $rootScope.version=version;
  })
  .factory('nomo', function(nomoAPI) {
    return{
      api:nomoAPI.execute
    }
  })
  .factory('nomoSniff', function() {
    return{
      isMobileBrowser:function(){
        //return true;
        var agent=(navigator.userAgent || navigator.vendor || window.opera);
        if (agent.match(/Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i))
          return true;
        else
          return false;
      }
    }
  })
