/* global angular */
(function (angular) {
  'use strict';

  // MODULE DEFINITION
  angular.module('app.issue')
    .controller('IssueCtrl', ['$scope', '$stateParams', '$state', 'AppConfig', 'SecurityService', 'UserService',
      'IssueService', 'TagService', 'ProjectService', 'CommentService', '$mdDialog', '$interval',
      function ($scope, $stateParams, $state, AppConfig, SecurityService, UserService,
                IssueService, TagService, ProjectService, CommentService, $mdDialog, $interval) {
        var vm = this;

        vm.typeList = [];
        vm.statusList = [];
        vm.priorityList = [];

        vm.editing = $stateParams.id;
        vm.projectId = $stateParams.project;

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

          vm.comment = {};
          vm.statusChangeData = {};
          vm.query = {
            pageSize: 10,
            page: 1,
            total: 0
          };
          vm.promise = undefined;

          if (vm.editing) {
            IssueService.get(vm.editing)
              .then(function (response) {
                vm.issue = response.data;
                vm.loadComments();
                console.log('issue details', vm.issue);
              });
          }
          else {
            vm.issue = {
              tags: [],
              testers: [],
              fixers: [],
              status_id: 1,
              type_id: 1,
              priority_id: 1
            };

            if (vm.projectId) {
              ProjectService.get(vm.projectId)
                .then(function (response) {
                  vm.issue.project = response.data;
                });
            }

            vm.issue.testers.push(SecurityService.getUser());
          }

          vm.projectSearch = {
            search: function (text) {
              return ProjectService.search(text).then(function (response) {
                return response.data.list;
              });
            }
          };

          vm.testerSearch = {
            search: function (text) {
              return UserService.search(text).then(function (response) {
                return response.data.list;
              });
            }
          };

          vm.fixerSearch = {
            search: function (text) {
              return UserService.search(text).then(function (response) {
                return response.data.list;
              });
            }
          };

          vm.tagSearch = {
            search: function (value, index, array) {
              return value.name.toLowerCase().indexOf(vm.tagSearch.searchText.toLowerCase()) >= 0;
            }
          };
        };

        vm.create = function () {
          IssueService.create(vm.issue)
            .then(function (response) {
              if (vm.projectId) {
                $state.go('app.project-detail', {id: vm.projectId});
              }
              else {
                $state.go('app.issue-detail', {id: response.data.id})
              }
            })
            .catch(function (response) {
              console.log('creation error', response);
            });
        };

        vm.update = function () {
          IssueService.update(vm.issue)
            .then(function (response) {
              $state.go('app.issue-detail', {id: response.data.id});
            }, function (error) {
              console.log('update error', error);
            });
        };

        vm.resetStatusForm = function () {
          vm.statusChangeData = {};

          vm.statusForm.$setPristine();
          vm.statusForm.$setUntouched();
        };

        vm.resetCommentForm = function () {
          vm.comment = {};

          vm.commentForm.$setPristine();
          vm.commentForm.$setUntouched();
        };

        vm.loadComments = function(params, ignoreLoadingBar) {
          params = params || {};
          params = angular.extend({}, vm.query, params);
          params.issueId = vm.issue.id;

          vm.promise = CommentService.all(params, ignoreLoadingBar)
            .then(function (response) {
              vm.comments = response.data;

              vm.query.total = vm.comments.page.totalCount;
              vm.query.page = vm.comments.page.current;
              vm.query.limit = vm.comments.page.numItemsPerPage;

              // console.log('comments', vm.comments);

              return response;
            })
            .catch(function (response) {
              console.log('unable to load comments', response);
            });
        };

        vm.addComment = function () {
          vm.comment.issue = vm.issue;
          CommentService.create(vm.comment)
            .then(function () {
              vm.resetCommentForm();
              vm.loadComments();
            });
        };

        vm.removeComment = function (comment) {
          var confirm = $mdDialog.confirm({
            textContent: 'Are you sure you want to delete this comment?',
            ok: 'Delete',
            cancel: 'Cancel'
          });

          $mdDialog.show(confirm).then(function () {
            CommentService.delete(comment)
              .then(function () {
                vm.loadComments();
              });
          });
        };

        vm.updateStatus = function () {
          // did status change?
          if (vm.statusChangeData.status.id == vm.issue.status.id) return;

          var issue = angular.extend({}, vm.issue, {status_id: vm.statusChangeData.status.id});
          IssueService.update(issue)
            .then(function (response) {
              vm.issue = response.data;

              vm.statusChangeData.issue = vm.issue;
              CommentService.create(vm.statusChangeData)
                .then(function (response) {
                  vm.resetStatusForm();
                  vm.loadComments();
                })
                .catch(function (response) {
                  console.log('error updating status comment', response);
                });
            })
            .catch(function (response) {
              console.error('error updating status', response);
            });
        };

        vm.deleteIssue = function () {
          var issue = vm.issue;
          var confirm = $mdDialog.confirm({
            htmlContent: '<p><b>' + issue.name + '</b> will be deleted.</p>This cannot be undone.',
            ok: 'Delete',
            cancel: 'Cancel'
          });

          $mdDialog.show(confirm).then(function () {
            IssueService.delete(issue)
              .then(function (response) {
                $state.go('app.project-detail', {id: issue.project.id});
              });
          })
        };

        vm.init();


        var timer = $interval(function() {
          if ($state.current.name == 'app.issue-detail') {
            vm.loadComments(null, true);
          }
        }, AppConfig.UPDATE_INTERVAL);
        $scope.$on('$destroy', function() {
          // console.log('destroying comments timer');
          
          $interval.cancel(timer);
        });
      }])
  ;

})(angular);