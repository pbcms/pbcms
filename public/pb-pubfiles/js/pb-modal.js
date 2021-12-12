const PbModal = (function() {
    var modals = [];
    
    return new class PbModal {
        constructor() {
            if (document.querySelector("body > div.modals-container") == null) {
                const modalContainer = document.createElement("div");
                modalContainer.classList.add("modal-container");
                document.body.appendChild(modalContainer);
            }
        }

        create(properties = {}) {
            Object.assign({
                type: "default",
                title: "",
                subtitle: null,
                message: "",
                actions: []
            }, properties);

            switch(properties.type) {
                case 'default':
                    
            }
        }

        close() {

        }
    }
})();

Object.freeze(PbModal);

// PbModal.create({
//     title: "Modal name",
//     message: "This is the modal content.",
//     actions: [
//         {
//             content: "Okay!",
//             magic: "function",
//             action: function() {
//                 alert("Cool!");
//             }
//         },
//         {
//             content: "Close",
//             magic: "close",
//             style: "black"
//         }
//     ]
// });