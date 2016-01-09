/* global $ */
/* global angular */
(function (angular) {
  'use strict';

  // MODULE DEFINITION
  angular.module('app', ['app.firebase', 'app.user', 'app.issue', 'app.project', 'ui.router', 'ngMaterial', 'ngAnimate', 'ngAria', 'ngSanitize', 'angular-loading-bar', 'md-sidenav-menu', 'angularMoment'])

  // measures against template caching
  // Credit: http://stackoverflow.com/a/27432167
    .config(['$provide', function ($provide) {
      // Set a suffix outside the decorator function 
      var cacheBuster = Date.now().toString();

      function templateFactoryDecorator($delegate) {
        var fromUrl = angular.bind($delegate, $delegate.fromUrl);
        $delegate.fromUrl = function (url, params) {
          if (url !== null && angular.isDefined(url) && angular.isString(url)) {
            url += (url.indexOf("?") === -1 ? "?" : "&");
            url += "v=" + cacheBuster;
          }

          return fromUrl(url, params);
        };

        return $delegate;
      }

      $provide.decorator('$templateFactory', ['$delegate', templateFactoryDecorator]);
    }])

    .run(function () {
      // remove preloader
      var preloader = $('#preloader').addClass('animated fadeOut');
      setTimeout(function (preloader) {
        preloader.remove()
      }, 1000, preloader);
    });

})(angular);