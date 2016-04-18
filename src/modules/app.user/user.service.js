(function (angular) {
  'use strict';

  angular.module('app.user')
    .factory('UserService', ['AppConfig', 'SecurityService', '$http', 'FileUploader',
      function (AppConfig, SecurityService, $http, FileUploader) {
        var API_URI = AppConfig.API_URI;

        var service = {
          get: function (id) {
            return $http.get(API_URI + '/users/' + id);
          },

          all: function (options) {
            return $http.get(API_URI + '/users?' + $.param(options));
          },

          create: function (user) {
            return $http.post(API_URI + '/users', user);
          },

          update: function (user) {
            return $http.patch(API_URI + '/users/' + user.id, user);
          },

          delete: function (user) {
            return $http.delete(API_URI + '/users/' + user.id);
          },

          search: function(text) {
            return $http.get(API_URI + '/users/search?query=' + encodeURIComponent(text));
          },

          updateProfile: function (user) {
            return $http.post(API_URI + '/users/profile', user);
          },

          changePassword: function (user, data) {
            return $http.post(API_URI + '/users/' + user.id + '/change-password', data);
          },

          forgotPassword: function (data) {
            return $http.post(API_URI + '/users/forgot-password', data);
          },

          resetPassword: function (data) {
            return $http.post(API_URI + '/users/reset-password', data);
          },

          activateAccount: function (data) {
            return $http.post(API_URI + '/users/activate-account', data);
          },

          checkname: function (username) {
            return $http.get(API_URI + '/users/check-username/' + encodeURIComponent(username));
          },

          getPhotoUploadUrl: function (user) {
            return API_URI + '/photos/user/' + user.id;
          },

          getPhotoUploader: function (user) {
            var uploader = new FileUploader({
              url: service.getPhotoUploadUrl(user),
              removeAfterUpload: true,
              queueLimit: 1,
              withCredentials: true,
              headers: {
                Authorization: 'Bearer ' + SecurityService.getToken()
              }
            });

            uploader.filters.push({
              name: 'imageFilter',
              fn: function (item /*{File|FileLikeObject}*/, options) {
                var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
              }
            });

            return uploader;
          },

          removePhoto: function (user) {
            return $http.delete(API_URI + '/photos/user/' + user.id);
          }
        };

        return service;
      }])
  ;

})(angular);