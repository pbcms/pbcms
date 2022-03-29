<section class="page-introduction">
    <h1>
        <?php echo $this->lang->get("pages.pb-dashboard.users.page-title", "Manage users"); ?>
    </h1>
    <p>
        <?php echo $this->lang->get("pages.pb-dashboard.users.page-subtitle", "View and manage users on your website."); ?>
    </p>
</section>

<section class="no-margin transparent overflow-scroll">
    <table>
        <thead>
            <th class="smaller">
                ID
            </th>
            <th class="medium">
                Name
            </th>
            <th>
                E-mail
            </th>
            <th>
                Username
            </th>
            <th>
                Status
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
            <tr :for="user in users">
                <td>
                    {{ user.id }}
                </td>
                <td>
                    {{ user.fullname }}
                </td>
                <td>
                    {{ user.email }}
                </td>
                <td>
                    {{ user.username }}
                </td>
                <td>
                    {{ user.status }}
                </td>
                <td>
                    {{ user.created }}
                </td>
                <td>
                    {{ user.updated }}
                </td>
                <td>
                    <a href="#" @click="location.href=SITE_LOCATION + 'pb-dashboard/users/' + user.id">
                        manage
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</section>