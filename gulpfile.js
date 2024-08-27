/* This JS file is used by NPM to compile my SCSS files on explicit save.
* Mostly impacted by The Net Ninja on YouTube:
* https://www.youtube.com/watch?v=Sk5jMurFHCo
*/
"use strict";
const {src, dest, watch, series} = require('gulp');
const sass = require('gulp-sass')(require('sass'));

function compileCSS(){ // this is called to compile SCSS to normal CSS
    return src("./scss/*.scss")
        .pipe(sass())
        .pipe(dest("dest"))
}

function watchChanges() { //this is used to automatically compile the files on file save.
    watch(['./scss/*.scss'], compileCSS);
}

exports.default = series(compileCSS, watchChanges);