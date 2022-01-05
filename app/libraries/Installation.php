<?php
    require 'Database.php';
    require APP_DIR . '/Core.php';

    use Library\Database;
    use Library\DatabaseMigrator as Migrator;

    function validateDatabase() {
        if (isset($_POST['DB_HOSTNAME']) && isset($_POST['DB_USERNAME']) && isset($_POST['DB_DATABASE'])) {
            $hostname = $_POST['DB_HOSTNAME'];
            $username = $_POST['DB_USERNAME'];
            $password = (isset($_POST['DB_PASSWORD']) ? $_POST['DB_PASSWORD'] : "");
            $database = $_POST['DB_DATABASE'];

            define("DATABASE_HOSTNAME", $hostname);
            define("DATABASE_USERNAME", $username);
            define("DATABASE_PASSWORD", $password);
            define("DATABASE_DATABASE", $database);
            define("DATABASE_TABLE_PREFIX", "pb_");

            if (empty($_POST['DB_HOSTNAME']) || empty($_POST['DB_USERNAME']) || empty($_POST['DB_DATABASE'])) {
                return array(
                    "success" => false,
                    "status" => -1,
                    "error" => "Empty hostname, username or database."
                );
            }

            $conn = new \mysqli($hostname, $username, $password, $database);
            if ($conn->connect_errno == 0) {
                $tables = $conn->query('show tables');
                if ($tables->num_rows > 0) {
                    return array(
                        "success" => false,
                        "status" => -1,
                        "error" => "Given database is not empty."
                    );
                } else {
                    return array(
                        "success" => true
                    );
                }

                return array(
                    "success" => true,
                    "tables" => $tables,
                    "info" => $tables->fetch_row()
                );
            } else {
                return array(
                    "success" => false,
                    "status" => $conn->connect_errno,
                    "error" => $conn->connect_error
                );
            }
        } else {
            return array(
                "success" => false,
                "status" => -1,
                "error" => "Missing hostname, username or database in POST data."
            );
        }
    }

    function validateUser() {
        if (isset($_POST['USER_FIRSTNAME']) && isset($_POST['USER_LASTNAME']) && isset($_POST['USER_EMAIL']) && isset($_POST['USER_PASSWORD'])) {
            $firstname = $_POST['USER_FIRSTNAME'];
            $lastname = $_POST['USER_LASTNAME'];
            $username = (isset($_POST['USER_USERNAME']) ? $_POST['USER_USERNAME'] : NULL);
            $email = $_POST['USER_EMAIL'];
            $password = $_POST['USER_PASSWORD'];

            define("USER_FIRSTNAME", $firstname);
            define("USER_LASTNAME", $lastname);
            define("USER_USERNAME", $username);
            define("USER_EMAIL", $email);
            define("USER_PASSWORD", $password);

            if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
                return array(
                    "success" => false,
                    "status" => -1,
                    "error" => "Empty firstname, lastname, email or password."
                );
            }

            $uppercase  = preg_match('@[A-Z]@', $password);
            $lowercase  = preg_match('@[a-z]@', $password);
            $number     = preg_match('@[0-9]@', $password);
            $special    = preg_match('@[^\w]@', $password);
            $length     = strlen($password);
            $minLength  = 12;

            if (!$uppercase || !$lowercase || !$number || !$special || $length < $minLength) {
                return array(
                    "success" => false,
                    "status" => 1,
                    "requirements" => array(
                        "uppercase" => $uppercase,
                        "lowercase" => $lowercase,
                        "number" => $number,
                        "special" => $special,
                        "length" => !($length < $minLength),
                        "givenLength" => $length,
                        "minLength" => $minLength
                    ),
                    "post" => $_POST
                );
            } else {
                return array(
                    "success" => true
                );
            }
        } else {
            return array(
                "success" => false,
                "status" => -1,
                "error" => "Missing firstname, lastname, email or password in POST data."
            );
        }
    }

    if (isset($_POST['SITE_LOC_DETECTION'])) {
        die("DETECTION_SUCCESSFUL");
    }

    if (isset($_POST['DATABASE_VALIDATION'])) {
        header("Content-Type: application/json");
        print_r(json_encode(validateDatabase()));
        die();
    }

    if (isset($_POST['USER_VALIDATION'])) {
        header("Content-Type: application/json");
        print_r(json_encode(validateUser()));
        die();
    }

    if (isset($_POST['FINALIZE'])) {
        ini_set("display_errors", 1);
        header("Content-Type: application/json");
        $databaseValidation = (object) validateDatabase();
        if ($databaseValidation->success) {
            $userValidation = (object) validateUser();
            if ($userValidation->success) {
                $db = new Database();
                $migrator = new Migrator();

                ob_start();     //Start logging migration.
                $migrator->migrate();
                $migrationlogs = ob_get_clean();

                $firstname = USER_FIRSTNAME;
                $lastname = USER_LASTNAME;
                $username = USER_USERNAME;
                $email = USER_EMAIL;
                $password = password_hash(USER_PASSWORD, PASSWORD_DEFAULT);

                if ($username != NULL) {
                    $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "users` (`firstname`, `lastname`, `username`, `email`, `password`, `status`) VALUES ('$firstname', '$lastname', '$username', '$email', '$password', 'VERIFIED')");
                    $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "relations` (`type`, `origin`, `target`) VALUES ('user:role', '$db->insert_id', '1')");
                } else {
                    $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "users` (`firstname`, `lastname`, `email`, `password`, `status`) VALUES ('$firstname', '$lastname', '$email', '$password', 'VERIFIED')");
                    $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "relations` (`type`, `origin`, `target`) VALUES ('user:role', '$db->insert_id', '1')");
                }

                $site_title = $_POST["SITE_TITLE"];
                $site_description = $_POST["SITE_DESCRIPTION"];
                $site_location = $_POST["SITE_LOCATION"];
                $site_indexing = ($_POST["SITE_INDEXING"] == "on" ? 1 : 0);
                $site_email = $_POST["SITE_EMAIL"];
                $site_email_public = ($_POST["SITE_EMAIL_PUBLIC"] == "on" ? 1 : 0);
                
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "policies` SET `value`='$site_title' WHERE `name`='site-title'");
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "policies` SET `value`='$site_description' WHERE `name`='site-description'");
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "policies` SET `value`='$site_location' WHERE `name`='site-location'");
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "policies` SET `value`='$site_indexing' WHERE `name`='site-indexing'");
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "policies` SET `value`='$site_email' WHERE `name`='site-email'");
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "policies` SET `value`='$site_email_public' WHERE `name`='site-email-public'");

                $configtemplate = file_get_contents(APP_DIR . '/sources/templates/config.template.php');
                $configtemplate = str_replace("VAL_PBCMS_DEBUG_MODE", "false", $configtemplate);
                $configtemplate = str_replace("VAL_PBCMS_SAFE_MODE", "false", $configtemplate);
                $configtemplate = str_replace("VAL_DATABASE_HOSTNAME", DATABASE_HOSTNAME, $configtemplate);
                $configtemplate = str_replace("VAL_DATABASE_USERNAME", DATABASE_USERNAME, $configtemplate);
                $configtemplate = str_replace("VAL_DATABASE_PASSWORD", DATABASE_PASSWORD, $configtemplate);
                $configtemplate = str_replace("VAL_DATABASE_DATABASE", DATABASE_DATABASE, $configtemplate);
                $configtemplate = str_replace("VAL_DATABASE_TABLE_PREFIX", DATABASE_TABLE_PREFIX, $configtemplate);
                $configfile = fopen(ROOT_DIR . "/config.php", "w") or die(json_encode(array("success" => false, "error" => "config_file_creation_error", "message"=>"Unable to create configuration file!")));
                fwrite($configfile, $configtemplate);
                fclose($configfile);

                try {
                    chmod(ROOT_DIR . '/config.php', 644);
                } catch(e) {}

                print_r(json_encode(array(
                    "success" => true,
                    "migration_logs" => $migrationlogs
                )));

                die();
            } else {
                print_r(json_encode($userValidation));
                die();
            }
        } else {
            print_r(json_encode($databaseValidation));
            die();
        }
    }
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="pbcms_debug_mode" content="<?php echo (defined('PBCMS_DEBUG_MODE') && PBCMS_DEBUG_MODE ? 'true' : 'false'); ?>">
        <title>PBCMS Installation procedure</title>

        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Didact+Gothic&display=swap" rel="stylesheet">
        
        <style>
            <?php echo file_get_contents(PUBFILES_DIR . '/css/pbcms-system-pages.css'); ?>
        </style>
    </head>
    <body>
        <form class="pbcms-installation pbcms-system-display">
            <div class="sidebar">
                <h1 class="title">
                    PBCMS Installation
                </h1>

                <ul>
                    <li section-name="preloader" style="display:none;"></li>
                    <li section-name="introduction">
                        Introduction
                    </li>
                    <li section-name="personalisation">
                        Personalisation
                    </li>
                    <li section-name="database">
                        Database
                    </li>
                    <li section-name="account">
                        Your account
                    </li>
                    <li section-name="finalize">
                        Finalize
                    </li>
                </ul>
            </div>
            <div class="sections">

            <!-- PRELOADER -->

                <section section-name="preloader">
                    <svg class="animated-spinner" height="50" width="50">
                        <circle class="path" cx="25" cy="25.2" r="19.9" fill="none" stroke-width="4" stroke-miterlimit="10" />
                    </svg>
                </section>

            <!-- INTRODUCTION -->

                <section section-name="introduction">
                    <h2>Welcome to your new website</h2>  
                    
                    <p>
                        Thanks for choosing PBCMS! <br>
                        We put in a lot of time and effort to build an amazing experience for you to be one of our proud users.
                        <br><br>
                        Please sit through the installation guide and set-up your website the way <i>you</i> want while we configure everything in the back. 
                        <br><br>
                        Don't worry, it will only take you a few minutes ;)
                    </p>

                    <button type="button" class="process-section">
                        Next
                    </button>
                </section>

            <!-- PERSONALISATION -->

                <section section-name="personalisation">
                    <h2>
                        What's your website about?
                    </h2>

                    <p>
                        Personalize your website to your needs. <br>
                        The title and description are not required to fill in. Generic information will be shown to your visitors.
                    </p>

                    <div class="input-field">
                        <input type="text" name="site-title" placeholder=" ">
                        <span>
                            Title
                        </span>
                    </div>

                    <div class="input-field">
                        <input type="text" name="site-description" placeholder=" ">
                        <span>
                            Description
                        </span>
                    </div>

                    <p>
                        Enter your domain name and the root (subfolder or location) of your website to make sure PBCMS's router will work correctly. <br>
                        You can also turn on or off indexing by search engines. If you turn this off, your website will not be discovered by search engines.
                    </p>

                    <div class="input-field">
                        <input type="text" name="site-location" placeholder=" ">
                        <span>
                            Website location
                        </span>
                    </div>

                    <div class="input-toggle">
                        <label>Robots indexing</label>
                        <input type="checkbox" name="allow-indexing" id="allow-indexing" checked>
                        <label for="allow-indexing"></label>
                    </div>

                    <h3>
                        Website E-mail
                    </h3>

                    <p>
                        Enter the E-mail address of this website. This E-mail address will be used to send E-mail's with and if enabled, published to search engines like Google. <br>
                        To disable the E-mail address being published in your site's Meta, turn off <i>Publish E-mail</i>.
                    </p>

                    <div class="input-field">
                        <input type="text" name="site-email" placeholder=" ">
                        <span>
                            Website E-mail
                        </span>
                    </div>

                    <div class="input-toggle">
                        <label>Publish E-mail</label>
                        <input type="checkbox" name="publish-email" id="publish-email" checked>
                        <label for="publish-email"></label>
                    </div>

                    <button type="button" class="process-section">
                        Next
                    </button>
                </section>

            <!-- DATABASE -->

                <section section-name="database">
                    <h2>
                        Configure your database.
                    </h2>

                    <p>
                        Configure a MySQL or MariaDB database by entering it's hostname, username, password and database name. <br>
                        Make sure the user has CRUD (<i><b>C</b>reate, <b>R</b>ead, <b>U</b>pdate and <b>D</b>elete</i>) permissions on the database.
                    </p>

                    <p class="error"></p>

                    <div class="input-field">
                        <input type="text" name="db-hostname" placeholder=" ">
                        <span>
                            Hostname
                        </span>
                    </div>

                    <div class="input-field">
                        <input type="text" name="db-username" placeholder=" ">
                        <span>
                            Username
                        </span>
                    </div>

                    <div class="input-field">
                        <input type="password" name="db-password" placeholder=" ">
                        <span>
                            Password
                        </span>
                    </div>

                    <div class="input-field">
                        <input type="text" name="db-database" placeholder=" ">
                        <span>
                            Database
                        </span>
                    </div>

                    <button type="button" class="process-section">
                        Next
                    </button>
                </section>

            <!-- ACCOUNT -->

                <section section-name="account">
                    <h2>
                        Make your account.
                    </h2>

                    <p>
                        You will need at least one local administrator account. With this account you can administer the settings and content on your website.
                    </p>

                    <p class="error"></p>
                    <div class="input-fields">
                        <div class="input-field">
                            <input type="text" name="user-firstname" placeholder=" ">
                            <span>
                                Firstname
                            </span>
                        </div>

                        <div class="input-field">
                            <input type="text" name="user-lastname" placeholder=" ">
                            <span>
                                Lastname
                            </span>
                        </div>
                    </div>

                    <div class="input-field">
                        <input type="text" name="user-username" placeholder=" ">
                        <span>
                            Username (optional)
                        </span>
                    </div>

                    <div class="input-field">
                        <input type="text" name="user-email" placeholder=" ">
                        <span>
                            E-mail address
                        </span>
                    </div>

                    <div class="input-field">
                        <input type="password" name="user-password" placeholder=" ">
                        <span>
                            Password
                        </span>
                    </div>

                    <button type="button" class="process-section">
                        Finalize
                    </button>
                </section>

            <!-- FINALIZE -->

                <section section-name="finalize">
                    <h2>
                        Start the installation?
                    </h2>

                    <p>
                        By clicking "start" your website will be configured with the given details. A connection to your database will be establed and we will apply your preferences and account.
                    </p>

                    <p class="error"></p>                    
                    <button type="button" class="process-section">
                        Start
                    </button>
                </section>
            </div>
        </form>

        <script>
            <?php echo file_get_contents(PUBFILES_DIR . '/js/pbcms-installation.js'); ?>
        </script>
    </body>
</html>