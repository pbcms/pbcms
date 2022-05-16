(function() {
    var updatedInputs = [];
    document.querySelectorAll(".policy-list tbody tr input").forEach(el => {
        el.addEventListener("input", e => {
            if (!updatedInputs.includes(el)) updatedInputs.push(el);
            document.querySelector('section.policy-list tbody tr[policy-name=' + el.getAttribute('name') + '] #policy-name').style.fontStyle = "italic";
        });
    });

    document.querySelector("a.button.update-policies").addEventListener("click", e => {
        const api = PbAuth.apiInstance();
        const params = {};
        updatedInputs.forEach(current => {
            params[current.getAttribute('name')] = (current.type == 'checkbox' ? (current.checked ? 1 : 0) : current.value);
        });
        
        api.post('policy/update', params).then(res => {
            if (res.data.success == undefined) {
                alert('An unknown error has occured! (unknown_error)');
            } else if (res.data.success == false) {
                alert(res.data.message + ' (' + res.data.error + ')');
            } else {
                alert("success!");
            }
        });
    });

    DashboardContentLoader.close();
})();