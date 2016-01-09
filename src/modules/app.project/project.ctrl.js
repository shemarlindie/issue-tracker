/* global angular */
(function (angular) {
  'use strict';
  
  // MODULE DEFINITION
  angular.module('app.project')
    .controller('ProjectCtrl', ['$scope', '$state', 'User', 'ProjectService', 'TagService', '$stateParams',
      function ($scope, $state, User, ProjectService, TagService, $stateParams) {
        var vm = this;

        vm.init = function () {
          vm.editing = $stateParams.id;

          if (vm.editing) {
            ProjectService.get(vm.editing)
              .then(function (data) {
                vm.project = data;
              });
          }
          else {
            vm.project = {
              tags: [],
              collaborators: []
            }

            User.get().then(function (user) {
              vm.project.collaborators.push(user);
            });
          }

          vm.userSearch = {
            users: User.all().then(function (data) {
              vm.userSearch.users = data;
            }),

            search: function (value, index, array) {
              return (value.first_name + value.last_name + value.email).toLowerCase().indexOf(vm.userSearch.searchText.toLowerCase()) >= 0;
            }
          }

          vm.tagSearch = {
            tags: TagService.projectTags().then(function (tags) {
              vm.tagSearch.tags = tags.map(function (val) {
                return { name: val.$value, exists: true };
              });
            }),

            transformChip: function (chip) {
              if (angular.isObject(chip)) {
                return chip;
              }

              return { name: chip, exists: false }
            },

            search: function (value, index, array) {
              return value.name.indexOf(vm.tagSearch.searchText.toLowerCase()) >= 0;
            },
          }
        }

        vm.create = function () {
          TagService.addToProjectTags(vm.project.tags);

          ProjectService.create(vm.project)
            .then(function (ref) {
              $state.go('app.project-detail', { id: ref.key() })
            });
        }

        vm.update = function () {
          ProjectService.update(vm.project).then(function (response) {
            $state.go('app.project-detail', { id: vm.project.$id });
          }, function (error) {
            console.log('update error', error);            
          });
        }


        vm.init();
      }])
  ;

})(angular);