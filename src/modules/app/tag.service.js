(function (angular) {
  'use strict';

  angular.module('app')
    .factory('TagService', ['AppConfig', '$http', function (AppConfig, $http) {
      var API_URI = AppConfig.API_URI;

      var service = {
        all: function (options) {
          return $http.get(API_URI + '/tags/?' + $.param(options));
        },
        search: function (text) {
          return $http.get(API_URI + '/tags/?search=' + encodeURIComponent(text));
        },
      };

      return service;
    }])
  ;

})(angular);