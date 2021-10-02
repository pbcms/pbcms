<?php
    use Library\Policy;
    use Library\Language;

    $policy = new Policy;
    $lang = new Language;
    $lang->detectLanguage();
    $lang->load();
?>

<p class="error"><?php if (isset($_GET['error'])) echo $lang->get('messages.api-auth.access-token.error-' . $_GET['error'], $lang->get('common.messages.error-unknown_error', 'An unknown error has occured.')); ?></p>

<p>
    For security, please identify yourself one more time.
</p>

<div class="input-field">
    <input type="text" name="identifier" placeholder=" " required>
    <span>
        <?php 
            if (intval($policy->get('usernames-enabled')) == 1) {
                echo $lang->get("pages.pb-auth.signin.field-identifier-full", "E-mail address or username");
            } else {
                echo $lang->get("pages.pb-auth.signin.field-identifier", "E-mail address");
            }
        ?>
    </span>
    <ul class="error-list"></ul>
</div>

<div class="input-field">
    <input type="password" name="password" placeholder=" " required>
    <span>
        <?php echo $lang->get("pages.pb-auth.signin.field-password", "Password"); ?>
    </span>
    <ul class="error-list"></ul>
</div>

<div class="input-buttons">
    <button type="submit" class="process-section">
    <?php echo $lang->get("pages.pb-auth.signin.button-continue", "Continue"); ?>
    </button>

    <a href="<?php echo SITE_LOCATION; ?>pb-auth/signup">
        <?php echo $lang->get("pages.pb-auth.signin.link-signup", "Don't have an account yet?"); ?>
    </a>
</div>