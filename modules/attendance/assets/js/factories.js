/**
 * Created by Ákos on 2014.11.26..
 */

/*jshint loopfunc: true */
/*global attendanceModule:true */
/*global BASE_URL:true */

(function () {
   "use strict";

   if (BASE_URL === undefined) {
      BASE_URL = '/';
   }

   attendanceModule.factory('helpers', function () {

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
   });

   attendanceModule.factory('dataService', function ($http, $q, helpers) {
      var _attendances = [];

      var _absenceTypes = [
         {id: '1', code: '25004', label: 'TERHESSÉGI GYERMEKÁGYI SEGÉLY'},
         {id: '2', code: '25005', label: 'GYERMEKGONDOZÁSI DÍJ'},
         {id: '3', code: '25008', label: 'TÁPPÉNZ, EGYÉB KERESŐKÉPTELENSÉG'},
         {id: '4', code: '26002', label: 'APÁKAT MEGILLETŐ MUNKAIDŐKEDVEZMÉNY'},
         {id: '5', code: '91001', label: 'RENDES SZABADSÁG'},
         {id: '6', code: '91003', label: 'TANULMÁNYI SZABADSÁG ILLETMÉNNYEL'},
         {id: '7', code: '91004', label: 'FIZETÉS NÉLKÜLI SZABADSÁG'},
         {id: '8', code: '91009', label: 'GYERMEKGONDOZÁSI SEGÉLY'},
         {id: '9', code: '91011', label: 'RENDKÍVÜLI SZABADSÁG'},
         {id: '10', code: '93001', label: 'IGAZOLATLAN TÁVOLLÉT'},
         {id: '11', code: '93009', label: 'FELMENTÉSI IDŐ'},
         {id: '12', code: '93026', label: 'IGAZOLT TÁVOLLÉT'}
      ];


      var _getAbsenceTypes = function () {
         return _absenceTypes;
      };

      var _convertAbsences = function (_temp) {
         _.each(_temp, function (element, index, list) {

            if (element.userAbsenceCode !== undefined) {
               var absenceType = _.find(_absenceTypes, function (item) {
                  return item.code === element.userAbsenceCode;
               });

               if (absenceType !== undefined) {
                  element.userAbsence = absenceType;
               }
            }
         });
      };

      var _getAttendances = function (year, month) {
         var deferred = $q.defer();
         $http({
            url   : BASE_URL + 'attendance/default/get-attendances',
            method: "GET",
            params: {year: year, month: month}
         })
            .then(function (result) {
               var _temp = result.data.attendances === undefined ? [] : result.data.attendances;

               _temp = helpers.completeEmptyDays(_temp, year, month);

               _convertAbsences(_temp);

               angular.copy(_temp, _attendances);
               deferred.resolve();
            }, function () {
               deferred.reject();
            });
         return deferred.promise;
      };

      var _saveAttendances = function () {
         var csrfToken = $('meta[name="csrf-token"]').attr("content");
         var deferred = $q.defer();

         var data = [];
         angular.copy(_attendances, data);

         data = _.map(data, function (item) {
            if (item.absence !== undefined) {
               item.absence = item.absence.id;
            }
            return item;
         });
         $http({
            url   : BASE_URL + 'attendance/default/save-attendances',
            method: "POST",
            data  : data
         })
            .then(function (result) {
               deferred.resolve();
            }, function () {
               deferred.reject();
            });
         return deferred.promise;
      };

      var _setRedLetterDay = function (date, type) {
         var deferred = $q.defer();

         var d = {
            date: date
         };

         var isHolidayOnWeekend = (type === "HOLIDAY" && helpers.isWeekend(date));
         var isWorkingDayOnWeekday = (type === "WORKING_DAY" && !helpers.isWeekend(date));
         if (isHolidayOnWeekend || isWorkingDayOnWeekday) {
            d.delete = true;
         } else {
            d.type = type;
         }

         $http({
            url   : BASE_URL + 'attendance/default/set-red-letter-day',
            method: "POST",
            data  : d
         })
            .then(function (result) {
               deferred.resolve();
            }, function () {
               deferred.reject();
            });
         return deferred.promise;

      };


      var _setAbsence = function (date, code) {
         var deferred = $q.defer();

         var d = {
            date: date,
            code: code
         };


         $http({
            url   : BASE_URL + 'attendance/default/set-absence',
            method: "POST",
            data  : d
         })
            .then(function (result) {
               deferred.resolve();
            }, function () {
               deferred.reject();
            });
         return deferred.promise;

      };

      var _removeAbsence = function (date) {
         var deferred = $q.defer();

         var d = {
            date: date,
         };

         $http({
            url   : BASE_URL + 'attendance/default/remove-absence',
            method: "POST",
            data  : d
         })
            .then(function (result) {
               deferred.resolve();
            }, function () {
               deferred.reject();
            });
         return deferred.promise;

      };

      return {
         getAttendances : _getAttendances,
         attendances    : _attendances,
         saveAttendances: _saveAttendances,
         getAbsenceTypes: _getAbsenceTypes,
         absenceTypes   : _absenceTypes,
         setRedLetterDay: _setRedLetterDay,
         setAbsence     : _setAbsence,
         removeAbsence  : _removeAbsence
      };
   });
})();