<?php
    use Library\Policy;
    $policy = new Policy;
?>

<div class="input-fields">
    <div class="input-field">
        <input type="text" name="user-firstname" placeholder=" " required>
        <span>
            Firstname
        </span>
    </div>

    <div class="input-field">
        <input type="text" name="user-lastname" placeholder=" " required>
        <span>
            Lastname
        </span>
    </div>
</div>

<?php
    if (intval($policy->get('usernames-enabled')) == 1) {
        ?>
            <div class="input-field">
                <input type="text" name="user-username" placeholder=" " <?php echo (intval($policy->get('usernames-required')) == 1 ? 'required' : ''); ?>>
                <span>
                    Username<?php echo (intval($policy->get('usernames-required')) == 1 ? '' : ' (optional)'); ?>
                </span>
            </div>
        <?php
    }
?>

<div class="input-field">
    <input type="text" name="user-email" placeholder=" " required>
    <span>
        E-mail address
    </span>
</div>

<div class="input-field">
    <input type="password" name="user-password" placeholder=" " required>
    <span>
        Password
    </span>
</div>


<div class="input-buttons">
    <button type="button" class="process-section">
        Continue
    </button>

    <a href="<?php echo SITE_LOCATION; ?>pb-auth/signin">Already have an account?</a>
</div>