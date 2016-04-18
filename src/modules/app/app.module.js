/* global $ */
/* global angular */
(function (angular) {
  'use strict';

  // MODULE DEFINITION
  angular.module('app', [
      'ui.router',
      'ngMaterial',
      'ngAnimate',
      'ngAria',
      'ngSanitize',
      'angular-loading-bar',
      'md-sidenav-menu',
      'angularMoment',
      'angular-storage',
      'ngCacheBuster',
      'angularFileUpload',
      'md.data.table',

      'app.user',
      'app.issue',
      'app.project'
    ])
    // add token header to each request
    .config(['$httpProvider', function ($httpProvider) {
      $httpProvider.interceptors.push(['store', function (store) {
        return {
          request: function (config) {
            var token = store.get('token');

            if (token) {
              config.headers['Authorization'] = 'Bearer ' + token;
            }

            return config;
          }
        }
      }]);
    }])
    // ngCacheBuster config
    .config(['httpRequestInterceptorCacheBusterProvider',
      function (httpRequestInterceptorCacheBusterProvider) {
        httpRequestInterceptorCacheBusterProvider.setMatchlist([/.*views\/.+\.html.*/], true);
      }])
    .run(['$rootScope', '$state', 'SecurityService', function ($rootScope, $state, SecurityService) {
      SecurityService.loadUser();

      // refresh user object on app start
      SecurityService.refreshUser();

      $rootScope.$state = $state;
    }])
    .run(function () {
      // remove preloader
      var preloader = $('#preloader').addClass('animated fadeOut');
      setTimeout(function (preloader) {
        preloader.remove()
      }, 1000, preloader);
    });

})(angular);