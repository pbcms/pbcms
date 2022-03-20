<?php
    use Library\Policy;
    use Library\Language;

    $policy = new Policy;
    $lang = new Language;
    $lang->detectLanguage();
    $lang->load();
?>

<p class="success"></p>
<p class="error"><?php if (isset($_GET['error'])) echo $lang->get('messages.api-auth.reset-password.error-' . $_GET['error'], $lang->get('common.messages.error-unknown_error', 'An unknown error has occured.')); ?></p>

<div class="input-field">
    <input type="text" name="identifier" placeholder=" ">
    <span>
        <?php 
            if (intval($policy->get('usernames-enabled')) == 1) {
                echo $lang->get("pages.pb-auth.forgot-password.field-identifier-full", "E-mail address or username");
            } else {
                echo $lang->get("pages.pb-auth.forgot-password.field-identifier", "E-mail address");
            }
        ?>
    </span>
    <ul class="error-list"></ul>
</div>

<div class="input-buttons">
    <button type="submit" class="process-section">
    <?php echo $lang->get("pages.pb-auth.forgot-password.button-send-email", "Send e-mail"); ?>
    </button>

    <a href="<?php echo SITE_LOCATION; ?>pb-auth/signup">
        <?php echo $lang->get("pages.pb-auth.forgot-password.link-signin", "Signin"); ?>
    </a>
</div>