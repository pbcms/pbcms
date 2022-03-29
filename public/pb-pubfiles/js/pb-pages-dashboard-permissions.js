import { Rable } from '../../pb-pubfiles/js/rable.js';

const api = PbAuth.apiInstance();
const app = new Rable({
    data: {
        permissions: [],

        new_permission_node: "",
        new_permission_target: "",
        new_permission_granted: true,

        async refreshPermissions() {
            const res = await api.post('permissions/list');
            if (res.data.success == undefined) {
                alert('An unknown error has occured! (unknown_error)');
            } else if (res.data.success == false) {
                alert(res.data.message + ' (' + res.data.error + ')');
            } else {
                for(var i = 0; i < res.data.list.length; i++) {
                    res.data.list[i].type = res.data.list[i].granted == '1' ? "Granted" : "Rejected";
                }
            
                app.data.permissions = res.data.list;
            }
        },

        async grantPermission(target, permission, e) {
            if (e) e.preventDefault();
            return new Promise(resolve => {
                api.post('permissions/grant', {
                    target_type: target.split(':')[0],
                    target_value: target.split(':')[1],
                    permission: permission
                }).then(async res => {
                    if (res.data && res.data.success) {
                        await this.refreshPermissions();
                        resolve(true);
                    } else {
                        alert(`${res.data.message} (${res.data.error})`);
                        resolve(false);
                    }
                });
            });
        },

        rejectPermission(target, permission, e) {
            e.preventDefault();
            return new Promise(resolve => {
                api.post('permissions/reject', {
                    target_type: target.split(':')[0],
                    target_value: target.split(':')[1],
                    permission: permission
                }).then(async res => {
                    if (res.data && res.data.success) {
                        await this.refreshPermissions();
                        resolve(true);
                    } else {
                        alert(`${res.data.message} (${res.data.error})`);
                        resolve(false);
                    }
                });
            });
        },

        async clearPermission(id, e) {
            e.preventDefault();
            return new Promise(resolve => {
                api.delete('permissions/clear', {
                    data: {
                        permission_id: id
                    }
                }).then(async res => {
                    if (res.data && res.data.success) {
                        await this.refreshPermissions();
                        resolve(true);
                    } else {
                        alert(`${res.data.message} (${res.data.error})`);
                        resolve(false);
                    }
                });
            });
        },

        async createPermission() {
            if (this.new_permission_granted) {
                var success = await this.grantPermission(this.new_permission_target, this.new_permission_node);
            } else {
                var success = await this.rejectPermission(this.new_permission_target, this.new_permission_node);
            }

            if (success) {
                this.new_permission_node = "";
                this.new_permission_target = "";
                this.new_permission_granted = true;
            }
        }
    }
});

await app.importComponent('input-field', SITE_LOCATION + "pb-pubfiles/components/InputField.html");
await app.importComponent('input-toggle', SITE_LOCATION + "pb-pubfiles/components/InputToggle.html");
app.mount('.content');

app.data.refreshPermissions();