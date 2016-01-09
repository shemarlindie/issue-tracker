/* global angular */
(function (angular) {
  'use strict';
  
  // MODULE DEFINITION
  angular.module('app.issue')
    .controller('IssueCtrl', ['$scope', '$stateParams', '$state', 'IssueService', 'User', 'TagService', 'ProjectService', 'ConstantService', '$mdDialog',
      function ($scope, $stateParams, $state, IssueService, User, TagService, ProjectService, ConstantService, $mdDialog) {
        var vm = this;

        vm.typeList = ConstantService.constants.type;
        vm.statusList = ConstantService.constants.status;
        vm.priorityList = ConstantService.constants.priority;

        vm.init = function () {
          vm.editing = $stateParams.id;
          vm.projectId = $stateParams.project;

          if (vm.editing) {
            IssueService.get(vm.editing)
              .then(function (issue) {
                vm.issue = issue;
                vm.statusComment = {
                  status: vm.issue.status
                };
                vm.userComment = {};
                
                // keep denormalized copy updated
                IssueService.issues.$watch(function (changeData) {
                  console.log('data changed', changeData);
                  if (changeData.event == 'child_changed' && vm.issue.$id == changeData.key) {
                    console.log('re-denormalizing issue');

                    vm.issue = IssueService.denormalize(IssueService.issues.$getRecord(changeData.key));
                  }
                });
                console.log('issue details', vm.issue);
              });
          }
          else {
            vm.issue = {
              tags: [],
              testers: [],
              assigned_to: [],
              status: 0,
              type: 0,
              priority: 0
            }

            if (vm.projectId) {
              ProjectService.get(vm.projectId)
                .then(function (project) {
                  vm.issue.project = project;
                });
            }

            User.get().then(function (user) {
              vm.issue.testers.push(user);
            });
          }

          vm.projectSearch = {
            projects: ProjectService.all().then(function (projects) {
              vm.projectSearch.projects = projects;
            }),

            search: function (value, index, array) {
              return (value.name).toLowerCase().indexOf(vm.projectSearch.searchText.toLowerCase()) >= 0;
            }
          },

          vm.testerSearch = {
            users: User.all().then(function (users) {
              vm.testerSearch.users = users;
            }),

            search: function (value, index, array) {
              return (value.first_name + value.last_name + value.email).toLowerCase().indexOf(vm.testerSearch.searchText.toLowerCase()) >= 0;
            }
          },

          vm.fixerSearch = {
            users: User.all().then(function (users) {
              vm.fixerSearch.users = users;
            }),

            search: function (value, index, array) {
              return (value.first_name + value.last_name + value.email).toLowerCase().indexOf(vm.fixerSearch.searchText.toLowerCase()) >= 0;
            }
          }

          vm.tagSearch = {
            tags: TagService.issueTags()
              .then(function (tags) {
                vm.tagSearch.tags = tags.map(function (val) {
                  return { name: val.$value, exists: true };
                });
              }),

            search: function (value, index, array) {
              return value.name.toLowerCase().indexOf(vm.tagSearch.searchText.toLowerCase()) >= 0;
            },
          }
        }

        vm.create = function () {
          IssueService.create(vm.issue)
            .then(function (ref) {
              TagService.addToIssueTags(vm.issue.tags);
              if (vm.projectId) {
                $state.go('app.project-detail', { id: vm.projectId });
              }
              else {
                $state.go('app.issue-detail', { id: ref.key() })
              }
            }, function (error) {
              console.log('creation error', error);
            });
        }

        vm.update = function () {
          IssueService.update(vm.issue)
            .then(function (ref) {
              $state.go('app.issue-detail', { id: ref.key() });
            }, function (error) {
              console.log('update error', error);
            });
        }

        vm.addComment = function () {
          IssueService.addComment(vm.issue, vm.userComment)
            .then(function () {
              vm.userComment = {};
              vm.userCommentForm.$setPristine();
              vm.userCommentForm.$setUntouched();
            });
        }

        vm.updateStatus = function () {      
          // did status change?
          if (vm.statusComment.status == vm.issue.status) return;

          IssueService.addComment(vm.issue, vm.statusComment)
            .then(function () {
              vm.statusComment = {};
              vm.statusCommentForm.$setPristine();
              vm.statusCommentForm.$setUntouched();
            });
        }

        vm.deleteIssue = function (issue) {
          var confirm = $mdDialog.confirm({
            htmlContent: '<p><b>' + issue.name + '</b> will be deleted.</p>This cannot be undone.',
            ok: 'Delete',
            cancel: 'Cancel'
          })
                    
          $mdDialog.show(confirm).then(function () {
              IssueService.delete(issue)
                .then(function (ref) {
                  if (vm.projectId) {
                    $state.go('app.project-detail', { id: vm.projectId });
                  }
                  else {
                    $state.go('app.issues');
                  }
                });
            })
        }


        vm.init();

      }])
  ;

})(angular);