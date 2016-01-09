/**
 * md-sidenav-menu
 * by Shemar Lindie
 * 
 * Version: 0.0.1
 * 
 * A list of collapsable, non-collapsable and headered menu links for use with md-sidenav.
 * 
 * Adapted from the side navigation menu on the angular-material docs site.
 * Credit goes to the people working on that:
 * https://github.com/angular/material 
 * 
 */

/* global angular */
(function (angular) {
  'use strict';

  var DEV_MODE = false;

  angular.module('md-sidenav-menu', ['ngMaterial'])

    .directive('mdSidenavMenu', function () {
      return {
        restrict: 'AE',
        templateUrl: 'partials/md-sidenav-menu.tpl.html',
        scope: {
          sections: '=',
          onSelect: '=',
          selectedSection: '='
        },
        controller: ['$scope', function ($scope) {
          $scope.isSelected = function (section) {
            return $scope.selectedSection === section;
          }

          $scope.selectSection = function (section) {
            $scope.selectedSection = section;
            if ((typeof $scope.onSelect) === 'function') {
              $scope.onSelect($scope.selectedSection);
            }
          }
        }]
      };
    })

    .directive('menuLink', function () {
      return {
        templateUrl: 'partials/menu-link.tpl.html'
      };
    })

    .directive('menuToggle', ['$timeout', function ($timeout) {
      return {
        templateUrl: 'partials/menu-toggle.tpl.html',
        link: function ($scope, $element) {
          $scope.isOpen = false;

          $scope.toggle = function () {
            $scope.isOpen = !$scope.isOpen;
          };

          $scope.$watch(function () {
            return $scope.isOpen;
          },
            function (open) {
              var $ul = $element.find('ul');
              var targetHeight = open ? getTargetHeight() : 0;
              $timeout(function () {
                $ul.css({ height: targetHeight + 'px' });
              }, 0, false);

              function getTargetHeight() {
                var targetHeight;
                $ul.addClass('no-transition');
                $ul.css('height', '');
                targetHeight = $ul.prop('clientHeight');
                $ul.css('height', 0);
                $ul.removeClass('no-transition');
                return targetHeight;
              }
            });

          // TODO: fix this
          var parentNode = $element[0].parentNode.parentNode.parentNode;
          if (parentNode.classList.contains('parent-list-item')) {
            var heading = parentNode.querySelector('h2');
            $element[0].firstChild.setAttribute('aria-describedby', heading.id);
          }
        }
      };
    }])
  ;


  if (!DEV_MODE) {
    angular.module('md-sidenav-menu')
      .run(['$templateCache', function ($templateCache) {
        $templateCache.put('partials/md-sidenav-menu.tpl.html',
          '<ul class="md-sidenav-menu">\n' +
          '  <li ng-repeat="section in sections" class="parent-list-item">\n' +
          '    <h2 class="menu-heading md-subhead" ng-if="section.type === \'heading\'">{{section.name}}</h2>\n' +
          '    <menu-link ng-click="selectSection(section)" ng-if="!section.type || section.type === \'link\'"></menu-link>\n' +
          '    <menu-toggle section="section" ng-if="section.type === \'toggle\'"></menu-toggle>\n' +
          '    <ul ng-if="section.children" class="menu-nested-list">\n' +
          '      <li ng-repeat="section in section.children">\n' +
          '        <menu-link ng-if="!section.type || section.type === \'link\'"></menu-link>\n' +
          '        <menu-toggle ng-if="section.type === \'toggle\'"></menu-toggle>\n' +
          '      </li>\n' +
          '    </ul>\n' +
          '  </li>\n' +
          '</ul>\n' +
          '');
      }])
      
      .run(['$templateCache', function ($templateCache) {
        $templateCache.put('partials/menu-link.tpl.html',
          '<md-button ng-class="{\'active\' : false}" aria-label="{{section.name}}" ng-click="selectSection(section)">{{section.name}}</md-button>\n' +
          '');
      }])

      .run(['$templateCache', function ($templateCache) {
        $templateCache.put('partials/menu-toggle.tpl.html',
          '<div ng-class="{\'active\': isOpen}">\n' +
          '  <md-button class="md-button-toggle" ng-click="toggle()" aria-label="{{section.name}}">\n' +
          '    <div flex layout="row">\n' +
          '      <span ng-bind="section.name"></span>\n' +
          '      <span flex></span>\n' +
          '      <span aria-hidden="true" class="md-toggle-icon" ng-class="{\'toggled\' : isOpen}">\n' +
          '        <md-icon md-svg-src="md-toggle-arrow"></md-icon>\n' +
          '      </span>\n' +
          '    </div>\n' +
          '  </md-button>\n' +
          '  <ul class="menu-toggle-list">\n' +
          '    <li ng-repeat="section in section.pages">\n' +
          '      <menu-link></menu-link>\n' +
          '    </li>\n' +
          '  </ul>\n' +
          '</div>\n' +
          '');
      }])
    ;
  }

})(angular);