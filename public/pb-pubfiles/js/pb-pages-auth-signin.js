(function() {
    const portal = document.querySelector('form.portal');

    portal.addEventListener("submit", async e => {
        e.preventDefault();
        portal.querySelector('p.error').innerText = '';

        if (await validatePortal()) {
            const data = fieldData();
            const params = new URLSearchParams();
            params.append('identifier', data.identifier);
            params.append('password', data.password);
            params.append('stay_signedin', data.stay_signedin);
            PB_API.post('auth/create-session', params).then(res => {
                if (res.data.success == undefined) {
                    portal.querySelector('p.error').innerText = 'An unknown error has occured! (unknown_error)';
                } else if (res.data.success == false) {
                    portal.querySelector('p.error').innerText = res.data.message + ' (' + res.data.error + ')';
                } else {
                    location.reload();
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
            identifier: portal.querySelector('input[name=identifier]').value,
            password: portal.querySelector('input[name=password]').value,
            stay_signedin: (portal.querySelector('input[type=checkbox][name=stay-signedin]') ? (portal.querySelector('input[type=checkbox][name=stay-signedin]').checked ? 1 : 0) : 0)
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

        if (data.password.length == 0) {
            portal.querySelector('input[name=password]').parentNode.classList.add('red-border');
            portal.querySelector('input[name=password]').parentNode.querySelector('ul').innerHTML = '<li>This field cannot be empty!</li>';
            success = false;
        }

        return success;
    }
})();