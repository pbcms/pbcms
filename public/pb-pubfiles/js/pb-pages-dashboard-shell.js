import { Rable } from '../../pb-pubfiles/js/rable.js';

const api = PbAuth.apiInstance();
const app = new Rable({
    data: {
        input: '',
        output: '',
        history: [""],
        historyCurrent: 0,

        async execute() {
            let outputEl = document.querySelector('#shell .output');
            let scrolled = outputEl.scrollTop == outputEl.scrollHeight - outputEl.clientHeight;
            if (this.input !== '')  {
                if (this.input == 'clear') {
                    this.input = "";
                    this.output = "";
                    return;
                } else if (this.input == 'shell') {
                    this.input = "";
                    return;
                }
                
                let res = (await api.post('site/execute-command', {
                    input: this.input
                })).data;
    
                if (res.success) {
                    this.output += res.prompt + this.input + "\n";
                    this.output += res.output.replaceAll(/[\u001b\u009b][[()#;?]*(?:[0-9]{1,4}(?:;[0-9]{0,4})*)?[0-9A-ORZcf-nqry=><]/g, '');
                } else {
                    this.output = `${res.message} (${res.error})`;
                }
    
                this.history.push(`${this.input}`);
                this.input = "";

                if (scrolled) setTimeout(() => {
                    outputEl.scrollTop = outputEl.scrollHeight;
                }, 10);

                this.historyCurrent = 0;
            }
        },

        keydownWorker(e) {
            if (e.key == 'Enter') {
                this.execute();
            } else if (e.keyCode == '38') {
                this.historyCurrent++;
                if (this.historyCurrent > this.history.length - 1) this.historyCurrent = this.history.length - 1;
                console.log(this.historyCurrent);
                this.input = this.history[this.historyCurrent];
            } else if (e.keyCode == '40') {
                this.historyCurrent--;
                if (this.historyCurrent < 0) this.historyCurrent = 0;
                console.log(this.historyCurrent);
                this.input = this.history[this.historyCurrent];
            } 
        }
    }
});

app.mount('#shell');

DashboardContentLoader.close();