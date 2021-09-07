(function (angular) {
  "use strict";

  angular.module('app.issue')
    .factory('CommentService', ['AppConfig', '$http',
      function (AppConfig, $http) {
        var API_URI = AppConfig.API_URI;

        var service = {
          get: function (id) {
            return $http.get(API_URI + '/comments/' + id + '/');
          },

          all: function (options, ignoreLoadingBar) {
            options = options || {};
            return $http.get(API_URI + '/comments/?' + $.param(options), {ignoreLoadingBar: ignoreLoadingBar});
          },

          create: function (comment) {
            return $http.post(API_URI + '/comments/', service.serialize(comment));
          },

          update: function (comment) {
            return $http.patch(API_URI + '/comments/' + comment.id + '/', service.serialize(comment));
          },

          delete: function (comment) {
            return $http.delete(API_URI + '/comments/' + comment.id + '/', {});
          },

          serialize: function(comment) {
            comment = angular.copy(comment);

            if (comment.status) {
              comment.status_id = comment.status.id;
              delete comment.status;
            }

            if (comment.issue) {
              comment.issue = comment.issue.id;
            }

            return comment;
          }
        };

        return service;
      }])

})(angular);