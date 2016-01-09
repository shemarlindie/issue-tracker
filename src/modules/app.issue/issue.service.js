(function (angular) {
  "use strict";

  angular.module('app.issue')
    .factory('IssueService', ['FirebaseService', '$firebaseArray', '$firebaseObject', 'User', 'ProjectService', '$q',
      function (FirebaseService, $firebaseArray, $firebaseObject, User, ProjectService, $q) {

        var service = {
          issues: undefined,

          connect: function () {
            if (this.issues === undefined) {
              console.log('connecting issue service');
              
              var ref = FirebaseService.getRef().child('issues');
              this.issues = $firebaseArray(ref);
            }
          },

          disconnect: function () {
            if (this.issues) {
              console.log('disconnecting issue service');

              this.issues.$destroy();
              this.issues = undefined;
            }
          },

          get: function (id) {
            service.connect();

            return this.issues.$loaded().then(function (issues) {
              return service.denormalize(issues.$getRecord(id));
            });
          },

          all: function () {
            service.connect();

            return this.issues.$loaded();
          },

          create: function (issue) {
            service.connect();
            issue.reported_by = User.getUid();
            var now = Date.now();
            issue.date_created = now;
            issue.date_updated = now;

            var nl = service.normalize(issue);

            return this.issues && this.issues.$add(nl);
          },

          update: function (issue) {
            service.connect();

            issue.date_updated = Date.now();

            var nl = service.normalize(issue);
            var updated = angular.extend(this.issues.$getRecord(nl.$id), nl);
            var index = this.issues.$indexFor(updated.$id);

            return this.issues && this.issues.$save(index);
          },

          delete: function (issue) {
            service.connect();

            var index = this.issues.$indexFor(issue.$id);

            return this.issues && this.issues.$remove(index);
          },

          addComment: function (issue, comment) {
            var now = Date.now();
            comment.date_added = now;
            comment.date_updated = now;
            comment.user = User.getUid();
            
            var iss = service.issues.$getRecord(issue.$id);
            if (!iss.comments) {
              iss.comments = [comment];
            }
            else {
              iss.comments.push(comment);
            }
            
            if (comment.status) {
              iss.status = comment.status;
            }
            
            return service.issues.$save(iss);
          },

          denormalize: function (issue) {
            var iss = angular.copy(issue);

            iss.project = {};
            ProjectService.get(issue.project)
              .then(function (project) {
                iss.project = project;
              });

            if (issue.date_due) {
              iss.date_due = new Date(issue.date_due);
            }

            iss.reported_by = undefined;
            if (issue.reported_by) {
              User.get(issue.reported_by)
                .then(function (user) {
                  iss.reported_by = user;
                });
            }

            iss.assigned_to = [];
            if (issue.assigned_to) {
              User.uidToObj(issue.assigned_to)
                .then(function (users) {
                  iss.assigned_to = users;
                });
            }

            iss.testers = [];
            if (issue.testers) {
              User.uidToObj(issue.testers)
                .then(function (users) {
                  iss.testers = users;
                });
            }

            iss.tags = [];
            if (issue.tags) {
              iss.tags = issue.tags.map(function (val, index, array) {
                return { name: val, exists: true };
              });
            }

            if (!issue.comments) {
              iss.comments = [];
            }
            else {
              iss.comments = iss.comments.map(function (val) {
                var uid = val.user;
                val.user = {};
                User.get(uid).then(function (user) {
                  val.user = user;
                });

                return val;
              });
            }

            return iss;
          },

          normalize: function (issue) {
            var iss = angular.copy(issue);

            iss.project = issue.project.$id;

            if (issue.date_due) {
              iss.date_due = issue.date_due.valueOf();
            }

            if (issue.reported_by) {
              iss.reported_by = issue.reported_by;
            }
            if (issue.assigned_to) {
              iss.assigned_to = issue.assigned_to.map(function (val, index, array) {
                return val.uid;
              });
            }
            if (issue.testers) {
              iss.testers = issue.testers.map(function (val, index, array) {
                return val.uid;
              });
            }
            if (issue.tags) {
              iss.tags = issue.tags.map(function (val, index, array) {
                return val.name;
              });
            }

            if (issue.comments && issue.comments.length) {
              iss.comments = issue.comments.map(function (val) {
                val.user = val.user.uid;

                return val;
              });
            }

            return iss;
          }
        }

        return service;
      }])

})(angular);