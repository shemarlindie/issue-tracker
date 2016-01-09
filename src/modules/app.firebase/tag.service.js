(function (angular) {
  "use strict";

  angular.module('app.firebase')
    .factory('TagService', ['FirebaseService', '$firebaseArray',
      function (FirebaseService, $firebaseArray) {

        var service = {
          tags: undefined,

          connect: function () {
            if (this.tags === undefined) {
              console.log('connecting tag service');
              
              this.tags = {
                project: $firebaseArray(FirebaseService.getRef().child('tags/project')),
                issue: $firebaseArray(FirebaseService.getRef().child('tags/issue'))
              }
            }
          },

          disconnect: function () {
            if (this.tags) {
              console.log('disconnecting tag service');

              this.tags.project.$destroy();
              this.tags.issue.$destroy();
              this.tags = undefined;
            }
          },

          projectTags: function () {
            service.connect();

            return this.tags.project.$loaded();
          },

          issueTags: function () {
            service.connect();

            return this.tags.issue.$loaded();
          },

          addToProjectTags: function (tagArr) {
            service.connect();

            for (var i = 0; i < tagArr.length; i++) {
              if (!tagArr[i].exists) {
                this.tags.project.$add(tagArr[i].name);
              }
            }
          },

          addToIssueTags: function (tagArr) {
            service.connect();

            for (var i = 0; i < tagArr.length; i++) {
              if (!tagArr[i].exists) {
                this.tags.issue.$add(tagArr[i].name);
              }
            }
          }
        }

        return service;
      }])

})(angular);