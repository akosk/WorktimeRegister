/**
 * Created by Ákos on 2014.11.10..
 */

/*jshint loopfunc: true */
/*global  isAdmin: true */

var attendanceModule;

(function () {
   "use strict";


//
// MODULE
//

   attendanceModule = angular.module("attendance", ['ngRoute']);

//
// CONFIG
//

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


//
// CONTROLLER
//

   attendanceModule.controller('attendanceController',
      function ($scope, $http, $routeParams, dataService, helpers) {

         $scope.ourData = dataService;
         $scope.year = $routeParams.year !== undefined ? parseInt($routeParams.year, 10) : helpers.getYear();
         $scope.month = $routeParams.month !== undefined ? parseInt($routeParams.month, 10) : helpers.getMonth();
         $scope.focusedItem = null;

         $scope.isBusy = true;
         $scope.isSave = false;
         $scope.isAdmin = isAdmin;
         $scope.helpers = helpers;



         $scope.$watch('ourData', function (newVal, oldVal) {
            if (oldVal.attendances.length !== 0 && $scope.isAttendancesValid()) {
               var firstChanged = _.find(newVal.attendances, function (item, idx) {
                  return item.from !== oldVal.attendances[idx].from || item.to !== oldVal.attendances[idx].to;
               });
               if (firstChanged !== undefined) {
                  $scope.oldFocusedItem= _.clone(firstChanged);
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
            }
         }, true);

         $scope.isAttendancesValid = function () {
            return _.every(dataService.attendances, function (item) {
               return $scope.isValid(item);
            });
         };

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

         $scope.isValidTime = function (time) {
            if (time === null || time === undefined) {
               return true;
            }
            var regex = /([01]\d|2[0-3]):([0-5]\d)/;
            return regex.test(time) && time.length===5;
         };

         $scope.isValid = function (item) {
            if (item.from === null && item.to === null) {
               return true;
            }
            return $scope.isValidTime(item.from) && $scope.isValidTime(item.to);

         };

         $scope.getRowBg = function (item) {
            if (!$scope.isValid(item)) {
               return 'alert alert-danger';
            }

            if (helpers.isCurrentDay(item.date)) {
               return "info";
            }

            return item.workDay ? "" : "";

         };


         $scope.clearTimes = function (item) {
            $scope.focusedItem = item;
            item.from = null;
            item.to = null;
            if (item.userAbsence !== undefined) {
               dataService.removeAbsence(item.date)
                  .then(
                  function () {
                  },
                  function () {
                     console.log("Cannot remove absence!");
                  }
               )
                  .then(function () {

                  });

               item.userWorkDay = item.workDay;
               delete item.userAbsence;
            }
         };

         $scope.setFocusedItem = function (item) {
            $scope.focusedItem = item;
            if (item !== undefined && item !== null) {
               $scope.oldFocusedItem = _.clone(item);
            }
         };

         $scope.unsetFocusedItem = function (item) {
            if ($scope.oldFocusedItem !== undefined && $scope.oldFocusedItem !== null && !$scope.isValid(item)) {
               item.from = $scope.oldFocusedItem.from;
               item.to = $scope.oldFocusedItem.to;
            }
            $scope.focusedItem = null;
         };

         $scope.getPreviousMonthUrl = function () {
            return "#/year/" + (parseInt($scope.month) === 1 ? parseInt($scope.year) - 1 : parseInt($scope.year)) + "/month/" + (parseInt($scope.month) === 1 ? 12 : parseInt($scope.month) - 1);
         };

         $scope.getNextMonthUrl = function () {
            return "#/year/" + ($scope.month === 12 ? $scope.year + 1 : $scope.year) + "/month/" + ($scope.month === 12 ? 1 : $scope.month + 1);
         };

         $scope.offDayText = function (item) {
            var workDayStr = item.workDay ? '' : 'Munkaszüneti nap';
            var holidayStr = item.userAbsence !== undefined ? item.userAbsence.label : '';
            var separator = workDayStr.length > 0 && holidayStr.length > 0 ? ' - ' : '';
            return workDayStr + separator + holidayStr;
         };

         $scope.setRedLetterDay = function (item) {
            $scope.focusedItem = item;
            $scope.isSave = true;
            dataService.setRedLetterDay(item.date, 'HOLIDAY')
               .then(
               function () {
               },
               function () {
                  //error
                  console.log("Cannot set red letter day!");
               }
            )
               .then(function () {
                  item.workDay = false;
                  item.userWorkDay = false;
                  $scope.isSave = false;
                  $scope.focusedItem = null;
               });

         };
         $scope.setWorkingDay = function (item) {
            $scope.focusedItem = item;
            $scope.isSave = true;
            dataService.setRedLetterDay(item.date, 'WORKING_DAY')
               .then(
               function () {
               },
               function () {
                  //error
                  console.log("Cannot set red letter day!");
               }
            )
               .then(function () {
                  item.workDay = true;
                  item.userWorkDay = true;
                  $scope.isSave = false;
                  $scope.focusedItem = null;
               });


         };

         $scope.setAbsence = function (item, absenceType) {
            $scope.focusedItem = item;
            $scope.isSave = true;
            dataService.setAbsence(item.date, absenceType.code)
               .then(
               function () {
               },
               function () {
                  //error
                  console.log("Cannot set absence!");
               }
            )
               .then(function () {
                  item.userAbsence = absenceType;
                  item.from = null;
                  item.to = null;
                  item.userWorkDay = false;
                  $scope.isSave = false;
                  $scope.focusedItem = null;
               });


         };

         $scope.getAttendances();
      }
   );


})();