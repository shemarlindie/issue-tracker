// GULP NODE MODULE
var gulp = require('gulp');

// GULP PLUGINS
var plumber = require('gulp-plumber');
var less = require('gulp-less');
var minifyCss = require('gulp-minify-css');
var autoPrefixer = require('gulp-autoprefixer');
var uglify = require('gulp-uglify');
var sourcemaps = require('gulp-sourcemaps');
var rename = require('gulp-rename');
var concat = require('gulp-concat');

// UTILITIES
var del = require('del');

// BROWSER SYNC
var browserSync = require('browser-sync').create();

//--------------------------------------------------------
//  GULP TASKS
//--------------------------------------------------------

// SETTINGS
var DIST_DIR = './dist';
var COPY_FILES = [
  './src/api/**/*.*', // symfony project
  '!./src/api/var/cache/**/*.*',
  '!./src/api/var/logs/**/*.*',
  '!./src/api/var/sessions/**/*.*',

  './src/bower_components/**/*.*',
  './src/css/**/*.*',
  './src/favicon/**/*.*',
  './src/fonts/**/*.*',

  './src/modules/**/*.*',
  '!./src/modules/**/*.js', // exclude; copied during minification

  './src/browserconfig.xml',
  './src/favicon.ico',
  './src/index.html'
];

var CONCAT_JS = {
  'app.js': [
    './src/modules/**/*.module.js',
    './src/modules/**/*.js'
  ],

  'vendor.js': [
    './src/bower_components/jquery/dist/jquery.min.js',
    './src/bower_components/angular/angular.min.js',
    './src/bower_components/angular-animate/angular-animate.min.js',
    './src/bower_components/angular-aria/angular-aria.min.js',
    './src/bower_components/angular-sanitize/angular-sanitize.min.js',
    './src/bower_components/angular-ui-router/release/angular-ui-router.min.js',
    './src/bower_components/angular-material/angular-material.min.js',
    './src/bower_components/moment/min/moment.min.js',
    './src/bower_components/angular-moment/angular-moment.min.js',
    './src/bower_components/marked/lib/marked.js',
    './src/bower_components/angular-marked/dist/angular-marked.min.js',
    './src/bower_components/angular-loading-bar/build/loading-bar.min.js',
    './src/bower_components/a0-angular-storage/dist/angular-storage.min.js',
    './src/bower_components/angular-cache-buster/angular-cache-buster.js',
    './src/bower_components/angular-file-upload/dist/angular-file-upload.min.js',
    './src/bower_components/angular-material-data-table/dist/md-data-table.min.js'
  ]
};

var CONCAT_CSS = {
  'vendor.css': [
    'src/bower_components/animate.css/animate.min.css',
    'src/bower_components/angular-loading-bar/build/loading-bar.css',
    'src/bower_components/angular-material/angular-material.min.css',
    'src/bower_components/angular-material-data-table/dist/md-data-table.min.css',
    'src/css/material-icons.css',
    'src/modules/external/md-sidenav-menu/md-sidenav-menu.css'
  ]
};

var INJECT_FILES = {
  'dev': [
    './src/modules/**/*.module.js',
    './src/modules/**/*.js'
  ],

  'dist': [
    './src/bower_components/jquery/dist/jquery.min.js',
    './src/bower_components/angular/angular.min.js',
    './src/bower_components/angular-animate/angular-animate.min.js',
    './src/bower_components/angular-aria/angular-aria.min.js',
    './src/bower_components/angular-sanitize/angular-sanitize.min.js',
    './src/bower_components/angular-ui-router/release/angular-ui-router.min.js',
    './src/bower_components/angular-material/angular-material.min.js',
    './src/bower_components/moment/min/moment.min.js',
    './src/bower_components/angular-moment/angular-moment.min.js',
    './src/bower_components/marked/lib/marked.js',
    './src/bower_components/angular-marked/dist/angular-marked.min.js',
    './src/bower_components/angular-loading-bar/build/loading-bar.min.js',
    './src/bower_components/a0-angular-storage/dist/angular-storage.min.js',
    './src/bower_components/angular-cache-buster/angular-cache-buster.js',
    './src/bower_components/angular-file-upload/dist/angular-file-upload.min.js',
    './src/bower_components/angular-material-data-table/dist/md-data-table.min.js'
  ]
};

//  LESS COMPILE 
gulp.task('less', function () {
  gulp.src('./src/less/style.less')
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(less())
    .pipe(autoPrefixer({ browsers: ['> 5%', 'last 5 versions'], cascade: false }))
    .pipe(minifyCss())
    .pipe(sourcemaps.write('./sourcemaps'))
    .pipe(gulp.dest('./src/css'))
    .pipe(browserSync.stream());
});

gulp.task('clean', function () {
  del.sync(DIST_DIR);
});

gulp.task('copy', function () {
  gulp.src(COPY_FILES, { base: './src' })
    .pipe(gulp.dest(DIST_DIR));
});

gulp.task('minify-js', function () {
  gulp.src(DIST_DIR + '/app/**/*.js')
    .pipe(plumber())
    .pipe(uglify())
    .pipe(gulp.dest(DIST_DIR + '/app'));
});

gulp.task('concat', function() {
  for (var key in CONCAT_JS) {
    gulp.src(CONCAT_JS[key])
      .pipe(concat(key))
      .pipe(gulp.dest(DIST_DIR + '/app'));
  }

  for (var key in CONCAT_CSS) {
    gulp.src(CONCAT_CSS[key])
      .pipe(concat(key))
      .pipe(minifyCss())
      .pipe(gulp.dest(DIST_DIR + '/css'));
  }
});

//  BROWSER SYNC SERVER
gulp.task('browser-sync', function () {
  browserSync.init({
    server: {
      baseDir: './src'
    }
  });
});

//  WATCHES
gulp.task('watch', function () {
  gulp.watch('./src/less/**/*.less', ['less']);
  gulp.watch('./src/modules/**/*.js').on('change', browserSync.reload);
  gulp.watch('./src/modules/**/*.html').on('change', browserSync.reload);
});

// GULP DEFAULT TASK
gulp.task('default', ['build', 'watch']);

// BUILD, WATCH and AUTO-RELOAD
gulp.task('start', ['build', 'watch', 'browser-sync']);

// BUILD TASKS
gulp.task('build', ['less']);
gulp.task('dist', ['clean', 'less', 'minify-js', 'copy']);