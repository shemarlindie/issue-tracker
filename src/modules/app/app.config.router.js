/* global angular */
(function (angular) {
  'use strict';

  angular.module('app')
    .config(['$stateProvider', '$urlRouterProvider', '$locationProvider',
      function ($stateProvider, $urlRouterProvider, $locationProvider) {
        // $locationProvider.html5Mode(true);

        $stateProvider
          .state('app', {
            url: '/',
            templateUrl: 'modules/app/views/app.layout.html',
            abstract: true,
            resolve: {
              auth: ['SecurityService',
                function (SecurityService) {
                  // console.log('refreshing user..');
                  
                  return SecurityService.refreshUser();
                }]
            },
            data: {
              hasSidenav: true,
              permissions: {
                except: ['anonymous'],
                redirectTo: 'login'
              }
            }
          })
          .state('app.home', {
            url: '^/projects',
            views: {
              'page@app': {
                templateUrl: 'modules/app.project/views/projects.html'
              }
            }
          })
          .state('app.projects', {
            url: '^/projects',
            views: {
              'page@app': {
                templateUrl: 'modules/app.project/views/projects.html'
              }
            }
          })
          .state('app.project-detail', {
            url: '^/project/{id:[0-9]+}',
            views: {
              'page@app': {
                templateUrl: 'modules/app.project/views/project.detail.html'
              }
            }
          })
          .state('app.project-edit', {
            url: '^/project/{id:[0-9]+}/edit',
            views: {
              'page@app': {
                templateUrl: 'modules/app.project/views/project.edit.html'
              }
            }
          })
          .state('app.project-create', {
            url: '^/project/create',
            views: {
              'page@app': {
                templateUrl: 'modules/app.project/views/project.edit.html'
              }
            }
          })
          .state('app.issue-detail', {
            url: '^/issue/:id',
            views: {
              'page@app': {
                templateUrl: 'modules/app.issue/views/issue.detail.html'
              }
            }
          })
          .state('app.issue-edit', {
            url: '^/issue/{id:[0-9]+}/edit',
            views: {
              'page@app': {
                templateUrl: 'modules/app.issue/views/issue.edit.html'
              }
            }
          })
          .state('app.issue-create', {
            url: '^/issue/create?project',
            views: {
              'page@app': {
                templateUrl: 'modules/app.issue/views/issue.edit.html'
              }
            }
          })
          .state('signup', {
            url: '/signup',
            templateUrl: 'modules/app.user/views/signup.html',
            data: {
              permissions: {
                only: ['anonymous']
              }
            }
          })
          .state('login', {
            url: '/login?redirect',
            templateUrl: 'modules/app.user/views/login.html',
            data: {permissions: {only: ['anonymous']}}
          })
          .state('password-reset', {
            url: '/password-reset',
            templateUrl: 'modules/app.user/views/password.reset.html',
            data: {permissions: {only: ['anonymous']}}
          })
          .state('account', {
            url: '/account?section',
            templateUrl: 'modules/app.user/views/account.html',
            reloadOnSearch: false,
            data: {
              hasSidenav: true,
              permissions: {
                except: ['anonymous'],
                redirectTo: 'login'
              }
            }
          });


        $urlRouterProvider.otherwise(function ($injector, $location) {
          var SecurityService = $injector.get('SecurityService');
          var $state = $injector.get('$state');
          if (SecurityService.isAuthenticated()) {
            return $state.go('app.home');
          }

          return $state.go('login');
        });
      }])

    // measures against AJAX request caching
    // Credit: http://stackoverflow.com/a/19771501
    .config(['$httpProvider', function ($httpProvider) {
      //initialize get if not there
      if (!$httpProvider.defaults.headers.get) {
        $httpProvider.defaults.headers.get = {};
      }

      // Answer edited to include suggestions from comments
      // because previous version of code introduced browser-related errors

      //disable IE ajax request caching
      $httpProvider.defaults.headers.get['If-Modified-Since'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
      // extra
      $httpProvider.defaults.headers.get['Cache-Control'] = 'no-cache';
      $httpProvider.defaults.headers.get['Pragma'] = 'no-cache';
    }])
    .run(['$rootScope', 'SecurityService', '$state', function ($rootScope, SecurityService, $state) {

      $rootScope.$on('$stateChangeStart',
        function (event, toState, toParams, fromState, fromParams, options) {
          // console.log('changing state...');

          var permissions = toState.data ? toState.data.permissions : undefined;


          // if (toState.requiresLogin && !User.isAuthenticated()) {
          //   event.preventDefault();
          //
          //   $state.go('login', {
          //     redirect: encodeURIComponent(JSON.stringify({
          //       name: toState.name,
          //       params: toParams
          //     }))
          //   });
          // }


          if (permissions) {
            if (permissions.only) {
              var allowed = false;

              for (var i = 0; i < permissions.only.length; i++) {
                if (SecurityService.userHasPermission(permissions.only[i])) {
                  allowed = true;
                  break;
                }
              }

              if (!allowed) {
                var redirect = permissions.redirectTo || 'login';
                event.preventDefault();
                $state.go(redirect);
              }
            }
            else if (permissions.except) {
              var allowed = true;

              for (var i = 0; i < permissions.except.length; i++) {
                if (SecurityService.userHasPermission(permissions.except[i])) {
                  allowed = false;
                  break;
                }
              }

              if (!allowed) {
                var redirect = permissions.redirectTo || 'login';
                event.preventDefault();
                $state.go(redirect);
              }
            }
            else if (permissions.specific) {
              var allowed = SecurityService.userTypeIs(permissions.specific);

              if (!allowed) {
                var redirect = permissions.redirectTo || 'login';
                event.preventDefault();
                $state.go(redirect);
              }
            }
          }
        });

    }])
  ;

})(angular);
