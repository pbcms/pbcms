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
})();