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
          fixers: undefined,
          type: undefined,
          status: undefined,
          priority: undefined,
          sort_by: 'date_created',
          sort_order: '-'
        };
        vm.filters = {}

        vm.typeList = [];
        vm.statusList = [];
        vm.priorityList = [];
        vm.issues = {};

        vm.query = {
          limit: 10,
          page: 1
        };

        vm.me = SecurityService.getUser();

        vm.init = function () {
          IssueService.types().then(function (response) {
            vm.typeList = response.data.results;
          });

          IssueService.statuses().then(function (response) {
            vm.statusList = response.data.results;
          });

          IssueService.priorities().then(function (response) {
            vm.priorityList = response.data.results;
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
          if (params.sort_by) {
            params.ordering = params.sort_by

            if (params.sort_order) {
              params.ordering = params.sort_order + params.ordering
            }
          }
          delete params.sort_by
          delete params.sort_order
          // remove empty filter params
          for (var k in params) {
            if (!params[k]) {
              delete params[k]
            }
          }

          params.project = $stateParams.id;

          vm.promise = IssueService.all(params, ignoreLoadingBar)
            .then(function (response) {
              vm.issues = response.data;

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
          angular.extend(vm.filters, FILTER_DEFAULTS);

          vm.onFiltersChanged();

          return vm.filters;
        };

        vm.onFiltersChanged = function() {
          localStorage.filterCache = JSON.stringify(vm.filters);
          vm.loadIssues();
        };

        vm.isListFiltered = function() {
          return vm.filters.fixers ||
              vm.filters.priority ||
              vm.filters.status ||
              vm.filters.type;
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