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
        return userStr ? JSON.parse(userStr) : null;
    }

    // Obter API Key
    static getApiKey() {
        let apiKey = localStorage.getItem(this.API_KEY_KEY);
        if (!apiKey) {
            apiKey = 'frontend-gerenciamento-acc-2025';
        }
        return apiKey;
    }

    // Verificar se está logado
    static isLoggedIn() {
        const token = this.getToken();
        const apiKey = this.getApiKey();
        if (!(token && apiKey)) return false;

        // Verificar expiração do JWT
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            if (payload.exp && Date.now() / 1000 > payload.exp) {
                // Token expirado, fazer logout
                this.logout();
                return false;
            }
        } catch (e) {
            this.logout();
            return false;
        }

        return true;
    }

    // Logout
    static async logout() {
        try {
            await this.registrarAcao('LOGOUT', 'Usuário fez logout');

            localStorage.removeItem(this.TOKEN_KEY);
            localStorage.removeItem(this.USER_KEY);
            localStorage.removeItem(this.API_KEY_KEY);

            window.location.href = 'login.php';
        } catch (error) {
            console.error('Erro no logout:', error);
            localStorage.removeItem(this.TOKEN_KEY);
            localStorage.removeItem(this.USER_KEY);
            localStorage.removeItem(this.API_KEY_KEY);
            window.location.href = 'login.php';
        }
    }

    // Método getHeaders
    static getHeaders() {
        const headers = {
            'Content-Type': 'application/json'
        };

        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const apiKey = this.getApiKey();
        if (apiKey) {
            headers['X-API-Key'] = apiKey;
        }

        return headers;
    }

    // Fazer requisição autenticada
    static async fetch(url, options = {}) {
        const defaultHeaders = this.getHeaders();
        let headers = { ...defaultHeaders, ...(options.headers || {}) };

        if (options.body instanceof FormData) {
            delete headers['Content-Type'];
        }

        const response = await fetch(url, {
            ...options,
            headers: headers,
        });

        // Sempre tentar fazer o parsing do JSON, independente do status
        try {
            const responseText = await response.text();
            
            // Se a resposta estiver vazia, retornar erro específico
            if (!responseText.trim()) {
                throw new Error('Resposta vazia do servidor');
            }
            
            // Fazer parse do JSON
            const jsonData = JSON.parse(responseText);
            
            // Se não foi bem-sucedido, lançar erro com a mensagem do servidor
            if (!response.ok) {
                throw new Error(jsonData.error || jsonData.message || `HTTP error! status: ${response.status}`);
            }
            
            // Retornar um objeto que simula a interface Response mas com json() já processado
            return {
                ok: response.ok,
                status: response.status,
                statusText: response.statusText,
                headers: response.headers,
                json: async () => jsonData,
                // Adicionar os dados diretamente para compatibilidade
                data: jsonData
            };
            
        } catch (parseError) {
            console.error('Erro ao fazer parse da resposta:', parseError);
            if (parseError.message.includes('Unexpected')) {
                throw new Error('Erro de comunicação com o servidor - resposta inválida');
            }
            throw parseError;
        }
    }

    // Método para fazer login
    static async login(email, senha) {
        try {
            const response = await fetch('../../backend/api/routes/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, senha })
            });

            const data = await response.json();

            if (data.success) {
                // Salvar dados no localStorage
                localStorage.setItem(this.TOKEN_KEY, data.token);
                localStorage.setItem(this.USER_KEY, JSON.stringify(data.usuario));
                localStorage.setItem(this.API_KEY_KEY, data.api_key);

                console.log('Login realizado com sucesso');
                console.log('Token salvo:', data.token);
                console.log('API Key salva:', data.api_key);
                console.log('Usuário salvo:', data.usuario);

                return data;
            } else {
                throw new Error(data.error || 'Erro no login');
            }
        } catch (error) {
            console.error('Erro no login:', error);
            throw error;
        }
    }

    // Método para salvar dados de login
    static saveLoginData(data) {
        if (data.token) {
            localStorage.setItem(this.TOKEN_KEY, data.token);
        }
        if (data.usuario) {
            localStorage.setItem(this.USER_KEY, JSON.stringify(data.usuario));
        }
        if (data.api_key) {
            localStorage.setItem(this.API_KEY_KEY, data.api_key);
        }

        console.log('Dados salvos no localStorage:');
        console.log('Token:', localStorage.getItem(this.TOKEN_KEY));
        console.log('User:', localStorage.getItem(this.USER_KEY));
        console.log('API Key:', localStorage.getItem(this.API_KEY_KEY));
    }

    // Método para debug
    static debugAuth() {
        console.log('=== DEBUG AUTH ===');
        console.log('Token:', this.getToken());
        console.log('API Key:', this.getApiKey());
        console.log('User:', this.getUser());
        console.log('Headers:', this.getHeaders());
        console.log('Is Logged In:', this.isLoggedIn());
    }

    // Método para registrar ações do frontend
    static async registrarAcao(acao, detalhes) {
        return { success: true };
    }
}

// Adicionar método global para debug
window.debugAuth = () => AuthClient.debugAuth();

// Verificar autenticação em páginas protegidas
function requireAuth() {
    console.log('Verificando autenticação na página:', window.location.pathname);
    if (!AuthClient.isLoggedIn()) {
        console.log('Usuário não autenticado, redirecionando para login');
        window.location.href = 'login.php';
        return false;
    }
    return true;
}

// Auto-verificação
setInterval(() => {
    if (!AuthClient.isLoggedIn() &&
        !window.location.pathname.includes('login.php')) {
        console.log('Token expirado, fazendo logout automático');
        AuthClient.logout();
    }
}, 30000);

document.addEventListener('DOMContentLoaded', function () {
    // Registrar acesso à página
    if (AuthClient.isLoggedIn()) {
        const pagina = window.location.pathname.split('/').pop();
        AuthClient.registrarAcao('ACESSAR_PAGINA', `Acesso à página: ${pagina}`);
    }
});

// Registrar antes de sair da página
window.addEventListener('beforeunload', function () {
    if (AuthClient.isLoggedIn()) {
        navigator.sendBeacon('/Gerenciamento-ACC/backend/api/routes/auditoria.php',
            JSON.stringify({
                usuario_id: AuthClient.getUser()?.id,
                acao: 'SAIR_PAGINA',
                descricao: `Saiu da página: ${window.location.pathname}`
            })
        );
    }
});
