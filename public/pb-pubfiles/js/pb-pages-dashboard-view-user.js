import { Rable } from '../../pb-pubfiles/js/rable.js';

const api = PbAuth.apiInstance();
const userId = location.href.match(/\/pb\-dashboard\/users\/([0-9]+)/g)[0].split('/').pop()
const profileoverview = new Rable();
const userroles = new Rable({
    data: {
        roles: [],
        original: {},
        message: "",
        showMessage: false,

        async saveRoles() {
            for(var i = 0; i < this.roles.length; i++) {
                let role = this.roles[i];
                if (role.assigned != this.original[role.id]) {
                    if (role.assigned) {
                        const res = await api.post('relation/create', {
                            type: 'user:role',
                            origin: userId,
                            target: role.id
                        });

                        if (!res.data.success) {
                            alert(`${res.data.message} (${res.data.error})`);
                        }
                    } else {
                        const res = await api.delete('relation/delete', {
                            data: {
                                type: 'user:role',
                                origin: userId,
                                target: role.id
                            }
                        });

                        if (!res.data.success) {
                            alert(`${res.data.message} (${res.data.error})`);
                        }
                    }
                }
            }

            this.displayMessage("Roles saved!");
        },

        async refreshRoles() {
            return new Promise(resolve => {
                api.get('roles/list').then(async res => {
                    this.roles = [];
                    if (!res.data.success) {
                        alert(`${res.data.message} (${res.data.error})`);
                    } else {
                        let roles = res.data.roles;
                        roles = roles.sort((a, b) => {
                            if ( a.weight < b.weight ) return -1;
                            if ( a.weight > b.weight ) return 1;
                            return 0;
                        });
                
                        for(var i = 0; i < roles.length; i++) {
                            let role = roles[i];
                            const rel = await api.post('relation/find', {
                                type: "user:role",
                                origin: userId,
                                target: role.id
                            });
                
                            if (rel.data.success) {
                                role.assigned = rel.data.relation != null;
                                this.original[role.id] = rel.data.relation != null;
                                userroles.data.roles.push(role);
                            } else {
                                console.error("Could not retrieve relation between user " + userId + " and role " + role.id + "!");
                                console.error(`${res.data.message} (${res.data.error})`);
                            }
                        }
                    }

                    resolve();
                });
            });
        },

        displayMessage(msg, persistant = false) {
            this.message = msg;
            this.showMessage = true;
            if (!persistant) setTimeout(() => {
                if (msg == this.message) this.showMessage = false;
            }, 2000);
        }
    }
});

await userroles.importComponent('input-toggle', SITE_LOCATION + "pb-pubfiles/components/InputToggle.html");
userroles.mount('#userroles');
userroles.data.refreshRoles();

api.get('user/info/' + userId).then(async res => {
    if (!res.data.success) {
        alert(`${res.data.message} (${res.data.error})`);
    } else {
        Object.keys(res.data.user).forEach(prop => profileoverview.data[prop] = res.data.user[prop]);      
        profileoverview.mount('.profile-overview');  
    }
});