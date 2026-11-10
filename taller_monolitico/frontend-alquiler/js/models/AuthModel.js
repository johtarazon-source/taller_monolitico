import { ApiClient } from '../services/ApiClient.js';

export const AuthModel = {
  async login(usuario, password) {
    const respuesta = await ApiClient.post('/auth/login', { usuario, password }, { auth: false });

    return respuesta.data.token;
  },

  async logout() {
    await ApiClient.post('/auth/logout', null);
  },
};
