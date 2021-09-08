!function(e){"use strict";e.module("app.issue").controller("IssueFeedCtrl",["$scope","$stateParams","$state","AppConfig","SecurityService","IssueService","UserService","ProjectService","$interval",function(t,s,r,i,o,n,l,a,u){var c=this,d={fixers:void 0,type:void 0,status:void 0,priority:void 0,sort_by:"date_created",sort_order:"-"};c.filters={},c.typeList=[],c.statusList=[],c.priorityList=[],c.issues={},c.query={limit:10,page:1},c.me=o.getUser(),c.init=function(){n.types().then(function(e){c.typeList=e.data.results}),n.statuses().then(function(e){c.statusList=e.data.results}),n.priorities().then(function(e){c.priorityList=e.data.results}),c.loadFilters(),c.loadIssues()},c.onPaginate=function(){c.loadIssues()},c.loadIssues=function(t,r){t=t||{},t=e.extend({},c.query,t),t=e.extend(t,c.filters),t.sort_by&&(t.ordering=t.sort_by,t.sort_order&&(t.ordering=t.sort_order+t.ordering)),delete t.sort_by,delete t.sort_order;for(var i in t)t[i]||delete t[i];t.project=s.id,c.promise=n.all(t,r).then(function(e){return c.issues=e.data,e})["catch"](function(e){console.log("unable to load issues",e)})},c.loadFilters=function(){localStorage.filterCache?c.filters=JSON.parse(localStorage.filterCache):c.resetFilters()},c.resetFilters=function(){return e.extend(c.filters,d),c.onFiltersChanged(),c.filters},c.onFiltersChanged=function(){localStorage.filterCache=JSON.stringify(c.filters),c.loadIssues()},c.isListFiltered=function(){return c.filters.fixers||c.filters.priority||c.filters.status||c.filters.type},c.init();var f=u(function(){c.loadIssues(null,!0)},i.UPDATE_INTERVAL);t.$on("$destroy",function(){u.cancel(f)})}])}(angular);
//# sourceMappingURL=../sourcemaps/app.issue/issue.feed.ctrl.js.map
