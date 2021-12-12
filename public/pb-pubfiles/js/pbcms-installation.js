(async function() {
    class Section {
        constructor(sectionname) {
            document.querySelectorAll('.current-section').forEach(el => el.classList.remove('current-section'));
            document.querySelectorAll('[section-name=' + sectionname + ']').forEach(el => el.classList.add('current-section'));
            document.querySelector('li.current-section').addEventListener('click', e => new Section(sectionname));
            document.querySelector('li.current-section').classList.add('was-active');
            (inputEl => inputEl ? inputEl.focus() : 0)(document.querySelector('section.current-section input'));
        }
    }
    
    class Installation {
        constructor() {
            this.progress = 0;
            this.sections = ['introduction', 'personalisation', 'database', 'account', 'finalize'];
            this.sectionHandlers = {
                introduction: () => {
                    this.sectionFinished();
                },
                personalisation: () => {
                    function validate() {
                        let url;
      
                        try {
                            url = new URL(document.querySelector('input[name=site-location]').value);
                        } catch (_) {
                            return false;  
                        }
    
                        return url.protocol === "http:" || url.protocol === "https:";
                    }
    
                    if (validate()) {
                        this.sectionFinished();
                    } else {
                        document.querySelector('input[name=site-location]').parentNode.classList.add('red-border');
                        document.querySelector('input[name=site-location]').focus();
                    }
                },
                database: () => {
                    var formData = new FormData();
                    formData.append('DATABASE_VALIDATION', '1');
                    formData.append('DB_HOSTNAME', document.querySelector('form.pbcms-installation input[name=db-hostname]').value);
                    formData.append('DB_USERNAME', document.querySelector('form.pbcms-installation input[name=db-username]').value);
                    formData.append('DB_PASSWORD', document.querySelector('form.pbcms-installation input[name=db-password]').value);
                    formData.append('DB_DATABASE', document.querySelector('form.pbcms-installation input[name=db-database]').value);
    
                    const sec = document.querySelector('section[section-name=database]');
    
                    fetch(location.href, { 
                        method: 'POST', 
                        body: formData 
                    }).then(res => res.json()).then(res => {
                        console.log(res);
                        if (res.success) {
                            sec.querySelectorAll('p.error').forEach(el => el.innerHTML = '');
                            this.sectionFinished();
                        } else {
                            sec.querySelectorAll('.input-field').forEach(el => el.classList.remove('red-border'));
                            switch(res.status) {
                                case -1:
                                    sec.querySelector('p.error').innerHTML = res.error;
                                    break;
                                case 2002:
                                    sec.querySelector('input[name=db-hostname]').parentNode.classList.add('red-border');
                                    sec.querySelector('p.error').innerHTML = "Unknown or invalid hostname.";
                                    break;
                                case 1045:
                                    sec.querySelector('input[name=db-username]').parentNode.classList.add('red-border');
                                    sec.querySelector('input[name=db-password]').parentNode.classList.add('red-border');
                                    sec.querySelector('p.error').innerHTML = "Invalid username or password.";
                                    break;
                                case 1044:
                                    sec.querySelector('input[name=db-database]').parentNode.classList.add('red-border');
                                    sec.querySelector('p.error').innerHTML = "User does not have access to the database or the database does not exist.";
                                    break;
                                default:
                                    sec.querySelector('p.error').innerHTML = `Error ${res.status}: ${res.error}`;
                                    break;
                            }
    
                            if (sec.querySelector('.red-border')) sec.querySelector('.red-border input').focus();
                        }
                    });
                },
                account: () => {
                    var formData = new FormData();
                    formData.append('USER_VALIDATION', '1');
                    formData.append('USER_FIRSTNAME', document.querySelector('form.pbcms-installation input[name=user-firstname]').value);
                    formData.append('USER_LASTNAME', document.querySelector('form.pbcms-installation input[name=user-lastname]').value);
                    formData.append('USER_USERNAME', document.querySelector('form.pbcms-installation input[name=user-username]').value);
                    formData.append('USER_EMAIL', document.querySelector('form.pbcms-installation input[name=user-email]').value);
                    formData.append('USER_PASSWORD', document.querySelector('form.pbcms-installation input[name=user-password]').value);
    
                    const sec = document.querySelector('section[section-name=account]');
    
                    fetch(location.href, { 
                        method: 'POST', 
                        body: formData 
                    }).then(res => res.json()).then(res => {
                        console.log(res);
                        if (res.success) {
                            sec.querySelectorAll('p.error').forEach(el => el.innerHTML = '');
                            this.sectionFinished();
                        } else {
                            sec.querySelectorAll('.input-field').forEach(el => el.classList.remove('red-border'));
                            switch(res.status) {
                                case -1:
                                    sec.querySelector('p.error').innerHTML = res.error;
                                    break;
                                case 1:
                                    sec.querySelector('input[name=user-password]').parentNode.classList.add('red-border');

                                    var reqs = ['uppercase', 'lowercase', 'number', 'special', 'length'];
                                    var message = 'The following requirements were not met: ';
                                    var missing = [];

                                    reqs.forEach(req => {
                                        if (!res.requirements[req]) missing.push(req);
                                    });

                                    message += ([...missing]).slice(0, -1).join(', ') + ' and ' + missing.slice(-1) + '.';
                                    message.replace('special', 'special character');
                                    message.replace('length', 'length (min. ' + res.requirements.minLength + ')');

                                    sec.querySelector('p.error').innerHTML = message;
                                    break;
                                default:
                                    sec.querySelector('p.error').innerHTML = `Error ${res.status}: ${res.error}`;
                                    break;
                            }
    
                            if (sec.querySelector('.red-border')) sec.querySelector('.red-border input').focus();
                        }
                    });
                },
                finalize: () => {
                    var formData = new FormData();
                    formData.append('FINALIZE', '1');
                    formData.append('SITE_TITLE', document.querySelector('form.pbcms-installation input[name=site-title]').value);
                    formData.append('SITE_DESCRIPTION', document.querySelector('form.pbcms-installation input[name=site-description]').value);
                    formData.append('SITE_LOCATION', document.querySelector('form.pbcms-installation input[name=site-location]').value);
                    formData.append('SITE_INDEXING', document.querySelector('form.pbcms-installation input[name=allow-indexing]').value);
                    formData.append('SITE_EMAIL', document.querySelector('form.pbcms-installation input[name=site-email]').value);
                    formData.append('SITE_EMAIL_PUBLIC', document.querySelector('form.pbcms-installation input[name=publish-email]').value);
                    formData.append('DB_HOSTNAME', document.querySelector('form.pbcms-installation input[name=db-hostname]').value);
                    formData.append('DB_USERNAME', document.querySelector('form.pbcms-installation input[name=db-username]').value);
                    formData.append('DB_PASSWORD', document.querySelector('form.pbcms-installation input[name=db-password]').value);
                    formData.append('DB_DATABASE', document.querySelector('form.pbcms-installation input[name=db-database]').value);
                    formData.append('USER_FIRSTNAME', document.querySelector('form.pbcms-installation input[name=user-firstname]').value);
                    formData.append('USER_LASTNAME', document.querySelector('form.pbcms-installation input[name=user-lastname]').value);
                    formData.append('USER_USERNAME', document.querySelector('form.pbcms-installation input[name=user-username]').value);
                    formData.append('USER_EMAIL', document.querySelector('form.pbcms-installation input[name=user-email]').value);
                    formData.append('USER_PASSWORD', document.querySelector('form.pbcms-installation input[name=user-password]').value);
                    const sec = document.querySelector('section[section-name=finalize]');
    
                    fetch(location.href, { 
                        method: 'POST', 
                        body: formData 
                    }).then(res => res.json()).then(res => {
                        console.log(res);
                        if (res.success) {
                            if (res.migration_logs.includes("Unable to insert newly executed migrations into migrations database")) {
                                sec.querySelector('p.error').innerHTML = "<b>!!!INSTALLATION SUCCEEDED, BUT CRITICAL ERROR OCCURED WHILE REGISTERING DATABASE MIGRATIONS!!!</b><br><br>" + res.migration_logs;
                            } else {
                                location.href = document.querySelector('form.pbcms-installation input[name=site-location]').value;
                            }
                        } else {
                            if (res.error == 'config_file_creation_error') {
                                sec.querySelector('p.error').innerHTML = res.message;
                            } else {
                                sec.querySelector('p.error').innerHTML = "<pre>" + JSON.stringify(res) + "</pre>";
                            }
                        }
                    });
                }
            }
    
            this.startInstallation();
        }
    
        startInstallation() {
            document.querySelectorAll('.process-section').forEach(el => el.addEventListener('click', e => this.processSection(this.sections[this.progress])));
            document.addEventListener('keyup', e => {
                if (e.key == "Enter" || e.key == 13) {
                    this.processSection(this.sections[this.progress]);
                }
            });
    
            document.querySelectorAll('input').forEach(el => el.addEventListener('input', e => {
                el.parentElement.classList.remove('red-border');
            }));
    
            new Section('introduction');
        }
    
        processSection(sec) {
            this.sectionHandlers[sec]();
        }
    
        sectionFinished() {
            this.progress = this.sections.findIndex(item => item == document.querySelector('li.current-section').getAttribute('section-name')) + 1;
            if (this.progress == this.sections.length) {
                location.reload();
            } else {
                new Section(this.sections[this.progress]);
            }
        }
    
        static async detectSiteLocation() {
            var siteLocation = null;
            var pathParts = location.pathname;
            var magicData = new FormData();
                magicData.append('SITE_LOC_DETECTION', '1');
    
            for(var i = 0; i < pathParts.length; i++) {
                if (!siteLocation) {
                    var curPath = ([...pathParts]).slice(0, i+1).join('/');
                    const res = await (await fetch(location.origin + '/' + curPath, {
                        method: 'POST',
                        body: magicData
                    })).text();
                    siteLocation = location.origin + '/' + (curPath == '/' ? '' : curPath);
                    console.log(curPath);
                }
            }
    
            return (loc => loc + (loc.slice(-1) == '/' ? '' : '/'))(siteLocation ? siteLocation : location.href);
        }
    }

    new Section('preloader');

    var preventInstallation = true;
    setTimeout(() => {
        if (!preventInstallation) new Installation();
        preventInstallation = false;
    }, 400);
    
    Installation.detectSiteLocation().then(loc => {
        document.querySelector('input[name=site-location]').value = loc;

        if (!preventInstallation) new Installation();
        preventInstallation = false;
    });

    (debug => debug ? (debug.getAttribute('content') == 'true' ? (() => {window.Installation = Installation; window.Section = Section})() : 0) : 0)(document.querySelector('meta[name=pbcms_debug_mode]'));
})();