/* global angular */
/* global Firebase */
(function (angular) {
  'use strict';

  angular.module('app.firebase')
    .factory('FirebaseService', ['FIREBASE_URL', function (FIREBASE_URL) {
      
      var service = {
        ref: undefined,
        
        getRef: function () {
          if (this.ref === undefined) {
            Firebase.goOnline();
            this.ref = new Firebase(FIREBASE_URL);
          }
          
          return this.ref;
        },
        
        unsetRef: function () {
          Firebase.goOffline();
          this.ref = undefined;
        },
        
        onAuth: function (callback) {
          this.getRef().onAuth(callback);
        },
        
        offAuth: function (callback) {
          this.getRef().offAuth(callback);
        }
      };

      return service;

    }]);

})(angular);