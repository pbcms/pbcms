import { Rable } from '../../pb-pubfiles/js/rable.js';

const app = new Rable({
    data: {
        roles: []
    }
});

app.mount('.content');

PbAuth.apiInstance().post('roles/list').then(res => {
    if (res.data.success == undefined) {
        alert('An unknown error has occured! (unknown_error)');
    } else if (res.data.success == false) {
        alert(res.data.message + ' (' + res.data.error + ')');
    } else {
        console.log(res);    
        app.data.roles = res.data.roles;
    }
});