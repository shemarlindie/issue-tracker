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
    .filter('UserList', ['User', function (User) {
      var cache = {};
      var formatter = function (users, separator) {
        return users.map(function (val) {
          return val.first_name + ' ' + val.last_name;
        }).join(separator)
      }

      return function (uids, separator) {
        if (!uids) return '-';

        var key = uids.join();

        if (!cache[key]) {
          User.uidToObj(uids).then(function (result) {
            if (result && result.length) {
              cache[key] = result;
            }
            else {
              cache[key] = [];
            }
          });

          return '...';
        }
        else {
          return formatter(cache[key]);
        }
      }
    }]);

})(angular);