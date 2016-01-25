/* global angular */
(function (angular) {
  'use strict';

  angular.module('app')
    .config(['$stateProvider', '$urlRouterProvider',
      function ($stateProvider, $urlRouterProvider) {

        $stateProvider
          .state('app', {
            url: '/',
            templateUrl: 'modules/app/views/app.layout.html',
            abstract: true,
            requiresLogin: true,
            hasSidenav: true,
            resolve: {
              auth: ['FirebaseService', '$firebaseAuth', 'ConstantService',
                function (FirebaseService, $firebaseAuth, ConstantService) {
                  return $firebaseAuth(FirebaseService.getRef()).$requireAuth()
                    .then(function (auth) {
                      if (auth) {
                        ConstantService.connect();
                      }

                      return auth;
                    });
                }]
            }
          })
          .state('app.project-detail', {
            url: '^/project/:id/view',
            requiresLogin: true,
            views: {
              'page@app': {
                templateUrl: 'modules/app.project/views/project.detail.html'
              }
            }
          })
          .state('app.project-edit', {
            url: '^/project/:id/edit',
            requiresLogin: true,
            views: {
              'page@app': {
                templateUrl: 'modules/app.project/views/project.edit.html'
              }
            }
          })
          .state('app.project-create', {
            url: '^/project/create',
            requiresLogin: true,
            views: {
              'page@app': {
                templateUrl: 'modules/app.project/views/project.edit.html'
              }
            }
          })
          .state('app.issues', {
            url: '^/issues',
            requiresLogin: true,
            views: {
              'page@app': {
                templateUrl: 'modules/app.issue/views/issues.html'
              }
            }
          })
          .state('app.issue-detail', {
            url: '^/issue/:id/view',
            requiresLogin: true,
            views: {
              'page@app': {
                templateUrl: 'modules/app.issue/views/issue.detail.html'
              }
            }
          })
          .state('app.issue-edit', {
            url: '^/issue/:id/edit',
            requiresLogin: true,
            views: {
              'page@app': {
                templateUrl: 'modules/app.issue/views/issue.edit.html'
              }
            }
          })
          .state('app.issue-create', {
            url: '^/issue/create?project',
            requiresLogin: true,
            views: {
              'page@app': {
                templateUrl: 'modules/app.issue/views/issue.edit.html'
              }
            }
          })
          .state('signup', {
            url: '/signup',
            templateUrl: 'modules/app.user/views/signup.html',
            notWhileLoggedIn: true
          })
          .state('login', {
            url: '/login?redirect',
            templateUrl: 'modules/app.user/views/login.html',
            notWhileLoggedIn: true
          })
          .state('password-reset', {
            url: '/password-reset',
            templateUrl: 'modules/app.user/views/password.reset.html',
            notWhileLoggedIn: true
          })
          .state('account', {
            url: '/account?section',
            templateUrl: 'modules/app.user/views/account.html',
            requiresLogin: true,
            hasSidenav: true,
            reloadOnSearch: false
          });


        $urlRouterProvider.otherwise(function ($injector, $location) {
          var User = $injector.get('User');
          var $state = $injector.get('$state');
          if (User.isAuthenticated()) {
            return $state.go('app.issues');
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

    .run(['$rootScope', '$state', 'User',
      function ($rootScope, $state, User) {
        $rootScope.$on('$stateChangeStart',
          function (event, toState, toParams, fromState, fromParams) {
            if (toState.requiresLogin && !User.isAuthenticated()) {
              event.preventDefault();

              $state.go('login', {
                redirect: encodeURIComponent(JSON.stringify({
                  name: toState.name,
                  params: toParams
                }))
              });
            }

            if (toState.notWhileLoggedIn && User.isAuthenticated()) {
              event.preventDefault();
              $state.go('app.issues');
            }
          })
      }]);

})(angular);
