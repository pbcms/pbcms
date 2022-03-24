<section class="profile-overview">
    <div class="profile-image">
        <img :bind:src="picture.url">
    </div>
    <div class="profile-details">
        <h1 id="user-fullname">
            {{ firstname }} {{ lastname }}
        </h1>
        <p id="user-properties">
            <span class="capitalize">
                {{ type }} account
            </span>
            <span>
                {{ email }}
            </span>
            <span :if="username">
                {{ username }}
            </span>
        </p>
    </div>
</section>

<section class="profile-editor">    
    <div class="two-fields">
        <input-field @input="typing(componentData)" @keydown="submit(event)" $autofill="new-password" $name="firstname" $placeholder="Firstname" &firstname="value"></input-field>
        <input-field @input="typing(componentData)" @keydown="submit(event)" $autofill="new-password" $name="lastname" $placeholder="Lastname" &lastname="value"></input-field>
    </div>

    <input-field @input="typing(componentData)" @keydown="submit(event)" $autofill="new-password" $name="email" $placeholder="E-mail address" &email="value" :if="type == 'local'" $type="email"></input-field>
    <input-field @input="typing(componentData)" @keydown="submit(event)" $autofill="new-password" $name="username" $placeholder="Username" &username="value" :if="usernames_enabled"></input-field>
    <input-field @input="typing(componentData)" @keydown="submit(event)" $autofill="new-password" $name="password" $placeholder="New password" &password="value" $type="password"></input-field>

    <div class="submitter">
        <button @click="submit()">
            Save profile
        </button>
        <p class="message" :class:show="showMessage">
            {{ message }}
        </p>
    </div>
</section>
