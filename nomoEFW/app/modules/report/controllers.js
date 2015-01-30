angular.module('nomoEFW.report')
  .controller('NomoReportCtrl', function ($scope,$location,nomoAPI,version) {
		$scope.version=version;
    if(!$scope.view) $scope.view={};
    if(!$scope.view.type){
      var route=$location.path().substring(nomoAPI.getBasePath().length, $location.path().length)
      $scope.view.type=route.split('/')[2];
    }





	  nomoAPI.execute({
			"className":$scope.view.type,
			"method":"getReportDefinition"
		}).then(function(success){
			$scope.view.xaxis_options=success.data.xaxis_options; $scope.view.xaxis=$scope.view.xaxis_options[0];
			$scope.view.yaxis_options=success.data.yaxis_options;	$scope.view.yaxis=$scope.view.yaxis_options[0];
			$scope.view.interval_options=success.data.interval_options;	$scope.view.interval=$scope.view.interval_options[0];
			$scope.view.from='2014-01-01';
  		$scope.view.to='2014-12-01';

      //$location.search('r', nomoGridService.filterToSearchParam(outfilter));
			var filterParam=$location.search().r;
			if(filterParam){
				var filter=angular.fromJson(filterParam);

				for(var i=0;i<$scope.view.xaxis_options.length;i++){
				  if($scope.view.xaxis_options[i].name==filter.xaxis)
						$scope.view.xaxis=$scope.view.xaxis_options[i];
				}

				for(var i=0;i<$scope.view.yaxis_options.length;i++){
				  if($scope.view.yaxis_options[i].name==filter.yaxis)
						$scope.view.yaxis=$scope.view.yaxis_options[i];
				}

				for(var i=0;i<$scope.view.interval_options.length;i++){
				  if($scope.view.interval_options[i].name==filter.interval)
						$scope.view.interval=$scope.view.interval_options[i];
				}

				if(filter.from) $scope.view.from=filter.from;
				if(filter.to) $scope.view.to=filter.to;
			}

			//$scope.view.interval=$scope.view.xaxis.interval_options[0];

			$scope.advanced_filter={
		  	"type":$scope.view.type
			}

			return getResults();
		})


		function getResults(){
			//console.log($scope.advanced_filter.filters);
			return nomoAPI.execute({
				"className":$scope.view.type,
				"method":"getReport",
				"params":{
				  xaxis:$scope.view.xaxis.name,
					yaxis:$scope.view.yaxis.name,
					interval:$scope.view.interval.name,
					from:$scope.view.from,
					to:$scope.view.to,
					filters:$scope.advanced_filter.outfilter
				}
			}).then(function(success){
				$(function () {

					var from=$scope.view.from.split('-');
					var min=(new Date(parseInt(from[0]),parseInt(from[1])-1,parseInt(from[2]))).getTime();
					var to=$scope.view.to.split('-');
					var max=(new Date(parseInt(to[0]),parseInt(to[1])-1,parseInt(to[2]))).getTime();

					var series = [];
					var table_rows =[]
					for (var i = 0; i < success.data.rows.length; i ++) {
						var item=success.data.rows[i];
						series.push([item.xaxis, item.yaxis]);

						if($scope.view.interval.name=="month" || $scope.view.interval.name=="day"){
							var date=new Date(parseInt(item.xaxis));

							var year = date.getFullYear();
							// minutes part from the timestamp
							var month = "0" + (date.getMonth()+1);
							// seconds part from the timestamp
							var day = "0" + date.getDate();
							if($scope.view.interval.name=="month")
								var date_string= year + '-' + month.substr(month.length-2);
							else
								var date_string= year + '-' + month.substr(month.length-2) + '-' + day.substr(day.length-2);
							table_rows.push([date_string,item.yaxis])
						}else{
							table_rows.push([item.xaxis, item.yaxis]);
						}
					}
					$scope.table_rows=table_rows;

					if($scope.view.interval.name=="month"){
						var plotConfig={
							series: {
								lines: { show: true },
								points: { show: true }
							},
							xaxis: {
								mode:"time",
								minTickSize: [1, "month"],
								min: min,
								max: max
							}
						}
					}else if($scope.view.interval.name=="day"){
						var plotConfig={
							series: {
								lines: { show: true },
								points: { show: true }
							},
							xaxis: {
								mode: "time",
								tickLength: [1, "day"],
								min: min,
								max: max
							}
						}
					}else{
						var plotConfig={
							series: {
								lines: { show: true },
								points: { show: true }
							},
							xaxis: {
								mode:"categories",
							}
						}
					}

					$.plot("#placeholder", [
						{ label: $scope.view.yaxis.label, data: series }
					], plotConfig);
					//$('#report_table').datatable();

				});

			},function(error){
				console.log(error);
				bootbox.alert(error.data.message);
			})
		}

    $scope.showReportClick=function(){
			$scope.advanced_filter.setOutFilter()

			var filter={
				xaxis:$scope.view.xaxis.name,
				yaxis:$scope.view.yaxis.name,
				interval:$scope.view.interval.name,
				from:$scope.view.from,
				to:$scope.view.to
			}
			$location.search('r', angular.toJson(filter));

			getResults();
		}

  })
