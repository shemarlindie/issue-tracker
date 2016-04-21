/* global angular */
(function (angular) {
  'use strict';

  // MODULE DEFINITION
  angular.module('app.project')
    .controller('ProjectCtrl', ['$scope', '$state', 'SecurityService', 'UserService', 'ProjectService', 'TagService',
      '$stateParams', '$mdDialog',
      function ($scope, $state, SecurityService, UserService, ProjectService, TagService,
                $stateParams, $mdDialog) {
        var vm = this;

        vm.init = function () {
          vm.editing = $stateParams.id;

          if (vm.editing) {
            ProjectService.get(vm.editing)
              .then(function (response) {
                vm.project = response.data;

                // set project in parent scope: issue feed ctrl
                $scope.$parent.project = vm.project;
              });
          }
          else {
            vm.project = {
              tags: [],
              collaborators: []
            };

            vm.project.collaborators.push(SecurityService.getUser());
          }

          vm.userSearch = {
            search: function (text) {
              return UserService.search(text).then(function (response) {
                return response.data.list;
              });
            }
          };

          vm.tagSearch = {
            transformChip: function (chip) {
              if (angular.isObject(chip)) {
                return chip;
              }

              return {name: chip, exists: false}
            },

            search: function (value, index, array) {
              return value.name.indexOf(vm.tagSearch.searchText.toLowerCase()) >= 0;
            }
          };
        };

        vm.create = function () {
          ProjectService.create(vm.project)
            .then(function (response) {
              $state.go('app.project-detail', {id: response.data.id})
            });
        };

        vm.update = function () {
          ProjectService.update(vm.project).then(function (response) {
            $state.go('app.project-detail', {id: response.data.id});
          }, function (error) {
            console.log('update error', error);
          });
        };

        vm.delete = function () {
          var confirm = $mdDialog.confirm({
            htmlContent: '<p><b>' + vm.project.name + '</b> will be deleted.</p>This cannot be undone.',
            ok: 'Delete',
            cancel: 'Cancel'
          });

          $mdDialog.show(confirm).then(function () {
            ProjectService.delete(vm.project)
              .then(function (ref) {
                $state.go('app.home');
              });
          })
        };

        vm.init();
      }])
  ;

})(angular);