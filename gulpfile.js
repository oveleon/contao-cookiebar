const gulp = require('gulp');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');

gulp.task('js', function() {
    return gulp.src(['public/scripts/*.js', '!public/scripts/*.min.js'])
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('public/scripts'))
});

gulp.task('css', function(){
    return gulp.src(['public/styles/*.scss', '!public/styles/*.css'])
        //.pipe(sourcemaps.init())
        .pipe(sass())
        //.pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('public/styles'))
});
