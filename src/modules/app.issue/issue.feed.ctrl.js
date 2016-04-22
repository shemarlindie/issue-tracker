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
        var FILTER_DEFAULTS = {
          assignedToId: undefined,
          typeId: undefined,
          statusId: undefined,
          priorityId: undefined,
          sortBy: 'dateUpdated',
          sortOrder: 'DESC'
        };

        vm.typeList = [];
        vm.statusList = [];
        vm.priorityList = [];
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

          vm.loadFilters();
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

        vm.loadFilters = function () {
          if (localStorage.filterCache) {
            vm.filters = JSON.parse(localStorage.filterCache);
          }
          else {
            vm.resetFilters();
          }
        };

        vm.resetFilters = function () {
          vm.filters = FILTER_DEFAULTS;

          vm.onFiltersChanged();

          return vm.filters;
        };

        vm.onFiltersChanged = function() {
          localStorage.filterCache = JSON.stringify(vm.filters);
          vm.loadIssues();
        };

        vm.isListFiltered = function() {
          return vm.filters.assignedToId ||
              vm.filters.priorityId ||
              vm.filters.statusId ||
              vm.filters.typeId;
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