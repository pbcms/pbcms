import { Rable } from '../../pb-pubfiles/js/rable.js';

const app = new Rable({
    data: {
        permissions: []
    }
});

app.mount('.content');

PbAuth.apiInstance().post('permissions/list').then(res => {
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
});