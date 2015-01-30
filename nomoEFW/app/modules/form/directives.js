//'use strict';

angular.module('nomoEFW.form')
  .directive('nomoForm', function (nomoFormFactory, version, $rootScope) {
    return {
      restrict: 'A',
      controller: 'nomoFormController',
      templateUrl: function (element, attrs) {
        return (attrs.nomoFormTemplateUrl || '/nomoEFW/app/modules/form/view.html') + '?ver=' + version;
      },
      scope: {
        form: '=nomoForm',
		attrClass: '=?nomoFormClass',
		attrId: '=?nomoFormId'
      }
    }
  })

  .directive('nomoControl', function(version){
		return {
      restrict: 'A',
			transclude: true,
			template: '<span ng-if="templateUrl" ng-include="templateUrl"></span>',
      scope: {
        control: '=nomoControl',
      },
      controller: function ($scope) {
				$scope.form={}
				$scope.form.data=$scope.control.data;
				if(!$scope.templateUrl)
					$scope.templateUrl='/nomoEFW/app/modules/control_'+$scope.control.controltype+'/view.html' + '?ver=' + version;
      }

		}
	})
	.directive('nomoControlDate', function(){
     var link=function($scope,element,attrs){
       var init=function(){
        var inputElement=$('input',element).eq(0);
        if(parseInt($scope.form.data[$scope.control.name])==0){
          //if(console)console.log('date empty @'+$scope.control.sqlname);
          $scope.form.data[$scope.control.name]=0
        }
        if($scope.control.controltype=='date'){
          var defaultConfig={
            autoclose: true,
            weekStart: 1,
            format: "yyyy-mm-dd",
            viewmode: "years"
          }
          //$(inputElement).mask('9999-99-99');
          $(element).datepicker(defaultConfig);
          if($scope.form.data[$scope.control.name])
            $(element).datepicker("update", $scope.form.data[$scope.control.name])
          $(element).datepicker().on('change',function(e){
            $scope.form.data[$scope.control.name]=$(inputElement).val();
            $scope.$apply($scope.form.definition)
          })

        } else if($scope.control.controltype=='datetime'){
//         console.log($scope.form.data);
					var defaultConfig={
            date: $scope.form.data[$scope.control.name] || new Date(),
            //format: "yyyy-mm-dd hh:mm:ss",
            autoclose: true,
            weekStart: 1,
            minuteStep:5,
            showSeconds:false,
            showMeridian:false
          }
          $(inputElement).mask('9999-99-99 99:99');
          $(inputElement).datetimepicker(defaultConfig)

          if($scope.form.data[$scope.control.name])
            $(inputElement).datetimepicker("update", $scope.form.data[$scope.control.name])
          $(inputElement).datetimepicker().on('change',function(e){
            $scope.form.data[$scope.control.name]=$(inputElement).val();
            $scope.$apply($scope.form.data)
          })
          $('.date-set',element).click(function(){
            $(inputElement).focus();
          })
          $('button',element).click(function(){
            //console.log($scope.form.data[$scope.control.name],$scope.control.name);
            var d=new Date();
            var y=d.getFullYear();
            var mo=d.getMonth()+1; mo=(mo>9)?mo:'0'+mo;
            var da=d.getDate(); da=(da>9)?da:'0'+da;
            var h=d.getHours(); h=(h>9)?h:'0'+h;
            var m=d.getMinutes(); m=(m>9)?m:'0'+m;
            var now=y+"-"+mo+"-"+da+" "+h+":"+m;
            if(!!$scope.form.data[$scope.control.name]){
              bootbox.confirm('<h4>Biztosan felülírja a már beírt időpontot?</h4>Kattintson a "rendben" gombra, ha a beírt értéket a jelenlegi időpontra kaarja átírni <h4>Új időpont: '+now+'</h4>',function(result){
                  if(result) {

                    $scope.form.data[$scope.control.name]=now;
                    $(inputElement).datetimepicker("update", $scope.form.data[$scope.control.name]);
                    $scope.$apply($scope.form.data)
                  }
              })
            } else {
              $scope.form.data[$scope.control.name]=now;
              $(inputElement).datetimepicker("update", $scope.form.data[$scope.control.name])
              $scope.$apply($scope.form.data)
            }
          })

        } else if($scope.control.controltype=='month'){
		  var defaultConfig={
            date: $scope.form.data[$scope.control.name] || new Date(),
            autoclose: true,
            format: "yyyy-mm",
            viewMode: "months",
            minViewMode: "months"
          }
          //$(inputElement).mask('9999-99-99 99:99');
          $(element).datepicker(defaultConfig);
          if($scope.form.data[$scope.control.name])
            $(element).datepicker("update", $scope.form.data[$scope.control.name])
          $(element).datepicker().on('change',function(e){
            $scope.form.data[$scope.control.name]=$(inputElement).val();
            $scope.$apply($scope.form.definition)
          })
        } else if($scope.control.controltype=='time'){
          setTimeout(function(){$(inputElement).mask('00:00')},100); //különben digest közben futna le
          $('button',element).click(function(){
            //console.log($scope.form.data[$scope.control.name],$scope.control.name);
            var d=new Date();
            var h=d.getHours(); h=(h>9)?h:'0'+h;
            var m=d.getMinutes(); m=(m>9)?m:'0'+m;
            if($scope.form.data[$scope.control.name]!=''){
              bootbox.confirm('<h4>Biztosan felülírja a már beírt időpontot?</h4>Kattintson a "rendben" gombra, ha a beírt értéket a jelenlegi időpontra kaarja átírni <h4>Új időpont: '+h+':'+m+'</h4>',function(result){
                  if(result) {
                    $scope.form.data[$scope.control.name]=h+':'+m;
                    $scope.$apply($scope.form.data)
                  }
              })
            } else {
              $scope.form.data[$scope.control.name]=h+':'+m;
              $scope.$apply($scope.form.data)
            }
          })
        } else return;
      }


      init();
    }
    return {
      restrict:'A',
      link:link
    }
  })
.directive('nomoControlFile',function(nomoAPI){
    var link=function($scope,element,attrs){
      $scope.nomoInputFile={}
      $scope.nomoInputFile.deleteFile=function(){
        $scope.form.data[$scope.control.name]=0;
        $scope.nomoInputFile.file={}
        $scope.nomoInputFile.state='new'
        /*
        var updateRecord={}
        updateRecord[$scope.control.name]='';
        nomoAPI.execute({
          className:$scope.form.type,
          method:'update',
          params:{
            record:updateRecord,
            filters:[
              {field:'rowid',value:$scope.form.rowid}
            ]
          }
        }).then(
          function(success){
            return nomoAPI.execute({
              className:$scope.control.params.type_id,
              method:'delete',
              params:{
                filters:[
                  {field:'rowid',value:$scope.form.data[$scope.control.name]}
                ]
              }
            })
        },function(error){bootbox.alert('<h3>A filefeltöltés nem sikerült</h3>'+error)}
        ).then(
          function(success){
            $scope.nomoInputFile.file={}
            $scope.nomoInputFile.state='new'
          },
          function(error){bootbox.alert('<h3>Hiba a törlés közben</h3>')})
          */
      }
      var updateFileSrc=function(){
        if(!$scope.nomoInputFile.file) return;
        var isImage=$scope.nomoInputFile.file.mime.match(/image/i);
        $scope.nomoInputFile.type=(isImage)?'image':'';
        var getParams={
          className:$scope.control.params.type_id,
          method:'get',
          params:{
            rowid:$scope.form.data[$scope.control.name],
            rnd:Math.random()
          }
        }
        $scope.nomoInputFile.file.src='/api?json='+angular.toJson(getParams);

        getParams.params.size='x90', //szintaxis: '90x90', '90x', 'x90' -> dobozba, szélesség fix, magasság fix
        $scope.nomoInputFile.file.thumbsrc='/api?json='+angular.toJson(getParams);
      }

      var getFileData=function(){
         nomoAPI.execute({
          className:$scope.control.params.type_id,
          method:'select',
          params:{
            filters:[
              {field:"rowid", value:$scope.form.data[$scope.control.name]}
            ]
          }
        }).then(
          function(success){
            $scope.nomoInputFile.file=success.data[0];
            updateFileSrc();
          },
          function(error){bootbox.alert('<h3>Hiba a fájl lekérése közben</h3>'+error.data)}
        );
      }
      var getPostParams=function(){
        //console.info($scope.form.data[$scope.control.name]);
				var definition=angular.copy($scope.control);
				definition.templateUrl=null;//különben behal a PHP json_parser
        return {
          className:$scope.control.params.type_id,
          method:'create',
          params: {
            record:{
              type_id:$scope.form.type,
              type_rowid:$scope.form.rowid
            },
            definition: definition
          }
        }
      }
      var init=function(){
        var inputElement=$('input[type="file"]',element);
        //inputElement.attr('accept',$scope.control.params.attributeAccept);
        //inputElement.attr('capture',$scope.control.params.attributeCapture);
        $scope.nomoInputFile.state=($scope.form.data[$scope.control.name]!=0)?'exists':'new';

        if($scope.nomoInputFile.state)
          getFileData();
          $scope.nomoInputFile.label={
            //'browse':'Fájl kiválasztása',
            'progress':'Válasszon fájlt'
          }

        var input=$(element).find('input');
        $(element).find('input').fileupload({
            dataType: 'json',
            autoUpload: true,
            add: function (e, data) {
                data.url=encodeURI('/api?json='+angular.toJson(getPostParams()));
                data.submit();
                console.log(data);
                $('.progress',element).slideDown();
                $(input).attr('disabled', true);
                //$scope.nomoInputFile.label.browse='Feltöltés folyamatban...';
                if(console) console.info('Upload started');
            },
            error: function(jqXHR, textStatus, errorThrown){
              bootbox.alert('<h3>A filefeltöltés hibába ütközött</h3> A hiba oka: '+jqXHR.responseJSON.message);
                $(input).attr('disabled', false);
                $('.progress',element).delay(1500).slideUp();
            },
            done: function (e, data) {
                $scope.form.data[$scope.control.name] = data.jqXHR.responseJSON.data.sqlresult.insert_id;
                $scope.nomoInputFile.state='exists';
                $scope.nomoInputFile.file=data.jqXHR.responseJSON.data.record;
                //$scope.nomoInputFile.label.browse=($scope.form.data[$scope.control.name])?'Csere':'Fájl kiválasztása';
                $scope.nomoInputFile.label.progress='Complete';
                updateFileSrc();
                $(input).attr('disabled', false);
                $('.progress',element).delay(1500).slideUp();
                $scope.$apply();
                if(console) console.info('Upload finished');
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $(element).find('.progress-bar').css(
                    'width',
                    progress + '%'
                );
                $scope.nomoInputFile.label.progress=progress + '% Complete';
            }
        });
        //console.log($(element).find('input').fileupload);


      }
     init();
    }
    return {
      restrict:'A',
      link:link
    }
  })
  .directive('nomoControlSelect2',function(){
    var link=function($scope,element,attrs){
      var init=function(){
        //convert formdef to select2 data input object
        initData=[];
				if(!$scope.control.options) return;
        for(var i=0;i<$scope.control.options.length;i++){
          initData.push({
            id:$scope.control.options[i].value,
            text:$scope.control.options[i].label
          })
        }
        $(element).select2({
          placeholder:'Válasszon egy elemet a listából!',
          data: initData,
          multiple: ($scope.control.multiple==true)
        })
        .select2('val',$scope.form.data[$scope.control.name]);
        $(element).on("change", function(e){
          $scope.form.data[$scope.control.name]=e.val;
          $scope.$apply($scope.form.definition);
          //log("change "+JSON.stringify({val:e.val, added:e.added, removed:e.removed}));
        });
      }
     init();
    }
    return {
      restrict:'A',
      link:link
    }
  })
	.directive('nomoControlTextarea',function(){
    var link=function($scope,element,attrs){
      var init=function(element){
      	 $(element).autosize();
      }


     init(element);

     $scope.$watch('form.data',function(){
         $(element).trigger('autosize.resize');
     });
    }
    return {
      restrict:'A',
      link:link
    }
  })
  .directive('nomoControlUrl',function($filter){
    return {
      restrict:'A',
      link:function($scope,element,attrs){
        if($scope.control.data_source){
          var content_source='form.data.'+$scope.control.data_source;
          $scope.$watch(content_source,function(newValue,oldValue){
            newValue = newValue || '';
            oldValue = oldValue || '';
            if($filter('normalize')(oldValue) == ($scope.form.data[$scope.control.name] || ''))
              $scope.form.data[$scope.control.name]=$filter('normalize')(newValue);
          });
        }
      }
    }
  })
  .directive('nomoControlMarkdown', function ( $sce) {
		 return {
				restrict: 'A',
				link:function($scope,element,attrs){
  				var markDown=$(element).markdown({autofocus:false,savable:false});
          $scope.$watch('form.data.'+$scope.control.name,function(){
            $scope.markdown_html= $sce.trustAsHtml(markdown.toHTML($scope.form.data[$scope.control.name] || ''));
          });

          $(element).autosize();
          $scope.$watch('form.data',function(){
            $(element).trigger('autosize.resize');
          });
        }
		}
	})
  .directive('nomoControlName',function(){
			var link=function($scope,element,attrs){
        var _this=this;
			}
			return {
				restrict:'A',
				link:link
			}
		})
  .directive('nomoControlSelectAjax',function(nomoAPI){
    var link=function($scope,element,attrs){
      var init=function(){
        nomoAPI.execute({
  					className:$scope.control.params.type_id,
  					method:'select',
  					params:{
  						"resultType":$scope.control.params.resultType || 'select2',
              "numberOfRows":'*',
              "filters":$scope.control.params.filters

  					}
        }).then(
          function(success){
            $scope.control.options=success.data;
          },
          function(error){
            bootbox.alert("Az űrlap feltöltése a "+$scope.control.params.type_id+" típus értékeivel nem sikerült.<br>A hiba oka:"+error.data.message, function () {})
          }
        );
      }
      init();
    }
    return {
      restrict:'A',
      link:link,
			scope:true
    }
  })
  .directive('nomoControlSelect2Ajax',function(nomoAPI){
    var link=function($scope,element,attrs){
      var init=function(){

        $(element).select2({
          placeholder:($scope.control.params.emptyText || "Kérjük válasszon a listából"),

		  allowClear:($scope.control.params.allowClear!==false)?true:false,
          ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
              url: "/api/"+$scope.control.params.type_id,
              dataType: 'json',
              data: function (term, page) {
                  var filters=($scope.control.params && $scope.control.params.filters)?angular.copy($scope.control.params.filters):[];
									filters.push({
											"field":'label',
											"operator":"like",
											"value":"%"+term+"%"
                  });
                  return {
                    json: angular.toJson({
                      "className":$scope.control.params.type_id,
                      "method":$scope.control.params.method || 'select',
                      "params":{
                        "resultType":$scope.control.params.resultType || 'select2',
                        "filters":filters
                      }
                    })
                  };
              },
              results: function (resp, page) { // parse the results into the format expected by Select2.
                  // since we are using custom formatting functions we do not need to alter remote JSON data
                  select2Data=[]

									if($scope.control.params.emptyValue){
										select2Data.push({
                      id:'',
                      text:'--Üres--'
                    })
									}
                  for(var i=0;i<resp.data.length;i++){
                    select2Data.push({
                      id:resp.data[i]["rowid"],
                      text:resp.data[i]["label"]
                    })
                  }
                  return {results: select2Data};
              }
          }
        }).select2('data',$scope.initData).select2('readonly',($scope.control.readonly==true)?true:false);


        $(element).on("change", function(e){
          $scope.form.data[$scope.control.name]=e.val;
          $scope.$apply($scope.form.definition);
        });

        $scope.$watch('form.data.'+$scope.control.name,function(newValue,oldValue){
          var data=$(element).select2('data');
          if(!data || newValue!=data.id){
            if(newValue==0){
              $scope.initData=null;
              $(element).select2('data',$scope.initData)
            }else{
              nomoAPI.execute({
      					className:$scope.control.params.type_id,
      					method:'select',
      					params:{
      						"resultType":$scope.control.params.resultType || 'select2',
      						filters:[
      							{field:"rowid", value:$scope.form.data[$scope.control.name]}
      						]
      					}
              }).then(
        				function(result){
        					if(result.data.length==0)
        						$scope.initData=null;
        					else
        						$scope.initData={
        							id:result.data[0].rowid,
        							text:result.data[0].label
        						};
                  $(element).select2('data',$scope.initData)
        				}
      			);
            }
          }
        },true);

      }

      if($scope.form.data[$scope.control.name]==="0"){
        $scope.initData=null;
        init();
      }else if($scope.form.data[$scope.control.name] && $scope.form.data[$scope.control.name+"_nomoefw_label"]){
        $scope.initData={id:$scope.form.data[$scope.control.name],text:$scope.form.data[$scope.control.name+"_nomoefw_label"]};
        init();
      } else {
  			nomoAPI.execute({
					className:$scope.control.params.type_id,
					method:'select',
					params:{
						"resultType":$scope.control.params.resultType || 'select2',
						filters:[
							{field:"rowid", value:$scope.form.data[$scope.control.name]}
						]
					}
        }).then(
  				function(result){
  					if(result.data.length==0)
  						$scope.initData=null;//{id:'',text:($scope.control.params.emptyText || "Kérjük válasszon a listából")}
  					else
  						$scope.initData={
  							id:result.data[0].rowid,
  							text:result.data[0].label
  						};
  					init();
  				},
  				function(){},
  				function(){}
  			);
			}


    }
    return {
      restrict:'A',
      link:link,
			scope:true
    }
  })
	.directive('nomoControlCode',function(){
		return {
			restrict: 'A',
			link:function($scope, element, attrs){
				var code;
				var handleScopeUpdate=function(newValue, oldValue){
							if($scope.form.data[$scope.control.name] !== code.getValue())
								code.setValue($scope.form.data[$scope.control.name]);
						};
				var init=function(){
					if(angular.isUndefined(code)){
						var codeContainer=$(element).append(document.createElement('div'));
						code = CodeMirror($(codeContainer).get(0),{
							lineNumbers: true,
							matchBrackets: true,
							mode: "htmlmixed"
						});
						if(!$scope.form.data[$scope.control.name]) $scope.form.data[$scope.control.name]='';
						handleScopeUpdate($scope.form.data[$scope.control.name]);
						code.on("change", function(cm, change) {
							$scope.form.data[$scope.control.name]=code.getValue();
							if (!$scope.$$phase) {
								$scope.$apply();
							}
						});
						//codemirror container
						$scope.$watch('formItem.value',handleScopeUpdate);
					}
				}
				init();
			}
    }
	})
  .directive('nomoInputMask',function(){
      var link=function($scope,element,attrs){
          if(attrs.nomoInputMask){
            setTimeout(function(){$(element).mask(attrs.nomoInputMask);},200);
          }
      }
      return {
          restrict:'A',
          link:link
      }
  })

