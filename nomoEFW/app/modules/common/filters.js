'use strict';

/* Filters */

angular.module('nomoEFW.common')
.filter('interpolate', ['version', function(version) {
    return function(text) {
      return String(text).replace(/\%VERSION\%/mg, version);
   	}
 }])
.filter('weekOfYear', function() {
  return function(date) {
      var dateSplit = date.split('-');
		var jan1= new Date(dateSplit[0],0,1);
		var d=new Date(date);
		var dayDiff = (d - jan1) / 86400000;
		return (1 + Math.ceil(dayDiff / 7));
  };
})
.filter('dayString', function() {
  return function(date) {
			var dayOfWeek=(new Date(date).getDay()+6)%7;
			var daysString=new Array('h','k','s','c','p','s','v');
			return daysString[dayOfWeek]
  };
})
.filter('normalize', function() {
  return function(text) {
      text = text || '';
      text = text.toString();
			return text
        .toLowerCase()
        .replace(/ /g,'-')
        .replace(/[á]/g,'a').replace(/[é]/g,'e').replace(/[í]/g,'i')
        .replace(/[ó]/g,'o').replace(/[ö]/g,'o').replace(/[ő]/g,'o')
        .replace(/[ú]/g,'u').replace(/[ü]/g,'u').replace(/[ű]/g,'u')
        .replace(/[^\w-]+/g,'')
        ;
  };
})
.filter('byte', function() {
	return function(bytes, precision) {
		if (isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '-';
		if (typeof precision === 'undefined') precision = 1;
		var units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB'],
			number = Math.floor(Math.log(bytes) / Math.log(1024));
		return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) +  ' ' + units[number];
	}
});
