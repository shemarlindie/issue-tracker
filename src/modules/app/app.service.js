(function (angular) {
  'use strict';

  angular.module('app')
    .factory('AppConfig', function () {
      var service = {
        // API_URI: '/api/web',
        API_URI: 'https://django-api.shemarlindie.com/api',
        // API_URI: 'http://localhost:8000',
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
            var headers = {
              'Authorization': 'Basic ' + btoa(data.username + ':' + data.password)
            }
            return $http.post(API_URI + '/auth/login/', data, {headers: headers})
              .then(function (response) {
                service.setAuthData(response.data);

                return response;
              });
          },

          logout: function () {
            return $http.post(API_URI + '/auth/logout/', {})
                .finally(function () {
                  service.clearUserData();
                })
          },

          setAuthData: function (data) {
            // add token and user data to local storage
            service.setToken(data.token);
            service.setUser(data.user);
          },

          authCheck: function () {
            return $http.get(API_URI + '/auth/check/', {})
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
                access = user.is_superuser;
                break;

              case 'staff':
                access = user.is_staff;
                break;
            }

            return access;
          },

          userTypeIs: function(role) {
            var user = service.getUser()
            if (user) {
              if (role == 'superadmin') {
                return user.is_super_user
              }
              else if (role == 'staff') {
                return user.is_staff
              }
              else {
                return true
              }
            }

            return false;
          }
        };

        return service;
      }])
  ;

})(angular);