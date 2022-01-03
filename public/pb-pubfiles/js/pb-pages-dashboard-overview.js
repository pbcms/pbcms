(function() {
    document.querySelector('section.database-migrations [action-migrate-database]').addEventListener('click', e => {
        PbAuth.apiInstance().get('site/migrate-database').then(res => {
            if (res.data && res.data.success) {
                document.querySelector('section.database-migrations .migration-logs').innerHTML = '<br>' + res.data.logs.join('<br>');
            } else {
                alert(res.message + ' (' + res.error + ')');
            }
        });
    });
})();