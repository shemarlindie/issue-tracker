(function (angular) {
  'use strict';

  angular.module('app')
    .controller('AppCtrl', ['FirebaseService', 'ConstantService', 'User', 'ProjectService', 'IssueService', 'TagService', '$scope', '$state', '$location', '$mdMedia', '$mdSidenav',
      function (FirebaseService, ConstantService, User, ProjectService, IssueService, TagService, $scope, $state, $location, $mdMedia, $mdSidenav) {
        var vm = this; // view model
        
        vm.sidenav = {
          sections: [
            {
              name: 'Projects',
              type: 'heading',
              children: []
            }
          ],

          selectedSection: undefined
        }

        vm.onSectionSelected = function (project) {
          vm.toggleSidenav();
          $state.go('app.project-detail', { id: project.$id });
        }

        vm.user = undefined;

        var authCallback = function (authData) {
          if (authData) {
            User.get().then(function (user) {
              vm.user = user;
              ProjectService.all().then(function (projects) {
                vm.sidenav.sections[0].children = projects;
              });
            });
          }
          else {
            ConstantService.disconnect();
            ProjectService.disconnect();
            IssueService.disconnect();
            TagService.disconnect();          
            vm.user = undefined;
          }
        }

        FirebaseService.onAuth(authCallback);

        $scope.$on('$destroy', function () {
          FirebaseService.offAuth(authCallback);
        });

        vm.logout = function () {
          User.logout();
        }

        vm.hasSidenav = function () {
          return $state.current.hasSidenav || $state.includes('app');
        }

        vm.showSidenavToggle = function () {
          return $mdMedia('xs') || $mdMedia('sm');
        }

        vm.toggleSidenav = function () {
          $mdSidenav('sidenav-main').toggle();
        }
      }]);

})(angular);