/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
(() => {
/*!**********************************************************!*\
  !*** ./com_radicalschema/scss/administrator/config.scss ***!
  \**********************************************************/
// extracted by mini-css-extract-plugin
})();

// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
(() => {
/*!********************************************************!*\
  !*** ./com_radicalschema/es6/administrator/config.es6 ***!
  \********************************************************/
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

document.addEventListener('DOMContentLoaded', function () {
  var container = document.querySelector('#config joomla-tab#configTabs');

  // Sort tabs
  var buttonsContainer = container.querySelector('div[role="tablist"]');
  ['permissions'].forEach(function (name) {
    var button = buttonsContainer.querySelector('button[aria-controls="' + name + '"]');
    buttonsContainer.append(button);
  });

  // Set return
  var config = Joomla.getOptions('com_radicalschema.config');
  document.querySelector('input[name="return"]').value = config.return_link;
});
Joomla.sendForm = function (task, validate) {
  var form = document.getElementById('component-form');
  if (validate && document.formvalidator.isValid(form)) {
    document.body.appendChild(document.createElement('joomla-core-loader'));
  } else if (!validate) {
    document.body.appendChild(document.createElement('joomla-core-loader'));
  }
  Joomla.submitform(task, form, validate);
};
})();

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoianMvYWRtaW5pc3RyYXRvci9jb25maWcuanMiLCJtYXBwaW5ncyI6Ijs7O1VBQUE7VUFDQTs7VUFFQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTs7VUFFQTtVQUNBOztVQUVBO1VBQ0E7VUFDQTs7Ozs7Ozs7OztBQ3RCQSx1Qzs7Ozs7Ozs7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBQSxRQUFRLENBQUNDLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLFlBQVk7RUFDdEQsSUFBSUMsU0FBUyxHQUFHRixRQUFRLENBQUNHLGFBQWEsQ0FBQywrQkFBK0IsQ0FBQzs7RUFFdkU7RUFDQSxJQUFJQyxnQkFBZ0IsR0FBR0YsU0FBUyxDQUFDQyxhQUFhLENBQUMscUJBQXFCLENBQUM7RUFDckUsQ0FBQyxhQUFhLENBQUMsQ0FBQ0UsT0FBTyxDQUFDLFVBQVVDLElBQUksRUFBRTtJQUNwQyxJQUFJQyxNQUFNLEdBQUdILGdCQUFnQixDQUFDRCxhQUFhLENBQUMsd0JBQXdCLEdBQUdHLElBQUksR0FBRyxJQUFJLENBQUM7SUFDbkZGLGdCQUFnQixDQUFDSSxNQUFNLENBQUNELE1BQU0sQ0FBQztFQUNuQyxDQUFDLENBQUM7O0VBRUY7RUFDQSxJQUFJRSxNQUFNLEdBQUdDLE1BQU0sQ0FBQ0MsVUFBVSxDQUFDLDBCQUEwQixDQUFDO0VBQzFEWCxRQUFRLENBQUNHLGFBQWEsQ0FBQyxzQkFBc0IsQ0FBQyxDQUFDUyxLQUFLLEdBQUdILE1BQU0sQ0FBQ0ksV0FBVztBQUM3RSxDQUFDLENBQUM7QUFFRkgsTUFBTSxDQUFDSSxRQUFRLEdBQUcsVUFBQ0MsSUFBSSxFQUFFQyxRQUFRLEVBQUs7RUFDbEMsSUFBSUMsSUFBSSxHQUFHakIsUUFBUSxDQUFDa0IsY0FBYyxDQUFDLGdCQUFnQixDQUFDO0VBQ3BELElBQUlGLFFBQVEsSUFBSWhCLFFBQVEsQ0FBQ21CLGFBQWEsQ0FBQ0MsT0FBTyxDQUFDSCxJQUFJLENBQUMsRUFBRTtJQUNsRGpCLFFBQVEsQ0FBQ3FCLElBQUksQ0FBQ0MsV0FBVyxDQUFDdEIsUUFBUSxDQUFDdUIsYUFBYSxDQUFDLG9CQUFvQixDQUFDLENBQUM7RUFDM0UsQ0FBQyxNQUFNLElBQUksQ0FBQ1AsUUFBUSxFQUFFO0lBQ2xCaEIsUUFBUSxDQUFDcUIsSUFBSSxDQUFDQyxXQUFXLENBQUN0QixRQUFRLENBQUN1QixhQUFhLENBQUMsb0JBQW9CLENBQUMsQ0FBQztFQUMzRTtFQUVBYixNQUFNLENBQUNjLFVBQVUsQ0FBQ1QsSUFBSSxFQUFFRSxJQUFJLEVBQUVELFFBQVEsQ0FBQztBQUMzQyxDQUFDLEMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9yYWRpY2Fsc2NoZW1hL3dlYnBhY2svYm9vdHN0cmFwIiwid2VicGFjazovL3JhZGljYWxzY2hlbWEvLi9jb21fcmFkaWNhbHNjaGVtYS9zY3NzL2FkbWluaXN0cmF0b3IvY29uZmlnLnNjc3MiLCJ3ZWJwYWNrOi8vcmFkaWNhbHNjaGVtYS8uL2NvbV9yYWRpY2Fsc2NoZW1hL2VzNi9hZG1pbmlzdHJhdG9yL2NvbmZpZy5lczYiXSwic291cmNlc0NvbnRlbnQiOlsiLy8gVGhlIG1vZHVsZSBjYWNoZVxudmFyIF9fd2VicGFja19tb2R1bGVfY2FjaGVfXyA9IHt9O1xuXG4vLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcblx0dmFyIGNhY2hlZE1vZHVsZSA9IF9fd2VicGFja19tb2R1bGVfY2FjaGVfX1ttb2R1bGVJZF07XG5cdGlmIChjYWNoZWRNb2R1bGUgIT09IHVuZGVmaW5lZCkge1xuXHRcdHJldHVybiBjYWNoZWRNb2R1bGUuZXhwb3J0cztcblx0fVxuXHQvLyBDcmVhdGUgYSBuZXcgbW9kdWxlIChhbmQgcHV0IGl0IGludG8gdGhlIGNhY2hlKVxuXHR2YXIgbW9kdWxlID0gX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fW21vZHVsZUlkXSA9IHtcblx0XHQvLyBubyBtb2R1bGUuaWQgbmVlZGVkXG5cdFx0Ly8gbm8gbW9kdWxlLmxvYWRlZCBuZWVkZWRcblx0XHRleHBvcnRzOiB7fVxuXHR9O1xuXG5cdC8vIEV4ZWN1dGUgdGhlIG1vZHVsZSBmdW5jdGlvblxuXHRfX3dlYnBhY2tfbW9kdWxlc19fW21vZHVsZUlkXShtb2R1bGUsIG1vZHVsZS5leHBvcnRzLCBfX3dlYnBhY2tfcmVxdWlyZV9fKTtcblxuXHQvLyBSZXR1cm4gdGhlIGV4cG9ydHMgb2YgdGhlIG1vZHVsZVxuXHRyZXR1cm4gbW9kdWxlLmV4cG9ydHM7XG59XG5cbiIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpbiIsIi8qXG4gKiBAcGFja2FnZSAgIFJhZGljYWxTY2hlbWFcbiAqIEB2ZXJzaW9uICAgX19ERVBMT1lfVkVSU0lPTl9fXG4gKiBAYXV0aG9yICAgIERtaXRyaXkgVmFzeXVrb3YgLSBodHRwczovL2ZpY3Rpb25sYWJzLnJ1XG4gKiBAY29weXJpZ2h0IENvcHlyaWdodCAoYykgMjAyNSBGaWN0aW9ubGFicy4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cbiAqIEBsaWNlbnNlICAgR05VL0dQTCBsaWNlbnNlOiBodHRwOi8vd3d3LmdudS5vcmcvY29weWxlZnQvZ3BsLmh0bWxcbiAqIEBsaW5rICAgICAgaHR0cHM6Ly9maWN0aW9ubGFicy5ydS9cbiAqL1xuXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgZnVuY3Rpb24gKCkge1xuICAgIHZhciBjb250YWluZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjY29uZmlnIGpvb21sYS10YWIjY29uZmlnVGFicycpO1xuXG4gICAgLy8gU29ydCB0YWJzXG4gICAgdmFyIGJ1dHRvbnNDb250YWluZXIgPSBjb250YWluZXIucXVlcnlTZWxlY3RvcignZGl2W3JvbGU9XCJ0YWJsaXN0XCJdJyk7XG4gICAgWydwZXJtaXNzaW9ucyddLmZvckVhY2goZnVuY3Rpb24gKG5hbWUpIHtcbiAgICAgICAgdmFyIGJ1dHRvbiA9IGJ1dHRvbnNDb250YWluZXIucXVlcnlTZWxlY3RvcignYnV0dG9uW2FyaWEtY29udHJvbHM9XCInICsgbmFtZSArICdcIl0nKTtcbiAgICAgICAgYnV0dG9uc0NvbnRhaW5lci5hcHBlbmQoYnV0dG9uKTtcbiAgICB9KTtcblxuICAgIC8vIFNldCByZXR1cm5cbiAgICBsZXQgY29uZmlnID0gSm9vbWxhLmdldE9wdGlvbnMoJ2NvbV9yYWRpY2Fsc2NoZW1hLmNvbmZpZycpO1xuICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJ2lucHV0W25hbWU9XCJyZXR1cm5cIl0nKS52YWx1ZSA9IGNvbmZpZy5yZXR1cm5fbGluaztcbn0pXG5cbkpvb21sYS5zZW5kRm9ybSA9ICh0YXNrLCB2YWxpZGF0ZSkgPT4ge1xuICAgIGxldCBmb3JtID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2NvbXBvbmVudC1mb3JtJylcbiAgICBpZiAodmFsaWRhdGUgJiYgZG9jdW1lbnQuZm9ybXZhbGlkYXRvci5pc1ZhbGlkKGZvcm0pKSB7XG4gICAgICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnam9vbWxhLWNvcmUtbG9hZGVyJykpO1xuICAgIH0gZWxzZSBpZiAoIXZhbGlkYXRlKSB7XG4gICAgICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnam9vbWxhLWNvcmUtbG9hZGVyJykpO1xuICAgIH1cblxuICAgIEpvb21sYS5zdWJtaXRmb3JtKHRhc2ssIGZvcm0sIHZhbGlkYXRlKTtcbn0iXSwibmFtZXMiOlsiZG9jdW1lbnQiLCJhZGRFdmVudExpc3RlbmVyIiwiY29udGFpbmVyIiwicXVlcnlTZWxlY3RvciIsImJ1dHRvbnNDb250YWluZXIiLCJmb3JFYWNoIiwibmFtZSIsImJ1dHRvbiIsImFwcGVuZCIsImNvbmZpZyIsIkpvb21sYSIsImdldE9wdGlvbnMiLCJ2YWx1ZSIsInJldHVybl9saW5rIiwic2VuZEZvcm0iLCJ0YXNrIiwidmFsaWRhdGUiLCJmb3JtIiwiZ2V0RWxlbWVudEJ5SWQiLCJmb3JtdmFsaWRhdG9yIiwiaXNWYWxpZCIsImJvZHkiLCJhcHBlbmRDaGlsZCIsImNyZWF0ZUVsZW1lbnQiLCJzdWJtaXRmb3JtIl0sInNvdXJjZVJvb3QiOiIifQ==