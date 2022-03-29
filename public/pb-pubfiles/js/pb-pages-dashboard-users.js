import { Rable } from '../../pb-pubfiles/js/rable.js';

const api = PbAuth.apiInstance();
const app = new Rable({
    data: {
        users: [],

        async refreshUsers() {
            let res = await api.get('user/list');
            if (res.data.success == undefined) {
                alert('An unknown error has occured! (unknown_error)');
            } else if (res.data.success == false) {
                alert(res.data.message + ' (' + res.data.error + ')');
            } else {
                this.users = res.data.users;
            }
        }
    }
});

app.mount('.content');

await app.data.refreshUsers();