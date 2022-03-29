<section class="profile-overview">
    <div class="profile-image">
        <img :bind:src="picture.url">
    </div>
    <div class="profile-details" test.atttr='1'>
        <h1 id="user-fullname">
            {{ firstname }} {{ lastname }}
        </h1>
        <p id="user-properties">
            <span class="capitalize">
                {{ type }} account
            </span>
            <span>
                {{ email }}
            </span>
            <span :if="username">
                {{ username }}
            </span>
        </p>
    </div>
</section>

<section class="no-margin transparent overflow-scroll" id="userroles">
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
            <th>
                Assigned
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
                <td>
                    {{role.weight}}
                </td>
                <td>
                    <input-toggle &role.assigned="checked"></input-toggle>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="submitter">
        <button @click="saveRoles()">
            Save roles
        </button>
        <p class="message" :class:show="showMessage">
            {{ message }}
        </p>
    </div>
</section>