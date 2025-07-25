<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AtividadeComplementar.php';
require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/LogAcoesController.php';

use backend\api\config\Database;
use backend\api\models\AtividadeComplementar;
use backend\api\models\AtividadesDisponiveis;
use backend\api\controllers\Controller;
use backend\api\controllers\LogAcoesController;

class AtividadeComplementarController extends Controller {
    
    public function cadastrarComJWT($dados) {
        try {
            
            // Validações específicas
            if (empty($dados['titulo']) || strlen(trim($dados['titulo'])) < 3) {
                throw new Exception("Título deve ter pelo menos 3 caracteres");
            }
            
            if (empty($dados['data_inicio']) || empty($dados['data_fim'])) {
                throw new Exception("Datas de início e fim são obrigatórias");
            }
            
            // Validar se data_fim > data_inicio
            if (strtotime($dados['data_fim']) <= strtotime($dados['data_inicio'])) {
                throw new Exception("Data de término deve ser posterior à data de início");
            }
            
            if (empty($dados['carga_horaria_solicitada']) || $dados['carga_horaria_solicitada'] <= 0) {
                throw new Exception("Carga horária solicitada deve ser maior que zero");
            }
            
            if (empty($dados['orientador_id']) || !is_numeric($dados['orientador_id'])) {
                throw new Exception("Orientador deve ser selecionado");
            }
            
            $atividade_id = AtividadeComplementar::create($dados);

            if (!$atividade_id) {
                throw new Exception("Falha ao criar atividade");
            }
            
            // Buscar nome do usuário para o log
            $db = \backend\api\config\Database::getInstance()->getConnection();
            $stmtUsuario = $db->prepare("SELECT nome FROM Usuario WHERE id = ?");
            $stmtUsuario->bind_param("i", $dados['aluno_id']);
            $stmtUsuario->execute();
            $resultUsuario = $stmtUsuario->get_result();
            $usuarioData = $resultUsuario->fetch_assoc();
            $nomeUsuario = $usuarioData ? $usuarioData['nome'] : '';

            // Registrar log de ação
            LogAcoesController::registrar(
                $dados['aluno_id'],
                'CADASTRAR_ATIVIDADE',
                "Atividade '{$dados['titulo']}' cadastrada pelo usuário {$nomeUsuario}"
            );

            return $atividade_id;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarController::cadastrarComJWT: " . $e->getMessage());
            throw $e;
        }
    }

    public function listarOrientadores() {

        try {
            
            $orientadores = AtividadeComplementar::listarOrientadores();
            
            echo json_encode([
                'success' => true,
                'data' => $orientadores
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarOrientadores: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false, 
                'error' => 'Erro ao buscar orientadores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarCoordenadores() {
        try {
            $coordenadores = AtividadeComplementar::listarCoordenadores();
            
            echo json_encode([
                'success' => true,
                'data' => $coordenadores
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarCoordenadores: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false, 
                'error' => 'Erro ao buscar coordenadores: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function listarPorAluno($aluno_id = null) {
        try {
            if ($aluno_id === null) {
                session_start();
                if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'aluno') {
                    return [
                        'success' => false,
                        'error' => 'Acesso negado'
                    ];
                }
                $aluno_id = $_SESSION['usuario']['id'];
            }

            $atividades = AtividadeComplementar::buscarPorAluno($aluno_id);
            return [
                'success' => true,
                'data' => $atividades
            ];
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarPorAluno: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro interno do servidor'
            ];
        }
    }
    
    public function buscar($id) {
        try {
            session_start();
            if (empty($_SESSION['usuario'])) {
                $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                return;
            }
            
            $atividade = AtividadeComplementar::buscarPorId($id);
            
            if (!$atividade) {
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            if ($_SESSION['usuario']['tipo'] === 'aluno' && $atividade['aluno_id'] != $_SESSION['usuario']['id']) {
                $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                return;
            }
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividade
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::buscar: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function listarPendentesOrientadorComJWT($orientador_id) {
        try {
            $atividades = AtividadeComplementar::buscarPendentesOrientador($orientador_id);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarPendentesOrientadorComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function listarAvaliadasOrientadorComJWT($orientador_id) {
        try {
            $atividades = AtividadeComplementar::buscarAvaliadasOrientador($orientador_id);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarAvaliadasOrientadorComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function avaliarAtividadeComJWT($orientador_id, $atividade_id = null, $status = null, $observacoes_analise = null, $carga_horaria_aprovada = null, $certificado_caminho = null) {
        try {
            error_log("=== INICIO avaliarAtividadeComJWT ===");
            error_log("Parâmetros recebidos:");
            error_log("orientador_id: " . ($orientador_id ?? 'null'));
            error_log("atividade_id: " . ($atividade_id ?? 'null'));
            error_log("status: " . ($status ?? 'null'));
            error_log("observacoes_analise: " . ($observacoes_analise ?? 'null'));
            error_log("carga_horaria_aprovada: " . ($carga_horaria_aprovada ?? 'null'));
            
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input) {
                error_log("Dados do JSON: " . print_r($input, true));
                
                $atividade_id = $atividade_id ?? $input['atividade_id'] ?? null;
                $status = $status ?? $input['status'] ?? null;
                $observacoes_analise = $observacoes_analise ?? $input['observacoes_analise'] ?? null;
                $carga_horaria_aprovada = $carga_horaria_aprovada ?? $input['carga_horaria_aprovada'] ?? null;
                $certificado_caminho = $certificado_caminho ?? $input['certificado_caminho'] ?? null;
            }
          
            if (!$atividade_id || !$status) {
                error_log("Tentando $_POST...");
                $atividade_id = $atividade_id ?? $_POST['atividade_id'] ?? null;
                $status = $status ?? $_POST['status'] ?? null;
                $observacoes_analise = $observacoes_analise ?? $_POST['observacoes_analise'] ?? null;
                $carga_horaria_aprovada = $carga_horaria_aprovada ?? $_POST['carga_horaria_aprovada'] ?? null;
                $certificado_caminho = $certificado_caminho ?? $_POST['certificado_caminho'] ?? null;
            }

            // Validar dados obrigatórios para avaliação normal
            $erros = $this->validarDadosAvaliacao([
                'atividade_id' => $atividade_id,
                'carga_horaria_aprovada' => $carga_horaria_aprovada,
                'observacoes_analise' => $observacoes_analise,
                'status' => $status
            ]);
            
            if (!empty($erros)) {
                error_log("Erros de validação: " . implode(', ', $erros));
                $this->sendJsonResponse(['error' => implode(', ', $erros)], 400);
                return;
            }
            
            // Validar status
            if (!in_array($status, ['Aprovada', 'Rejeitada'])) {
                $this->sendJsonResponse(['error' => 'Status inválido. Deve ser "Aprovada" ou "Rejeitada"'], 400);
                return;
            }
            
            if ($status === 'Rejeitada') {
                $carga_horaria_aprovada = 0;
            }
            
            if ($status === 'Aprovada' && $carga_horaria_aprovada <= 0) {
                $this->sendJsonResponse(['error' => 'Para aprovar, a carga horária deve ser maior que zero'], 400);
                return;
            }
            
            // Buscar atividade
            $atividade = AtividadeComplementar::buscarPorId($atividade_id);
            if (!$atividade) {
                error_log("Atividade não encontrada: " . $atividade_id);
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            if ($atividade['orientador_id'] != $orientador_id) {
                $this->sendJsonResponse(['error' => 'Você não tem permissão para avaliar esta atividade'], 403);
                return;
            }
            
            // Verificar se a atividade ainda está pendente
            if ($atividade['status'] !== 'Pendente') {
                $this->sendJsonResponse(['error' => 'Esta atividade já foi avaliada'], 400);
                return;
            }
            
            // Verificar se não excede as horas solicitadas
            if ($status === 'Aprovada' && $carga_horaria_aprovada > $atividade['carga_horaria_solicitada']) {
                $this->sendJsonResponse([
                    'error' => "Não é possível aprovar mais horas ({$carga_horaria_aprovada}h) do que o aluno solicitou ({$atividade['carga_horaria_solicitada']}h)"
                ], 400);
                return;
            }
            
            // Chama o Model para salvar avaliação
            $sucesso = AtividadeComplementar::avaliarAtividade(
                $atividade_id,
                $orientador_id,
                $carga_horaria_aprovada,
                $observacoes_analise,
                $status,
                null
            );
            
            if ($sucesso) {
                // Buscar nome da atividade para o log
                $atividade_nome = $atividade['titulo'] ?? ('ID ' . $atividade_id);
                LogAcoesController::registrar(
                    $orientador_id,
                    'AVALIAR_ATIVIDADE',
                    "Atividade '{$atividade_nome}' avaliada como '{$status}' com {$carga_horaria_aprovada}h aprovadas"
                );
                
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => "Atividade {$status} com sucesso",
                    'status' => $status,
                    'horas_aprovadas' => $carga_horaria_aprovada,
                    'atividade_id' => $atividade_id
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Falha ao salvar avaliação no banco de dados'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarController::avaliarAtividadeComJWT: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendJsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    public function processarUploadCertificado($orientador_id, $atividade_id) {
        try {
            error_log("=== INICIO processarUploadCertificado ===");
            error_log("Orientador ID: " . $orientador_id);
            error_log("Atividade ID: " . $atividade_id);
            error_log("FILES: " . print_r($_FILES, true));
            
            // Verificar se a atividade existe
            $atividade = AtividadeComplementar::buscarPorId($atividade_id);
            if (!$atividade) {
                error_log("Atividade não encontrada: " . $atividade_id);
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            
            error_log("Atividade encontrada: " . print_r($atividade, true));
            
            // Verificar se o orientador é o responsável pela atividade
            if ($atividade['orientador_id'] != $orientador_id) {
                $this->sendJsonResponse(['error' => 'Você não tem permissão para enviar certificado para esta atividade'], 403);
                return;
            }
            
            // Verificar se a atividade está aprovada
            if ($atividade['status'] !== 'Aprovada') {
                $this->sendJsonResponse(['error' => 'Só é possível enviar certificado para atividades aprovadas'], 400);
                return;
            }
            
            // Verificar se há arquivo enviado
            if (!isset($_FILES['certificado']) || $_FILES['certificado']['error'] !== UPLOAD_ERR_OK) {
                error_log("Erro no arquivo: " . ($_FILES['certificado']['error'] ?? 'arquivo não encontrado'));
                $this->sendJsonResponse(['error' => 'Arquivo de certificado é obrigatório'], 400);
                return;
            }
            
            // Processar upload do arquivo
            $uploadDir = __DIR__ . '/../../uploads/certificados/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $file = $_FILES['certificado'];
            $fileName = time() . '_' . $atividade_id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $filePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                error_log("Erro ao mover arquivo de " . $file['tmp_name'] . " para " . $filePath);
                $this->sendJsonResponse(['error' => 'Erro ao salvar o arquivo'], 500);
                return;
            }
            
            // Caminho relativo para salvar no banco
            $certificadoCaminho = 'uploads/certificados/' . $fileName;
            
            // Atualizar certificado na atividade
            $sucesso = AtividadeComplementar::atualizarCertificado($atividade_id, $certificadoCaminho);
            
            if ($sucesso) {
                // Registrar ação no log de auditoria
                LogAcoesController::registrar(
                    $orientador_id,
                    'UPLOAD_CERTIFICADO',
                    "Certificado enviado para atividade ID: {$atividade_id}"
                );
                
                error_log("Certificado salvo com sucesso: " . $certificadoCaminho);
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Certificado enviado com sucesso!',
                    'arquivo' => $fileName
                ]);
            } else {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $this->sendJsonResponse(['error' => 'Erro ao salvar certificado no banco de dados'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Erro em processarUploadCertificado: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function enviarCertificadoProcessado($aluno_id, $atividade_id) {
        try {
            // Verificar se há arquivo enviado
            if (!isset($_FILES['arquivo_comprovante']) || $_FILES['arquivo_comprovante']['error'] !== UPLOAD_ERR_OK) {
                $this->sendJsonResponse(['error' => 'Arquivo de comprovante é obrigatório'], 400);
                return;
            }

            // Verificar se coordenador_id foi fornecido
            $coordenador_id = $_POST['coordenador_id'] ?? null;
            if (!$coordenador_id) {
                $this->sendJsonResponse(['error' => 'ID do coordenador é obrigatório'], 400);
                return;
            }

            // Verificar se a atividade existe e pertence ao aluno
            $atividade = AtividadeComplementar::buscarPorId($atividade_id);
            if (!$atividade) {
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }

            if ($atividade['aluno_id'] != $aluno_id) {
                $this->sendJsonResponse(['error' => 'Você não tem permissão para enviar certificado para esta atividade'], 403);
                return;
            }

            // Verificar se a atividade está aprovada
            if ($atividade['status'] !== 'Aprovada') {
                $this->sendJsonResponse(['error' => 'Só é possível enviar certificado para atividades aprovadas'], 400);
                return;
            }

            // Processar upload do arquivo
            $uploadDir = __DIR__ . '/../../uploads/certificados_aluno/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $file = $_FILES['arquivo_comprovante'];
            $fileName = time() . '_' . $atividade_id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                $this->sendJsonResponse(['error' => 'Erro ao salvar o arquivo'], 500);
                return;
            }

            // Caminho relativo para salvar no banco
            $certificadoCaminho = 'uploads/certificados_aluno/' . $fileName;

            // Atualizar certificado_processado e avaliador_id
            $sucesso = AtividadeComplementar::atualizarCertificadoProcessado($atividade_id, $certificadoCaminho, $coordenador_id);

            if ($sucesso) {
                // Registrar ação no log de auditoria
                LogAcoesController::registrar(
                    $aluno_id,
                    'ENVIAR_CERTIFICADO_COORDENADOR',
                    "Certificado enviado para coordenador (ID: {$coordenador_id}) para atividade '{$atividade['titulo']}' (ID: {$atividade_id})"
                );

                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Certificado enviado com sucesso! Ele será analisado pela coordenação.',
                    'arquivo' => $fileName
                ]);
            } else {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $this->sendJsonResponse(['error' => 'Erro ao salvar certificado no banco de dados'], 500);
            }

        } catch (Exception $e) {
            error_log("Erro em enviarCertificadoProcessado: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function listarCertificadosPendentesCoordenadorComJWT($coordenador_id) {
        try {
            $atividades = AtividadeComplementar::buscarCertificadosPendentesPorCoordenador($coordenador_id);

            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
        } catch (\Exception $e) {
            error_log("Erro em listarCertificadosPendentesCoordenadorComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function listarCertificadosProcessadosCoordenadorComJWT($coordenador_id) {
        try {
            $atividades = AtividadeComplementar::buscarCertificadosProcessadosPorCoordenador($coordenador_id);

            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
        } catch (\Exception $e) {
            error_log("Erro em listarCertificadosProcessadosCoordenadorComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    private function validarDadosAvaliacao($dados) {
        $erros = [];
        
        // Validar atividade_id
        if (empty($dados['atividade_id']) || !is_numeric($dados['atividade_id'])) {
            $erros[] = 'ID da atividade é obrigatório e deve ser um número válido';
        }
        
        // Validar carga_horaria_aprovada
        if (!isset($dados['carga_horaria_aprovada']) || !is_numeric($dados['carga_horaria_aprovada']) || $dados['carga_horaria_aprovada'] < 0) {
            $erros[] = 'Carga horária aprovada deve ser um número válido maior ou igual a zero';
        }
        
        // Validar observacoes_analise
        if (empty($dados['observacoes_analise']) || strlen(trim($dados['observacoes_analise'])) < 10) {
            $erros[] = 'Observações da análise são obrigatórias e devem ter pelo menos 10 caracteres';
        }
        
        // Validar status
        if (empty($dados['status']) || !in_array($dados['status'], ['Aprovada', 'Rejeitada'])) {
            $erros[] = 'Status deve ser "Aprovada" ou "Rejeitada"';
        }
        
        return $erros;
    }

    public function rejeitarCertificadoComJWT($coordenador_id, $atividade_id, $observacoes) {
        try {
            error_log("=== INICIO rejeitarCertificadoComJWT ===");
            error_log("Coordenador ID: " . $coordenador_id);
            error_log("Atividade ID: " . $atividade_id);
            error_log("Observações: " . $observacoes);
            
            // Verificar se a atividade existe
            $atividade = AtividadeComplementar::buscarPorId($atividade_id);
            if (!$atividade) {
                error_log("Atividade não encontrada: " . $atividade_id);
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            
            error_log("Atividade encontrada: " . print_r($atividade, true));
            
            if (empty($atividade['certificado_processado'])) {
                $this->sendJsonResponse(['error' => 'Esta atividade não possui certificado para rejeição'], 400);
                return;
            }
            
            if ($atividade['avaliador_id'] != $coordenador_id) {
                $this->sendJsonResponse(['error' => 'Este certificado não foi enviado para você'], 403);
                return;
            }
            
            // Rejeitar o certificado
            $sucesso = AtividadeComplementar::rejeitarCertificado($atividade_id, $coordenador_id, $observacoes);
            
            if ($sucesso) {
                // Registrar ação no log de auditoria
                LogAcoesController::registrar(
                    $coordenador_id,
                    'REJEITAR_CERTIFICADO',
                    "Certificado rejeitado para atividade ID: {$atividade_id} - Motivo: {$observacoes}"
                );
                
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Certificado rejeitado com sucesso'
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Erro ao rejeitar certificado'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Erro em rejeitarCertificadoComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function aprovarCertificadoComJWT($coordenador_id, $atividade_id, $observacoes = '') {
        try {
            error_log("=== INICIO aprovarCertificadoComJWT ===");
            error_log("Coordenador ID: " . $coordenador_id);
            error_log("Atividade ID: " . $atividade_id);
            error_log("Observações: " . $observacoes);
            
            // Verificar se a atividade existe
            $atividade = AtividadeComplementar::buscarPorId($atividade_id);
            if (!$atividade) {
                error_log("Atividade não encontrada: " . $atividade_id);
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            
            error_log("Atividade encontrada: " . print_r($atividade, true));
            
            if (empty($atividade['certificado_processado'])) {
                $this->sendJsonResponse(['error' => 'Esta atividade não possui certificado para aprovação'], 400);
                return;
            }
            
            if ($atividade['avaliador_id'] != $coordenador_id) {
                $this->sendJsonResponse(['error' => 'Este certificado não foi enviado para você'], 403);
                return;
            }
            
            // Verificar se já foi aprovado usando observacoes_Analise
            if (!empty($atividade['observacoes_Analise']) && 
                strpos($atividade['observacoes_Analise'], '[CERTIFICADO APROVADO PELO COORDENADOR') !== false) {
                $this->sendJsonResponse(['error' => 'Este certificado já foi aprovado'], 400);
                return;
            }
            
            // Aprovar o certificado
            $sucesso = AtividadeComplementar::aprovarCertificado($atividade_id, $coordenador_id, $observacoes);
            
            if ($sucesso) {
                // Registrar ação no log de auditoria
                LogAcoesController::registrar(
                    $coordenador_id,
                    'APROVAR_CERTIFICADO',
                    "Certificado aprovado para atividade ID: {$atividade_id}" . ($observacoes ? " - Observações: {$observacoes}" : "")
                );
                
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Certificado aprovado com sucesso'
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Erro ao aprovar certificado'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Erro em aprovarCertificadoComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function listarCategorias() {
        require_once __DIR__ . '/../models/AtividadeComplementar.php';
        $categorias = \backend\api\models\AtividadeComplementar::listarCategorias();
        echo json_encode([
            'success' => true,
            'data' => $categorias
        ]);
    }
}

if (isset($_GET['orientadores'])) {
    $controller = new AtividadeComplementarController();
    $controller->listarOrientadores();
    exit;
}

if (isset($_GET['coordenadores'])) {
    $controller = new AtividadeComplementarController();
    $controller->listarCoordenadores();
    exit;
}