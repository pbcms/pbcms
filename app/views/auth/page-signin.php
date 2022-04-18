<?php
    use Library\Policy;
    use Library\Language;
    use Registry\Event;

    $policy = new Policy;
    $lang = new Language;
    $lang->detectLanguage();
    $lang->load();

    $externalProviders = Event::trigger("auth-button-external-provider", array('type' => "signin"));
?>

<p class="error"><?php if (isset($_GET['error'])) echo $lang->get('messages.api-auth.access-token.error-' . $_GET['error'], $lang->get('common.messages.error-unknown_error', 'An unknown error has occured.')); ?></p>

<div class="input-field">
    <input type="text" name="identifier" placeholder=" ">
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
    <input type="password" name="password" placeholder=" ">
    <span>
        <?php echo $lang->get("pages.pb-auth.signin.field-password", "Password"); ?>
    </span>
    <ul class="error-list"></ul>
</div>

<?php
    if (intval($policy->get("allow-stay-signedin")) === 1) {
        ?>
            <div class="input-checkbox">
                <input type="checkbox" name="stay-signedin">
                <span>
                    <?php echo $lang->get("pages.pb-auth.signin.box-stay-signedin", "Stay signed-in?"); ?>
                </span>
            </div>
        <?php
    }
?>

<div class="input-buttons">
    <button type="submit" class="process-section">
    <?php echo $lang->get("pages.pb-auth.signin.button-continue", "Continue"); ?>
    </button>

    <?php
        if (intval($policy->get("signup-allowed")) === 1) {
            ?>
                <a href="<?php echo SITE_LOCATION; ?>pb-auth/signup">
                    <?php echo $lang->get("pages.pb-auth.signin.link-signup", "Don't have an account yet?"); ?>
                </a>
            <?php
        }
    ?>
</div>

<?php
    if (count($externalProviders) > 0) {
        ?>
            <div class="alternatives">
                <h4>
                    You can also signin with
                </h4>

                <div class="input-buttons">
                    <?php  foreach($externalProviders as $button) { echo $button; } ?>
                </div>
            </div>
        <?php
    }
?>
