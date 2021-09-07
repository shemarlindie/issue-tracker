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
            limit: 10,
            page: 1
          };

          vm.promise = undefined;

          vm.projects = {
          };

          vm.loadProjects();
        };

        vm.loadProjects = function (params, ignoreLoadingBar) {
          // console.log('loadProjects');

          params = params || {};
          params = angular.extend({}, vm.query, params);

          vm.promise = ProjectService.all(params, ignoreLoadingBar)
            .then(function (response) {
              vm.projects = response.data;

              // console.log('projects', vm.projects);

              return response;
            })
            .catch(function (response) {
              console.error('Unable to load projects', response);
            });
        };

        vm.onPaginate = function () {
          console.log('onpaginate project list', vm.query);

          vm.loadProjects();
        };

        vm.init();

        var timer = $interval(function() {
          vm.loadProjects(null, true)
        }, AppConfig.UPDATE_INTERVAL);
        $scope.$on('$destroy', function() {
          // console.log('destroying project list timer');

          $interval.cancel(timer);
        });
      }])
  ;

})(angular);