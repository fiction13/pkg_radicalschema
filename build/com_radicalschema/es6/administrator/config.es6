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
})

Joomla.sendForm = (task, validate) => {
    let form = document.getElementById('component-form')
    if (validate && document.formvalidator.isValid(form)) {
        document.body.appendChild(document.createElement('joomla-core-loader'));
    } else if (!validate) {
        document.body.appendChild(document.createElement('joomla-core-loader'));
    }

    Joomla.submitform(task, form, validate);
}