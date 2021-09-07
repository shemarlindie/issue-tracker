(function (angular) {
  "use strict";

  angular.module('app.issue')
    .factory('IssueService', ['AppConfig', '$http', '$q',
      function (AppConfig, $http, $q) {
        var API_URI = AppConfig.API_URI;

        var service = {
          CACHE: {
            types: undefined,
            statuses: undefined,
            priorities: undefined
          },

          get: function (id) {
            return $http.get(API_URI + '/issues/' + id + '/')
              .then(function (response) {
                response.data = service.unserialize(response.data);

                return response;
              });
          },

          all: function (options, ignoreLoadingBar) {
            options = options || {};
            return $http.get(API_URI + '/issues/?' + $.param(options), {ignoreLoadingBar: ignoreLoadingBar});
          },

          create: function (issue) {
            return $http.post(API_URI + '/issues/', service.serialize(issue));
          },

          update: function (issue) {
            return $http.patch(API_URI + '/issues/' + issue.id + '/', service.serialize(issue));
          },

          delete: function (issue) {
            return $http.delete(API_URI + '/issues/' + issue.id + '/');
          },

          types: function () {
            if (!service.CACHE.types) {
              return $http.get(API_URI + '/issue-types/?limit=100')
                .then(function (response) {
                  service.CACHE.types = response.data.results;

                  return response;
                });
            }

            return $q.when({data: {results: service.CACHE.types}});
          },

          priorities: function () {
            if (!service.CACHE.priorities) {
              return $http.get(API_URI + '/issue-priorities/?limit=100')
                .then(function (response) {
                  service.CACHE.priorities = response.data.results;

                  return response;
                });
            }

            return $q.when({data: {results: service.CACHE.priorities}});
          },

          statuses: function () {
            if (!service.CACHE.statuses) {
              return $http.get(API_URI + '/issue-statuses/?limit=100')
                .then(function (response) {
                  service.CACHE.statuses = response.data.results;

                  return response;
                });
            }

            return $q.when({data: {results: service.CACHE.statuses}});
          },

          serialize: function (issue) {
            issue = angular.copy(issue);

            issue.project_id = issue.project.id;
            if (!issue.date_due && issue.project.date_due) {
              issue.date_due = issue.project.date_due;
            }

            issue.testers_ids = issue.testers.map(function (user) {
              return user.id;
            });
            delete issue.testers

            issue.fixers_ids = issue.fixers.map(function (user) {
              return user.id;
            });
            delete issue.fixers

            issue.tags_ids = issue.tags.map(function (tag) {
              return tag.id;
            });
            delete issue.tags

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