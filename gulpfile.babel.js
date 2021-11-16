import { dest, src, series } from 'gulp';
import notify from 'gulp-notify';
import del from 'del';
import plumber from 'gulp-plumber';
import rename from 'gulp-rename';

// CSS related plugins.
import autoprefixer from 'gulp-autoprefixer';
import cleanCSS from 'gulp-clean-css';
import dartSass from 'sass';
import gulpSass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import styleLint from 'gulp-stylelint';

const sass = gulpSass( dartSass );

/**
 * Custom Error Handler.
 *
 * @param {Object} error
 */
const errorHandler = error => {
	notify.onError( {
		title: 'Gulp error in ' + error.plugin,
		message: error.toString(),
		sound: false,
	} )( error );
};

/**
 * Task: `sassLinter`.
 * This task does the following:
 *    1. Gets all our scss files
 *    2. Lints theme files to keep code up to standards and consistent
 */
export const sassLinter = () => {
	return src( './src/scss/**/*.scss' )
		.pipe( plumber( errorHandler ) )
		.pipe( styleLint( {
			customSyntax: 'postcss-scss',
			reporters: [ {
				formatter: 'string',
				console: true,
			} ],
		} ) );
};
sassLinter.description = 'Lint through all our SASS/SCSS files so our code is consistent across files.';

/**
 * Task: `css`.
 *
 * This task does the following:
 *    1. Gets the source scss file
 *    2. Compiles Sass to CSS
 *    3. Writes Sourcemaps for it
 *    4. Autoprefixes it
 *    5. Renames the CSS file with suffix .min.css
 *    6. Minifies the CSS file and generates *.min.css
 *    7. Injects CSS or reloads the browser via server
 *
 * @param {Function} done Callback function for async purposes.
 */
export const css = done => {
	del( './assets/css/*' );

	src( 'src/scss/*.scss' )
		.pipe( plumber( errorHandler ) )
		.pipe( sourcemaps.init() )
		.pipe( sass( { outputStyle: 'expanded' } ).on( 'error', sass.logError ) )
		.pipe( autoprefixer( {
			cascade: false,
		} ) )
		.pipe( cleanCSS( {
			level: {
				2: {
					all: false,
					mergeIntoShorthands: true,
					mergeMedia: true,
				},
			},
		} ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( '.', {
			includeContent: false,
			sourceRoot: '../../src/scss',
		} ) )
		.pipe( dest( './assets/css' ) );

	done();
};
css.description = 'Compiles Sass, Autoprefixes it and Minifies CSS.';

export const styles  = series( sassLinter, css );
export const build   = css;

export default build;
