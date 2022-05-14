<div id="shell">
    <div class="output">{{ output }}</div>
    <div class="input">
        <input type="text" placeholder="Enter a command or run help to get a list of commands" :value="input" @keydown="keydownWorker(event)" autofocus>
        <div class="execute-command" @click="execute()">
            <i data-feather="chevron-right"></i>
        </div>
    </div>
</div>