/**
 * Created by Ákos on 2014.12.11..
 */

(function () {
   "use strict";

   angular.module('attendance')
      .directive('focusTime', focusTime);


   function focusTime($timeout) {
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
   }

})();