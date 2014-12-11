/**
 * Created by Ákos on 2014.11.26..
 */

/*global attendanceModule:true */

(function () {
   "use strict";

   angular.module('attendance')
      .filter('justDay', justDay);

   function justDay(helpers) {
      return function (input) {
         var date = new Date(input);

         return date.getDate() + ". " + helpers.DAY_NAMES[date.getDay()];
      };
   }
})();