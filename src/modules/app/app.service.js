(function (angular) {
  'use strict';

  angular.module('app')
    .factory('AppConfig', function () {
      var service = {
        // API_URI: '/api/web',
        // API_URI: 'http://issue-tracker.version75.com/api/web',
        API_URI: 'http://localhost:8000',
        UPDATE_INTERVAL: 10000
      };

      return service;
    })
    .factory('SecurityService', ['AppConfig', '$rootScope', '$http', '$q', 'store',
      function (AppConfig, $rootScope, $http, $q, store) {
        var API_URI = AppConfig.API_URI;

        var service = {
          isAuthenticated: function () {
            return !!service.getToken();
          },

          login: function (data) {
            return $http.post(API_URI + '/auth/login_check', data)
              .then(function (response) {
                service.setAuthData(response.data);

                return response;
              });
          },

          logout: function () {
            var q = $q.defer();

            service.clearUserData();
            q.resolve();

            return q.promise;
          },

          setAuthData: function (data) {
            // add token and user data to local storage
            service.setToken(data.token);
            service.setUser(data.user);
          },

          authCheck: function () {
            return $http.post(API_URI + '/auth/auth_check', {})
              .then(function (response) {
                // update local user data
                service.setUser(response.data);

                return response;
              })
              .catch(function(response) {
                if (response.status == 401) {
                  // not authenticated
                  service.clearUserData();
                }
              });
          },

          refreshUser: function() {
            return service.authCheck();
          },

          getToken: function () {
            return store.get('token');
          },

          setToken: function (token) {
            store.set('token', token);
          },

          getUser: function () {
            return store.get('user');
          },

          setUser: function (user) {
            store.set('user', user);
            service.loadUser();
          },

          clearUserData: function () {
            store.remove('token');
            store.remove('user');
            $rootScope.user = undefined;
          },

          loadUser: function () {
            $rootScope.user = service.getUser();
          },

          userHasPermission: function (permission, user) {
            user = user || service.getUser();

            // if no user object is present then the user is anon.
            if (!user && (permission === 'anonymous')) { // only true if anon perm is being checked
              return true;
            }
            else if (!user) {
              return false;
            }

            var access = false;
            switch (permission) {
              case 'superadmin':
                access = user.user_type === 'ROLE_SUPER_ADMIN';
                break;

              case 'admin':
                access = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'].indexOf(user.user_type) !== -1;
                break;

              case 'staff':
                access = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_STAFF'].indexOf(user.user_type) !== -1;
                break;

              case 'provider':
                access = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_STAFF', 'ROLE_PROVIDER'].indexOf(user.user_type) !== -1;
                break;

              case 'customer':
                access = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_STAFF', 'ROLE_PROVIDER', 'ROLE_CUSTOMER'].indexOf(user.user_type) !== -1;
                break;
            }

            return access;
          },

          userTypeIs: function(role) {
            return service.getUser() && service.getUser().user_type === role;
          }
        };

        return service;
      }])
  ;

})(angular);