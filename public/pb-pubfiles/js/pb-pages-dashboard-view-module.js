(function() {
    var apiInstance = PbAuth.apiInstance();

    var enableButton = document.querySelector('section.actions a[enable-module]');
    enableButton.addEventListener('click', e => {
        if (confirm('Do you want to enable this module?')) {
            apiInstance.get('modules/enable/' + enableButton.getAttribute('enable-module')).then(res => {
                if (res.data.success) {
                    alert('Module is now enabled.');
                    location.reload();
                } else {
                    alert(res.data.message + '(' + res.data.error + ')');
                }
            });
        }
    });

    var disableButton = document.querySelector('section.actions a[disable-module]');
    disableButton.addEventListener('click', e => {
        if (confirm('Do you want to disable this module?')) {
            apiInstance.get('modules/disable/' + disableButton.getAttribute('disable-module')).then(res => {
                if (res.data.success) {
                    alert('Module is now disabled.');
                    location.reload();
                } else {
                    alert(res.data.message + '(' + res.data.error + ')');
                }
            });
        }
    });
})();