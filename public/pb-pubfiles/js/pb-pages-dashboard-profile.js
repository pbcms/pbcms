import { Rable } from '../../pb-pubfiles/js/rable.js';

const app = new Rable({
    data: {
        profile: {
            firstname: "Micha",
            lastname: "de Vries",
            email: "m.devries@mtdv.nl",
            image: "https://lh3.googleusercontent.com/ogw/ADea4I5PzTV1z7GD4aZUROvcxJhqslAPoUZHJ41FlNQfHA=s1024-c-mo",
            username: "mtdev",
            type: "local"
        }
    }
});

app.mount('.content');