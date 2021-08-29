<?php
    use Library\Policy;
    $policy = new Policy;
?>

<p class="error"></p>

<div class="input-field">
    <input type="text" name="identifier" placeholder=" ">
    <span>
        E-mail address<?php if (intval($policy->get('usernames-enabled')) == 1) echo " or username"; ?>
    </span>
    <ul class="error-list"></ul>
</div>

<div class="input-field">
    <input type="password" name="password" placeholder=" ">
    <span>
        Password
    </span>
    <ul class="error-list"></ul>
</div>

<div class="input-checkbox">
    <input type="checkbox" name="stay-signedin">
    <span>Stay signed-in?</span>
</div>

<div class="input-buttons">
    <button type="submit" class="process-section">
        Continue
    </button>

    <a href="<?php echo SITE_LOCATION; ?>pb-auth/signup">Don't have an account yet?</a>
</div>