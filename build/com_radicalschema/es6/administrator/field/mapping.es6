/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

(() => {
	const init = (root) => {
		root.querySelectorAll('[data-radicalschema-mapping-container]').forEach((container) => {
			// Skip already initialized containers
			if (container.dataset.radicalschemaInit) {
				return;
			}

			container.dataset.radicalschemaInit = '1';

			let select = container.querySelector('select');
			let input  = container.querySelector('input');

			if (!select || !input) {
				return;
			}

			select.addEventListener('change', function (event) {
				let value = event.target.value;

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
	};

	document.addEventListener('DOMContentLoaded', () => init(document));

	// New rows of repeatable subform (AggregateOffer prices)
	document.addEventListener('subform-row-add', (event) => {
		init(event.detail && event.detail.row ? event.detail.row : document);
	});
})();
