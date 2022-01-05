//Defined and stored in constant to prevent tempering from potential malicious code.
const PbAuth = (function() {

    //Token worker is not accessable by external scripts, but is accessable by the PbAuth class since it resides in the same scope.
    //The token worker makes sure a token is available by the auth class to use. This has been split up to make it harder to access the access token.
    //If no access token can be retrieved, the user will be redirected to a authentication page.

    const TokenWorker = new class TokenWorker {
        #access_token = '';

        constructor() {
            this.refreshToken();
        }

        async retrieveToken(forceRefresh = false) {
            if (forceRefresh || this.#access_token == '') await this.refreshToken();
            return this.#access_token;
        }

        async refreshToken() {
            const res = await axios.get(SITE_LOCATION + 'pb-api/auth/access-token');
            if (res.data.success) {
                this.#access_token = res.data.token;
                setTimeout(() => {
                    if (this.#access_token == res.data.token) this.refreshToken();
                }, new Date(res.data.expiration * 1000) - new Date().getTime());
                return true;
            } else {
                location.href = SITE_LOCATION + 'pb-auth/signin?error=' + res.data.error + '&followup=' + location.pathname;
                return false;
            }
        }
    }

    return new class PbAuth {
        apiInstance() {
            var instance = axios.create({
                baseURL: SITE_LOCATION + 'pb-api/'
            });

            instance.interceptors.request.use(async config => {
                const access_token = await TokenWorker.retrieveToken();
                config.headers = { 
                    'Authorization': `Bearer ${access_token}`
                };

                return config;
            }, error => {
                Promise.reject(error)
            });
              
            instance.interceptors.response.use((response) => {
                return response
            }, async function (error) {
                const originalRequest = error.config;
                if (error.response.status === 403 && !originalRequest._retry) {
                    originalRequest._retry = true;
                    const access_token = await TokenWorker.retrieveToken(true);            
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + access_token;
                    return instance(originalRequest);
                }
                
                return Promise.reject(error);
            });

            return instance;
        }
    }
})();

//Object is freezed
Object.freeze(PbAuth);