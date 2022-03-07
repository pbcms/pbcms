class Rable {
    #root = null;
    #ready = false;
    #listeners = {};
    data = {};
    functions = {};

    constructor(options = {}) {
        const eventTransporter = new EventTarget;
        this.eventTransporter = eventTransporter;

        const validator = {
            get: (target, key) => {
                if (typeof target[key] === 'object' && target[key] !== null) {
                    return new Proxy(target[key], validator)
                } else {
                    return target[key];
                }
            },
            set: (obj, prop, value) => {
                if (typeof value == 'function') value = value.bind(this.data);
                obj[prop] = value;
                eventTransporter.dispatchEvent(new CustomEvent('triggerListeners', { detail: { listeners: 'data:updated' } } ));
                return true;
            }
        }

        this.data = new Proxy({}, validator);
        if (options.data) Object.keys(options.data).forEach(key => this.data[key] = options.data[key]);
        this.addEventListeners();
    }

    addEventListeners() {
        this.eventTransporter.addEventListener('triggerListeners', e => this.triggerListeners(e.detail.listeners));
        this.eventTransporter.addEventListener('retrieveData', e => e.detail.resolve(this.data));
        this.eventTransporter.addEventListener('retrieveScopeData', e => e.detail.resolve(this.data));

        this.eventTransporter.addEventListener('registerListener', e => {
            if (!this.#listeners[e.detail.type]) this.#listeners[e.detail.type] = [];
            this.#listeners[e.detail.type].push(e.detail.listener);
        });
    }

    triggerListeners(listener) {
        if (!this.#ready) return false;
        if (this.#listeners[listener]) {
            this.#listeners[listener].forEach(listener => listener());
            return true;
        } else {
            return false;
        }
    }

    mount(query) {
        let queried = document.querySelector(query);
        if (queried) {
            this.#root = queried;
            processElementAttributes(this.#root, this.eventTransporter);
            processTextNodes(this.#root, this.eventTransporter);
            this.#ready = true;
            this.triggerListeners('data:updated');
            return true;
        } else {
            return false;
        }
    }
}

function processTextNodes(el, eventTransporter) {
    let nodes = el.childNodes;
    nodes.forEach(node => {
        if (!node.parentNode.doNotProcessTextNodes) {
            if (node.nodeName == '#text') {
                node.originalData = node.data;
                let matches = [...node.data.matchAll(/{{(.*?)}}/g)];
                if (matches.length > 0) matches.forEach(match => {
                    eventTransporter.dispatchEvent(new CustomEvent('registerListener', {
                        detail: {
                            type: 'data:updated',
                            listener: async () => {
                                const data = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveData', { detail: { resolve } })));
                                const scopeData = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveScopeData', { detail: { resolve } })));
                                node.data = node.originalData.replaceAll(/{{(.*?)}}/g, (match, target) => {
                                    let keys = Object.keys(data);
                                    keys.push('return ' + target);
                                    let runner = Function.apply({}, keys);
                                    try {
                                        let res = runner.apply(scopeData, Object.values(data));
                                        return res;
                                    } catch(e) {
                                        console.error(e);
                                        return undefined;
                                    }
                                });
                            }
                        }
                    }));
                });
            } else if (node.childNodes.length > 0) {
                processTextNodes(node, eventTransporter);
            }
        }
    });
}

function processElementAttributes(el, eventTransporter) {
    const logic_if = [];
    var latestif = 0;

    // IF - ELSEIF - ELSE
    eventTransporter.dispatchEvent(new CustomEvent('registerListener', {
        detail: {
            type: 'data:updated',
            listener: async () => {
                for(var i = 0; i < logic_if.length; i++) {
                    var prevRes = false;
                    const logic = logic_if[i];

                    for(var j = 0; j < logic.length; j++) {
                        const block = logic[j];
                        if (!prevRes) {
                            if (block.validator) {
                                const data = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveData', { detail: { resolve } })));
                                const scopeData = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveScopeData', { detail: { resolve } })));
                                let keys = Object.keys(data);
                                keys.push('return ' + block.validator);
                                let runner = Function.apply({}, keys);
                                try {
                                    prevRes = runner.apply(scopeData, Object.values(data));
                                    if (prevRes) {
                                        block.node.style.display = null;
                                    } else {
                                        block.node.style.display = 'none';
                                    }
                                } catch(e) {
                                    console.error(e);
                                    block.node.style.display = 'none';
                                }
                            } else {
                                block.node.style.display = null;
                            }
                        } else {
                            block.node.style.display = 'none';
                        }
                    }
                }
            }
        }
    }));

    let nodes = el.childNodes;
    nodes.forEach(node => {
        if (node.childNodes.length > 0) processElementAttributes(node, eventTransporter);
        if (node.nodeName != '#text') {
            [...node.attributes].forEach(async attribute => {
                var attrName = attribute.name;
                if (attrName.slice(0, 1) == '@') attrName = ':on:' + attrName.slice(1);
                if (attrName.slice(0, 1) == ':' || attrName.slice(0, 4) == 'rbl:' || attrName.slice(0, 6) == 'rable:') {
                    let processedName = attrName.split(':').slice(1);
                    if (processedName[0]) switch(processedName[0]) {
                        case 'on':
                        case 'event':
                            if (processedName[1] && attribute.value !== '') {
                                const data = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveData', { detail: { resolve } })));
                                const scopeData = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveScopeData', { detail: { resolve } })));
                                node.addEventListener(processedName[1], e => {
                                    const localData = {...data};
                                    localData.event = e;
                                    let keys = Object.keys(localData);
                                    keys.push(attribute.value);
                                    let runner = Function.apply(localData, keys);
                                    try {
                                        runner.apply(scopeData, Object.values(localData));
                                    } catch(e) {
                                        console.error(e);
                                    }
                                });

                                node.removeAttribute(attribute.name);
                            }
    
                            break;
                        case 'for':
                            const parentNode = node.parentNode;
                            const clonedNode = node.cloneNode(true);
                            clonedNode.removeAttribute(attribute.name);
                            node.doNotProcessTextNodes = true;
                            eventTransporter.dispatchEvent(new CustomEvent('registerListener', {
                                detail: {
                                    type: 'data:updated',
                                    listener: async () => {
                                        const replacementNodes = [];
                                        const data = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveData', { detail: { resolve } })));
                                        const asloop = (res => (res.length > 0 ? res[0].slice(1, 4) : null))([...attribute.value.matchAll(/^(.*)\sas\s(.*)\s\=\>\s(.*)$/g)]),
                                              inloop = (res => (res.length > 0 ? res[0].slice(1, 3) : null))([...attribute.value.matchAll(/^(.*)\sin\s(.*)$/g)]);

                                        if (asloop) {
                                            var target = asloop[0];
                                            var key = asloop[1];
                                            var value = asloop[2];
                                        } else if (inloop) {
                                            var target = inloop[1];
                                            var key = null;
                                            var value = inloop[0];
                                        } else {
                                            console.error(attribute.value, "is not a valid loop statement.");
                                            return;
                                        }

                                        parentNode.innerHTML = '';
                                        if (typeof data[target] == 'object') {
                                            const keys = Object.keys(data[target]);
                                            const values = Object.values(data[target]);
                                            for (var i = 0; i < keys.length; i++) {
                                                const item = keys[i];
                                                const replacementNode = clonedNode.cloneNode(true);
                                                const updatedData = {...data};
                                                updatedData[value] = data[target][item];
                                                if (key) updatedData[key] = item;

                                                const temporaryEventTransporter = new EventTarget;
                                                temporaryEventTransporter.addEventListener('retrieveData', e => e.detail.resolve(updatedData));   
                                                temporaryEventTransporter.addEventListener('registerListener', e => e.detail.listener());                                     
                                                temporaryEventTransporter.addEventListener('retrieveScopeData', e => e.detail.resolve(data));

                                                processElementAttributes(replacementNode, temporaryEventTransporter);
                                                processTextNodes(replacementNode, temporaryEventTransporter);
                                                parentNode.appendChild(replacementNode);
                                            }
                                        } else {
                                            console.error("Targeted data-item is not a valid object.");
                                        }
                                    }
                                }
                            }));
                            break;
                        case 'value':
                            if (typeof node.value == 'string') {
                                const data = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveData', { detail: { resolve } })));
                                eventTransporter.dispatchEvent(new CustomEvent('registerListener', {
                                    detail: {
                                        type: 'data:updated',
                                        listener: async () => node.value = data[attribute.value]
                                    }
                                }));

                                node.addEventListener('input', e => {
                                    data[attribute.value] = node.value;
                                });
                            } else if (node.isContentEditable) {
                                //When updating the innerText property, you lose focus on the element. Therefor, when content of the element is updated itself it should not be updated.
                                var updateData = true;
                                const data = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveData', { detail: { resolve } })));
                                eventTransporter.dispatchEvent(new CustomEvent('registerListener', {
                                    detail: {
                                        type: 'data:updated',
                                        listener: async () => {
                                            if (updateData) node.innerText = data[attribute.value];
                                            if (!updateData) updateData = true;
                                        }
                                    }
                                }));

                                node.addEventListener('input', e => {
                                    updateData = false;
                                    data[attribute.value] = node.innerText;
                                });
                            }
                            break;
                        case 'checked':
                            if (typeof node.checked == 'boolean') {
                                const data = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveData', { detail: { resolve } })));
                                eventTransporter.dispatchEvent(new CustomEvent('registerListener', {
                                    detail: {
                                        type: 'data:updated',
                                        listener: async () => node.checked = data[attribute.value]
                                    }
                                }));

                                node.addEventListener('input', e => {
                                    data[attribute.value] = node.checked;
                                });
                            }
                            break;
                        case 'if':
                            if (logic_if[latestif]) latestif++;
                            logic_if[latestif] = [];
                            logic_if[latestif].push({
                                node: node,
                                validator: attribute.value
                            });
                            break;
                        case 'elseif':
                        case 'else-if':
                            if (!logic_if[latestif]) {
                                console.error("If statement should start with if block!");
                            } else {
                                logic_if[latestif].push({
                                    node: node,
                                    validator: attribute.value
                                });
                            }
                            break;
                        case 'else':
                            if (!logic_if[latestif]) {
                                console.error("If statement should start with if block!");
                            } else {
                                logic_if[latestif].push({
                                    node: node
                                });

                                latestif++;
                            }
                            break;
                        case 'class':
                            if (!processedName[1]) {
                                console.error("No class defined!");
                                return;
                            }
                            const className = processedName[1];
                            eventTransporter.dispatchEvent(new CustomEvent('registerListener', {
                                detail: {
                                    type: 'data:updated',
                                    listener: async () => {
                                        const data = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveData', { detail: { resolve } })));
                                        const scopeData = await new Promise(resolve => eventTransporter.dispatchEvent(new CustomEvent('retrieveScopeData', { detail: { resolve } })));
                                        let keys = Object.keys(data);
                                        keys.push('return ' + attribute.value);
                                        let runner = Function.apply({}, keys);
                                        try {
                                            let res = runner.apply(scopeData, Object.values(data));
                                            if (res) {
                                                node.classList.add(className);
                                            } else {
                                                node.classList.remove(className);
                                            }
                                        } catch(e) {
                                            console.error(e);
                                            node.style.display = 'none';
                                        }
                                    }
                                }
                            }));
                            break;
                    }
                }
            });
        }
    });
}

export { Rable };
export default Rable;
