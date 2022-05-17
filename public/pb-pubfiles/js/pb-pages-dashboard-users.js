import { Rable } from '../../pb-pubfiles/js/rable.js';

const api = PbAuth.apiInstance();
const app = new Rable({
    data: {
        users: [],

        typing_slowdown: 0,

        filter: {
            search: ''
        },

        pagination: {
            limit: "10",
            count: 0,
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

        new_user: false,
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
            let body = {
                limit: this.pagination.limit,
                offset: this.pagination.limit * (this.pagination.page - 1)
            };

            if (this.filter.search != '') body.search = this.filter.search;
            this.users = (await api.post('user/list', body)).data.users;
            this.pagination.count = (await api.post('user/count', body)).data.count;
        },

        displayMessage(msg, persistant = false) {
            this.message = msg;
            this.showMessage = true;
            if (!persistant) setTimeout(() => {
                if (msg == this.message) this.showMessage = false;
            }, 2000);
        },

        waitFinishTyping() {
            this.typing_slowdown++;
            let current = this.typing_slowdown;
            setTimeout(() => {
                if (current == this.typing_slowdown) {
                    this.refreshUsers();
                }
            }, 300);
        }
    }
});

await app.importComponent('input-field', SITE_LOCATION + "pb-pubfiles/components/InputField.html");
await app.importComponent('input-select', SITE_LOCATION + "pb-pubfiles/components/InputSelect.html");
app.mount('.content');

await app.data.refreshUsers();
DashboardContentLoader.close();