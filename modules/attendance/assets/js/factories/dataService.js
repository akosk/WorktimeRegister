/**
 * Created by Ákos on 2014.12.11..
 */

/*jshint loopfunc: true */
/*global BASE_URL:true */


(function () {
   "use strict";

   if (BASE_URL === undefined) {
      BASE_URL = '/';
   }

   angular.module('attendance')
      .factory('dataService', dataService);

   function dataService($http, $q, helpers) {
      var _attendances = [];
      var _attendances_closed = false;
      var _absences_closed = false;
      var _instructorAttendance = false;
      var _absences_closed_day = null;

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
         {id: '12', code: '93026', label: 'IGAZOLT TÁVOLLÉT'},
         {id: '13', code: '93030', label: 'CSÚSZTATÁS (TÚLÓRA/TÚLMUNKA MIATT)'},
         {id: '14', code: '93031', label: 'PIHENŐNAP/SZABADNAP'},
         {id: '15', code: '93032', label: 'TOVÁBBKÉPZÉS, BELFÖLDI KIKÜLDETÉS'}
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

      var _getAttendances = function (year, month, userId) {
         var deferred = $q.defer();
         $http({
            url   : BASE_URL + 'attendance/default/get-attendances/' + userId,
            method: "GET",
            params: {year: year, month: month}
         })
            .then(function (result) {
               var _temp = result.data.attendances === undefined ? [] : result.data.attendances;
               _attendances_closed = result.data.attendances_closed;
               _absences_closed = result.data.absences_closed;
               _absences_closed_day = result.data.absences_closed_day;

               _temp = helpers.completeEmptyDays(_temp, year, month);

               _convertAbsences(_temp);

               angular.copy(_temp, _attendances);
               deferred.resolve();
            }, function () {
               deferred.reject();
            });
         return deferred.promise;
      };

      var _saveAttendances = function (userId) {
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
            url   : BASE_URL + 'attendance/default/save-attendances/' + userId,
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

      var _setCustomWorkingDay = function (date, type, userId) {
         var deferred = $q.defer();

         var d = {
            date: date,
            type: type
         };


         $http({
            url   : BASE_URL + 'attendance/default/set-custom-working-day/' + userId,
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


      var _setAbsence = function (date, code, userId) {
         var deferred = $q.defer();

         var d = {
            date: date,
            code: code
         };


         $http({
            url   : BASE_URL + 'attendance/default/set-absence/' + userId,
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

      var _removeAbsence = function (date, userId) {
         var deferred = $q.defer();

         var d = {
            date: date
         };

         $http({
            url   : BASE_URL + 'attendance/default/remove-absence/' + userId,
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

      var _setInstructorAttendance = function (year, month, value, userId) {
         var deferred = $q.defer();


         $http({
            url   : BASE_URL + 'attendance/default/set-instructor-attendance/' + userId,
            method: "GET",
            params: {
               year : year,
               month: month,
               value: value
            }
         })
            .then(function (result) {
               deferred.resolve();
            }, function () {
               deferred.reject();
            });
         return deferred.promise;


      };

      var _getInstructorAttendance = function (year, month, userId) {
         var deferred = $q.defer();


         $http({
            url   : BASE_URL + 'attendance/default/get-instructor-attendance/' + userId,
            method: "GET",
            params: {
               year : year,
               month: month
            }
         })
            .then(function (result) {
               _instructorAttendance = result.data.value;
               deferred.resolve();
            }, function () {
               deferred.reject();
            });
         return deferred.promise;


      };


      var _isAttendancesClosed = function () {
         return _attendances_closed;
      };

      var _isAbsencesClosed = function () {
         return _absences_closed;
      };

      var _getAbsencesClosedDay = function () {
         return _absences_closed_day;
      };

      var _getInstructorAttendanceValue = function () {
         return _instructorAttendance;
      };

      return {
         getAttendances              : _getAttendances,
         attendances                 : _attendances,
         saveAttendances             : _saveAttendances,
         getAbsenceTypes             : _getAbsenceTypes,
         absenceTypes                : _absenceTypes,
         setRedLetterDay             : _setRedLetterDay,
         setAbsence                  : _setAbsence,
         removeAbsence               : _removeAbsence,
         isAttendancesClosed         : _isAttendancesClosed,
         isAbsencesClosed            : _isAbsencesClosed,
         instructorAttendance        : _instructorAttendance,
         setInstructorAttendance     : _setInstructorAttendance,
         getInstructorAttendance     : _getInstructorAttendance,
         getInstructorAttendanceValue: _getInstructorAttendanceValue,
         getAbsencesClosedDay        : _getAbsencesClosedDay,
         setCustomWorkingDay         : _setCustomWorkingDay
      };
   }

})();