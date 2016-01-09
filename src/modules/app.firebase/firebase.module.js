/* global angular */
(function (angular) {
  'use strict';
  
  // MODULE DEFINITION
  angular.module('app.firebase', ['firebase'])
    .constant('FIREBASE_URL', 'https://v75-issue-tracker.firebaseio.com')
    // .constant('FIREBASE_URL', 'https://dev-v75-issue-tracker.firebaseio.com')
  ;

})(angular);