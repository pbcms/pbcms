<section class="page-introduction">
    <h1>
        Permissions
    </h1>
    <p>
        Assign, reject and clear permissions.
    </p>
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
            <th>
                Created
            </th>
            <th>
                Updated
            </th>
        </thead>
        <tbody>
            <tr :for="perm in permissions">
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
            </tr>
        </tbody>
    </table>
</section>