<section class="profile-overview">
    <div class="profile-image">
        <img :bind:src="profile.image">
    </div>
    <div class="profile-details">
        <h1 id="user-fullname">
            {{ profile.firstname }} {{ profile.lastname }}
        </h1>
        <p id="user-properties">
            <span class="capitalize">
                {{ profile.type }} account
            </span>
            <span>
                {{ profile.email }}
            </span>
            <span :if="profile.username">
                {{ profile.username }}
            </span>
        </p>
    </div>
</section>

<section class="profile-editor pbcms-system-display">    
    <div class="input-field">
        <input type="text" name="firstname" placeholder=" ">
        <span>
            Firstname
        </span>
        <ul class="error-list"></ul>
    </div>
</section>
