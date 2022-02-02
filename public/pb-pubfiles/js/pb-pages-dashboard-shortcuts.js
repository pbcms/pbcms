(function() {
    document.querySelector('form.new-short-shortcut').addEventListener('submit', e => {
        e.preventDefault();
        var icon = document.querySelector('form.new-short-shortcut input[name=icon]');
        var title = document.querySelector('form.new-short-shortcut input[name=title]');
        var target = document.querySelector('form.new-short-shortcut input[name=target]');

        if (icon.value == '' || title.value == '' || target.value == '') {
            alert('empty icon, title or target!');
        } else {
            var type = 'custom';
            try {
                new URL(target.value);
                type = 'remote';
            } catch(e) {}

            const api = PbAuth.apiInstance();
            api.post(SITE_LOCATION + '/pb-api/site/dashboard/create-shortcut', {
                icon: icon.value,
                title: title.value,
                target: target.value,
                'shortcut-type': type
            }).then(res => {
                if (res.data.success) {
                    location.reload();
                } else {
                    console.log(res.data.message + ' (' + res.data.error + ')');
                    alert(res.data.message + ' (' + res.data.error + ')');
                }
            });
        }
    });

    document.querySelectorAll('a[delete-shortcut]').forEach(shortcut => shortcut.addEventListener('click', e => {
        if (e.target.getAttribute('confirm') == '1') {
            const api = PbAuth.apiInstance();
            api.get(SITE_LOCATION + '/pb-api/site/dashboard/delete-shortcut/' + e.target.getAttribute('delete-shortcut')).then(res => {
                if (res.data.success) {
                    location.reload();
                } else {
                    console.log(res.data.message + ' (' + res.data.error + ')');
                    alert(res.data.message + ' (' + res.data.error + ')');
                }
            });
        } else {
            e.target.setAttribute('confirm', '1');
            e.target.innerHTML = "Confirm deletion?";
            e.target.style.color = 'red';
        }
    }));
})();