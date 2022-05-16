(function() {
    var availableModulesRetrieved = false;

    document.querySelector('section.actions a[show-available-modules]').addEventListener('click', e => {
        const apiInstance = PbAuth.apiInstance();

        if (!availableModulesRetrieved) apiInstance.get('modules/list-repository-modules').then(res => {
            availableModulesRetrieved = true;
            if (res.data.success) {
                res.data.list.forEach(item => {
                    let row = document.createElement('tr');

                    let dataName = document.createElement('td');
                    dataName.innerText = item.name;
                    row.appendChild(dataName);

                    let dataDescription = document.createElement('td');
                    dataDescription.innerText = item.description;
                    row.appendChild(dataDescription);

                    let dataAuthor = document.createElement('td');
                    dataAuthor.innerText = item.author;
                    row.appendChild(dataAuthor);

                    let dataVersion = document.createElement('td');
                    dataVersion.innerText = item.version;
                    row.appendChild(dataVersion);

                    let dataLicense = document.createElement('td');
                    dataLicense.innerText = item.license;
                    row.appendChild(dataLicense);

                    let dataActions = document.createElement('td');
                    let actionInstall = document.createElement('a');
                    actionInstall.innerText = 'Install';
                    actionInstall.setAttribute('href', '#');
                    actionInstall.addEventListener('click', e => {
                        if (confirm('Do you want to install module: ' + item.name)) {
                            apiInstance.get('modules/install/' + item.module).then(res => {
                                if (res.data.success) {
                                    alert('Module is installed! Enable it to get started.');
                                    location.href = SITE_LOCATION + 'pb-dashboard/modules/' + item.module;
                                } else {
                                    alert(res.data.message + '(' + res.data.error + ')');
                                }
                            });
                        }
                    });

                    dataActions.appendChild(actionInstall);
                    row.appendChild(dataActions);

                    document.querySelector('section.table-modules table.available-modules tbody').appendChild(row);
                });
            } else {
                alert(res.data.message + '(' + res.data.error + ')');
            }
        });

        document.querySelector('section.table-modules').classList.toggle('show-available-modules');
    });

    DashboardContentLoader.close();
})();