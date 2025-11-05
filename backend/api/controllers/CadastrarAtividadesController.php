<?php
namespace backend\api\controllers;

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/CadastrarAtividadesModel.php';

use backend\api\models\CadastrarAtividadesModel;

class CadastrarAtividadesController extends Controller {
    
    public function cadastrarAtividade() {
        try {
            // Obter dados do usuário autenticado
            $usuarioLogado = $_POST['usuario_logado'] ?? $_REQUEST['usuario_logado'] ?? null;
            if (!$usuarioLogado) {
                throw new \Exception('Usuário não autenticado');
            }
            
            // Obter dados da requisição - verificar se é FormData ou JSON
            if (isset($_POST['data'])) {
                // Dados enviados via FormData (com arquivo)
                $data = json_decode($_POST['data'], true);
                if (!$data) {
                    throw new \Exception('Dados JSON inválidos');
                }
            } else {
                // Dados enviados via FormData diretamente ou JSON
                $data = [];
                foreach ($_POST as $key => $value) {
                    if ($key !== 'usuario_logado') {
                        $data[$key] = $value;
                    }
                }
                
                // Se não há dados em POST, tentar JSON
                if (empty($data)) {
                    $jsonData = $this->getRequestData();
                    if ($jsonData) {
                        $data = $jsonData;
                    }
                }
            }
            
            // Usar o ID do usuário autenticado
            $data['aluno_id'] = $usuarioLogado['id'];
            
            // Validar campos obrigatórios
            $this->validateRequiredFields($data, [
                'atividades_por_resolucao_id', 
                'titulo',
                'ch_solicitada'
            ]);
            
            // Validar se ch_solicitada é um número positivo
            if (!is_numeric($data['ch_solicitada']) || $data['ch_solicitada'] <= 0) {
                $this->sendJsonResponse(['error' => 'Carga horária solicitada deve ser um número positivo'], 400);
                return;
            }
            
            // Preparar dados para inserção
            $dadosAtividade = [
                'aluno_id' => $data['aluno_id'],
                'atividades_por_resolucao_id' => $data['atividades_por_resolucao_id'],
                'titulo' => $data['titulo'],
                'descricao' => isset($data['descricao']) ? $data['descricao'] : null,
                'ch_solicitada' => (int)$data['ch_solicitada'],
                'ch_atribuida' => 0, // Valor padrão
                'caminho_declaracao' => null, // Será preenchido se houver upload
                'status' => 'Aguardando avaliação', // Status padrão
                'observacoes_avaliador' => null,
                'avaliado_por' => null,
                'data_avaliacao' => null,
                'avaliado' => 0 // Valor padrão
            ];
            
            // Processar upload de arquivo se existir
            if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
                $caminhoArquivo = $this->processarUploadDeclaracao($_FILES['declaracao']);
                if ($caminhoArquivo) {
                    $dadosAtividade['caminho_declaracao'] = $caminhoArquivo;
                }
            }
            
            // Cadastrar atividade no banco
            $atividadeId = CadastrarAtividadesModel::cadastrarAtividade($dadosAtividade);
            
            if ($atividadeId) {
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Atividade cadastrada com sucesso',
                    'atividade_id' => $atividadeId
                ], 201);
            } else {
                $this->sendJsonResponse(['error' => 'Erro ao cadastrar atividade'], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Erro em CadastrarAtividadesController::cadastrarAtividade: " . $e->getMessage());
            $this->sendJsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    private function processarUploadDeclaracao($arquivo) {
        try {
            // Definir diretório de upload
            $diretorioUpload = __DIR__ . '/../../../uploads/declaracoes/';
            
            // Criar diretório se não existir
            if (!is_dir($diretorioUpload)) {
                mkdir($diretorioUpload, 0755, true);
            }
            
            // Validar tipo de arquivo
            $tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($arquivo['type'], $tiposPermitidos)) {
                throw new \Exception('Tipo de arquivo não permitido. Use PDF, JPG ou PNG.');
            }
            
            // Validar tamanho do arquivo (máximo 5MB)
            $tamanhoMaximo = 5 * 1024 * 1024; // 5MB em bytes
            if ($arquivo['size'] > $tamanhoMaximo) {
                throw new \Exception('Arquivo muito grande. Tamanho máximo: 5MB.');
            }
            
            // Gerar nome único para o arquivo
            $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
            $nomeArquivo = uniqid('declaracao_') . '.' . $extensao;
            $caminhoCompleto = $diretorioUpload . $nomeArquivo;
            
            // Mover arquivo para o diretório de destino
            if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
                // Retornar caminho relativo para armazenar no banco
                return 'uploads/declaracoes/' . $nomeArquivo;
            } else {
                throw new \Exception('Erro ao fazer upload do arquivo.');
            }
            
        } catch (\Exception $e) {
            error_log("Erro no upload: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function editar() {
        try {
            // Obter dados do usuário autenticado
            $usuarioLogado = $_POST['usuario_logado'] ?? $_REQUEST['usuario_logado'] ?? null;
            if (!$usuarioLogado) {
                throw new \Exception('Usuário não autenticado');
            }
            
            // Obter dados da requisição
            if (isset($_POST['data'])) {
                // Dados enviados via FormData (com arquivo)
                $data = json_decode($_POST['data'], true);
                if (!$data) {
                    throw new \Exception('Dados JSON inválidos');
                }
            } else {
                // Dados enviados via FormData diretamente ou JSON
                $data = [];
                foreach ($_POST as $key => $value) {
                    if ($key !== 'usuario_logado') {
                        $data[$key] = $value;
                    }
                }
                
                // Se não há dados em POST, tentar JSON
                if (empty($data)) {
                    $jsonData = $this->getRequestData();
                    if ($jsonData) {
                        $data = $jsonData;
                    }
                }
            }
            
            // Validar campos obrigatórios
            $this->validateRequiredFields($data, [
                'id',
                'atividades_por_resolucao_id', 
                'titulo',
                'descricao',
                'ch_solicitada'
            ]);
            
            $atividadeId = $data['id'];
            
            // Verificar se a atividade pertence ao usuário logado
            $atividadeExistente = CadastrarAtividadesModel::obterAtividadePorId($atividadeId);
            if (!$atividadeExistente) {
                throw new \Exception('Atividade não encontrada');
            }
            
            if ($atividadeExistente['aluno_id'] != $usuarioLogado['id']) {
                throw new \Exception('Você não tem permissão para editar esta atividade');
            }
            
            // Processar upload de arquivo se fornecido
            $caminhoDeclaracao = null;
            if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
                $caminhoDeclaracao = $this->processarUploadDeclaracao($_FILES['declaracao']);
                $data['caminho_declaracao'] = $caminhoDeclaracao;
            }
            
            // Editar atividade
            $sucesso = CadastrarAtividadesModel::editarAtividade($atividadeId, $data);
            
            if ($sucesso) {
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Atividade editada com sucesso!',
                    'atividade_id' => $atividadeId
                ]);
            } else {
                throw new \Exception('Erro ao editar atividade');
            }
            
        } catch (\Exception $e) {
            error_log("Erro em CadastrarAtividadesController::editar: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
?>