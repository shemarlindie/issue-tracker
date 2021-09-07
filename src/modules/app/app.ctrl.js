(function (angular) {
  'use strict';

  angular.module('app')
    .controller('AppCtrl', ['AppConfig', 'SecurityService', 'UserService', 'ProjectService', 'IssueService', 'TagService', '$scope', '$state',
      '$location', '$mdMedia', '$mdSidenav', '$mdToast', '$mdDialog', '$interval',
      function (AppConfig, SecurityService, UserService, ProjectService, IssueService, TagService, $scope, $state,
                $location, $mdMedia, $mdSidenav, $mdToast, $mdDialog, $interval) {
        var vm = this; // view model

        vm.sidenav = {
          sections: [
            {
              name: 'Recent Projects',
              type: 'heading',
              children: []
            }
          ],

          selectedSection: undefined
        };

        vm.init = function() {
          vm.loggingOut = false;

          vm.loginState = {
            loading: false,
            errors: {},
            credentials: {}
          };

          vm.registerState = {
            loading: false,
            errors: {},
            data: {}
          };

          vm.forgotPasswordState = {
            loading: false,
            errors: {},
            data: {}
          };

          vm.activateAccountState = {
            loading: false,
            activated: false,
            errors: {},
            data: {}
          };

          vm.loadProjects();
        };

        vm.loadProjects = function() {
          ProjectService.all(null, true)
            .then(function (response) {
              vm.sidenav.sections[0].children = response.data.results;
            })
            .catch(function (response) {
              console.error('error loading sidenav projects', response);
            });
        };

        vm.onSectionSelected = function (project) {
          vm.toggleSidenav();
          $state.go('app.project-detail', {id: project.id});
        };

        vm.hasSidenav = function () {
          return $state.current.data && $state.current.data.hasSidenav;
        };

        vm.showSidenavToggle = function () {
          return $mdMedia('xs') || $mdMedia('sm');
        };

        vm.toggleSidenav = function () {
          $mdSidenav('sidenav-main').toggle();
        };

        vm.login = function () {
          vm.loginState.loading = true;
          vm.loginState.errors = {};

          SecurityService.login(vm.loginState.credentials)
            .then(function (response) {
              vm.init();
              $state.go('app.home');
            })
            .catch(function (response) {
              vm.loginState.errors.form = response.data.message;
            })
            .finally(function () {
              vm.loginState.loading = false;
            });
        };

        vm.logout = function () {
          vm.loggingOut = true;

          SecurityService.logout()
            .then(function (response) {
              vm.init();

              $state.go('login');
            })
            .catch(function (response) {
              console.error(response);
            })
            .finally(function () {
              vm.loggingOut = false;
            })
          ;
        };

        vm.register = function () {
          vm.registerState.loading = true;
          vm.registerState.errors = {};

          UserService.create(vm.registerState.data)
            .then(function (response) {
              vm.registerState.data = {};
              vm.registerState.form.$setPristine();
              vm.registerState.form.$setUntouched();

              var user = response.data;
              if (user.enabled) {
                SecurityService.setAuthData({token: user.token, user: user});
                $state.go('app.home');
              }
              else {
                $mdToast.showSimple('Account created! Check your email for activation instructions.');
              }
            })
            .catch(function (response) {
              vm.registerState.errors = response.data;
            })
            .finally(function () {
              vm.registerState.loading = false;
            });
        };

        vm.sendPasswordRecoveryEmail = function () {
          vm.forgotPasswordState.loading = true;
          vm.forgotPasswordState.errors = {};
          var sendingToast = $mdToast.showSimple('Sending message...');

          UserService.forgotPassword(vm.forgotPasswordState.data)
            .then(function (response) {
              var failed = response.data;

              if (failed.length) {
                $mdDialog.show($mdDialog.alert({
                  title: 'Unable to send message',
                  textContent: 'The message could not be delivered to ' + failed.join(', ')
                }));
              }
              else {
                $mdToast.hide(sendingToast);
                $mdToast.showSimple('Message sent!');
                vm.forgotPasswordState.data = {};
              }
            })
            .catch(function (response) {
              vm.forgotPasswordState.errors = response.data;
            })
            .finally(function () {
              vm.forgotPasswordState.loading = false;
              $mdToast.hide(sendingToast);
            });
        };

        vm.resetPassword = function () {
          vm.forgotPasswordState.loading = true;
          vm.forgotPasswordState.errors = {};
          vm.forgotPasswordState.data.token = $state.params.token;

          UserService.resetPassword(vm.forgotPasswordState.data)
            .then(function (response) {
              vm.forgotPasswordState.data = {};

              $mdToast.show($mdToast.simple({
                  textContent: 'Password reset!',
                  action: 'Login'
                }))
                .then(function (response) {
                  if (response == 'ok') {
                    $state.go('login');
                  }
                });
            })
            .catch(function (response) {
              vm.forgotPasswordState.errors = response.data;
            })
            .finally(function () {
              vm.forgotPasswordState.loading = false;
            });
        };

        vm.activateAccount = function () {
          vm.activateAccountState.loading = true;
          vm.activateAccountState.errors = {};
          vm.activateAccountState.data.token = $state.params.token;

          UserService.activateAccount(vm.activateAccountState.data)
            .then(function (response) {
              vm.activateAccountState.activated = true;
              vm.activateAccountState.data = {};

              $mdToast.show($mdToast.simple({
                  textContent: 'Account activated!',
                  action: 'Login'
                }))
                .then(function (response) {
                  if (response == 'ok') {
                    $state.go('login');
                  }
                });
            })
            .catch(function (response) {
              vm.activateAccountState.errors = response.data;
            })
            .finally(function () {
              vm.activateAccountState.loading = false;
            });
        };

        vm.init();


        var timer = $interval(function() {
          if (SecurityService.isAuthenticated()) {
            vm.loadProjects();
          }
        }, AppConfig.UPDATE_INTERVAL);
        $scope.$on('$destroy', function() {
          // console.log('destroying sidenav recent project list timer');
          $interval.cancel(timer);
        });
      }]);

})(angular);