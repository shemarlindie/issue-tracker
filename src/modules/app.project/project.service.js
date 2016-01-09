(function (angular) {
  "use strict";

  angular.module('app.project')
    .factory('ProjectService', ['FirebaseService', '$firebaseArray', '$firebaseObject', 'User',
      function (FirebaseService, $firebaseArray, $firebaseObject, User) {

        var service = {
          projects: undefined,

          issues: {

          },

          connect: function () {
            if (this.projects === undefined) {
              console.log('connecting project service');
              
              var ref = FirebaseService.getRef().child('projects');
              this.projects = $firebaseArray(ref);
            }
          },

          disconnect: function () {
            if (this.projects) {
              console.log('disconnecting project service');

              this.projects.$destroy();
              this.projects = undefined;
            }
          },

          get: function (id) {
            service.connect();

            return this.projects.$loaded().then(function (projects) {
              var p = projects.$getRecord(id);              
              var dnl = service.denormalize(p);
              
              return dnl;
            });
          },

          all: function () {
            service.connect();

            return this.projects.$loaded();
          },

          create: function (project) {
            service.connect();
            project.owner = User.getUid();

            var nl = service.normalize(project);

            return this.projects && this.projects.$add(nl);
          },

          update: function (project) {
            service.connect();

            var nl = service.normalize(project);
            
            var updated = angular.extend(this.projects.$getRecord(nl.$id), nl);  
            var index = this.projects.$indexFor(updated.$id);         

            return this.projects && this.projects.$save(index);
          },

          delete: function (project) {
            service.connect();
            
            var index = this.projects.$indexFor(project.$id); 
            
            return this.projects && this.projects.$remove(index);
          },

          getIssues: function (pid) {
            service.connect();

            if (!this.issues[pid]) {
              this.issues[pid] = $firebaseArray(FirebaseService.getRef().child('issues')
                .orderByChild('project')
                .equalTo(pid)
                );
            }

            return this.issues[pid].$loaded();
          },

          denormalize: function (project) {
            if (!project) return null;
            
            var proj = angular.copy(project);

            if (project.date_due) {
              proj.date_due = new Date(proj.date_due);
            }

            proj.collaborators = [];
            if (project.collaborators) {
              User.uidToObj(project.collaborators)
                .then(function (data) {
                  proj.collaborators = data;
                });
            }

            if (project.tags) {
              proj.tags = proj.tags.map(function (val) {
                return { name: val, exists: true };
              });
            }
            else {
              proj.tags = [];
            }

            return proj;
          },

          normalize: function (project) {
            if (!project) return null;
            
            var proj = angular.copy(project);

            if (project.date_due) {
              proj.date_due = project.date_due.valueOf();
            }

            if (project.collaborators) {
              proj.collaborators = project.collaborators.map(function (val, index, array) {
                return val.uid;
              });
            }

            if (project.tags) {
              proj.tags = project.tags.map(function (val, index, array) {
                return val.name;
              });
            }

            return proj;
          }
        }

        return service;
      }])

})(angular);