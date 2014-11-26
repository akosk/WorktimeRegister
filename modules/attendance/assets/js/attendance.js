/**
 * Created by Ákos on 2014.11.10..
 */

/*jshint loopfunc: true */
/*global BASE_URL:true */


(function () {
   "use strict";

   if (BASE_URL === undefined) {
      BASE_URL = '/';
   }
// MASTER DATA


   var DAY_NAMES = ['vasárnap', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat'];
   var dayTypes = {
      WORKDAY       : {
         id   : "WORKDAY",
         label: "Munkanap"
      },
      WEEKEND       : {
         id   : 'WEEKEND',
         label: "Hétvége",
         free : true
      },
      RED_LETTER_DAY: {
         id   : "RED_LETTER_DAY",
         label: "Ünnepnap",
         free : true
      },
      HOLIDAY       : {
         id   : 'HOLIDAY',
         label: "Szabadság",
         free : true
      }
   };


// ANGULAR

   var attendanceModule = angular.module("attendance", ['ngMask', 'ngRoute']);

   attendanceModule.config(function ($routeProvider) {
      var $template = $('#attendance-template').html();

      $routeProvider.when("/", {
         controller: "attendanceController",
         template  : $template
      });
      $routeProvider.when("/year/:year/month/:month", {
         controller: "attendanceController",
         template  : $template
      });

      $routeProvider.otherwise("/");

   });

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

   attendanceModule.filter('justDay', function () {
      return function (input) {
         var date = new Date(input);

         return date.getDate() + ". " + DAY_NAMES[date.getDay()];
      };
   });

   attendanceModule.factory('helpers', function () {

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

            if (found === undefined) {
               var absence = _isWeekend(currentDate) ? dayTypes.WEEKEND : dayTypes.WORKDAY;
               d.push({
                  date   : currentDate,
                  from   : null,
                  to     : null,
                  absence: absence
               });
            }
         }

         d = _.sortBy(d, function (item) {
            return item.date;
         });


         return d;
      };

      var _convertDayTypes = function (attendances) {
         if (attendances !== undefined) {
            for (var i = 0; i < attendances.length; i++) {
               if (attendances[i].absence !== undefined) {
                  attendances[i].absence = dayTypes[attendances[i].absence];
               }
            }
         }
         return attendances;
      };

      var _isWeekend = function (date) {
         var day = new Date(date).getDay();
         return day === 0 || day === 6;
      }

      return {
         getYear          : _getYear,
         getMonth         : _getMonth,
         isCurrentDay     : _isCurrentDay,
         completeEmptyDays: _completeEmptyDays,
         convertDayTypes  : _convertDayTypes,
         getMonthName     : _getMonthName,
         isWeekend        : _isWeekend
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

      var _getAttendances = function (year, month) {
         var deferred = $q.defer();
         $http({
            url   : BASE_URL + 'attendance/default/get-attendances',
            method: "GET",
            params: {year: year, month: month}
         })
            .then(function (result) {
               var _temp = result.data.attendances === undefined ? [] : helpers.convertDayTypes(result.data.attendances);

               _temp = helpers.completeEmptyDays(_temp, year, month);

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

      return {
         getAttendances : _getAttendances,
         attendances    : _attendances,
         saveAttendances: _saveAttendances,
         getAbsenceTypes: _getAbsenceTypes,
         absenceTypes   : _absenceTypes
      };
   });

   attendanceModule.controller('attendanceController',
      function ($scope, $http, $routeParams, dataService, helpers) {

         $scope.ourData = dataService;
         $scope.year = $routeParams.year !== undefined ? parseInt($routeParams.year, 10) : helpers.getYear();
         $scope.month = $routeParams.month !== undefined ? parseInt($routeParams.month, 10) : helpers.getMonth();
         $scope.focusedItem = null;

         $scope.isBusy = true;
         $scope.isSave = false;
         $scope.helpers = helpers;


         $scope.$watch('ourData', function (newVal, oldVal) {
            if (oldVal.attendances.length !== 0) {
               $scope.isSave = true;
               dataService.saveAttendances().then(
                  function () {
                     console.log("Attendances saved.");
                  },
                  function () {
                     //error
                     console.log("Error during saving attendances!");
                  }
               ).then(function () {
                     $scope.isSave = false;
                  });

            }

         }, true);

         $scope.getAttendances = function () {
            dataService.getAttendances($scope.year, $scope.month)
               .then(
               function () {
               },
               function () {
                  //error
                  console.log("Cannot load attendances!");
               }
            )
               .then(function () {
                  $scope.isBusy = false;
               });

         };


         $scope.getRowBg = function (item) {
            if (helpers.isCurrentDay(item.date)) {
               return "info";
            }

            return $scope.isUserOnFreeDay(item) ? "" : "";

         };

         $scope.isUserOnFreeDay = function (item) {
            return item.absence !== undefined && item.absence.free;
         };

         $scope.isWorkDay = function (date) {
            return !helpers.isWeekend(date);
         };


         $scope.clearTimes = function (item) {
            $scope.focusedItem = item;
            item.from = null;
            item.to = null;
         };

         $scope.setFocusedItem = function (item) {
            $scope.focusedItem = item;
         };

         $scope.getPreviousMonthUrl = function () {
            return "#/year/" + (parseInt($scope.month) === 1 ? parseInt($scope.year) - 1 : parseInt($scope.year)) + "/month/" + (parseInt($scope.month) === 1 ? 12 : parseInt($scope.month) - 1);
         };

         $scope.getNextMonthUrl = function () {
            return "#/year/" + ($scope.month === 12 ? $scope.year + 1 : $scope.year) + "/month/" + ($scope.month === 12 ? 1 : $scope.month + 1);
         };


         $scope.getAttendances();
      }
   );


})();