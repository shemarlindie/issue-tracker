(function (angular) {
  "use strict";

  angular.module('app.firebase')
    .factory('ConstantService', ['FirebaseService', '$firebaseArray',
      function (FirebaseService, $firebaseArray) {

        var service = {
          constants: undefined,

          connect: function () {
            if (this.constants === undefined) {
              console.log('connecting constant service');
              
              this.constants = {
                priority: $firebaseArray(FirebaseService.getRef().child('constants/priority')),
                type: $firebaseArray(FirebaseService.getRef().child('constants/type')),
                status: $firebaseArray(FirebaseService.getRef().child('constants/status'))
              }
            }
          },

          disconnect: function () {
            if (this.constants) {
              console.log('disconnecting constant service');

              this.constants.priority.$destroy();
              this.constants.type.$destroy();
              this.constants.status.$destroy();
              this.constants = undefined;
            }
          },          
        }

        return service;
      }])

})(angular);