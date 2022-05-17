<section class="page-introduction">
    <h1>
        <?php echo $this->lang->get("pages.pb-dashboard.users.page-title", "Manage users"); ?>
    </h1>
    <p>
        <?php echo $this->lang->get("pages.pb-dashboard.users.page-subtitle", "View and manage users on your website."); ?>
    </p>
</section>

<section id="new-user" :if="new_user">
    <h2>
        New user
    </h2>

    <br>
    <div class="two-fields">
        <input-field &new_user_firstname="value" $placeholder="Firstname"></input-field>
        <input-field &new_user_lastname="value" $placeholder="Lastname"></input-field>
    </div>

    <input-field &new_user_email="value" $placeholder="E-mail address" $type="email"></input-field>
    <input-field &new_user_username="value" $placeholder="Username"></input-field>
    <input-select &new_user_status="selected" &new_user_status_options="options" $placeholder="User status"></input-select>
    <input-field &new_user_password="value" $placeholder="Password" $type="password"></input-field>

    <div class="submitter">
        <button @click="createUser()">
            Create
        </button>
        <p class="message" :class:show="showMessage">
            {{ message }}
        </p>
    </div>
</section>

<section class="transparent no-padding">
    <button :if="!new_user" @click="this.new_user = true">
        Add user
    </button>
</section>

<section class="no-margin transparent overflow-scroll">
    <table>
        <thead>
            <tr>
                <th colspan=8 class="table-filters">
                    <div>
                        <div class="filter-button add-filter">
                            <div :norender>
                                <i data-feather="filter"></i>
                            </div>
                        </div>
                        <div class="filters-search">
                            <div class="chips">

                            </div>
                            <input type="text" placeholder="Type to search" :value="filter.search" @input="waitFinishTyping()">
                        </div>
                        <div class="filter-button refresh" @click="refreshUsers()">
                            <div :norender>
                                <i data-feather="rotate-cw"></i>
                            </div>
                        </div>
                    </div>
                </th>
            </tr>
            <tr>
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
            </tr>
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
            <tr>
                <td colspan=8 class="table-pagination">
                    <div>
                        <p>
                            Rows per page
                        </p>
                        <input-select &filter_limit_options="options" &pagination.limit="selected" $compact=true $placeholder="" @input="refreshUsers()"></input-select>
                        <p>
                            {{ pagination.limit * (pagination.page - 1) + 1 }} - {{ pagination.limit * (pagination.page - 1) + users.length }} of {{ pagination.count }}
                        </p>
                        <div class="actions">
                            <div @click="this.pagination.page = 1; this.refreshUsers();">
                                <div :norender>
                                    <i data-feather="chevrons-left"></i>
                                </div>
                            </div>
                            <div @click="this.pagination.page--; this.refreshUsers();">
                                <div :norender>
                                    <i data-feather="chevron-left"></i>
                                </div>
                            </div>
                            <div @click="this.pagination.page++; this.refreshUsers();">
                                <div :norender>
                                    <i data-feather="chevron-right"></i>
                                </div>
                            </div>
                            <div @click="this.pagination.page = Math.ceil(pagination.count / pagination.limit); this.refreshUsers();">
                                <div :norender>
                                    <i data-feather="chevrons-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</section>