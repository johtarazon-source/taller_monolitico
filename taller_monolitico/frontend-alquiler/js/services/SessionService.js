const TOKEN_KEY = 'alquiler_token';

export const SessionService = {
  getToken() {
    return localStorage.getItem(TOKEN_KEY);
  },

  setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
  },

  clearToken() {
    localStorage.removeItem(TOKEN_KEY);
  },

  isAuthenticated() {
    return Boolean(this.getToken());
  },
};
