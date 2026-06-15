(function () {
    'use strict';

    function onClick(event) {
        var button = event.target.closest('.secret-field__reveal');
        if (!button) {
            return;
        }
        event.preventDefault();

        var control = document.getElementById(button.getAttribute('data-target'));
        if (!control) {
            return;
        }

        // Already revealed: hide and clear so the secret leaves the DOM and stays unchanged on save.
        if (button.getAttribute('data-revealed') === '1') {
            control.value = '';
            if (control.tagName === 'INPUT') {
                control.type = 'password';
            }
            button.setAttribute('data-revealed', '0');
            button.textContent = 'Reveal';
            return;
        }

        var url = control.getAttribute('data-reveal-url') + '&field='
            + encodeURIComponent(control.getAttribute('data-field'));

        fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (typeof data.value === 'undefined') {
                    return;
                }
                control.value = data.value;
                if (control.tagName === 'INPUT') {
                    control.type = 'text';
                }
                button.setAttribute('data-revealed', '1');
                button.textContent = 'Hide';
            })
            .catch(function () {});
    }

    document.addEventListener('click', onClick);
}());
