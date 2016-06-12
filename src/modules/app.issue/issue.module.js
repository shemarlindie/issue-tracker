/* global angular */
(function (angular) {
  'use strict';
  
  // MODULE DEFINITION
  angular.module('app.issue', ['hc.marked'])
    .config(['markedProvider', function (markedProvider) {
      markedProvider.setOptions({
        gfm: true,
        breaks: true,
        highlight: function (code, lang) {
          if (lang) {
            return hljs.highlight(lang, code, true).value;
          } else {
            return hljs.highlightAuto(code).value;
          }
        }
      });
    }]);

})(angular);