/* jshint undef: false  */

(function () {
   "use strict";

   describe('Attendance', function () {

      beforeEach(function () {
         module("attendance");

      });


      it('controller test', inject(function ($controller) {

         var theScope = {};
         var ctrl = $controller("attendanceController", {
            $scope: theScope
         });

         expect(ctrl).not.toBeNull();
         expect(theScope.ourData).toBeDefined();

      }));


   });
})
();

