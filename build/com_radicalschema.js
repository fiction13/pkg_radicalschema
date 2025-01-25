/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

const entry = {
	"administrator/config": {
		import: ['./com_radicalschema/scss/administrator/config.scss', './com_radicalschema/es6/administrator/config.es6'],
		filename: 'administrator/config.js',
	},
	"administrator/form": {
		import: ['./com_radicalschema/scss/administrator/form.scss', './com_radicalschema/es6/administrator/form.es6'],
		filename: 'administrator/form.js',
	},
	"administrator/field/mapping": {
		import: ['./com_radicalschema/es6/administrator/field/mapping.es6'],
		filename: 'administrator/field/mapping.js',
	},
};

const webpackConfig = require('./webpack.config.js');
const publicPath = '../com_radicalschema/media';
const production = webpackConfig(entry, publicPath);
const development = webpackConfig(entry, publicPath, 'development');

module.exports = [production, development]