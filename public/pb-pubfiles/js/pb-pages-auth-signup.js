(function() {
    const portal = document.querySelector('form.portal');

    portal.addEventListener("submit", async e => {
        e.preventDefault();

        if (await validatePortal()) {
            const params = new URLSearchParams();
            params.append('password', data.password);
            PB_API.post('auth/validate-password', params).then(res => {
                if (res.data.success == undefined) return resolve({success:false,error:'request_error',message:'An error occured while executing the request.'});
                resolve(res.data);
            });
        } else {

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
            stay_signedin: portal.querySelector('input[type=checkbox][name=stay-signedin]').checked
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
        } else {
            const passres = await new Promise((resolve, reject) => {
                const params = new URLSearchParams();
                params.append('password', data.password);
                PB_API.post('auth/validate-password', params).then(res => {
                    if (res.data.success == undefined) return resolve({success:false,error:'request_error',message:'An error occured while executing the request.'});
                    resolve(res.data);
                });
            });

            if (!passres.valid) {
                portal.querySelector('input[name=password]').parentNode.classList.add('red-border')
                success = false;

                var messages = [];
                if (passres.issues.includes('score') && passres.data.enforcedPolicy.includes('score=1')) {
                    if (!passres.factors.uppercase) messages.push("You need at least one uppercase character.");
                    if (!passres.factors.lowercase) messages.push("You need at least one lowercase character.");
                    if (!passres.factors.number) messages.push("You need at least one number.");
                    if (!passres.factors.special) messages.push("You need at least one special character.");
                    if (!passres.factors.length) messages.push("Too short. Your password should be at least " + passres.data.minimumLength + " characters long.");
                }

                messages.forEach(msg => {
                    portal.querySelector('input[name=password]').parentNode.querySelector('ul').innerHTML += '<li>' + msg + '</li>';
                });
            }

            console.log(passres);
        }

        return success;
    }

    function submitPortal() {

    }

    function showError(error) {

    }
})();