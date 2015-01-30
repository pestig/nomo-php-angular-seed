'use strict';

angular.module('nomoEFW.form')
  .factory('nomoFormFactory', function ($location, $route, $rootScope, $window, nomoAPI, nomoSniff, $routeParams, $q, version) {
    var _this = this;
    return {
      getFieldByName: function (name, fields) {
        for (var i = 0; i < fields.length; i++) {
          if (fields[i].name == name)
						return fields[i];
        }
        return false;
      },
		get: function (form) {
			var _this = this;
			return _this.init(form).then(function(form){
				return nomoAPI.getUserData().then(
					function (success) {
						form.userdata = success
						return _this.load(form);
					},
					function (error) {
						bootbox.alert('A useradatok lekérése nem sikerült');
						return null;
					}
				)
			});
		},
      load: function (form) {
        var _this = this;
        _this.form=form;
        form.definition = form.definition || {'fields':[]};
        var queryParams=(form.queryParams)?form.queryParams:$location.search()['params'];

				return _this.getData(form)
            .then(function (data) {
              form.data = data;
              var urlQueryPresets = angular.fromJson(queryParams);
              if (urlQueryPresets) {
                for (var i = 0; i < urlQueryPresets.length; i++) {
                  form.data[urlQueryPresets[i].field] = urlQueryPresets[i].value;
                }
              }
              return _this.getDefinition(form);
            })
						.then(function (response) {
              var isMobile = nomoSniff.isMobileBrowser();
              for (var i = 0; i < response.fields.length; i++) {
                if (isMobile && response.fields[i].controltype == 'select2') {
                  response.fields[i].controltype = 'select';
                } else if (isMobile && response.fields[i].controltype == 'select2ajax') {
                  response.fields[i].controltype = 'selectajax';
                }
              }

							if (form.state == 'new') {

								//Default értékek beállítása
								for(var i=0;i<response.fields.length;i++){
									if(typeof response.fields[i]["default"] !== 'undefined'){
										form.data[response.fields[i]["name"]]=response.fields[i]["default"];
									}
								}

								//Draft elem létrehozása
								if (_this.getFieldByName('nomocms_status',response.fields) !== false) {
									var createRecord = angular.copy(_this.form.data);
									createRecord.nomocms_status = 'draft';
									return nomoAPI.execute({
										className: _this.form.type,
										method: 'create',
										params:{
											record: createRecord
										}
									}).then(
										function (success) {
											$location.search( 'params', null);
											$location.path('/home/'+_this.form.type+'/'+success.data.sqlresult.insert_id);
											$location.replace();
											return null;
										},
										function (error) {
											bootbox.alert(error.data.message)
										}
									);
								}

							}

					    for(var i=0;i<response.fields.length;i++){
								response.fields[i]["data"]=form.data;

								if(!response.fields[i]["formTemplateUrl"]){
									//response.fields[i]["controltype"]='text';
									response.fields[i]["formTemplateUrl"]="/nomoEFW/app/modules/control_"+response.fields[i]["controltype"]+"/view.html?ver="+version;
								}
							}


              form.definition = angular.copy(response);
							return form;
            })
      },
      getData: function (form) {
				if(form.state == 'new'){
					var deferred=$q.defer();
					deferred.resolve(form.data);
					return deferred.promise;
				}else{
					return nomoAPI.execute({
						className: form.type,
						method: form.method,
						params: {
							resultType: form.resultType || "form",
							filters: [{
								field: "rowid",
								value: form.rowid
							}]
						}
					}).then(function(success){
						var deferred=$q.defer();
						deferred.resolve(success.data[0]);
						return deferred.promise;
					});
				}
      },
      getDefinition: function (form) {
        return nomoAPI.getDefinition({
          "name": form.type,
          "resultType": form.resultType
        })
      },
     	init: function (form) {
        var _this = this;

				form = angular.isDefined(form)?form:{};
       	form.type=form.type || nomoAPI.getTypeByPath();
        form.rowid=form.rowid || nomoAPI.getRowidByPath();
        form.method=form.method || 'select';
        form.state=(form.rowid == 'new') ? 'new' : 'exists';
        form.resultType=form.resultType || '';
        form.template='/nomoEFW/app/partials/form/form.partial.html' + '?ver=' + version;
        form.definition={'fields':[]};
        form.data= form.data || {};
        if ($routeParams.record) {
          form.data = angular.fromJson($routeParams.record);
        }

        var default_actions = {
          create: function (params) {
            var _this = this;
            params = params || {};
            params.form = form;
            return nomoAPI.execute({
              className: (params.type || params.form.type),
              method: 'create',
              params: {
                record: (params.data || params.form.data)
              }
            }).then(
              function (result) { /*success*/
                if (params.notify) {
                  var url = nomoAPI.getBasePath() + '/' + params.form.type + '/' + result.data.sqlresult.insert_id;
                  $.gritter.add({
                    title: 'Sikeresen elmentve!',
                    text: 'A <a href="' + url + '">' + url + '</a> elem sikeresen frissítve lett.',
                    image: './assets/img/avatar1.jpg',
                    sticky: false,
                    time: 3000
                  });
                }

                if (params.back) {
                  $window.history.back();
                } else if (params.edit) {
                  $location.path(nomoAPI.getBasePath() + '/' + form.type + '/' + result.data.sqlresult.insert_id);
                  $location.replace();
                } else if (params.new_item) {
                  //$location.reload() ;
                  $route.reload();
                  //$location.replace();
                }
              },
              function (result) {
                bootbox.alert("Az űrlap létrehozásába hiba csúszott.<br>" + result.data.message, function () {});
              }
            )
          },
          update: function (params) {
            var _this = this;
            params = params || {};
            params.form = form;
            return nomoAPI.execute({
              className: params.form.type,
              method: params.method || 'update',
              params: {
                record: params.form.data,
                filters: [{
                  field: "rowid",
                  value: form.rowid
                }]
              }
            }).then(
              function () {
                if (params.notify) {
                  var url = nomoAPI.getBasePath() + '/' + params.form.type + '/' + params.form.rowid;
                  $.gritter.add({
                    title: 'Sikeresen elmentve!',
                    text: 'A <a href="' + url + '">' + url + '</a> elem sikeresen frissítve lett.',
                    image: './assets/img/avatar1.jpg',
                    sticky: false,
                    time: 3000
                  });
                }

                if (params.back) {
                  $window.history.back();
                } else if (params.new_item) {
                  $location.path(nomoAPI.getBasePath() + '/' + form.type + '/new');
                  $location.replace();
                }
              },
              function (response) {
                bootbox.alert("Az űrlap mentésébe hiba csúszott.<br><br><strong>A hiba oka:</strong><br>" + response.data.message)
              }
            );
          },
          remove: function (params) {
            var _this = this;
            params = params || {};
            params.form = form;
            if (!params.noConfirm) {
              bootbox.confirm("Biztosan törölni akarja?", function (result) {
                if (result) {
                  params.noConfirm = true;
                  _this.remove(params);
                }
              });
            } else {
              return nomoAPI.execute({
                className: params.type || params.form.type,
                method: params.method || 'delete',
                params: {
                  filters: [
                    {
                      field: 'rowid',
                      value: params.form.data["rowid"]
                    }
                      ]
                }
              }).then(
                function (result) {
                  if (params.back) {
                    $window.history.back();
                  }
                },
                function (result) {
                  bootbox.alert("Az törlésbe hiba csúszott.<br>" + result.data.message, function () {})
                }
              )
            }
          },
          cancel: function (params) {
            var _this = this;
            params = params || {};
            params.form = form;

            var isDirty = false;
            if (params.checkDirty && isDirty) {
              bootbox.confirm("<h4>Az űrlap módosult, de nem lett elmentve.</h4>Amennyiben folytatja, a változások elvesznek.", function (result) {
                if (result) {
                  params.checkDirty = false;
                  _this.cancel(params);
                } else {

                }
              });
            } else if (params.back) {
              $window.history.back();
            }
          }
        };


        if (form.state == "new")
          form.buttons = (angular.isDefined(form.buttons))?form.buttons:angular.copy(_this.default_buttons_new);
        else
          form.buttons = (angular.isDefined(form.buttons))?form.buttons:angular.copy(_this.default_buttons_exists);
        form.actions = angular.copy(default_actions);

				var deferred=$q.defer();
				deferred.resolve(form);
				return deferred.promise;
      },
			default_buttons_exists: [
        {
          label: 'Mentés',
          action: 'update',
          params: {
            back: true,
            notify: true
          },
          className: 'blue'
        },
        {
          label: 'Mentés és tovább szerkesztés',
          action: 'update',
          params: {
              notify: true
          },
          className: 'blue'
        },
        {
          label: 'Mentés és új felvétele',
          action: 'update',
          params: {
              new_item: true,
              notify: true
          },
          className: 'blue'
        },
        {
          action: 'remove',
          icon: 'fa fa-times',
          label: 'Törlés',
          className: 'red',
          params: {
            back: true
          }

       }, {
          action: 'cancel',
          label: 'Mégse',
          params: {
            back: true
          }
       }
     ],
     default_buttons_new: [
        {
          label: 'Létrehozás',
          action: 'create',
          params: {
            back: true,
            notify: true
          },
          className: 'blue'
                },
        {
          label: 'Létrehozás és tovább szerkesztés ',
          action: 'create',
          params: {
            edit: true,
            notify: true
          },
          className: 'blue'
        },
        {
          label: 'Létrehozás és új felvétele ',
          action: 'create',
          params: {
            new_item: true,
            notify: true
          },
          className: 'blue'
        },
				{
          action: 'cancel',
          label: 'Mégse',
          params: {
            back: true
          }
        }
      ]
    }
  })
