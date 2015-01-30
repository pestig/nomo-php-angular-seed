'use strict';

angular.module('nomoEFW.common')
  .factory('nomoAPI', function($http,$exceptionHandler,$q,$location,$rootScope) {
    var definitions={};
    var userdata=null;
    var userdataPromise=null;

    var http=function(config){
        return $http(config).then(
          function(result){
            return result
          }
        )

    };

    return {
      execute:function(options){
        return http({
          url: '/api',
          method:'POST',
          data: "json="+encodeURIComponent(JSON.stringify(options)),
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(
          function(result){
            return result.data
          },function(error){
              var deferred = $q.defer();
              deferred.reject(error.data);
              return deferred.promise;
          }
        )
      },
        http:http,
		getDateStringFromDateObject:function(date){
			var nulledMonth=((date.getMonth()+1)<10)?'0'+(date.getMonth()+1):(date.getMonth()+1);
			var nulledDate=((date.getDate())<10)?'0'+(date.getDate()):(date.getDate());
			return date.getFullYear()+'-'+nulledMonth+'-'+nulledDate;
		},
		getBasePath:function(){
			return $location.path().split('/')[1];
		},
		getTypeByPath:function(){
			return $location.path().split('/')[2];
		},
		getRowidByPath:function(){
			return $location.path().split('/')[3];
		},
      isMemberOfGroup: function(gid){
        return this.getUserData().then(function(success){
            var usergid=success.session.groupid;
            return ((usergid^gid)!=(usergid+gid));;
        })
      },
			getUserData: function(){
				if(userdata === null){
					if(userdataPromise===null){
						userdataPromise=this.execute({
							className: "Session",
							method: 'get_current_session'
						}).then(
							function (resp) { /*success*/
								userdata=resp.data.user;
                                resp.data.user=null;
                                userdata.session=resp.data;

								return resp.data;
							},
							function () { /*error*/
								bootbox.alert("A sessionadatok lekérése nem sikerült.", function () {});
							},
							function () { /*notify*/ }
						)
					}
					return userdataPromise;
				} else {
					var deferred = $q.defer();
          deferred.resolve(userdata);
          return deferred.promise;
				}
			},
      getDefinition: function(options){
        if(definitions[options.name]){
          var deferred = $q.defer();
          definitions[options.name].cached="true";
          deferred.resolve(definitions[options.name]);
          return deferred.promise;
        } else {
          return this.execute({
              "className":options.name,
              "method":"getDefinition"
          }).then(function(result){
            if(result.ret==0){
              definitions[options.name]=result.data;
            }else {
              throw('>>'+result.message+'<< üres típusdefinicíó: nincs xml?');
            }
            if(definitions[options.name])
              return definitions[options.name];
            else
              throw('>>'+options.name+'<< üres típusdefinicíó: nincs xml?');
          },
          function(resp){/*error*/
                bootbox.alert("A művelet hibára futott.<br><br>A hiba oka:<br>"+resp.message);
            },
          function(){/*notify*/})
        }
      }
    }
  })
