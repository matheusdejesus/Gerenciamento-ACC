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
        console.log('=== AuthClient.fetch INICIADO ===');
        console.log('URL:', url);
        console.log('Options recebidas:', options);
        console.log('Token atual:', this.getToken());
        console.log('API Key atual:', this.getApiKey());
        console.log('Usuário atual:', this.getUser());
        
        try {
            // Preparar headers
            const defaultHeaders = this.getHeaders();
            let headers = { ...defaultHeaders, ...(options.headers || {}) };
            
            // Para FormData, não definir Content-Type (deixar o browser definir com boundary)
            if (options.body instanceof FormData) {
                delete headers['Content-Type'];
                console.log('FormData detectado - removendo Content-Type header');
            }
            
            console.log('Headers finais que serão enviados:', headers);
            
            const response = await fetch(url, {
                ...options,
                headers: headers
            });

            console.log('=== RESPOSTA FETCH ===');
            console.log('Status:', response.status);
            console.log('StatusText:', response.statusText);
            console.log('Headers da resposta:', Object.fromEntries(response.headers.entries()));
            console.log('OK:', response.ok);

            // Verificar se é erro de autenticação
            if (response.status === 401) {
                console.log('Erro de autenticação detectado, fazendo logout');
                this.logout();
                throw new Error('Sessão expirada. Faça login novamente.');
            }

            // Verificar se é erro de servidor (5xx)
            if (response.status >= 500) {
                console.error('Erro interno do servidor:', response.status);
                throw new Error(`Erro interno do servidor (${response.status}). Tente novamente em alguns instantes.`);
            }

            // Se for erro 4xx, tentar ler a resposta para obter detalhes
            if (response.status >= 400 && response.status < 500) {
                console.error('Erro de requisição:', response.status);
                // Tentar ler a resposta para obter detalhes do erro
                try {
                    const errorText = await response.text();
                    console.error('Texto da resposta de erro:', errorText);
                    if (errorText) {
                        try {
                            const errorData = JSON.parse(errorText);
                            console.error('Dados do erro parseados:', errorData);
                            throw new Error(errorData.error || errorData.message || `Erro ${response.status}: ${response.statusText}`);
                        } catch (parseError) {
                            console.error('Erro ao fazer parse do JSON de erro:', parseError);
                            throw new Error(`Erro ${response.status}: ${response.statusText} - ${errorText.substring(0, 100)}`);
                        }
                    } else {
                        throw new Error(`Erro ${response.status}: ${response.statusText}`);
                    }
                } catch (readError) {
                    console.error('Erro ao ler resposta de erro:', readError);
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
            }

            // Sempre tentar fazer o parsing do JSON, independente do status
            try {
                const responseText = await response.text();
                console.log('Response text length:', responseText.length);
                console.log('Response text (first 500 chars):', responseText.substring(0, 500));
                
                // Se a resposta estiver vazia, retornar erro específico
                if (!responseText.trim()) {
                    console.error('Resposta vazia do servidor');
                    throw new Error('Resposta vazia do servidor');
                }
                
                // Verificar se a resposta é HTML (erro de servidor)
                if (responseText.trim().startsWith('<')) {
                    console.error('Resposta HTML recebida em vez de JSON:', responseText.substring(0, 200));
                    throw new Error('Erro de servidor - resposta HTML recebida');
                }
                
                // Fazer parse do JSON
                let jsonData;
                try {
                    jsonData = JSON.parse(responseText);
                    console.log('JSON parsed successfully:', jsonData);
                } catch (jsonError) {
                    console.error('Erro ao fazer parse do JSON:', jsonError);
                    console.error('Response text que causou erro:', responseText);
                    throw new Error('Resposta do servidor não é um JSON válido');
                }
                
                // Se não foi bem-sucedido, lançar erro com a mensagem do servidor
                if (!response.ok) {
                    const errorMessage = jsonData.error || jsonData.message || `HTTP error! status: ${response.status}`;
                    console.error('Erro do servidor:', errorMessage);
                    throw new Error(errorMessage);
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
                console.error('Erro completo ao processar resposta:', parseError);
                console.error('Stack trace:', parseError.stack);
                
                if (parseError.message.includes('Unexpected')) {
                    throw new Error('Erro de comunicação com o servidor - resposta inválida');
                }
                throw parseError;
            }
            
        } catch (networkError) {
            console.error('Erro de rede ou requisição:', networkError);
            console.error('Stack trace:', networkError.stack);
            
            // Se for erro de rede, fornecer mensagem mais clara
            if (networkError.name === 'TypeError' && networkError.message.includes('fetch')) {
                throw new Error('Erro de conexão com o servidor. Verifique sua conexão de internet.');
            }
            
            // Se for erro de CORS
            if (networkError.message.includes('CORS')) {
                throw new Error('Erro de CORS. Verifique as configurações do servidor.');
            }
            
            throw networkError;
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
