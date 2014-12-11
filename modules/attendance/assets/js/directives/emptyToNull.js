/**
 * Created by Ákos on 2014.12.11..
 */

(function () {
   "use strict";

   angular.module('attendance')
      .directive('emptyToNull', emptyToNull);


   function emptyToNull() {
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
   }

})();