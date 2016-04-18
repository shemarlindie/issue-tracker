/* global angular */
(function (angular) {
  'use strict';
  
  // MODULE DEFINITION
  angular.module('app.issue')
    .controller('IssueFeedCtrl', ['$scope', '$stateParams', '$state', 'AppConfig', 'SecurityService', 'IssueService',
      'UserService', 'ProjectService', '$interval',
      function ($scope, $stateParams, $state, AppConfig, SecurityService, IssueService,
                UserService, ProjectService, $interval) {
        var vm = this;

        vm.typeList = [];
        vm.statusList = [];
        vm.priorityList = [];
        vm.users = [];
        vm.issues = {};

        vm.query = {
          pageSize: 10,
          page: 1,
          total: 0
        };

        vm.me = SecurityService.getUser();

        vm.init = function () {
          IssueService.types().then(function (response) {
            vm.typeList = response.data;
          });

          IssueService.statuses().then(function (response) {
            vm.statusList = response.data;
          });

          IssueService.priorities().then(function (response) {
            vm.priorityList = response.data;
          });

          UserService.all({pageSize: 20})
            .then(function (response) {
              vm.users = response.data.list;
            });

          vm.resetFilters();
          vm.loadIssues();
        };

        vm.onPaginate = function() {
          vm.loadIssues();
        };

        vm.loadIssues = function(params, ignoreLoadingBar) {
          params = params || {};
          params = angular.extend({}, vm.query, params);
          params = angular.extend(params, vm.filters);
          params.projectId = $stateParams.id;

          vm.promise = IssueService.all(params, ignoreLoadingBar)
            .then(function (response) {
              vm.issues = response.data;

              vm.query.total = vm.issues.page.totalCount;
              vm.query.page = vm.issues.page.current;
              vm.query.limit = vm.issues.page.numItemsPerPage;

              // console.log('issues', vm.issues);

              return response;
            })
            .catch(function (response) {
              console.log('unable to load issues', response);
            });
        };

        vm.resetFilters = function () {
          return vm.filters = {
            assignedToId: undefined,
            typeId: undefined,
            statusId: undefined,
            priorityId: undefined,
            sortBy: 'dateUpdated',
            sortOrder: 'DESC'
          }
        };

        vm.init();

        var timer = $interval(function() {
          vm.loadIssues(null, true);
        }, AppConfig.UPDATE_INTERVAL);
        $scope.$on('$destroy', function() {
          // console.log('destroying issue list timer');

          $interval.cancel(timer);
        });
      }])
  ;

})(angular);