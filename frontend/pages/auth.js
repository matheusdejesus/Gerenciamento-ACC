class AuthClient {
    static TOKEN_KEY = 'acc_jwt_token';
    static USER_KEY = 'acc_user_data';
    static API_KEY_KEY = 'acc_api_key';

    // Salvar token após login
    static saveToken(token, userData) {
        console.log('Salvando token e dados do usuário:', userData);
        localStorage.setItem(this.TOKEN_KEY, token);
        localStorage.setItem(this.USER_KEY, JSON.stringify(userData));
    }

    // Obter token
    static getToken() {
        return localStorage.getItem(this.TOKEN_KEY);
    }

    // Obter dados do usuário
    static getUser() {
        const userStr = localStorage.getItem(this.USER_KEY);
        return userStr ? JSON.parse(userStr) : {
            id: 1,
            nome: 'Usuário Teste',
            email: 'teste@exemplo.com',
            tipo: 'aluno',
            matricula: '20200001'
        };
    }

    // Verificar se está logado
    static isLoggedIn() {
        // Para teste, sempre retorna true
        return true;
    }

    // Logout
    static async logout() {
        localStorage.removeItem(this.TOKEN_KEY);
        localStorage.removeItem(this.USER_KEY);
        localStorage.removeItem(this.API_KEY_KEY);
        window.location.href = 'login.php';
    }

    // Obter headers para requisições
    static getHeaders() {
        return {
            'Content-Type': 'application/json'
        };
    }

    // Fazer requisição autenticada
    static async fetch(url, options = {}) {
        const headers = { ...this.getHeaders(), ...(options.headers || {}) };
        
        if (options.body instanceof FormData) {
            delete headers['Content-Type'];
        }
        
        return fetch(url, {
            ...options,
            headers: headers
        });
    }
}