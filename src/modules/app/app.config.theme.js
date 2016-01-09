/* global angular */
(function (angular) {
  'use strict';

  angular.module('app')
    .config(['$mdThemingProvider', function ($mdThemingProvider) {

      $mdThemingProvider.theme('default')
        .primaryPalette('blue-grey')
        .accentPalette('blue')
        .backgroundPalette('grey', {
          'default': '50',
          'hue-1': '100',
          'hue-2': '200',
          'hue-3': '300'
        });
        
        /*
        red, pink, purple, deep-purple, indigo, blue, light-blue, cyan, teal, green, light-green, lime, yellow, amber, orange, deep-orange, brown, grey, blue-grey
        */

    }]);

})(angular);
