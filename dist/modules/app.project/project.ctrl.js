!function(e){"use strict";e.module("app.project").controller("ProjectCtrl",["$scope","$state","SecurityService","UserService","ProjectService","TagService","$stateParams","$mdDialog",function(t,n,r,o,c,a,i,u){var p=this;p.init=function(){p.editing=i.id,p.editing?c.get(p.editing).then(function(e){p.project=e.data,t.$parent.project=p.project}):(p.project={tags:[],collaborators:[]},p.project.collaborators.push(r.getUser())),p.userSearch={search:function(e){return o.search(e).then(function(e){return e.data.results})}},p.tagSearch={transformChip:function(t){return e.isObject(t)?t:{name:t,exists:!1}},search:function(e,t,n){return e.name.indexOf(p.tagSearch.searchText.toLowerCase())>=0}}},p.create=function(){c.create(p.project).then(function(e){n.go("app.project-detail",{id:e.data.id})})},p.update=function(){c.update(p.project).then(function(e){n.go("app.project-detail",{id:e.data.id})},function(e){console.log("update error",e)})},p["delete"]=function(){var e=u.confirm({htmlContent:"<p><b>"+p.project.name+"</b> will be deleted.</p>This cannot be undone.",ok:"Delete",cancel:"Cancel"});u.show(e).then(function(){c["delete"](p.project).then(function(e){n.go("app.home")})})},p.init()}])}(angular);
//# sourceMappingURL=../sourcemaps/app.project/project.ctrl.js.map
