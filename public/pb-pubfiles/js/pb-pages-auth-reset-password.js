import { Rable } from '../../../pb-pubfiles/js/rable.js';

const app = new Rable({
    data: {
        progress: 0,
        errorMessage: "",
        taskMessage: "For security, please identify yourself one more time.",
        passwordErrors: [],
        passwordSubErrors: [],
        passwordVerificationErrors: [],

        verificationToken: location.href.split('/')[location.href.split('/').length - 1],
        identifier: '',
        password: '',
        passwordVerification: '',
        canContinue: true,

        async funcContinue() {
            switch(this.progress) {
                case 0:
                    PB_API.post('auth/reset-password/validate-request', {
                        identifier: this.identifier,
                        verification: this.verificationToken
                    }).then(res => {
                        if (res.data.success) {
                            this.progress = 1;
                            this.taskMessage = "Enter your new password and confirm it.";
                        } else {
                            this.errorMessage = `${res.data.errorMessage} (${res.data.error})`;
                        }
                    });
                case 1:
                    if (this.password == this.passwordVerification) {

                    } else {
                        this.errorMessage
                    }
            }
        },

        passwordInputHandler(type) {
            switch(type) {
                case 0:
                    PB_API.post('auth/validate-password', {
                        password: this.password
                    }).then(res => {
                        res = res.data;
                        this.passwordErrors = [];
                        this.passwordSubErrors = [];
                        if (res.valid) {
                            this.canContinue = true;
                        } else {
                            this.canContinue = false;
                            res.issues.forEach(issue => {
                                switch(issue) {
                                    case 'uppercase':
                                        this.passwordErrors.push("Your password needs to contain an uppercase character (A-Z).");
                                        break;
                                    case 'lowercase':
                                        this.passwordErrors.push("Your password needs to contain a lowercase character (a-z).");
                                        break;
                                    case 'number':
                                        this.passwordErrors.push("Your password needs to contain a number (0-9).");
                                        break;
                                    case 'special':
                                        this.passwordErrors.push("Your password needs to contain a special character (ex. !@#$%^&*).");
                                        break;
                                    case 'length':
                                        this.passwordErrors.push(`Your password needs to contain at least ${res.data.minimumLength} characters (currently ${res.data.length}).`);
                                        break;
                                    case 'score': 
                                        var difference = res.data.minimumScore - res.data.score;
                                        var missingFactorCount = parseInt(difference / 0.2);
                                        if (missingFactorCount < 1) missingFactorCount = 1;
                                        missingFactorCount -= (res.issues.length - 1);

                                        if (missingFactorCount > 0) {
                                            this.passwordErrors.push(`Your password should meet at least ${missingFactorCount} of the following factors:`);
                                            Object.keys(res.factors).forEach(factor => {
                                                if (!res.factors[factor] && !res.data.enforcedPolicy.includes(factor)) switch(factor) {
                                                    case 'uppercase':
                                                        this.passwordSubErrors.push('Contains an uppercase character (A-Z).');
                                                        break;
                                                    case 'lowercase':
                                                        this.passwordSubErrors.push('Contains a lowercase character (a-z).');
                                                        break;
                                                    case 'number':
                                                        this.passwordSubErrors.push('Contains a number (0-9).');
                                                        break;
                                                    case 'special':
                                                        this.passwordSubErrors.push('Contains a special character (ex. !@#$%^&*).');
                                                        break;
                                                    case 'length':
                                                        this.passwordSubErrors.push(`Is at least ${res.data.minimumLength} characters long (currently ${res.data.length}).`);
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
        }
    }
});

app.mount('.content');