import { Rable } from '../../pb-pubfiles/js/rable.js';

PbAuth.apiInstance().get('user/info').then(async res => {
    if (!res.data.success) {
        alert(`${res.data.message} (${res.data.error})`);
    } else {
        const profileoverview = new Rable({ data: {...res.data.user} });
        profileoverview.mount('.profile-overview');

        const profileeditor = new Rable({
            data: Object.assign({}, res.data.user, {
                password: "",
                message: "",
                showMessage: "",

                usernames_enabled: true,
                usernames_required: false,
                usernames_minimum_length: 5,
                usernames_maximum_length: 5,
                password_valid: true,

                typing(component) {
                    this.message = "";
                    this.showMessage = false;

                    switch(component.name) {
                        case 'firstname':
                            component.errors = []; component.sub_errors = [];
                            if (component.value == '') component.errors.push('This field cannot be empty!');
                            break;
                        case 'lastname':
                            component.errors = []; component.sub_errors = [];
                            if (component.value == '') component.errors.push('This field cannot be empty!');
                            break;
                        case 'email':
                            component.errors = []; component.sub_errors = [];
                            if (component.value == '') component.errors.push('This field cannot be empty!');
                            if (!component.value.match(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/)) component.errors.push('Not a valid E-mail address!');
                            break;
                        case 'username':
                            component.errors = []; component.sub_errors = [];
                            if (this.usernames_enabled && this.usernames_required && component.value == '') component.errors.push('This field cannot be empty!');
                            if (this.usernames_enabled && (this.usernames_required || component.value != '') && component.value.length < this.usernames_minimum_length) component.errors.push(`Username must be at least ${this.usernames_minimum_length} characters long! (currently ${component.value.length})`);
                            if (this.usernames_enabled && (this.usernames_required || component.value != '') && component.value.length > this.usernames_maximum_length) component.errors.push(`Username can be ${this.usernames_maximum_length} characters long at most! (currently ${component.value.length})`);
                            if (!component.value.match(/^[a-zA-Z0-9._-]+$/)) component.errors.push('Contains illegal charachters!');
                            break;
                        case 'password':
                            if (component.value != '') {
                                PB_API.post('auth/validate-password', {
                                    password: component.value
                                }).then(res => {
                                    res = res.data;
                                    component.errors = []; component.sub_errors = [];
                                    if (res.valid) {
                                        this.password_valid = true;
                                    } else {
                                        this.password_valid = false;
                                        res.issues.forEach(issue => {
                                            switch(issue) {
                                                case 'uppercase':
                                                    component.errors.push("Your password needs to contain an uppercase character (A-Z).");
                                                    break;
                                                case 'lowercase':
                                                    component.errors.push("Your password needs to contain a lowercase character (a-z).");
                                                    break;
                                                case 'number':
                                                    component.errors.push("Your password needs to contain a number (0-9).");
                                                    break;
                                                case 'special':
                                                    component.errors.push("Your password needs to contain a special character (ex. !@#$%^&*).");
                                                    break;
                                                case 'length':
                                                    component.errors.push(`Your password needs to contain at least ${res.data.minimumLength} characters (currently ${res.data.length}).`);
                                                    break;
                                                case 'score': 
                                                    var difference = res.data.minimumScore - res.data.score;
                                                    var missingFactorCount = parseInt(difference / 0.2);
                                                    if (missingFactorCount < 1) missingFactorCount = 1;
                                                    missingFactorCount -= (res.issues.length - 1);
            
                                                    if (missingFactorCount > 0) {
                                                        component.errors.push(`Your password should meet at least ${missingFactorCount} of the following factors:`);
                                                        Object.keys(res.factors).forEach(factor => {
                                                            if (!res.factors[factor] && !res.data.enforcedPolicy.includes(factor)) switch(factor) {
                                                                case 'uppercase':
                                                                    component.sub_errors.push('Contains an uppercase character (A-Z).');
                                                                    break;
                                                                case 'lowercase':
                                                                    component.sub_errors.push('Contains a lowercase character (a-z).');
                                                                    break;
                                                                case 'number':
                                                                    component.sub_errors.push('Contains a number (0-9).');
                                                                    break;
                                                                case 'special':
                                                                    component.sub_errors.push('Contains a special character (ex. !@#$%^&*).');
                                                                    break;
                                                                case 'length':
                                                                    component.sub_errors.push(`Is at least ${res.data.minimumLength} characters long (currently ${res.data.length}).`);
                                                                    break;
                                                            }
                                                        });
                                                    }
                                                    break;
                                            }
                                        })
                                    }
                                });
                            }

                            break;
                    }
                },

                submit(e = false) {
                    if (!e || e.key == 'Enter') {
                        if (this.firstname == "") return this.displayMessage("Enter your first name.");
                        if (this.lastname == "") return this.displayMessage("Enter your last name.");
                        if (this.email == "") return this.displayMessage("Enter your E-mail address.");
                        if (!this.email.match(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/)) return this.displayMessage("Enter a valid E-mail address.");
                        
                        if (this.usernames_enabled && this.usernames_required && this.username == '') return this.displayMessage("Enter a username.");
                        if (this.usernames_enabled && this.username != '' && !this.username.match(/^[a-zA-Z0-9._-]+$/)) return this.displayMessage("Remove illegal characters from your username.");
                    
                        if (this.password != '' && !this.password_valid) return this.displayMessage("Correct any errors in your password.");

                        let result = {
                            firstname: this.firstname,
                            lastname: this.lastname,
                            email: this.email
                        }

                        if (this.usernames_enabled && this.username != '') result.username = this.username;
                        if (this.password != '') result.password = this.password;

                        console.log(result);

                        PbAuth.apiInstance().patch('user/update', result).then(res => {
                            console.log(res.data);
                            if (res.data.success) {
                                profileoverview.data.firstname = this.firstname;
                                profileoverview.data.lastname = this.lastname;
                                profileoverview.data.username = this.username;
                                profileoverview.data.email = this.email;
                                this.password = "";
                                this.displayMessage("Profile updated successfully!");
                            } else {
                                this.displayMessage(`${res.data.message} (${res.data.error})`);
                            }
                        })
                    }
                },

                displayMessage(msg, persistant = false) {
                    this.message = msg;
                    this.showMessage = true;
                    if (!persistant) setTimeout(() => {
                        if (msg == this.message) this.showMessage = false;
                    }, 2000);
                }
            })
        });

        PB_API.get('auth/account-policies').then(res => {
            if (res.data.success) {
                profileeditor.data.usernames_enabled = res.data.policies['usernames-enabled'];
                profileeditor.data.usernames_required = res.data.policies['usernames-required'];
                profileeditor.data.usernames_minimum_length = res.data.policies['usernames-minimum-length'];
                profileeditor.data.usernames_maximum_length = res.data.policies['usernames-maximum-length'];
            }
        })

        await profileeditor.importComponent('input-field', SITE_LOCATION + "pb-pubfiles/components/InputField.html");
        await profileeditor.importComponent('input-toggle', SITE_LOCATION + "pb-pubfiles/components/InputToggle.html");
        await profileeditor.importComponent('input-select', SITE_LOCATION + "pb-pubfiles/components/InputSelect.html");
        profileeditor.mount('.profile-editor');
    }
});