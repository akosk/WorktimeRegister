(function () {
   "use strict";

   module.exports = function (grunt) {

      grunt.initConfig({
         jasmine: {
            pivotal: {
               src    : 'modules/attendance/assets/js/*.js',
               options: {
                  vendor: [
                     "vendor/bower/underscore/underscore.js",
                     "vendor/bower/angularjs/angular.js",
                     "vendor/bower/angular-mocks/angular-mocks.js"
                  ],
                  specs: 'modules/attendance/tests/tests.js'
               }
               //src    : 'modules/attendance/assets/js/simple.js',
               //options: {
               //   vendor: [
               //      "vendor/bower/underscore/underscore.js",
               //      "vendor/bower/angularjs/angular.js",
               //      "vendor/bower/angular-mocks/angular-mocks.js"
               //   ],
               //   specs: 'modules/attendance/tests/simpletest.js'
               //}
            }
         }
      });

      grunt.loadNpmTasks('grunt-contrib-jasmine');

      grunt.registerTask('default', ['jasmine']);

   };

})();