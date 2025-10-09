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
            matricula: '2019123456'
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
        console.log('🔧 AuthClient.fetch chamado para:', url);
        
        // Se a URL for relativa, usar o servidor backend
        const fullUrl = url.startsWith('http') ? url : `http://localhost:8000/backend${url}`;
        console.log('🌐 URL completa:', fullUrl);
        
        const headers = { ...this.getHeaders(), ...(options.headers || {}) };
        
        // Adicionar token JWT para autenticação
        const token = this.getToken();
        console.log('🎫 Token encontrado no localStorage:', !!token);
        
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
            console.log('✅ Usando token do localStorage');
        } else {
            // Gerar token temporário para teste com dados do usuário atual
            const user = this.getUser();
            console.log('👤 Dados do usuário para token temporário:', user);
            
            const header = btoa(JSON.stringify({typ: 'JWT', alg: 'HS256'}));
            const payload = btoa(JSON.stringify({
                iss: 'http://localhost:8081',
                aud: 'http://localhost:8081',
                iat: Math.floor(Date.now() / 1000),
                exp: Math.floor(Date.now() / 1000) + 3600,
                user_id: user.id,
                matricula: user.matricula,
                tipo: user.tipo
            }));
            const signature = 'Yt8Yt8Yt8Yt8Yt8Yt8Yt8Yt8Yt8Yt8Yt8Yt8Yt8';
            const tempToken = `${header}.${payload}.${signature}`;
            headers['Authorization'] = `Bearer ${tempToken}`;
            console.log('🔄 Token temporário gerado para matrícula:', user.matricula);
        }
        
        console.log('📤 Headers da requisição:', headers);
        
        if (options.body instanceof FormData) {
            delete headers['Content-Type'];
        }
        
        const response = await fetch(fullUrl, {
            ...options,
            headers: headers
        });
        
        console.log('📥 Resposta da requisição:', response.status, response.statusText);
        return response;
    }
}