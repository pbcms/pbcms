import { Rable } from '../../pb-pubfiles/js/rable.js';

const api = PbAuth.apiInstance();
const app = new Rable({
    data: {
        roles: [],

        new_role_name: "",
        new_role_description: "",
        new_role_weight: "",

        message: "",
        showMessage: false,

        deleteRole(id, e) {
            e.preventDefault();
            api.delete('roles/delete/' + id).then(async res => {
                if (res.data && res.data.success) {
                    await this.refreshRoles();
                } else {
                    alert(`${res.data.message} (${res.data.error})`);
                }
            });
        },

        createRole() {
            api.post('roles/create', {
                name: this.new_role_name,
                description: this.new_role_description,
                weight: this.new_role_weight
            }).then(async res => {
                if (res.data && res.data.success) {
                    await this.refreshRoles();

                    this.new_role_name = "";
                    this.new_role_description = "";
                } else {
                    this.displayMessage(`${res.data.message} (${res.data.error})`);
                }
            });
        },

        moveRole(id, weight) {
            api.patch('roles/update/' + id, {
                weight: weight
            }).then(async res => {
                if (res.data && res.data.success) {
                    await this.refreshRoles();

                    this.new_role_name = "";
                    this.new_role_description = "";
                } else {
                    alert(`${res.data.message} (${res.data.error})`);
                }
            });
        },

        async refreshRoles() {
            let res = await api.post('roles/list');
            if (res.data.success == undefined) {
                alert('An unknown error has occured! (unknown_error)');
            } else if (res.data.success == false) {
                alert(res.data.message + ' (' + res.data.error + ')');
            } else {
                let roles = res.data.roles;
                roles = roles.sort((a, b) => {
                    if ( a.weight < b.weight ) return -1;
                    if ( a.weight > b.weight ) return 1;
                    return 0;
                });

                this.roles = roles;
                this.new_role_weight = roles[roles.length - 1].weight + 1;
            }
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

await app.importComponent('input-field', SITE_LOCATION + "pb-pubfiles/components/InputField.html");
app.mount('.content');

await app.data.refreshRoles();