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
/*!********************************************************!*\
  !*** ./com_radicalschema/scss/administrator/form.scss ***!
  \********************************************************/
// extracted by mini-css-extract-plugin
})();

// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
(() => {
/*!******************************************************!*\
  !*** ./com_radicalschema/es6/administrator/form.es6 ***!
  \******************************************************/
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

Joomla.sendForm = function (task, validate) {
  var form = document.getElementById('adminForm') || document.querySelector('[name="adminForm"]') || document.querySelector('.form-validate');
  // if (validate && document.formvalidator.isValid(form)) {
  //     document.body.appendChild(document.createElement('joomla-core-loader'));
  // } else if (!validate) {
  //     document.body.appendChild(document.createElement('joomla-core-loader'));
  // }

  Joomla.submitform(task, form, validate);
};
Joomla.submitbutton = function (task) {
  var fullTask = task.split('.'),
    method = fullTask.length === 2 ? fullTask[1] : fullTask[0],
    validate = true;
  if (method === "cancel" || method === "reload") {
    validate = false;
  }
  Joomla.sendForm(task, validate);
};
})();

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoianMvYWRtaW5pc3RyYXRvci9mb3JtLmpzIiwibWFwcGluZ3MiOiI7OztVQUFBO1VBQ0E7O1VBRUE7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7O1VBRUE7VUFDQTs7VUFFQTtVQUNBO1VBQ0E7Ozs7Ozs7Ozs7QUN0QkEsdUM7Ozs7Ozs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQUEsTUFBTSxDQUFDQyxRQUFRLEdBQUcsVUFBQ0MsSUFBSSxFQUFFQyxRQUFRLEVBQUs7RUFDbEMsSUFBSUMsSUFBSSxHQUFHQyxRQUFRLENBQUNDLGNBQWMsQ0FBQyxXQUFXLENBQUMsSUFBSUQsUUFBUSxDQUFDRSxhQUFhLENBQUMsb0JBQW9CLENBQUMsSUFBSUYsUUFBUSxDQUFDRSxhQUFhLENBQUMsZ0JBQWdCLENBQUM7RUFDM0k7RUFDQTtFQUNBO0VBQ0E7RUFDQTs7RUFFQVAsTUFBTSxDQUFDUSxVQUFVLENBQUNOLElBQUksRUFBRUUsSUFBSSxFQUFFRCxRQUFRLENBQUM7QUFDM0MsQ0FBQztBQUVESCxNQUFNLENBQUNTLFlBQVksR0FBRyxVQUFDUCxJQUFJLEVBQUs7RUFDNUIsSUFBSVEsUUFBUSxHQUFHUixJQUFJLENBQUNTLEtBQUssQ0FBQyxHQUFHLENBQUM7SUFDMUJDLE1BQU0sR0FBSUYsUUFBUSxDQUFDRyxNQUFNLEtBQUssQ0FBQyxHQUFJSCxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUdBLFFBQVEsQ0FBQyxDQUFDLENBQUM7SUFDNURQLFFBQVEsR0FBRyxJQUFJO0VBR25CLElBQUlTLE1BQU0sS0FBSyxRQUFRLElBQUlBLE1BQU0sS0FBSyxRQUFRLEVBQUU7SUFDNUNULFFBQVEsR0FBRyxLQUFLO0VBQ3BCO0VBRUFILE1BQU0sQ0FBQ0MsUUFBUSxDQUFDQyxJQUFJLEVBQUVDLFFBQVEsQ0FBQztBQUNuQyxDQUFDLEMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9yYWRpY2Fsc2NoZW1hL3dlYnBhY2svYm9vdHN0cmFwIiwid2VicGFjazovL3JhZGljYWxzY2hlbWEvLi9jb21fcmFkaWNhbHNjaGVtYS9zY3NzL2FkbWluaXN0cmF0b3IvZm9ybS5zY3NzIiwid2VicGFjazovL3JhZGljYWxzY2hlbWEvLi9jb21fcmFkaWNhbHNjaGVtYS9lczYvYWRtaW5pc3RyYXRvci9mb3JtLmVzNiJdLCJzb3VyY2VzQ29udGVudCI6WyIvLyBUaGUgbW9kdWxlIGNhY2hlXG52YXIgX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fID0ge307XG5cbi8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG5mdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuXHR2YXIgY2FjaGVkTW9kdWxlID0gX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fW21vZHVsZUlkXTtcblx0aWYgKGNhY2hlZE1vZHVsZSAhPT0gdW5kZWZpbmVkKSB7XG5cdFx0cmV0dXJuIGNhY2hlZE1vZHVsZS5leHBvcnRzO1xuXHR9XG5cdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG5cdHZhciBtb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdID0ge1xuXHRcdC8vIG5vIG1vZHVsZS5pZCBuZWVkZWRcblx0XHQvLyBubyBtb2R1bGUubG9hZGVkIG5lZWRlZFxuXHRcdGV4cG9ydHM6IHt9XG5cdH07XG5cblx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG5cdF9fd2VicGFja19tb2R1bGVzX19bbW9kdWxlSWRdKG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG5cdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG5cdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbn1cblxuIiwiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luIiwiLypcbiAqIEBwYWNrYWdlICAgUmFkaWNhbFNjaGVtYVxuICogQHZlcnNpb24gICBfX0RFUExPWV9WRVJTSU9OX19cbiAqIEBhdXRob3IgICAgRG1pdHJpeSBWYXN5dWtvdiAtIGh0dHBzOi8vZmljdGlvbmxhYnMucnVcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDI1IEZpY3Rpb25sYWJzLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxuICogQGxpY2Vuc2UgICBHTlUvR1BMIGxpY2Vuc2U6IGh0dHA6Ly93d3cuZ251Lm9yZy9jb3B5bGVmdC9ncGwuaHRtbFxuICogQGxpbmsgICAgICBodHRwczovL2ZpY3Rpb25sYWJzLnJ1L1xuICovXG5cbkpvb21sYS5zZW5kRm9ybSA9ICh0YXNrLCB2YWxpZGF0ZSkgPT4ge1xuICAgIGxldCBmb3JtID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2FkbWluRm9ybScpIHx8IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJ1tuYW1lPVwiYWRtaW5Gb3JtXCJdJykgfHwgZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLmZvcm0tdmFsaWRhdGUnKTtcbiAgICAvLyBpZiAodmFsaWRhdGUgJiYgZG9jdW1lbnQuZm9ybXZhbGlkYXRvci5pc1ZhbGlkKGZvcm0pKSB7XG4gICAgLy8gICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnam9vbWxhLWNvcmUtbG9hZGVyJykpO1xuICAgIC8vIH0gZWxzZSBpZiAoIXZhbGlkYXRlKSB7XG4gICAgLy8gICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnam9vbWxhLWNvcmUtbG9hZGVyJykpO1xuICAgIC8vIH1cblxuICAgIEpvb21sYS5zdWJtaXRmb3JtKHRhc2ssIGZvcm0sIHZhbGlkYXRlKTtcbn1cblxuSm9vbWxhLnN1Ym1pdGJ1dHRvbiA9ICh0YXNrKSA9PiB7XG4gICAgbGV0IGZ1bGxUYXNrID0gdGFzay5zcGxpdCgnLicpLFxuICAgICAgICBtZXRob2QgPSAoZnVsbFRhc2subGVuZ3RoID09PSAyKSA/IGZ1bGxUYXNrWzFdIDogZnVsbFRhc2tbMF0sXG4gICAgICAgIHZhbGlkYXRlID0gdHJ1ZTtcblxuXG4gICAgaWYgKG1ldGhvZCA9PT0gXCJjYW5jZWxcIiB8fCBtZXRob2QgPT09IFwicmVsb2FkXCIpIHtcbiAgICAgICAgdmFsaWRhdGUgPSBmYWxzZTtcbiAgICB9XG5cbiAgICBKb29tbGEuc2VuZEZvcm0odGFzaywgdmFsaWRhdGUpO1xufSJdLCJuYW1lcyI6WyJKb29tbGEiLCJzZW5kRm9ybSIsInRhc2siLCJ2YWxpZGF0ZSIsImZvcm0iLCJkb2N1bWVudCIsImdldEVsZW1lbnRCeUlkIiwicXVlcnlTZWxlY3RvciIsInN1Ym1pdGZvcm0iLCJzdWJtaXRidXR0b24iLCJmdWxsVGFzayIsInNwbGl0IiwibWV0aG9kIiwibGVuZ3RoIl0sInNvdXJjZVJvb3QiOiIifQ==