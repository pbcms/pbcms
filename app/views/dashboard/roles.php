<section class="page-introduction">
    <h1>
        Roles
    </h1>
    <p>
        Manage roles and their weights.
    </p>
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
            <th>
                Description
            </th>
            <th>
                Weight
            </th>
            <th>
                Created
            </th>
            <th>
                Updated
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
                    {{role.created}}
                </td>
                <td>
                    {{role.updated}}
                </td>
            </tr>
        </tbody>
    </table>
</section>