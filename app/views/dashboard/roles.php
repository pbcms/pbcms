<section class="page-introduction">
    <h1>
        Roles
    </h1>
    <p>
        Manage roles and their weights.
    </p>
</section>

<section id="new-role">
    <h2>
        New role
    </h2>

    <br>
    <input-field &new_role_name="value" $placeholder="Name"></input-field>
    <input-field &new_role_description="value" $placeholder="Description"></input-field>
    <input-field &new_role_weight="value" $placeholder="Weight" $type="number"></input-field>

    <div class="submitter">
        <button @click="createRole()">
            Create
        </button>
        <p class="message" :class:show="showMessage">
            {{ message }}
        </p>
    </div>
</section>

<section class="no-margin transparent overflow-scroll">
    <table>
        <thead>
            <th class="smaller">
                ID
            </th>
            <th>
                Name
            </th>
            <th class="bigger">
                Description
            </th>
            <th class="smaller">
                Weight
            </th>
            <th class="medium">
                Created
            </th>
            <th class="medium">
                Updated
            </th>
            <th>
                Actions
            </th>
        </thead>
        <tbody>
            <tr :for="role in roles">
                <td>
                    {{role.id}}
                </td>
                <td>
                    {{role.name}}
                </td>
                <td>
                    {{role.description}}
                </td>
                <td class="weight-editor">
                    {{role.weight}}
                    <div>
                        <span @click="moveRole(role.id, role.weight - 1)"><i data-feather="chevron-up"></i></span>
                        <span @click="moveRole(role.id, role.weight + 1)"><i data-feather="chevron-down"></i></span>
                    </div>
                </td>
                <td>
                    {{role.created}}
                </td>
                <td>
                    {{role.updated}}
                </td>
                <td class="multiple-actions">
                    <a href="#" class="red" @click="deleteRole(role.id, event)">
                        delete
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</section>