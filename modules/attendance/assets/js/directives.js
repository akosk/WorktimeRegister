/**
 * Created by Ákos on 2014.11.26..
 */
/*global attendanceModule:true */

(function () {
   "use strict";
   attendanceModule.directive('emptyToNull', function () {
      return {
         restrict: 'A',
         require : 'ngModel',
         link    : function (scope, elm, attrs, ctrl) {
            ctrl.$parsers.push(function (viewValue) {
               if (viewValue === "") {
                  return null;
               }
               return viewValue;
            });
         }
      };
   });

   attendanceModule.directive('focusTime', function ($timeout) {
      return {
         restrict: 'A',
         link    : function (scope, element, attr) {
            if (scope.$last === true) {
               $timeout(function () {
                  $(".focused").focus();
               });
            }
         }
      };
   });

   attendanceModule.directive('akTimeMask', function ($parse) {
      return {
         restrict: 'A',
         link    : function (scope, element, attrs) {
            scope.$watch(attrs.ngModel, function (v) {
               if (v && v.length>0) {

                  var newValue = v.replace(/[^0-9:]/g, '').substr(0,5);
                  if (newValue[2]!==undefined && newValue[2]!==':') {
                     newValue=newValue.slice(0, 2) + ":" + newValue.slice(2);
                  }
                  var modelGetter = $parse(attrs['ngModel']);
                  var modelSetter = modelGetter.assign;
                  modelSetter(scope, newValue);

               }
            });
         }
      };
   });
})();