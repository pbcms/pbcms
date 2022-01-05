<?php
    use Library\DatabaseMigrator;
    use Library\Controller;

    $controller = new Controller;
    $userModel = $controller->__model('user');

    $dbmig = new DatabaseMigrator(array("shout" => false));
    $availableMigrations = $dbmig->availableMigrations();
?>

<section class="page-introduction">
    <h1>
        Overview
    </h1>
    <p>
        Overview of the site and actions that should be taken.
    </p>
</section>

<?php
    if (count($availableMigrations) > 0 && $userModel->check('site.migrate-database')) {
?>
    <section class="database-migrations">
        <h2>
            Database migrations
        </h2>
        <p>
            There <?php echo (count($availableMigrations) == 1 ? 'is <b>' : 'are <b>') . count($availableMigrations); ?></b> migrations available which should be executed to deliver optimal performance to your site.
        </p>


        <div class="foldable">
            <label action-migrate-database>Start migration</label>
            <span> - </span>
            <label for="foldable-available-migrations">Show migrations</label>
            <input type="checkbox" id="foldable-available-migrations">
            <div class="foldable-content">
                <ul>
                    <?php
                        foreach($availableMigrations as $migration) {
                            echo '<li>' . $migration->migration . '</li>';
                        }
                    ?>
                </ul>
            </div>
            <p class="migration-logs"></p>
        </div>
    </section>
<?php
    }
?>

