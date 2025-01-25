/******/ (() => { // webpackBootstrap
/******/ 	/************************************************************************/
var __webpack_exports__ = {};
/*!***************************************************************!*\
  !*** ./com_radicalschema/es6/administrator/field/mapping.es6 ***!
  \***************************************************************/
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-radicalschema-mapping-container]').forEach(function (container) {
    var select = container.querySelector('select');
    var input = container.querySelector('input');
    select.addEventListener('change', function (event) {
      var value = event.target.value;
      input.type = 'hidden';
      if (value === '_noselect_') {
        input.value = '';
      } else if (value === '_custom_') {
        input.type = 'text';
        input.value = '';
      } else {
        input.value = event.target.value;
      }
    });
    if (!input.value) {
      select.value = '_noselect_';
      input.type = 'hidden';
    } else {
      select.value = select.getAttribute('data-value');
    }
  });
});
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoianMvYWRtaW5pc3RyYXRvci9maWVsZC9tYXBwaW5nLmpzIiwibWFwcGluZ3MiOiI7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUFBLFFBQVEsQ0FBQ0MsZ0JBQWdCLENBQUMsa0JBQWtCLEVBQUUsWUFBWTtFQUN0REQsUUFBUSxDQUFDRSxnQkFBZ0IsQ0FBQyx3Q0FBd0MsQ0FBQyxDQUFDQyxPQUFPLENBQUMsVUFBQ0MsU0FBUyxFQUFLO0lBQzdGLElBQUlDLE1BQU0sR0FBR0QsU0FBUyxDQUFDRSxhQUFhLENBQUMsUUFBUSxDQUFDO0lBQzlDLElBQUlDLEtBQUssR0FBSUgsU0FBUyxDQUFDRSxhQUFhLENBQUMsT0FBTyxDQUFDO0lBRTdDRCxNQUFNLENBQUNKLGdCQUFnQixDQUFDLFFBQVEsRUFBRSxVQUFVTyxLQUFLLEVBQUU7TUFDbEQsSUFBSUMsS0FBSyxHQUFHRCxLQUFLLENBQUNFLE1BQU0sQ0FBQ0QsS0FBSztNQUU5QkYsS0FBSyxDQUFDSSxJQUFJLEdBQUcsUUFBUTtNQUVyQixJQUFJRixLQUFLLEtBQUssWUFBWSxFQUMxQjtRQUNDRixLQUFLLENBQUNFLEtBQUssR0FBRyxFQUFFO01BQ2pCLENBQUMsTUFBTSxJQUFJQSxLQUFLLEtBQUssVUFBVSxFQUFFO1FBQ2hDRixLQUFLLENBQUNJLElBQUksR0FBRyxNQUFNO1FBQ25CSixLQUFLLENBQUNFLEtBQUssR0FBRyxFQUFFO01BQ2pCLENBQUMsTUFBTTtRQUNORixLQUFLLENBQUNFLEtBQUssR0FBR0QsS0FBSyxDQUFDRSxNQUFNLENBQUNELEtBQUs7TUFDakM7SUFDRCxDQUFDLENBQUM7SUFFRixJQUFJLENBQUNGLEtBQUssQ0FBQ0UsS0FBSyxFQUFFO01BQ2pCSixNQUFNLENBQUNJLEtBQUssR0FBRyxZQUFZO01BQzNCRixLQUFLLENBQUNJLElBQUksR0FBRyxRQUFRO0lBQ3RCLENBQUMsTUFBTTtNQUNOTixNQUFNLENBQUNJLEtBQUssR0FBR0osTUFBTSxDQUFDTyxZQUFZLENBQUMsWUFBWSxDQUFDO0lBQ2pEO0VBQ0UsQ0FBQyxDQUFDO0FBQ04sQ0FBQyxDQUFDLEMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9yYWRpY2Fsc2NoZW1hLy4vY29tX3JhZGljYWxzY2hlbWEvZXM2L2FkbWluaXN0cmF0b3IvZmllbGQvbWFwcGluZy5lczYiXSwic291cmNlc0NvbnRlbnQiOlsiLypcbiAqIEBwYWNrYWdlICAgUmFkaWNhbFNjaGVtYVxuICogQHZlcnNpb24gICBfX0RFUExPWV9WRVJTSU9OX19cbiAqIEBhdXRob3IgICAgRG1pdHJpeSBWYXN5dWtvdiAtIGh0dHBzOi8vZmljdGlvbmxhYnMucnVcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDI1IEZpY3Rpb25sYWJzLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxuICogQGxpY2Vuc2UgICBHTlUvR1BMIGxpY2Vuc2U6IGh0dHA6Ly93d3cuZ251Lm9yZy9jb3B5bGVmdC9ncGwuaHRtbFxuICogQGxpbmsgICAgICBodHRwczovL2ZpY3Rpb25sYWJzLnJ1L1xuICovXG5cbmRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBmdW5jdGlvbiAoKSB7XG4gICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnW2RhdGEtcmFkaWNhbHNjaGVtYS1tYXBwaW5nLWNvbnRhaW5lcl0nKS5mb3JFYWNoKChjb250YWluZXIpID0+IHtcblx0XHRsZXQgc2VsZWN0ID0gY29udGFpbmVyLnF1ZXJ5U2VsZWN0b3IoJ3NlbGVjdCcpO1xuXHRcdGxldCBpbnB1dCAgPSBjb250YWluZXIucXVlcnlTZWxlY3RvcignaW5wdXQnKTtcblxuXHRcdHNlbGVjdC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBmdW5jdGlvbiAoZXZlbnQpIHtcblx0XHRcdGxldCB2YWx1ZSA9IGV2ZW50LnRhcmdldC52YWx1ZTtcblxuXHRcdFx0aW5wdXQudHlwZSA9ICdoaWRkZW4nO1xuXG5cdFx0XHRpZiAodmFsdWUgPT09ICdfbm9zZWxlY3RfJylcblx0XHRcdHtcblx0XHRcdFx0aW5wdXQudmFsdWUgPSAnJztcblx0XHRcdH0gZWxzZSBpZiAodmFsdWUgPT09ICdfY3VzdG9tXycpIHtcblx0XHRcdFx0aW5wdXQudHlwZSA9ICd0ZXh0Jztcblx0XHRcdFx0aW5wdXQudmFsdWUgPSAnJztcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdGlucHV0LnZhbHVlID0gZXZlbnQudGFyZ2V0LnZhbHVlO1xuXHRcdFx0fVxuXHRcdH0pO1xuXG5cdFx0aWYgKCFpbnB1dC52YWx1ZSkge1xuXHRcdFx0c2VsZWN0LnZhbHVlID0gJ19ub3NlbGVjdF8nO1xuXHRcdFx0aW5wdXQudHlwZSA9ICdoaWRkZW4nO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRzZWxlY3QudmFsdWUgPSBzZWxlY3QuZ2V0QXR0cmlidXRlKCdkYXRhLXZhbHVlJyk7XG5cdFx0fVxuICAgIH0pO1xufSk7Il0sIm5hbWVzIjpbImRvY3VtZW50IiwiYWRkRXZlbnRMaXN0ZW5lciIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJmb3JFYWNoIiwiY29udGFpbmVyIiwic2VsZWN0IiwicXVlcnlTZWxlY3RvciIsImlucHV0IiwiZXZlbnQiLCJ2YWx1ZSIsInRhcmdldCIsInR5cGUiLCJnZXRBdHRyaWJ1dGUiXSwic291cmNlUm9vdCI6IiJ9