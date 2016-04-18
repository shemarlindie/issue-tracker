/* global angular */
(function (angular) {
  'use strict';

  // MODULE DEFINITION
  angular.module('app.project')
    .controller('ProjectListCtrl', ['$scope', '$state', 'AppConfig', 'ProjectService', 'TagService', '$stateParams', '$interval',
      function ($scope, $state, AppConfig, ProjectService, TagService, $stateParams, $interval) {
        var vm = this;

        vm.init = function () {
          vm.query = {
            pageSize: 10,
            page: 1,
            total: 0
          };

          vm.promise = undefined;

          vm.projects = {
            list: []
          };

          vm.loadProjects();
        };

        vm.loadProjects = function (params) {
          console.log('loadProjects');

          params = params || {};
          params = angular.extend({}, vm.query, params);

          vm.promise = ProjectService.all(params)
            .then(function (response) {
              vm.projects = response.data;

              vm.query.total = vm.projects.page.totalCount;
              vm.query.page = vm.projects.page.current;
              vm.query.limit = vm.projects.page.numItemsPerPage;

              // console.log('projects', vm.projects);

              return response;
            })
            .catch(function (response) {
              console.error('Unable to load projects', response);
            });
        };

        vm.onPaginate = function () {
          console.log('onpaginate project list');

          vm.loadProjects();
        };

        vm.init();

        var timer = $interval(vm.loadProjects, AppConfig.UPDATE_INTERVAL);
        $scope.$on('$destroy', function() {
          // console.log('destroying project list timer');

          $interval.cancel(timer);
        });
      }])
  ;

})(angular);