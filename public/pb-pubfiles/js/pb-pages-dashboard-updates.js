import { Rable } from '../../pb-pubfiles/js/rable.js';

const api = PbAuth.apiInstance();
const modulerepositories = new Rable({
    data: {
        repositories: [],

        new_repo_name: "",
        new_repo_url: "",
        new_repo_enabled: true,

        toggleRepo(index, e) {
            e.preventDefault();
            api.get(`modules/${this.repositories[index].enabled ? 'disable' : 'enable'}-repository/${this.repositories[index].name}`).then(res => {
                if (res.data && res.data.success) {
                    this.repositories[index].enabled = !this.repositories[index].enabled;
                } else {
                    alert(`${res.data.message} (${res.data.error})`);
                }
            })
        },

        refreshRepo(index, e) {
            e.preventDefault();
            api.get(`modules/refresh-repository/${this.repositories[index].name}`).then(res => {
                if (res.data && res.data.success) {
                    console.log('Repository refreshed');
                } else {
                    alert(`${res.data.message} (${res.data.error})`);
                }
            })
        },

        removeRepo(index, e) {
            e.preventDefault();
            if (typeof this.repositories[index].removed == 'undefined') {
                this.repositories[index].removed = false;
            } else {
                api.delete(`modules/remove-repository/${this.repositories[index].name}`).then(res => {
                    if (res.data && res.data.success) {
                        this.repositories.splice(index, 1);
                    } else {
                        alert(`${res.data.message} (${res.data.error})`);
                    }
                });
            }
        },

        saveRepo(e) {
            e.preventDefault();

            api.post('modules/add-repository', {
                name: this.new_repo_name,
                url: this.new_repo_url,
                enabled: this.new_repo_enabled ? "enabled" : "disabled"
            }).then(res => {
                if (res.data && res.data.success) {
                    this.repositories.push({
                        name: this.new_repo_name,
                        url: this.new_repo_url,
                        enabled: this.new_repo_enabled
                    });

                    this.new_repo_name = "";
                    this.new_repo_url = "";
                    this.new_repo_enabled = true;
                } else {
                    alert(`${res.data.message} (${res.data.error})`);
                }
            });
        }
    }
});

await modulerepositories.importComponent('input-field', SITE_LOCATION + "pb-pubfiles/components/InputField.html");
await modulerepositories.importComponent('input-toggle', SITE_LOCATION + "pb-pubfiles/components/InputToggle.html");
modulerepositories.mount('#module-repositories');

api.get('modules/list-repositories').then(res => {
    if (res.data && res.data.success) {
        modulerepositories.data.repositories = res.data.repositories;
    }
});


const moduleupdates = new Rable({
    data: {
        updates: [],

        updateModule(index, e) {
            e.preventDefault();
            api.get(`modules/update/${this.updates[index].module}`).then(res => {
                if (res.data && res.data.success) {
                    this.updates.splice(index, 1);
                } else {
                    alert(`${res.data.message} (${res.data.error})`);
                }
            });
        },
    }
});

moduleupdates.mount("#module-updates");

api.get('modules/list').then(res => {
    if (res.data && res.data.success) {
        console.log(res.data);
        res.data.list.forEach(module => {
            if (module.repo) {
                if (module.local) {
                    if (module.local.version.localeCompare(module.repo.version, undefined, {numeric:true,sensitivity:'base'}) === -1) {
                        moduleupdates.data.updates.push(module);
                    }
                } else {
                    moduleupdates.data.updates.push(module);
                }
            }
        });
    }
});


DashboardContentLoader.close();
