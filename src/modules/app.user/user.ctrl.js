/* global angular */
(function (angular) {
  'use strict';

  angular.module('app.user')
    .controller('UserCtrl', ['UserService', '$state', '$stateParams', '$scope', '$mdDialog', '$mdSidenav', '$location',
      function (UserService, $state, $stateParams, $scope, $mdDialog, $mdSidenav, $location) {
        var vm = this; // view model
        
        vm.sidenav = {
          sections: [
            {
              name: 'Account Settings',
              type: 'heading',
              children: [
                {
                  name: 'Profile',
                  url: 'modules/app.user/views/account.profile.html',
                  type: 'link'
                },
                {
                  name: 'Security',
                  url: 'modules/app.user/views/account.security.html',
                  type: 'link'
                }
              ]
            }
          ],

          selectedSection: undefined
        };

        vm.firstLoad = true;

        vm.signingUp = false;
        vm.loggingIn = false;
        vm.updatingProifle = false;
        vm.changingEmail = false;
        vm.removingAcccount = false;
        vm.changingPassword = false;
        vm.resettingPassword = false;

        vm.signupData = {};
        vm.loginData = {
          rememberMe: true
        };
        vm.passwordResetData = {};

        vm.loginError = undefined;
        vm.signupError = undefined;
        vm.profileError = undefined;
        vm.emailError = undefined;
        vm.removeAccountError = undefined;
        vm.passwordChangeError = undefined;
        vm.passwordResetError = undefined;

        vm.profileMsg = undefined;
        vm.emailMsg = undefined;
        vm.passwordChangeMsg = undefined;
        vm.passwordResetMsg = undefined;

        vm.userEdit = {};
        vm.userDelete = {};
        vm.passwordChange = {};
        vm.user = undefined;

        vm.init = function () {
          // select section specified by the url on first load
          var secIndex = $location.search()['section'];
          if (secIndex >= 0 && secIndex < vm.sidenav.sections[0].children.length) {
            vm.onSectionSelected(vm.sidenav.sections[0].children[secIndex]);
          }
          else {
            vm.onSectionSelected(vm.sidenav.sections[0].children[0]);
          }

          vm.firstLoad = false;
        };

        vm.saveProfile = function () {
          vm.updatingProfile = true;
          vm.profileError = undefined;
          vm.profileMsg = undefined;

          vm.user.$save()
            .then(function (ref) {
              vm.profileEditForm.$setPristine();
              vm.profileEditForm.$setUntouched();
              vm.profileMsg = 'Profile updated.';
            })
            .catch(function (error) {
              vm.profileError = error;
            })
            .finally(function () {
              vm.updatingProfile = false;
            });
        };

        vm.changeEmail = function () {
          vm.changingEmail = true;
          vm.emailError = undefined;
          vm.emailMsg = undefined;

          var data = {
            oldEmail: vm.user.email,
            newEmail: vm.userEdit.newEmail,
            password: vm.userEdit.password
          };

          User.changeEmail(data)
            .then(function (data) {
              vm.userEdit = {};
              vm.changeEmailForm.$setPristine();
              vm.changeEmailForm.$setUntouched();
              vm.emailMsg = 'Account email changed from to ' + vm.user.email + '.'
            })
            .catch(function (error) {
              vm.emailError = error;
            })
            .finally(function () {
              vm.changingEmail = false;
            });
        };

        vm.removeAccount = function () {
          // confirm danger action
          var confDialog = $mdDialog.confirm()
            .htmlContent('Your Issue Tracker account, <b>' + (vm.user ? vm.user.email : '') + '</b>, will be deleted. This action is not reversable. Continue?')
            .ok('Delete My Account')
            .cancel('Cancel');

          $mdDialog.show(confDialog)
            .then(function () {
              // user about to lose privilages; stop listening for data
              IssueService.disconnect();
          
              // remove user
              vm.removingAccount = true;
              vm.removeAccountError = undefined;
              var data = {
                email: vm.user.email,
                password: vm.userDelete.password
              };
              User.remove(data)
                .then(function () {
                  vm.userDelete = {};
                  console.log('account removed');
                })
                .catch(function (error) {
                  vm.removeAccountError = error;
                })
                .finally(function () {
                  vm.removingAccount = false;
                });
            });
        };

        vm.changePassword = function () {
          vm.changingPassword = true;
          vm.passwordChangeError = undefined;
          vm.passwordChange.email = vm.user.email;

          User.changePassword(vm.passwordChange)
            .then(function () {
              vm.passwordChange = {};
              vm.changePasswordForm.$setPristine();
              vm.changePasswordForm.$setUntouched();
              vm.passwordChangeMsg = 'Password changed.';
            })
            .catch(function (error) {
              vm.passwordChangeError = error;
            })
            .finally(function () {
              vm.changingPassword = false;
            });
        };

        vm.resetPassword = function () {
          vm.resettingPassword = true;
          vm.passwordResetError = undefined;
          vm.passwordResetMsg = undefined;

          User.resetPassword(vm.passwordResetData)
            .then(function () {
              vm.passwordResetForm.$setPristine();
              vm.passwordResetForm.$setUntouched();
              vm.passwordResetMsg = 'Password reset email sent to ' + vm.passwordResetData.email;
              vm.passwordResetData = {};
            })
            .catch(function (error) {
              vm.passwordResetError = error;
            })
            .finally(function () {
              vm.resettingPassword = false;
            });
        };

        vm.signup = function () {
          vm.signingUp = true;
          User.create(vm.signupData)
            .then(function (data) {
              $state.go('app.home');
            })
            .catch(function (error) {
              vm.signupError = error;
            })
            .finally(function () {
              vm.signingUp = false;
            });
        };

        vm.login = function () {
          vm.loggingIn = true;
          User.login(vm.loginData)
            .then(function (data) {
              if ($stateParams.redirect) {
                var state = JSON.parse(decodeURIComponent($stateParams.redirect));
                $state.go(state.name, state.params);
              }
              else {
                $state.go('app.home');
              }
            })
            .catch(function (error) {
              vm.loginError = error;
            })
            .finally(function () {
              vm.loggingIn = false;
            });
        };

        vm.logout = function () {
          User.logout();
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

        vm.onSectionSelected = function (section) {
          vm.sidenav.selectedSection = section;
          
          // close sidenav if not locked open
          if (!vm.firstLoad) { // prevent exception caused when view has not yet been initialized
            $mdSidenav('sidenav-main').then(function (sidenav) {
              sidenav.close();
            });
          }
          
          // update url to remember section
          var secIndex = vm.sidenav.sections[0].children.indexOf(section);
          $state.go('.', { section: secIndex > -1 ? secIndex : 0 }, { notify: false });
        };


        // ---------------------
        // INITIALIZATION
        // ---------------------
        vm.init();
      }]);

})(angular);