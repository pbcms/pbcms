import { Rable } from '../../pb-pubfiles/js/rable.js';

const api = PbAuth.apiInstance();
const app = new Rable({
    data: {
        users: [],

        pagination: {
            limit: "10",
            count: (await api.get('user/count')).data.count,
            page: 1,
        },

        filter_limit_options: {
            "5": 5,
            "10": 10,
            "25": 25,
            "50": 50,
            "75": 75,
            "100": 100
        },

        new_user_firstname: "",
        new_user_lastname: "",
        new_user_email: "",
        new_user_username: "",
        new_user_status: "",
        new_user_password: "",

        new_user_status_options: {
            UNVERIFIED: 'Unverified',
            VERIFIED: "Verified",
            LOCKED: "Locked"
        },

        message: "",
        showMessage: false,

        async createUser() {
            const res = await api.post('user/create', {
                firstname: this.new_user_firstname,
                lastname: this.new_user_lastname,
                email: this.new_user_email,
                username: this.new_user_username,
                status: this.new_user_status,
                password: this.new_user_password
            });

            if (res.data && res.data.success) {
                this.displayMessage("User created succesfully!");

                this.new_user_firstname = "";
                this.new_user_lastname = "";
                this.new_user_email = "";
                this.new_user_username = "";
                this.new_user_status = "";
                this.new_user_password = "";

                await this.refreshUsers();
            } else {
                this.displayMessage(`${res.data.message} (${res.data.error})`, true);
            }
        },

        async refreshUsers() {
            let res = await api.post('user/list', {
                limit: this.pagination.limit,
                offset: this.pagination.limit * (this.pagination.page - 1)
            });
            if (res.data.success == undefined) {
                alert('An unknown error has occured! (unknown_error)');
            } else if (res.data.success == false) {
                alert(res.data.message + ' (' + res.data.error + ')');
            } else {
                this.users = res.data.users;
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
await app.importComponent('input-select', SITE_LOCATION + "pb-pubfiles/components/InputSelect.html");
app.mount('.content');

await app.data.refreshUsers();
DashboardContentLoader.close();