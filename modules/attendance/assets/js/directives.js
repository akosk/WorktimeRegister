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
})();