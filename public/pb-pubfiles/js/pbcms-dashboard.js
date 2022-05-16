class DashboardContentLoader {
    static status = 'loading';

    static cooldown(time = 350) {
        this.status = 'loading';
        setTimeout(() => {
            if (this.status == 'toggle') {
                this.toggle(time, true);
            } else if (this.status == 'close') {
                this.close(time, true);
            } else if (this.status == 'open') {
                this.open(time, true);
            } else {
                this.status = 'ready';
            }
        }, time);
    }

    static toggle(time = 350, force = false) {
        if (!force && this.status == 'loading') {
            this.status = 'toggle';
        } else {
            this.status = 'ready';
            document.querySelector('.content-container').classList.toggle('content-loading');
            this.cooldown(time);
        }
    }

    static close(time = 350, force = false) {
        if (!force && this.status == 'loading') {
            this.status = 'close';
        } else {
            this.status = 'ready';
            document.querySelector('.content-container').classList.remove('content-loading');
            this.cooldown(time);
        }
    }

    static open(time = 350, force = false) {
        if (!force && this.status == 'loading') {
            this.status = 'open';
        } else {
            this.status = 'ready';
            document.querySelector('.content-container').classList.add('content-loading');
            this.cooldown(time);
        }
    }
}

//Adjust the default position of the active section.
(function() {
    let sideOpts = document.querySelector('.sidebar .sidebar-options');
    let activeSect = sideOpts.querySelector('a[active]');
    let backupSect = sideOpts.querySelector('a[backup-active]');

    if (!activeSect && backupSect) {
        activeSect = backupSect;
        backupSect.setAttribute('active', '');
    }

    if (activeSect) {
        sideOpts.scrollTop = activeSect.offsetTop - sideOpts.offsetTop - (sideOpts.clientHeight / 2) + (activeSect.clientHeight / 2);
        sideOpts.style.scrollBehavior = 'smooth';
    }

    DashboardContentLoader.cooldown();
})();