/**
 * SCSS & CSS compiling, run the "gulp" task.
 */
var gulp = require('gulp'),
  sass = require('gulp-sass'),
  insert = require('gulp-insert');

gulp.task('default', ['compile-default-theme']);

/**
 *
 * Create a variables file from the two config arrays
 *
 * @param colorVariables
 * @param fontsVariables
 *
 * @returns {string}
 */
function createVariablesFile(colorVariables, fontsVariables) {
  var contents = '';

  for (var color in colorVariables) {
    contents =
      contents +
      '$' +
      String(color) +
      ':' +
      String(colorVariables[color]) +
      ';\n';
  }

  for (var font in fontsVariables) {
    if (fontsVariables[font].indexOf('rem') >= 0)
      contents =
        contents +
        '$' +
        String(font) +
        ':' +
        String(fontsVariables[font]) +
        ';\n';
    else
      contents =
        contents +
        '$' +
        String(font) +
        ':"' +
        String(fontsVariables[font]) +
        '";\n';
  }

  return contents;
}

/**
 *
 * Compile CSS file for a single theme
 *
 */
gulp.task('compile-default-theme', function () {

  var theme = 2;
  var json = require('../frontend/themes/app' + String(theme) + '/presets.json');

  if (
    json == null ||
    json['vars'] == null ||
    json['presets'] == null ||
    json['fonts'] == null
  ) {
    console.log('Missing colors or fonts from the SCSS config file');
  } else {
    // Merge variable names and color settings into a single object
    var colorVariables = {};
    for (var i = 0; i < json['vars'].length; i++) {
      colorVariables[json['vars'][i]] = json['presets']['1'][i];
    }

    // Create a string with all the variables settings
    var contents = createVariablesFile(colorVariables, json['fonts']);

    // Compile SCSS file and write it in the resources folder
    return gulp
      .src('../frontend/themes/app' + String(theme) + '/scss/*.scss')
      .pipe(insert.prepend(contents))
      .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
      .pipe(gulp.dest('../frontend/themes/app' + String(theme) + '/css/'));
  }
});

