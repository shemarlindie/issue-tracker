(function (angular) {
  "use strict";

  angular.module('app.issue')
    .factory('IssueService', ['AppConfig', '$http',
      function (AppConfig, $http) {
        var API_URI = AppConfig.API_URI;

        var service = {
          get: function (id) {
            return $http.get(API_URI + '/issues/' + id)
              .then(function (response) {
                response.data = service.unserialize(response.data);

                return response;
              });
          },

          all: function (options, ignoreLoadingBar) {
            options = options || {};
            return $http.get(API_URI + '/issues?' + $.param(options), {ignoreLoadingBar: ignoreLoadingBar});
          },

          create: function (issue) {
            return $http.post(API_URI + '/issues', service.serialize(issue));
          },

          update: function (issue) {
            return $http.patch(API_URI + '/issues/' + issue.id, service.serialize(issue));
          },

          delete: function (issue) {
            return $http.delete(API_URI + '/issues/' + issue.id);
          },

          types: function() {
            return $http.get(API_URI + '/issues/types');
          },

          priorities: function() {
            return $http.get(API_URI + '/issues/priorities');
          },

          statuses: function() {
            return $http.get(API_URI + '/issues/statuses');
          },

          serialize: function(issue) {
            issue = angular.copy(issue);

            issue.project_id = issue.project.id;
            if (!issue.date_due && issue.project.date_due) {
              issue.date_due = issue.project.date_due;
            }
            delete issue.project;

            issue.testers = issue.testers.map(function (user) {
              return user.id;
            });

            issue.fixers = issue.fixers.map(function (user) {
              return user.id;
            });

            return issue;
          },

          unserialize: function (issue) {
            if (issue.date_due) {
              issue.date_due = new Date(issue.date_due);
            }

            return issue;
          }
        };

        return service;
      }])

})(angular);