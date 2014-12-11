/**
 * Created by Ákos on 2014.12.11..
 */

(function () {
   "use strict";


   angular.module('attendance').directive('akTimeMask', akTimeMask);

   function akTimeMask($parse) {

      return {
         restrict: 'A',
         link    : function (scope, element, attrs) {
            scope.$watch(attrs.ngModel, function (v) {
               if (v && v.length > 0) {

                  var newValue = v.replace(/[^0-9:]/g, '');

                  if (newValue[1] === ':') {
                     newValue = '0' + newValue;
                  }

                  var colonPos = newValue.indexOf(':');
                  if (colonPos > 2) {
                     newValue = newValue.slice(0, colonPos - 1) + newValue.slice(colonPos);
                  }

                  newValue = newValue.substr(0, 5);
                  if (newValue[2] !== undefined && newValue[2] !== ':') {
                     newValue = newValue.slice(0, 2) + ":" + newValue.slice(2);
                  }
                  var modelGetter = $parse(attrs['ngModel']);
                  var modelSetter = modelGetter.assign;
                  modelSetter(scope, newValue);

               }
            });
         }
      };
   }


})();