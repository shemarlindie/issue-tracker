!function(t){"use strict";t.module("app").factory("TagService",["AppConfig","$http",function(t,n){var e=t.API_URI+"/issue",r={all:function(t){return n.get(e+"/tags/?"+$.param(t))},search:function(t){return n.get(e+"/tags/?search="+encodeURIComponent(t))}};return r}])}(angular);
//# sourceMappingURL=../sourcemaps/app/tag.service.js.map
