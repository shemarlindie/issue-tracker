(function (angular) {
  'use strict';

  angular.module('app')
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
