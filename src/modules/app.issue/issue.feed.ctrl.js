/* global angular */
(function (angular) {
  'use strict';
  
  // MODULE DEFINITION
  angular.module('app.issue')
    .controller('IssueFeedCtrl', ['$scope', '$stateParams', '$state', 'IssueService', 'User', 'TagService', 'ProjectService', 'ConstantService',
      function ($scope, $stateParams, $state, IssueService, User, TagService, ProjectService, ConstantService) {
        var vm = this;

        vm.typeList = ConstantService.constants.type;
        vm.statusList = ConstantService.constants.status;
        vm.priorityList = ConstantService.constants.priority;

        vm.me = User.getUid();

        vm.init = function () {
          vm.resetFilters();

          ProjectService.connect();
          vm.projects = ProjectService.projects;

          vm.users = User.users;

          vm.issues = [];
          IssueService.all()
            .then(function (issues) {
              vm.issues = issues;
              console.log('issues', issues);
            });

        }

        vm.resetFilters = function () {
          vm.filters = {
            project: $stateParams.id ? $stateParams.id : false,
            assigned_to: false,
            type: -1,
            status: -1,
            priority: -1,
            sort_by: 'date_updated',
            sort_order: true
          }
        }

        vm.filterFn = function (issue, index, arr) {
          var assigned_to = false;
          var type = false;
          var status = false;
          var priority = false;
          var project = false;

          if (vm.filters.assigned_to !== false) {
            assigned_to = (issue.assigned_to && issue.assigned_to.indexOf(vm.filters.assigned_to) >= 0);
          }
          else {
            assigned_to = true;
          }

          if (vm.filters.type !== -1) {
            type = (issue.type === vm.filters.type);
          }
          else {
            type = true;
          }

          if (vm.filters.status !== -1) {
            status = (issue.status === vm.filters.status);
          }
          else {
            status = true;
          }

          if (vm.filters.priority !== -1) {
            priority = (issue.priority === vm.filters.priority);
          }
          else {
            priority = true;
          }

          if (vm.filters.project !== false) {
            project = (issue.project == vm.filters.project)
          }
          else {
            project = true;
          }

          return assigned_to && type && status && priority && project;
        }


        vm.init();
      }])
  ;

})(angular);