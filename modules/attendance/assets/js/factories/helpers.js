/**
 * Created by Ákos on 2014.12.11..
 */

(function () {
   "use strict";

   angular.module('attendance')
      .factory('helpers', helpers);

   function helpers() {

      var _DAY_NAMES = ['vasárnap', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat'];

      var MONTH_NAMES = ["Január", "Február", "Március", "Április", "Május", "Június",
         "Július", "Augusztus", "Szeptember", "Október", "November", "December"];

      var _getYear = function () {
         var date = new Date();
         return date.getFullYear();
      };

      var _getMonth = function () {
         var date = new Date();
         return date.getMonth() + 1;
      };

      var _getMonthName = function (index) {
         return MONTH_NAMES[index - 1];
      };

      var _isCurrentDay = function (d) {
         var date = new Date(d).setHours(0, 0, 0, 0);
         var now = new Date().setHours(0, 0, 0, 0);
         return date === now;
      };

      var _isCurrentMonth = function (d) {
         var date = new Date(d);
         var now = new Date();
         return date.getMonth() === now.getMonth();
      };

      var _isAfterCurrentMonth = function (d) {
         var date = new Date(d);
         var now = new Date();

         return date.getFullYear() > now.getFullYear() || (date.getFullYear() === now.getFullYear() && date.getMonth() > now.getMonth());
      };

      var _completeEmptyDays = function (d, year, month) {

         d = _.filter(d, function (item) {
            var date = new Date(item.date);
            return date.getFullYear() === year && date.getMonth() + 1 === month;
         });

         var daysInMonth = new Date(year, month, 0).getDate();

         for (var i = 1; i <= daysInMonth; i++) {
            var currentDate = year + "-" + (month < 10 ? '0' : '') + month + "-" + (i < 10 ? "0" : "") + i;

            var found = _.find(d, function (item) {
               return item.date === currentDate;
            });

            var workDay = !_isWeekend(currentDate);

            if (found === undefined) {
               d.push({
                  date       : currentDate,
                  from       : null,
                  to         : null,
                  workDay    : workDay,
                  userWorkDay: workDay
               });
            } else {
               if (found.workDay === undefined) {
                  found.workDay = workDay;
               }
               if (found.userWorkDay === undefined) {
                  found.userWorkDay = workDay;
               }

            }
         }

         d = _.sortBy(d, function (item) {
            return item.date;
         });


         return d;
      };


      var _isWeekend = function (date) {
         var day = new Date(date).getDay();
         return day === 0 || day === 6;
      };

      return {
         DAY_NAMES          : _DAY_NAMES,
         getYear            : _getYear,
         getMonth           : _getMonth,
         isCurrentDay       : _isCurrentDay,
         isCurrentMonth     : _isCurrentMonth,
         isAfterCurrentMonth: _isAfterCurrentMonth,
         completeEmptyDays  : _completeEmptyDays,
         getMonthName       : _getMonthName,
         isWeekend          : _isWeekend
      };
   }

})();