'use strict';

angular.module('nomoEFW.admin_menu')
	.factory('nomoMenuService', function($http,$q,$routeParams,$rootScope,$location,nomoAPI,nomoSniff) {
		var adminMenuItems=null;
		var menuPromise=null;
		return {
      getItems: function() {
				if(adminMenuItems===null){
					if(menuPromise===null){
            menuPromise=nomoAPI.execute({
                "className":"Adminmenu",
                "method":"select",
                "params":{
                  "isMobileBrowser":nomoSniff.isMobileBrowser()
                }
            }).then(
							function (response) {
                adminMenuItems=response.data;
								for(var i=0;i<adminMenuItems.length;i++){
									if(adminMenuItems[i].outfilter=='') adminMenuItems[i].outfilter=$rootScope.basepath+'/menu/'+adminMenuItems[i].rowid;
								}
								return adminMenuItems;
							},
							function (response) {/*error*/},
							function (response) {/*notif*/}
						)
					}
					return menuPromise;
				} else {
					var deferred=$q.defer();
					deferred.resolve(adminMenuItems);
					return deferred.promise;
				}
			},
			getTree: function() {
			//for building the admin menu
					return this.getItems().then(
						function(result){
							var itemsByID = {};
							if(result){
								for(var i=0;i<result.length;i++){
									var item=result[i];
									item.menuitems=[];
									itemsByID[item.rowid] = item;
								}
								var items=[];
								for(var i=0;i<result.length;i++){
									var item=result[i];
									if(parseInt(item.parent) && itemsByID[item.parent]) {
										itemsByID[item.parent].menuitems.push(item);
									}else{
										items.push(item);
									}
								}
							}
							return items;
						})
			},
			getBreadCrumbs:function(type,id){
			//getting tree hierarchy of a leaf in array
				return this.getItems().then(
					function(items){
						var leaf=null;
						var outfilter=$rootScope.basepath+'/'+type;



						//findById
						var getParent=function(child){
							for(var i=0;i<items.length;i++){
								if(items[i].rowid==child.parent){
										return items[i];
								}
							}
							return null;
						}

						//find outfilter

						if(type=='menu'){
							for(var i=0;i<items.length;i++){
								if(items[i].rowid==id){
									leaf=items[i];
								}
							}
						} else {
							for(var i=0;i<items.length;i++){
								if(items[i].outfilter==outfilter){
									leaf=items[i];
								}
							}
						}

						//construct parenthesis
						var treePartial=[leaf];



						if(leaf!=null){
							var child=leaf;
							while(child!=null){
								child=getParent(child);
								if(child!=null) treePartial.push(child);
							}
							return treePartial.reverse();
						} else {
							return []
						}
					}
				)
			},
			getViewLabel:function(){
				//visszaadja a view címét, és leírását
				var getLabelFilter=$rootScope.basepath+'/'+nomoAPI.getTypeByPath();
				var getLabelByKey='outfilter';
				if($location.path().split("/")[2]=='menu'){
					getLabelFilter=$routeParams.id;
					getLabelByKey='rowid';
				}
				return this.getLabel(getLabelFilter,getLabelByKey).then(function(itemLabel){
					var label='';
					var description='';
					var type=$location.path().split("/")[2]
					if(type=='menu'){
						label='Navigáció';
						description=(itemLabel || '');
					}else if(type=='myprofile'){
						label='Fiók';
						description='Felhasználói profil';
					}else if($routeParams.id=='new'){
						label='Új elem létrehozása';
						description=itemLabel;
					} else if($routeParams.id){
						label=(itemLabel || type);
						description='Szerkesztés';
					} else{
						label=(itemLabel || type);
						description='Listanézet';
					}
					return {label:label, description:description}
				});
			},
			getLabel:function(filter,byKey){
		      //visszaadja azt, hogy az adminmenüben milyen név szerepel 'byKey' attribútum alatt (byKey defaultban 'outfilter')
              if(!byKey) byKey='outfilter';
				return this.getItems().then(function(menuItems){
					var label='';
					for(var i=0;i<menuItems.length;i++){
						if(menuItems[i][byKey]==filter){
							label=menuItems[i].name;
						}
					}
					return label;
				});
			}
    }
  })
