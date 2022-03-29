<section class="page-introduction">
    <h1>
        Permissions
    </h1>
    <p>
        Assign, reject and clear permissions.
    </p>
</section>

<section id="new-permission">
    <h2>
        New permission
    </h2>

    <br>
    <input-field &new_permission_node="value" $placeholder="Permission"></input-field>
    <input-field &new_permission_target="value" $placeholder="Target"></input-field>
    <input-toggle &new_permission_granted="checked" $label="Granted"></input-toggle>

    <div class="submitter">
        <button @click="createPermission()">
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
                Permission
            </th>
            <th>
                Target
            </th>
            <th>
                Type
            </th>
            <th class="medium">
                Created
            </th>
            <th class="medium">
                Updated
            </th>
            <th class="medium">
                Actions
            </th>
        </thead>
        <tbody>
            <tr :for="permissions as index => perm">
                <td>
                    {{perm.id}}
                </td>
                <td>
                    {{perm.permission}}
                </td>
                <td>
                    {{perm.target}}
                </td>
                <td>
                    {{perm.type}}
                </td>
                <td>
                    {{perm.created}}
                </td>
                <td>
                    {{perm.updated}}
                </td>
                <td class="multiple-actions">
                    <a href="@" class="red" @click="clearPermission(perm.id, event)">
                        clear
                    </a>
                    <span>-</span>
                    <a href="#" @click="perm.type == 'Granted' ? rejectPermission(perm.target, perm.permission, event) : grantPermission(perm.target, perm.permission, event)">
                        {{ perm.type == 'Granted' ? 'reject' : 'grant' }}
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</section>