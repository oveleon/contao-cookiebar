const gulp = require('gulp');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');

gulp.task('js', function() {
    return gulp.src(['src/Resources/public/scripts/*.js', '!src/Resources/public/scripts/*.min.js'])
        .pipe(uglify())
        //.pipe(sourcemaps.write('.'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('src/Resources/public/scripts'))
});

gulp.task('css', function(){
    return gulp.src(['src/Resources/public/styles/*.scss', '!src/Resources/public/styles/*.css'])
        //.pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed'}))
        //.pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('src/Resources/public/styles'))
});
