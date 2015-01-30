angular.module('nomoEFW.calendar')
.factory('nomoCalendarService', function($location,$route,$rootScope,$window,nomoAPI,nomoSniff,$routeParams) {
  var _this=this;
  return {
    getDefaultCalendar:{
      type_id:nomoAPI.getTypeByPath(),
      rowid:nomoAPI.getRowidByPath(),
      method:'select',
      params:{},
      events:[],
      defaultFullcalendarConfig:{
        header: {
          left: 'prev,next today',
          center: 'title',
          right: ''//'month,basicWeek,basicDay'
        },
        editable: false,
        events:[]
      },
      on:{
        eventClick:function(calEvent, jsEvent, view){ console.log('eventClick!',calEvent,jsEvent,view);},
        dayClick:function(date, allDay, jsEvent, view){ console.log('dayClick!',date, allDay, jsEvent, view);},
        eventMouseOver:function( event, jsEvent, view ) { console.log('eventMouseOver!',event, jsEvent, view);},
        eventMouseOut:function( event, jsEvent, view ) { console.log('eventMouseOut!',event, jsEvent, view);}
      }
    }
  }
})
.controller('nomoCalendarController', function ($scope, $window, $location, nomoAPI, nomoCalendarService) {
  $scope.calendar=angular.copy(nomoCalendarService.getDefaultCalendar);

  $scope.calendar.getData=function(){
    return nomoAPI.execute({
      className: $scope.calendar.type_id,
      method: $scope.calendar.method,
      params: $scope.calendar.params
    })
  };
})
.directive('nomoCalendar', function (nomoAPI) {
  var link=function($scope,element,attrs){
    nomoAPI.getUserData().then(
      function(success){
        //megvan a user data
        $scope.calendar.type_id='Beosztaskezeles';
        $scope.calendar.rowid=null;

        $scope.calendar.defaultFullcalendarConfig.eventClick=$scope.calendar.on['eventClick']
        $scope.calendar.defaultFullcalendarConfig.dayClick=$scope.calendar.on['dayClick']
        $scope.calendar.defaultFullcalendarConfig.eventMouseOver=$scope.calendar.on['eventMouseOver']
        $scope.calendar.defaultFullcalendarConfig.eventMouseOut=$scope.calendar.on['eventMouseOut']

        $scope.calendar.params={
          filters:[
            {field:'tech',value:success.userid}
          ]
        };
        return $scope.calendar.getData();
      }
    ).then(
      function(success){
        var events=[];
        for(var i=0;i<success.data.length;i++){
          events.push({
            title: 'korhaz:'+success.data[i].korhaz+'; gep:'+success.data[i].gep,
            start: new Date(success.data[i].datum),
            backgroundColor: App.getLayoutColorCode('green'),
            data:success.data[i]
          });
        }
        $scope.calendar.defaultFullcalendarConfig.events=events;
        $(element).fullCalendar($scope.calendar.defaultFullcalendarConfig);
      },
      function(error){}
    )
  }
  return {
    restrict:'A',
    controller:'nomoCalendarController',
    link:link
  }
})
