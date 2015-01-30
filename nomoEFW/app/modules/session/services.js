'use strict';

angular.module('nomoEFW.session')
    .factory('nomoSession',['$rootScope','$q','nomo', function($rootScope,$q,nomo) {
        var currentSession=null;
		var getCurrentSession=function(data){
			return nomo.api({
                className: "Session",
                method: 'get_current_session'
            }).then(
                function (result) { /*success*/
                    currentSession=result.data;
                    return currentSession;
                },
                function (error) { /*error*/
                    alert(error.message);
                    return
                }
            )
		}
        var get = function(){
            return currentSession;
        }

        var login = function(params){
            return nomo.api({
				"className":"User",
				"method":"logmein",
				"params":{
					"loginid":params.userid,
					"password":params.password
				}
			}).then(function(result){
				currentSession=result.data;
                return result.data;
			})
        }

        var logout = function(){
            return nomo.api({
				"className":"User",
				"method":"logmeout"
			}).then(function(result){
				currentSession=result.data;
			})
        }

		getCurrentSession();

		return {
			getCurrentSession:getCurrentSession,
            get:get,
            login:login,
            logout:logout
		}
	}])
