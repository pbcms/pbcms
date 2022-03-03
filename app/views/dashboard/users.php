<?php
    use Library\Users;
    use Library\Policy;

    $users = new Users;
    $policy = new Policy;

    $userlist = $users->list();
    $usernamesEnabled = $policy->get('usernames-enabled') == '1';

    $tableContent = '';
    foreach($userlist as $user) {
        $tableContent .= renderRow((object) $user, $usernamesEnabled);
    }

    function renderRow($item, $usernamesEnabled) {
        $result = '<tr id="' . $item->id. '"><td>' . $item->id . '</td><td>' . $item->firstname . ' ' . $item->lastname . '</td><td>' . $item->email . '</td>' . ($usernamesEnabled ? '<td>' . ($item->username ? $item->username : '') . '</td>' : '') . '<td>' . ucfirst(strtolower($item->status)) . '</td><td>' . $item->created . '</td><td>' . $item->updated . '</td></tr>';
        return $result;
    }
?>

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
            <th>
                Name
            </th>
            <th>
                E-mail
            </th>
            <?php
                if ($usernamesEnabled) echo '<th>Username</th>';
            ?>
            <th>
                Status
            </th>
            <th>
                Created
            </th>
            <th>
                Updated
            </th>
        </thead>
        <tbody>
            <?php
                echo $tableContent;
            ?>
        </tbody>
    </table>
</section>