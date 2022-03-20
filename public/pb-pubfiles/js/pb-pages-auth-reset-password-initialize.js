(function() {
    const portal = document.querySelector('form.portal');

    portal.addEventListener("submit", async e => {
        e.preventDefault();
        portal.querySelector('p.error').innerText = '';

        if (await validatePortal()) {
            const data = fieldData();
            PB_API.post('auth/reset-password', {
                identifier: data.identifier
            }).then(res => {
                if (res.data.success == undefined) {
                    portal.querySelector('p.error').innerText = 'An unknown error has occured! (unknown_error)';
                } else if (res.data.success == false) {
                    portal.querySelector('p.error').innerText = res.data.message + ' (' + res.data.error + ')';
                } else {
                    portal.querySelector('p.error').innerHTML = 'E-email sent, check your <b>spambox</b> too!';
                }
            });
        }
    });

    portal.querySelectorAll('.input-field input').forEach(el => el.addEventListener('input', e => {
        if (el.value.length > 0) {
            el.parentNode.classList.remove('red-border');
            el.parentNode.querySelector('ul').innerHTML = '';
        } else {
            el.parentNode.classList.add('red-border');
            el.parentNode.querySelector('ul').innerHTML = '<li>This field cannot be empty!</li>';
        }
    }));

    function fieldData() {
        return {
            identifier: portal.querySelector('input[name=identifier]').value
        }
    }

    async function validatePortal() {
        var success = true;
        const data = fieldData();
        if (data.identifier.length == 0) {
            portal.querySelector('input[name=identifier]').parentNode.classList.add('red-border');
            portal.querySelector('input[name=identifier]').parentNode.querySelector('ul').innerHTML = '<li>This field cannot be empty!</li>';
            success = false;
        }

        return success;
    }
})();