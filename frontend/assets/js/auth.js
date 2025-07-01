class AuthClient {
    static TOKEN_KEY = 'acc_jwt_token';
    static USER_KEY = 'acc_user_data';

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
        const userData = localStorage.getItem(this.USER_KEY);
        return userData ? JSON.parse(userData) : null;
    }

    // Verificar se está logado
    static isLoggedIn() {
        const token = this.getToken();
        if (!token) {
            console.log('Nenhum token encontrado');
            return false;
        }

        // Verificar se token não expirou
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const isValid = payload.exp > (Date.now() / 1000);
            console.log('Token válido:', isValid, 'Expira em:', new Date(payload.exp * 1000));
            return isValid;
        } catch (e) {
            console.error('Erro ao validar token:', e);
            return false;
        }
    }

    // Logout
    static logout() {
        console.log('Fazendo logout');
        localStorage.removeItem(this.TOKEN_KEY);
        localStorage.removeItem(this.USER_KEY);
        window.location.href = '/Gerenciamento-ACC/frontend/pages/login.php';
    }

    // Fazer requisição autenticada
    static async fetch(url, options = {}) {
        const token = this.getToken();

        if (!token) {
            throw new Error('Token não encontrado');
        }

        const defaultOptions = {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        };

        if (!options.body || options.body instanceof FormData) {
        } else {
            defaultOptions.headers['Content-Type'] = 'application/json';
        }

        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                // Só adiciona headers customizados se NÃO for FormData
                ...(options.body instanceof FormData ? {} : options.headers)
            }
        };

        console.log('Fazendo requisição para:', url);
        console.log('Options:', mergedOptions);

        const response = await fetch(url, mergedOptions);

        // Se token expirou, fazer logout
        if (response.status === 401) {
            console.log('Token expirado, fazendo logout');
            this.logout();
            throw new Error('Sessão expirada');
        }

        return response;
    }

    // Login
    static async login(email, senha) {
        console.log('Fazendo login para:', email);

        try {
            const response = await fetch('/Gerenciamento-ACC/backend/api/routes/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, senha })
            });

            console.log('Status da resposta:', response.status);

            const data = await response.json();
            console.log('Dados recebidos da API:', data);

            if (data.success) {
                this.saveToken(data.token, data.usuario);
                return data;
            } else {
                throw new Error(data.error || 'Erro no login');
            }
        } catch (error) {
            console.error('Erro na requisição de login:', error);
            throw error;
        }
    }
}

// Verificar autenticação em páginas protegidas
function requireAuth() {
    console.log('Verificando autenticação na página:', window.location.pathname);
    if (!AuthClient.isLoggedIn()) {
        console.log('Usuário não autenticado, redirecionando para login');
        window.location.href = '/Gerenciamento-ACC/frontend/pages/login.php';
        return false;
    }
    return true;
}

// Auto-verificação
setInterval(() => {
    if (!AuthClient.isLoggedIn() &&
        window.location.pathname !== '/Gerenciamento-ACC/frontend/pages/login.php' &&
        !window.location.pathname.includes('login.php')) {
        console.log('Token expirado, fazendo logout automático');
        AuthClient.logout();
    }
}, 30000);
