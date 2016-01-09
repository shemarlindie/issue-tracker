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
  gulp.src('./src/modules/**/*.js', { base: './src/modules' })
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(sourcemaps.write('./sourcemaps'))
    .pipe(gulp.dest(DIST_DIR + '/modules'));
})

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