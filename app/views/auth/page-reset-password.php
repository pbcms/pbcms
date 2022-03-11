<?php
    use Library\Policy;
    use Library\Language;

    $policy = new Policy;
    $lang = new Language;
    $lang->detectLanguage();
    $lang->load();
?>

<p class="error">{{errorMessage}}</p>

<p>{{taskMessage}}</p>

<div class="input-field" :if="progress == 0">
    <input type="text" placeholder=" " :value="identifier" required>
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

<div class="input-field" :if="progress == 1" :class:red-border="passwordErrors.length > 0">
    <input type="text" placeholder=" " :value="password" @input="passwordInputHandler(0)" required>
    <span>
        New password
    </span>
    <ul class="error-list">
        <li :for="e in passwordErrors">
            {{e}}
        </li>
        <ul>
        <li :for="e in passwordSubErrors">
            {{e}}
        </li>
        </ul>
    </ul>
</div>

<div class="input-field" :if="progress == 1">
    <input type="text" placeholder=" " :value="passwordVerification" @input="passwordInputHandler(1)" required>
    <span>
        Verify new password
    </span>
    <ul class="error-list"></ul>
</div>

<div class="input-buttons">
    <button type="button" class="process-section" @click="funcContinue()">
        <?php echo $lang->get("pages.pb-auth.signin.button-continue", "Continue"); ?>
    </button>

    <a href="<?php echo SITE_LOCATION; ?>pb-auth/signin">
        <?php echo "Cancel and signin"; ?>
    </a>
</div>