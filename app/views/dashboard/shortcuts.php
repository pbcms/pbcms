<?php
    use \Library\Users;
    use \Library\Controller;

    $controller = new Controller;
    $userModel = $controller->__model('user');
    $users = new Users();
    $user = $userModel->info();
    $shortcuts = json_decode($users->metaGet($user->id, 'dashboard-shortcuts'));
    if (!$shortcuts) $shortcuts = array();
?>

<section class="page-introduction">
    <h1>
        Shortcuts
    </h1>
    <p>
        Create new shortcuts or delete an existing one.
    </p>
</section>

<section class="new-shortcut">
    <h2>New shortcut</h2>
    <form class="new-short-shortcut">
        <div>
            <input type="text" name="icon" placeholder="name of icon (feathericons.com)" value="link">
            <input type="text" name="title" placeholder="Name of shortcut in sidebar">
        </div>
        <input class="full-width" type="text" name="target" placeholder="URL of the shortcut">
        <button>
            Create shortcut
        </button>
    </form>
</section>

<?php
    if (count($shortcuts) > 0) {
        ?>
            <section class="no-padding transparent existing-shortcuts">
                <table>
                    <thead>
                        <tr>
                            <th>
                                Icon
                            </th>
                            <th>
                                Title
                            </th>
                            <th>
                                Target
                            </th>
                            <th>
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i = 0;
                            foreach($shortcuts as $shortcut) {
                                $i++;
                                $shortcut = (array) $shortcut;
                                ?>
                                    <tr shortcut="<?=$i?>">
                                        <td selectable><i data-feather="<?=$shortcut['icon']?>"></i></td>
                                        <td selectable><?=$shortcut['title']?></td>
                                        <td>
                                            <?php
                                                if ($shortcut['shortcut-type'] == 'module-config') {
                                                    ?>
                                                        <a selectable href="<?=SITE_LOCATION?>pb-dashboard/module-config/<?=$shortcut['target']?>" target="_blank">
                                                            Module: <?=$shortcut['target']?>
                                                        </a>
                                                    <?php
                                                } else if ($shortcut['shortcut-type'] == 'custom') {
                                                    ?>
                                                        <a selectable href="<?=SITE_LOCATION?><?=$shortcut['target']?>" target="_blank">
                                                            <?=SITE_LOCATION?><?=$shortcut['target']?>
                                                        </a>
                                                    <?php
                                                } else {
                                                    ?>
                                                        <a selectable href="<?=$shortcut['target']?>" target="_blank">
                                                            <?=$shortcut['target']?>
                                                        </a>
                                                    <?php
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="#" delete-shortcut="<?=$i?>">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                </table>
            </section>
        <?php
    }
?>