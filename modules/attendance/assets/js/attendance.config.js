/**
 * Created by Ákos on 2014.12.11..
 */

(function () {
   "use strict";

   angular.module('attendance')
      .config(attendanceConfig);

   function attendanceConfig($routeProvider) {
      var $template = $('#attendance-template').html();

      $routeProvider.when("/", {
         controller: "AttendanceController",
         template  : $template
      });
      $routeProvider.when("/year/:year/month/:month", {
         controller: "AttendanceController",
         template  : $template
      });

      $routeProvider.otherwise("/");
   }

})();