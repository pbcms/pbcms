<section class="page-introduction">
    <h1>
        Updates
    </h1>
    <p>
        Keep your installation and it's modules up-to-date.
    </p>
</section>

<section id="module-repositories" class="transparent no-margin overflow-scroll">
    <h2>
        Module repositories
    </h2>

    <table>
        <thead>
            <tr>
                <th>
                    Name
                </th>
                <th>
                    Url
                </th>
                <th>
                    Enabled
                </th>
                <th>
                    Action
                </th>
            </tr>
        </thead>
        <tbody>
            <tr :for="repositories as index => repo">
                <td>
                    {{ repo.name }}
                </td>
                <td>
                    {{ repo.url }}
                </td>
                <td>
                    {{ repo.enabled ? "Yes" : "No" }}
                </td>
                <td class="repo-actions">
                    <a href="#" @click="refreshRepo(index, event)">
                        refresh
                    </a>

                    <span>-</span>

                    <a href="#" @click="removeRepo(index, event)" class="red">
                        {{ typeof repo.removed == 'undefined' ? "remove" : "CONFIRM" }}
                    </a>

                    <span>-</span>

                    <a href="#" @click="toggleRepo(index, event)">
                        {{ repo.enabled ? "disable" : "enable" }}
                    </a>
                </td>
            </tr>
            <tr>
                <td>
                    <input-field &new_repo_name="value" $placeholder="Name"></input-field>
                </td>
                <td>
                    <input-field &new_repo_url="value" $placeholder="Url"></input-field>
                </td>
                <td>
                    <input-toggle &new_repo_enabled="checked"></input-toggle>
                </td>
                <td class="repo-actions">
                    <a href="#" @click="saveRepo(event)">
                        save repository
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</section>

<section id="module-updates" class="no-margin transparent overflow-scroll">
    <h1>
        Module updates
    </h1>

    <table :if="updates.length > 0">
        <thead>
            <tr>
                <th>
                    Module
                </th>
                <th>
                    Local
                </th>
                <th>
                    Newest
                </th>
                <th>
                    Action
                </th>
            </tr>
        </thead>
        <tbody>
            <tr :for="updates as index => module">
                <td>
                    {{ module.repo.name }}
                </td>
                <td>
                    {{ module.local == null ? "" : module.local.version }}
                </td>
                <td>
                    {{ module.repo.version }}
                </td>
                <td class="repo-actions">
                    <a href="#" @click="updateModule(index, event)">
                        update
                    </a>
                </td>
            </tr>
        </tbody>
    </table>

    <p :else>
        <br>
        No updates available!
    </p>
</section>