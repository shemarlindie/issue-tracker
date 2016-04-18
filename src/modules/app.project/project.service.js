(function (angular) {
  "use strict";

  angular.module('app.project')
    .factory('ProjectService', ['AppConfig', '$http',
      function (AppConfig, $http) {
        var API_URI = AppConfig.API_URI;

        var service = {
          get: function (id) {
            return $http.get(API_URI + '/projects/' + id)
              .then(function (response) {
                response.data = service.unserialize(response.data);

                return response;
              });
          },

          all: function (options) {
            options = options || {};
            return $http.get(API_URI + '/projects?' + $.param(options));
          },

          create: function (project) {
            return $http.post(API_URI + '/projects', service.serialize(project));
          },

          update: function (project) {
            return $http.patch(API_URI + '/projects/' + project.id, service.serialize(project));
          },

          delete: function (project) {
            return $http.delete(API_URI + '/projects/' + project.id);
          },

          search: function (text) {
            return $http.get(API_URI + '/projects/search?query=' + encodeURIComponent(text));
          },

          serialize: function (project) {
            project = angular.copy(project);
            project.collaborators = project.collaborators.map(function (user) {
              return user.id;
            });

            return project;
          },

          unserialize: function (project) {
            if (project.date_due) {
              project.date_due = new Date(project.date_due);
            }

            return project;
          }
        };

        return service;
      }])

})(angular);