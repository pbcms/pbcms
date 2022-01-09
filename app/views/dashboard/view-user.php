<?php
    $user = $data['user'];
?>

<section class="profile-card">
    <div class="user-overview">
        <div class="left">
            <div class="profile-picture">
                <img src="<?php echo $user->picture->url; ?>" alt="<?php echo $user->firstname; ?>'s profile picture.">
            </div>
            <div class="user-main">
                <h1>
                    <?php echo $user->fullname; ?>
                </h1>
                <div class="details">
                    <span><?php echo $user->email; ?></span>
                    <span><?php echo $user->username; ?></span>
                    <span><?php echo ucfirst(strtolower($user->status)); ?></span>
                </div>
            </div>
        </div>
        <div class="user-dates">
            <span>Created: <?php echo $user->created; ?></span>
            <span>Updated: <?php echo $user->updated; ?></span>
        </div>
    </div>
</section>