// Configuração da API
const API_BASE_URL = '/Gerenciamento-de-ACC/backend/api';

// Classe para gerenciar as requisições à API
class ApiService {
    constructor() {
        this.token = localStorage.getItem('token');
    }

    // Método para fazer requisições HTTP
    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}/${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            ...(this.token && { 'Authorization': `Bearer ${this.token}` }),
            ...options.headers
        };

        try {
            const response = await fetch(url, {
                ...options,
                headers
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Erro na requisição');
            }

            return data;
        } catch (error) {
            console.error('Erro na requisição:', error);
            throw error;
        }
    }

    // Métodos para Usuários
    async login(email, senha) {
        const data = await this.request('usuarios/login', {
            method: 'POST',
            body: JSON.stringify({ email, senha })
        });
        
        if (data.token) {
            this.token = data.token;
            localStorage.setItem('token', data.token);
        }
        
        return data;
    }

    async getUsuarios() {
        return this.request('usuarios');
    }

    async getUsuario(id) {
        return this.request(`usuarios/${id}`);
    }

    async createUsuario(usuario) {
        return this.request('usuarios', {
            method: 'POST',
            body: JSON.stringify(usuario)
        });
    }

    async updateUsuario(id, usuario) {
        return this.request(`usuarios/${id}`, {
            method: 'PUT',
            body: JSON.stringify(usuario)
        });
    }

    async deleteUsuario(id) {
        return this.request(`usuarios/${id}`, {
            method: 'DELETE'
        });
    }

    async getUsuariosByTipo(tipo) {
        return this.request(`usuarios/tipo/${tipo}`);
    }

    // Método para logout
    logout() {
        this.token = null;
        localStorage.removeItem('token');
    }
}

// Exporta uma instância do serviço
const api = new ApiService(); 