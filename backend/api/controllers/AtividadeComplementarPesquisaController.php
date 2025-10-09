<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AtividadeComplementarPesquisa.php';
require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/HorasLimiteService.php';

use backend\api\config\Database;
use backend\api\models\AtividadeComplementarPesquisa;
use backend\api\models\AtividadesDisponiveis;
use backend\api\controllers\Controller;
use backend\api\middleware\AuthMiddleware;
use backend\api\services\HorasLimiteService;
use Exception;

class AtividadeComplementarPesquisaController extends Controller {

    /**
     * Cadastrar nova atividade de pesquisa com JWT
     */
    public function cadastrarComJWT($aluno_id, $dados) {
        try {
            // VALIDAÇÃO CRÍTICA: Verificar se o aluno já atingiu o limite total de 240h
            $totalHorasAtual = HorasLimiteService::calcularTotalHorasAluno($aluno_id);
            if ($totalHorasAtual >= 240) {
                throw new Exception("🚫 Limite total de 240 horas já foi atingido. Não é possível cadastrar novas atividades em nenhuma categoria.");
            }

            // Adicionar o ID do aluno aos dados
            $dados['aluno_id'] = $aluno_id;
            
            // Validar dados obrigatórios
            $this->validarDadosCadastro($dados);
            
            // VALIDAÇÃO CRÍTICA: Verificar limite da categoria Pesquisa (80h)
            $horasAtualPesquisa = HorasLimiteService::calcularHorasCategoria($aluno_id, 'pesquisa');
            $limitePesquisa = HorasLimiteService::getLimiteCategoria('pesquisa');
            $horasSolicitadas = (int)$dados['horas_realizadas'];
            
            // Verificar se já atingiu o limite da categoria
            if ($horasAtualPesquisa >= $limitePesquisa) {
                throw new Exception("🚫 Limite máximo de {$limitePesquisa} horas para atividades de Pesquisa já foi atingido. Você já possui {$horasAtualPesquisa}h cadastradas nesta categoria.");
            }
            
            // Verificar se a nova atividade excederia o limite da categoria
            $totalComNovaAtividade = $horasAtualPesquisa + $horasSolicitadas;
            if ($totalComNovaAtividade > $limitePesquisa) {
                $horasRestantes = $limitePesquisa - $horasAtualPesquisa;
                throw new Exception("⚠️ Limite da categoria Pesquisa seria excedido. Você possui {$horasAtualPesquisa}h cadastradas e pode adicionar no máximo {$horasRestantes}h adicionais nesta categoria. Reduza as horas desta atividade para prosseguir.");
            }

            // NOVA FUNCIONALIDADE: Validar limite de 80h para alunos 2017-2022
            $this->validarLimite80hExtracurriculares($aluno_id, (int)$dados['horas_realizadas']);

            // Processar upload se houver arquivo
            if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
                $dados['declaracao_caminho'] = $this->processarUploadArquivo($_FILES['declaracao']);
            }

            // Criar atividade
            $atividade_id = AtividadeComplementarPesquisa::create($dados);

            if (!$atividade_id) {
                throw new Exception("Falha ao criar atividade de pesquisa");
            }

            return $atividade_id;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarPesquisaController::cadastrarComJWT: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cadastrar nova atividade de pesquisa
     */
    public function cadastrar($dados = null) {
        try {
            error_log("=== INÍCIO CADASTRO ATIVIDADE PESQUISA ===");
            
            // Verificar autenticação
            $usuario = AuthMiddleware::validateToken();
            
            if (!$usuario) {
                error_log("ERRO: Usuário não autenticado");
                http_response_code(403);
                echo json_encode(['erro' => 'Acesso negado']);
                return;
            }
            
            error_log("Usuário autenticado: " . json_encode($usuario));

            // Se não foram passados dados, pegar do POST
            if ($dados === null) {
                $dados = $_POST;
            }
            
            error_log("Dados recebidos: " . json_encode($dados));

            // Adicionar o ID do aluno logado
            $dados['aluno_id'] = $usuario['id'];
            
            error_log("Dados após adicionar aluno_id: " . json_encode($dados));

            // VALIDAÇÃO CRÍTICA: Verificar se o aluno já atingiu o limite total de 240h
            $totalHorasAtual = HorasLimiteService::calcularTotalHorasAluno($usuario['id']);
            if ($totalHorasAtual >= 240) {
                throw new Exception("🚫 Limite total de 240 horas já foi atingido. Não é possível cadastrar novas atividades em nenhuma categoria.");
            }

            // Validar dados obrigatórios
            error_log("Iniciando validação dos dados...");
            $this->validarDadosCadastro($dados);
            error_log("Validação concluída com sucesso");
            
            // VALIDAÇÃO CRÍTICA: Verificar limite da categoria Pesquisa (80h)
            $horasAtualPesquisa = HorasLimiteService::calcularHorasCategoria($usuario['id'], 'pesquisa');
            $limitePesquisa = HorasLimiteService::getLimiteCategoria('pesquisa');
            $horasSolicitadas = (int)$dados['horas_realizadas'];
            
            // Verificar se já atingiu o limite da categoria
            if ($horasAtualPesquisa >= $limitePesquisa) {
                throw new Exception("🚫 Limite máximo de {$limitePesquisa} horas para atividades de Pesquisa já foi atingido. Você já possui {$horasAtualPesquisa}h cadastradas nesta categoria.");
            }
            
            // Verificar se a nova atividade excederia o limite da categoria
            $totalComNovaAtividade = $horasAtualPesquisa + $horasSolicitadas;
            if ($totalComNovaAtividade > $limitePesquisa) {
                $horasRestantes = $limitePesquisa - $horasAtualPesquisa;
                throw new Exception("⚠️ Limite da categoria Pesquisa seria excedido. Você possui {$horasAtualPesquisa}h cadastradas e pode adicionar no máximo {$horasRestantes}h adicionais nesta categoria. Reduza as horas desta atividade para prosseguir.");
            }

            // NOVA FUNCIONALIDADE: Validar limite de 80h para alunos 2017-2022
            $this->validarLimite80hExtracurriculares($usuario['id'], (int)$dados['horas_realizadas']);

            // Buscar matrícula do aluno para usar na busca da atividade
            $matricula = null;
            if (isset($dados['aluno_id'])) {
                $db = \backend\api\config\Database::getInstance()->getConnection();
                $sqlMatricula = "SELECT matricula FROM Aluno WHERE usuario_id = ?";
                $stmtMatricula = $db->prepare($sqlMatricula);
                $stmtMatricula->bind_param("i", $dados['aluno_id']);
                $stmtMatricula->execute();
                $resultMatricula = $stmtMatricula->get_result();
                if ($row = $resultMatricula->fetch_assoc()) {
                    $matricula = $row['matricula'];
                }
            }
            
            // Validar se a atividade disponível existe (passando matrícula para usar tabela correta)
            error_log("Buscando atividade disponível ID: " . $dados['atividade_disponivel_id'] . " para matrícula: " . $matricula);
            $atividadeDisponivel = AtividadesDisponiveis::buscarPorId($dados['atividade_disponivel_id'], $matricula);
            if (!$atividadeDisponivel) {
                error_log("ERRO: Atividade disponível não encontrada");
                throw new Exception("Atividade disponível não encontrada");
            }
            error_log("Atividade disponível encontrada: " . json_encode($atividadeDisponivel));

            // Verificar se há atividades já cadastradas para esta atividade específica
            error_log("Verificando atividades já cadastradas para o aluno...");
            $atividadesExistentes = AtividadeComplementarPesquisa::buscarPorAluno($dados['aluno_id']);
            $horasJaCadastradas = 0;
            
            foreach ($atividadesExistentes as $atividade) {
                if ($atividade['atividade_disponivel_id'] == $dados['atividade_disponivel_id'] && 
                    ($atividade['status'] === 'aprovada' || $atividade['status'] === 'Aguardando avaliação')) {
                    $horasJaCadastradas += $atividade['horas_realizadas'];
                }
            }
            
            // Usar o campo correto baseado na estrutura retornada pelo modelo
            $horasMaximas = $atividadeDisponivel['carga_horaria_maxima_por_atividade'] ?? $atividadeDisponivel['horas_max'] ?? 0;
            
            error_log("Validando horas: já cadastradas = {$horasJaCadastradas}, novas = {$dados['horas_realizadas']}, máximo = {$horasMaximas}");
            error_log("DEBUG: Estrutura da atividade disponível: " . json_encode($atividadeDisponivel));
            
            // Verificar se é uma atividade de "Apresentação em eventos científicos"
            $nomeAtividade = $atividadeDisponivel['nome'] ?? '';
            $isApresentacaoEvento = strpos($nomeAtividade, 'Apresentação em eventos científicos') !== false;
            
            error_log("DEBUG: Nome da atividade: '{$nomeAtividade}', É apresentação em evento: " . ($isApresentacaoEvento ? 'SIM' : 'NÃO'));
            error_log("DEBUG: Horas recebidas do frontend: {$dados['horas_realizadas']}");
            error_log("DEBUG: Matrícula do aluno: {$matricula}");
            
            if ($isApresentacaoEvento) {
                // Para apresentações em eventos científicos, verificar o ano da matrícula para definir o limite
                $anoMatricula = (int) substr($matricula, 0, 4);
                $limiteHoras = ($anoMatricula >= 2023) ? 9 : 20;
                
                error_log("Apresentação em eventos científicos detectada. Ano matrícula: {$anoMatricula}, Limite aplicado: {$limiteHoras}h");
                error_log("Horas já cadastradas: {$horasJaCadastradas}, Horas novas: {$dados['horas_realizadas']}");
                
                if ($horasJaCadastradas >= $limiteHoras) {
                    // Se já atingiu o limite máximo, mostrar mensagem baseada no ano da matrícula
                    error_log("Limite de {$limiteHoras}h já atingido. Bloqueando cadastro.");
                    $mensagem = ($anoMatricula >= 2023) ? "Você já possui 9h cadastradas." : "Você já possui 20h cadastradas.";
                    throw new Exception($mensagem);
                }
                
                // Se as novas horas excedem o limite, ajustar para o máximo permitido
                if ($horasJaCadastradas + $dados['horas_realizadas'] > $limiteHoras) {
                    $horasRestantes = $limiteHoras - $horasJaCadastradas;
                    $horasOriginais = $dados['horas_realizadas'];
                    $dados['horas_realizadas'] = $horasRestantes;
                    error_log("Ajustando horas de {$horasOriginais} para {$horasRestantes} (limite restante para matrícula {$anoMatricula}).");
                }
                
                $totalHoras = $horasJaCadastradas + $dados['horas_realizadas'];
                error_log("Apresentação em eventos científicos: total final = {$totalHoras}h, limite = {$limiteHoras}h");
                error_log("Validação especial concluída para apresentação em eventos científicos");
                
            } else {
                // Para outras atividades, manter a validação original
                $totalHoras = $horasJaCadastradas + $dados['horas_realizadas'];
                
                error_log("DEBUG: Validação geral - Total: {$totalHoras}h, Máximo: {$horasMaximas}h");
                
                if ($totalHoras > $horasMaximas) {
                    throw new Exception("Você já possui {$horasJaCadastradas}h cadastradas.");
                }
            }

            // Processar upload do arquivo se necessário
            error_log("Verificando upload de arquivo...");
            if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
                error_log("Processando upload do arquivo: " . $_FILES['declaracao']['name']);
                $dados['declaracao_caminho'] = $this->processarUploadArquivo($_FILES['declaracao']);
                error_log("Arquivo processado: " . $dados['declaracao_caminho']);
            } elseif (empty($dados['declaracao_caminho'])) {
                error_log("ERRO: Declaração/Certificado não fornecido");
                throw new Exception("Declaração/Certificado é obrigatório");
            }

            // Criar a atividade
            error_log("Criando atividade no banco de dados...");
            $id = AtividadeComplementarPesquisa::create($dados);
            error_log("Atividade criada com ID: " . $id);

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Atividade de pesquisa cadastrada com sucesso',
                'id' => $id
            ]);

        } catch (Exception $e) {
            error_log("ERRO ao cadastrar atividade de pesquisa: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("Dados que causaram o erro: " . json_encode($dados ?? []));
            error_log("POST data: " . json_encode($_POST));
            error_log("FILES data: " . json_encode($_FILES));
            
            // Garantir que não há saída anterior
            if (ob_get_level()) {
                ob_clean();
            }
            
            // Verificar se a mensagem de erro está vazia ou é muito genérica
            $errorMessage = $e->getMessage();
            if (empty($errorMessage) || $errorMessage === 'Exception') {
                $errorMessage = 'Erro interno do servidor. Verifique os logs para mais detalhes.';
            }
            
            $this->sendJsonResponse([
                'success' => false,
                'error' => $errorMessage,
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode()
                ]
            ], 400);
        }
    }

    /**
     * Listar atividades por aluno
     */
    public function listarPorAluno($aluno_id = null) {
        try {
            if ($aluno_id === null) {
                $aluno_id = $_GET['aluno_id'] ?? null;
            }

            if (empty($aluno_id)) {
                throw new Exception("ID do aluno é obrigatório");
            }

            $atividades = AtividadeComplementarPesquisa::buscarPorAluno($aluno_id);

            echo json_encode([
                'success' => true,
                'data' => $atividades
            ]);

        } catch (Exception $e) {
            error_log("Erro ao listar atividades de pesquisa por aluno: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Buscar atividade por ID
     */
    public function buscarPorId($id = null) {
        try {
            if ($id === null) {
                $id = $_GET['id'] ?? null;
            }

            if (empty($id)) {
                throw new Exception("ID da atividade é obrigatório");
            }

            $atividade = AtividadeComplementarPesquisa::buscarPorId($id);

            if (!$atividade) {
                throw new Exception("Atividade não encontrada");
            }

            $this->sendJsonResponse([
                'success' => true,
                'atividade' => $atividade
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar atividade de pesquisa por ID: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Listar todas as atividades (para coordenadores)
     */
    public function listarTodas() {
        try {
            $filtros = [];
            
            if (!empty($_GET['status'])) {
                $filtros['status'] = $_GET['status'];
            }
            
            if (!empty($_GET['curso_id'])) {
                $filtros['curso_id'] = $_GET['curso_id'];
            }
            
            if (!empty($_GET['tipo_atividade'])) {
                $filtros['tipo_atividade'] = $_GET['tipo_atividade'];
            }

            $atividades = AtividadeComplementarPesquisa::listarTodas($filtros);

            $this->sendJsonResponse([
                'success' => true,
                'atividades' => $atividades
            ]);

        } catch (Exception $e) {
            error_log("Erro ao listar todas as atividades de pesquisa: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Avaliar atividade (aprovar/rejeitar)
     */
    public function avaliarAtividade($dados = null) {
        try {
            if ($dados === null) {
                $dados = $_POST;
            }

            // Validar dados obrigatórios
            if (empty($dados['id']) || !is_numeric($dados['id'])) {
                throw new Exception("ID da atividade é obrigatório");
            }

            if (empty($dados['status']) || !in_array($dados['status'], ['aprovado', 'rejeitado'])) {
                throw new Exception("Status deve ser 'aprovado' ou 'rejeitado'");
            }

            if (empty($dados['avaliador_id']) || !is_numeric($dados['avaliador_id'])) {
                throw new Exception("ID do avaliador é obrigatório");
            }

            $observacoes = $dados['observacoes_avaliacao'] ?? null;

            // Verificar se a atividade existe
            $atividade = AtividadeComplementarPesquisa::buscarPorId($dados['id']);
            if (!$atividade) {
                throw new Exception("Atividade não encontrada");
            }

            // Verificar se a atividade ainda está pendente
            if ($atividade['status'] !== 'Aguardando avaliação') {
                throw new Exception("Esta atividade já foi avaliada");
            }

            // Atualizar status
            $sucesso = AtividadeComplementarPesquisa::atualizarStatus(
                $dados['id'],
                $dados['status'],
                $observacoes,
                $dados['avaliador_id']
            );

            if (!$sucesso) {
                throw new Exception("Erro ao atualizar status da atividade");
            }

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Atividade avaliada com sucesso'
            ]);

        } catch (Exception $e) {
            error_log("Erro ao avaliar atividade de pesquisa: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validar dados do cadastro
     */
    private function validarDadosCadastro($dados) {
        if (empty($dados['aluno_id']) || !is_numeric($dados['aluno_id'])) {
            throw new Exception("ID do aluno é obrigatório");
        }

        if (empty($dados['atividade_disponivel_id']) || !is_numeric($dados['atividade_disponivel_id'])) {
            throw new Exception("ID da atividade disponível é obrigatório");
        }

        if (empty($dados['tipo_atividade'])) {
            throw new Exception("Tipo de atividade é obrigatório");
        }

        $tiposValidos = ['apresentacao_evento', 'membro_evento', 'iniciacao_cientifica', 'publicacao_artigo'];
        if (!in_array($dados['tipo_atividade'], $tiposValidos)) {
            throw new Exception("Tipo de atividade inválido");
        }

        // Validar horas_realizadas ou quantidade_apresentacoes dependendo do tipo
        if ($dados['tipo_atividade'] === 'apresentacao_evento') {
            error_log("DEBUG: Validando quantidade_apresentacoes");
            error_log("DEBUG: Valor recebido: " . print_r($dados['quantidade_apresentacoes'] ?? 'NÃO DEFINIDO', true));
            error_log("DEBUG: Tipo do valor: " . gettype($dados['quantidade_apresentacoes'] ?? null));
            
            if (!isset($dados['quantidade_apresentacoes'])) {
                throw new Exception("Campo quantidade_apresentacoes não foi enviado");
            }
            
            $quantidade = trim($dados['quantidade_apresentacoes']);
            error_log("DEBUG: Valor após trim: '$quantidade'");
            
            if ($quantidade === '' || $quantidade === null) {
                throw new Exception("Quantidade de apresentações não pode estar vazia");
            }
            
            if (!is_numeric($quantidade) || $quantidade <= 0) {
                throw new Exception("Quantidade de apresentações deve ser um número positivo. Valor recebido: '$quantidade'");
            }
            
            $dados['quantidade_apresentacoes'] = (int)$quantidade;
            
            if ($dados['quantidade_apresentacoes'] > 10) {
                throw new Exception("Quantidade de apresentações não pode exceder 10");
            }
            // Para apresentação de eventos, usar as horas já calculadas pelo frontend
            // O frontend já faz o cálculo correto baseado no currículo (BCC17: 10h, BCC23: 5h por apresentação)
            // e limita ao máximo permitido pela atividade
            if (empty($dados['horas_realizadas']) || !is_numeric($dados['horas_realizadas']) || $dados['horas_realizadas'] <= 0) {
                throw new Exception("Horas realizadas deve ser um número positivo");
            }
        } else {
            if (empty($dados['horas_realizadas']) || !is_numeric($dados['horas_realizadas']) || $dados['horas_realizadas'] <= 0) {
                throw new Exception("Horas realizadas deve ser um número positivo");
            }
        }

        // Validar local_instituicao apenas para tipos que requerem
        $tiposQueRequeremLocal = ['apresentacao_evento', 'membro_evento'];
        if (in_array($dados['tipo_atividade'], $tiposQueRequeremLocal)) {
            if (empty($dados['local_instituicao']) || strlen(trim($dados['local_instituicao'])) < 3) {
                throw new Exception("Local/Instituição deve ter pelo menos 3 caracteres");
            }
        }

        // Validações específicas por tipo de atividade
        switch ($dados['tipo_atividade']) {
            case 'apresentacao_evento':
                if (empty($dados['nome_evento'])) {
                    throw new Exception("Nome do evento é obrigatório para apresentação em evento");
                }
                // Tema da apresentação é opcional
                break;

            case 'membro_evento':
                if (empty($dados['nome_evento'])) {
                    throw new Exception("Nome do evento é obrigatório");
                }
                break;

            case 'iniciacao_cientifica':
                if (empty($dados['nome_projeto'])) {
                    throw new Exception("Nome do projeto é obrigatório para iniciação científica");
                }
                if (empty($dados['data_inicio']) || empty($dados['data_fim'])) {
                    throw new Exception("Datas de início e fim são obrigatórias para iniciação científica");
                }
                break;

            case 'publicacao_artigo':
                if (empty($dados['nome_artigo'])) {
                    throw new Exception("Nome do artigo é obrigatório para publicação");
                }
                break;
        }
    }

    /**
     * Processar upload de arquivo
     */
    private function processarUploadArquivo($arquivo) {
        $diretorioUpload = __DIR__ . '/../../../uploads/atividades_pesquisa/';
        
        // Criar diretório se não existir
        if (!is_dir($diretorioUpload)) {
            mkdir($diretorioUpload, 0755, true);
        }

        // Validar tipo de arquivo
        $tiposPermitidos = ['pdf', 'jpg', 'jpeg', 'png'];
        $tiposMime = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extensao, $tiposPermitidos) && !in_array($arquivo['type'], $tiposMime)) {
            throw new Exception("Tipo de arquivo não permitido. Use PDF, JPG ou PNG");
        }

        // Validar tamanho (máximo 5MB)
        if ($arquivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("Arquivo muito grande. Máximo 5MB");
        }

        // Gerar nome único
        $nomeArquivo = uniqid() . '_' . time() . '.' . $extensao;
        $caminhoCompleto = $diretorioUpload . $nomeArquivo;

        // Mover arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new Exception("Erro ao fazer upload do arquivo");
        }

        return 'uploads/atividades_pesquisa/' . $nomeArquivo;
    }
    
    /**
     * NOVA FUNCIONALIDADE: Validar limite de 80h para alunos 2017-2022
     */
    private function validarLimite80hExtracurriculares($aluno_id, $horas_solicitadas) {
        try {
            // Buscar matrícula do aluno
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT matricula FROM Aluno WHERE usuario_id = ?");
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $aluno_data = $result->fetch_assoc();
            
            if (!$aluno_data) {
                throw new Exception("Dados do aluno não encontrados");
            }
            
            $matricula = $aluno_data['matricula'];
            $anoMatricula = (int) substr($matricula, 0, 4);
            
            // Verificar se é aluno elegível para limite de 80h (matrículas 2017-2022)
            if ($anoMatricula >= 2017 && $anoMatricula <= 2022) {
                error_log("DEBUG LIMITE 80H PESQUISA - Aluno elegível detectado. Matrícula: {$matricula}, Ano: {$anoMatricula}");
                
                // Calcular total de horas já utilizadas em TODAS as atividades extracurriculares
                $horasAcumuladasTotal = $this->calcularHorasExtracurricularesTotais($aluno_id);
                error_log("DEBUG LIMITE 80H PESQUISA - Horas acumuladas atuais: {$horasAcumuladasTotal}h");
                
                // Verificar se já atingiu o limite de 80h
                if ($horasAcumuladasTotal >= 80) {
                    throw new Exception("🚫 Limite máximo de 80 horas atingido para atividades extracurriculares. Você já possui {$horasAcumuladasTotal}h cadastradas. Não é possível cadastrar novas atividades.");
                }
                
                // Verificar se a nova atividade excederia o limite de 80h
                $totalComNovaAtividade = $horasAcumuladasTotal + $horas_solicitadas;
                if ($totalComNovaAtividade > 80) {
                    $horasRestantes = 80 - $horasAcumuladasTotal;
                    throw new Exception("⚠️ Limite de 80 horas seria excedido. Você possui {$horasAcumuladasTotal}h cadastradas e pode adicionar no máximo {$horasRestantes}h adicionais. Reduza as horas desta atividade para prosseguir.");
                }
                
                error_log("DEBUG LIMITE 80H PESQUISA - Validação aprovada. Total após cadastro: {$totalComNovaAtividade}h de 80h");
            }
            
        } catch (Exception $e) {
            error_log("Erro na validação de limite 80h: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * NOVA FUNCIONALIDADE: Calcular total de horas extracurriculares para limite de 80h
     */
    private function calcularHorasExtracurricularesTotais($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            $horasTotal = 0;
            
            // Definir todas as tabelas de atividades extracurriculares e seus campos de horas
            $tabelas = [
                'atividadecomplementaracc' => 'horas_realizadas',
                'AtividadeComplementarEnsino' => 'carga_horaria', 
                'atividadecomplementarestagio' => 'horas',
                'atividadecomplementarpesquisa' => 'horas_realizadas',
                'AtividadeSocialComunitaria' => 'horas_realizadas'
            ];
            
            foreach ($tabelas as $tabela => $campoHoras) {
                $sql = "SELECT SUM({$campoHoras}) as total_horas 
                        FROM {$tabela} 
                        WHERE aluno_id = ? 
                        AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
                        
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $aluno_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                $horasTabela = $row && $row['total_horas'] ? (int) $row['total_horas'] : 0;
                error_log("DEBUG CALC 80H PESQUISA - Tabela {$tabela}: {$horasTabela}h");
                
                $horasTotal += $horasTabela;
            }
            
            error_log("DEBUG CALC 80H PESQUISA - Total calculado para aluno {$aluno_id}: {$horasTotal}h");
            return $horasTotal;
            
        } catch (Exception $e) {
            error_log("Erro ao calcular horas extracurriculares totais: " . $e->getMessage());
            return 0;
        }
    }
}