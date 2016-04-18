(function (angular) {
  'use strict';

  angular.module('app')
    /*
     * E.Marks V75 2016-03-09
     * Modified by S.Lindie V75 2016-3-20
     *
     * Access control
     * Allows elements to be removed from the DOM based on a user's roles
     */
    .directive('restrict', ['SecurityService',
      function (SecurityService) {

        return {
          restrict: 'A',
          multiElement: true,
          transclude: 'element',
          priority: 100000,
          terminal: true,
          $$tlb: true,
          link: function (scope, element, attrs, ctrl, transclude) {
            var userRoles = [];
            var allowedRoles = attrs.restrict.split(' ');

            var user = SecurityService.getUser();

            // not authenticated; add anon role
            if (!user) {
              userRoles.push('anonymous');
            }
            else {
              userRoles = user.simple_roles;
            }

            // check if user has required role
            var allowed = false;
            for (var i = 0; i < allowedRoles.length; i++) {
              if (userRoles.indexOf(allowedRoles[i]) !== -1) {
                allowed = true;
                break;
              }
            }

            if (allowed) {
              // replace comment node with target element
              element.replaceWith(transclude());
            }
            else {
              // remove target element
              element.remove();
            }
          }
        }
      }])
  // Credit: http://odetocode.com/blogs/scott/archive/2014/10/13/confirm-password-validation-in-angularjs.aspx
    .directive('compareTo', function () {
      return {
        restrict: '',
        require: "ngModel",
        scope: {
          otherModelValue: "=compareTo"
        },
        link: function (scope, elm, attrs, ctrl) {
          ctrl.$validators.compareTo = function (modelValue) {
            return modelValue === scope.otherModelValue;
          };

          scope.$watch("otherModelValue", function () {
            ctrl.$validate();
          });
        }
      };
    });

})(angular);
