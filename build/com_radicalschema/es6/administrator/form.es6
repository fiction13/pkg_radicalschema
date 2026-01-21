/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

Joomla.sendForm = (task, validate) => {
    let form = document.getElementById('adminForm') || document.querySelector('[name="adminForm"]') || document.querySelector('.form-validate');

    Joomla.submitform(task, form, validate);
}

Joomla.submitbutton = (task) => {
    let fullTask = task.split('.'),
        method = (fullTask.length === 2) ? fullTask[1] : fullTask[0],
        validate = true;


    if (method === "cancel" || method === "reload") {
        validate = false;
    }

    Joomla.sendForm(task, validate);
}