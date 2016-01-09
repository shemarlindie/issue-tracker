(function (angular) {
  'use strict';
  
  angular.module('app')
    .filter('status', function () {
      return function (text, length, end) {
        var code = parseInt(text, 10);
        
        switch (code) {
          case 0:
            return 'Not Started';
          break;
          
          case 1:
            return 'In Design';          
          break;
          
          case 2:
            return 'In Development';          
          break;
          
          case 3:
            return 'Developed';          
          break;        
          
          default:
            return '';
        }        
      }
    })
  
})(angular);