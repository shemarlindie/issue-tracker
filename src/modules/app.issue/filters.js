/* global angular */
(function (angular) {
  'use strict';
  
  // MODULE DEFINITION
  angular.module('app.issue')
    .filter('IssueStatus', ['ConstantService', function (ConstantService) {
      return function (code) {
        return ConstantService.constants.status[code] ? ConstantService.constants.status[code].$value : '';
      }
    }])
    .filter('IssueType', ['ConstantService', function (ConstantService) {
      return function (code) {
        return ConstantService.constants.type[code] ? ConstantService.constants.type[code].$value : '';
      }
    }])
    .filter('IssuePriority', ['ConstantService', function (ConstantService) {
      return function (code) {
        return ConstantService.constants.priority[code] ? ConstantService.constants.priority[code].$value : '';
      }
    }])
    .filter('UserList', function () {
      var formatter = function (users, separator) {
        if (users) {
          return users.map(function (user) {
            return user.first_name + ' ' + user.last_name;
          }).join(separator)
        }
        else {
          return ' - ';
        }
      };

      return function (users, separator) {
        return formatter(users, separator);
      }
    });

})(angular);