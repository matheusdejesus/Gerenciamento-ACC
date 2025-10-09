<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;


class AtividadeComplementar {
    public static function create($dados) {
        try {
            // Validar dados obrigatórios
            $camposObrigatorios = ['aluno_id', 'titulo', 'data_inicio', 'data_fim', 'carga_horaria_solicitada'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    throw new Exception("Campo obrigatório não informado: $campo");
                }
            }
            /*
            if (empty($dados['orientador_id']) && empty($dados['avaliador_id'])) {
                throw new Exception("É obrigatório informar um orientador ou um avaliador (coordenador)");
            }
            */

            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();

            // Montar SQL base
            $campos = "aluno_id, titulo, descricao, data_inicio, data_fim, carga_horaria_solicitada, declaracao_caminho";
            $placeholders = "?, ?, ?, ?, ?, ?, ?";
            $tipos = "issssss";
            $valores = [
                $dados['aluno_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['data_inicio'],
                $dados['data_fim'],
                $dados['carga_horaria_solicitada'],
                $dados['declaracao_caminho']
            ];

            // Adicionar atividade_disponivel_id se fornecido
            if (!empty($dados['atividade_disponivel_id'])) {
                $campos .= ", atividade_disponivel_id";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['atividade_disponivel_id'];
            }

            // Adicionar categoria_id se fornecido
            if (!empty($dados['categoria_id'])) {
                $campos .= ", categoria_id";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['categoria_id'];
            }

            if (!empty($dados['orientador_id'])) {
                $campos .= ", orientador_id";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['orientador_id'];
            } else {
                $campos .= ", avaliador_id";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['avaliador_id'];
            }

            $sql = "INSERT INTO atividadecomplementaracc ($campos) VALUES ($placeholders)";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            $stmt->bind_param($tipos, ...$valores);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            $atividade_id = $db->insert_id;

            $db->commit();
            $db->autocommit(true);

            error_log("Atividade complementar criada: ID={$atividade_id}, Aluno={$dados['aluno_id']}");

            return $atividade_id;

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro ao criar atividade complementar: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function buscarPorAluno($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ac.id,
                        ac.titulo,
                        ac.descricao,
                        ac.data_inicio,
                        ac.data_fim,
                        ac.carga_horaria_solicitada,
                        ac.carga_horaria_aprovada,
                        ac.status,
                        ac.data_submissao,
                        ac.data_avaliacao,
                        ac.observacoes_Analise,
                        ac.declaracao_caminho,
                        ac.certificado_caminho,
                        CASE WHEN ac.declaracao_caminho IS NOT NULL AND ac.declaracao_caminho != '' THEN 1 ELSE 0 END as tem_declaracao,
                        ca.descricao AS categoria_nome,
                        u.nome AS orientador_nome
                    FROM atividadecomplementaracc ac
                    INNER JOIN CategoriaAtividade ca ON ac.categoria_id = ca.id
                    LEFT JOIN Usuario u ON ac.orientador_id = u.id
                    WHERE ac.aluno_id = ?
                    ORDER BY ac.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'carga_horaria_aprovada' => $row['carga_horaria_aprovada'] ? (int)$row['carga_horaria_aprovada'] : null,
                    'status' => $row['status'],
                    'data_submissao' => $row['data_submissao'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'observacoes_Analise' => $row['observacoes_Analise'],
                    'categoria_nome' => $row['categoria_nome'],
                    'orientador_nome' => $row['orientador_nome'],
                    'tem_declaracao' => (bool)$row['tem_declaracao'],
                    'declaracao_caminho' => $row['declaracao_caminho'],
                    'certificado_caminho' => $row['certificado_caminho']
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarPorAluno: " . $e->getMessage());
            throw $e;
        }
    }

    public static function listarOrientadores() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT u.id, u.nome, u.email, o.siape
                    FROM Usuario u
                    INNER JOIN Orientador o ON u.id = o.usuario_id
                    WHERE u.tipo = 'orientador'
                    ORDER BY u.nome";
            
            $result = $db->query($sql);
            $orientadores = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $orientadores[] = [
                        'id' => (int)$row['id'],
                        'nome' => $row['nome'],
                        'email' => $row['email'],
                        'siape' => $row['siape'] ?? null
                    ];
                }
            }
            
            return $orientadores;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::listarOrientadores: " . $e->getMessage());
            throw $e;
        }
    }

    public static function listarCoordenadores() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT u.id, u.nome, u.email, c.siape, cur.nome as curso_nome
                    FROM Usuario u
                    INNER JOIN Coordenador c ON u.id = c.usuario_id
                    INNER JOIN Curso cur ON c.curso_id = cur.id
                    WHERE u.tipo = 'coordenador'
                    ORDER BY u.nome";
            
            $result = $db->query($sql);
            $coordenadores = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $coordenadores[] = [
                        'id' => (int)$row['id'],
                        'usuario_id' => (int)$row['id'],
                        'nome' => $row['nome'],
                        'email' => $row['email'],
                        'siape' => $row['siape'] ?? null,
                        'curso_nome' => $row['curso_nome']
                    ];
                }
            }
            
            return $coordenadores;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::listarCoordenadores: " . $e->getMessage());
            throw $e;
        }
    }

    public static function buscarPendentesOrientador($orientador_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ac.id,
                        ac.titulo,
                        ac.descricao,
                        ac.data_inicio,
                        ac.data_fim,
                        ac.carga_horaria_solicitada,
                        ac.data_submissao,
                        ac.declaracao_caminho,
                        CASE WHEN ac.declaracao_caminho IS NOT NULL AND ac.declaracao_caminho != '' THEN 1 ELSE 0 END as tem_declaracao,
                        u.nome AS aluno_nome,
                        a.matricula AS aluno_matricula,
                        u.email AS aluno_email,
                        c.nome AS curso_nome
                    FROM atividadecomplementaracc ac
                    INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                    INNER JOIN Usuario u ON a.usuario_id = u.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    WHERE ac.orientador_id = ? AND ac.status = 'Aguardando avaliação'
                    ORDER BY ac.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $orientador_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'data_submissao' => $row['data_submissao'],
                    'nome_aluno' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'aluno_email' => $row['aluno_email'],
                    'curso_nome' => $row['curso_nome'],
                    'tem_declaracao' => (bool)$row['tem_declaracao'],
                    'declaracao_caminho' => $row['declaracao_caminho'],
                    'certificado_caminho' => $row['certificado_caminho'] ?? null
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarPendentesOrientador: " . $e->getMessage());
            throw $e;
        }
    }

    public static function buscarAvaliadasOrientador($orientador_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ac.id,
                        ac.titulo,
                        ac.descricao,
                        ac.data_inicio,
                        ac.data_fim,
                        ac.carga_horaria_solicitada,
                        ac.carga_horaria_aprovada,
                        ac.status,
                        ac.data_submissao,
                        ac.data_avaliacao,
                        ac.observacoes_Analise,
                        ac.declaracao_caminho,
                        ac.certificado_caminho,
                        CASE WHEN ac.declaracao_caminho IS NOT NULL AND ac.declaracao_caminho != '' THEN 1 ELSE 0 END as tem_declaracao,
                        u.nome AS aluno_nome,
                        a.matricula AS aluno_matricula,
                        u.email AS aluno_email,
                        c.nome AS curso_nome
                    FROM atividadecomplementaracc ac
                    INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                    INNER JOIN Usuario u ON a.usuario_id = u.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    WHERE ac.orientador_id = ? AND ac.status IN ('Aprovada', 'Rejeitada')
                    ORDER BY ac.data_avaliacao DESC";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $orientador_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'carga_horaria_aprovada' => $row['carga_horaria_aprovada'] ? (int)$row['carga_horaria_aprovada'] : null,
                    'status' => $row['status'],
                    'data_submissao' => $row['data_submissao'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'observacoes_Analise' => $row['observacoes_Analise'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'aluno_email' => $row['aluno_email'],
                    'curso_nome' => $row['curso_nome'],
                    'tem_declaracao' => (bool)$row['tem_declaracao'],
                    'declaracao_caminho' => $row['declaracao_caminho'],
                    'certificado_caminho' => $row['certificado_caminho'] ?? null
                ];
            }
            
            return $atividades;
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarAvaliadasOrientador: " . $e->getMessage());
            throw $e;
        }
    }

    public static function avaliarAtividade($atividade_id, $orientador_id, $carga_horaria_aprovada, $observacoes_analise, $status, $certificado_caminho = null) {
        try {
            $db = Database::getInstance()->getConnection();

            $sql = "UPDATE atividadecomplementaracc 
                    SET status = ?, 
                        carga_horaria_aprovada = ?, 
                        observacoes_analise = ?, 
                        certificado_caminho = ?, 
                        data_avaliacao = NOW()
                    WHERE id = ? AND orientador_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param(
                "sissii",
                $status,
                $carga_horaria_aprovada,
                $observacoes_analise,
                $certificado_caminho,
                $atividade_id,
                $orientador_id
            );
            $sucesso = $stmt->execute();
            return $sucesso;
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::avaliarAtividade: " . $e->getMessage());
            throw $e;
        }
    }

    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT * FROM atividadecomplementaracc WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }

    public static function buscarAtividadePorIdETipo($id, $tipo = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            error_log("=== buscarAtividadePorIdETipo ===");
            error_log("ID: " . $id);
            error_log("Tipo: " . ($tipo ?? 'null'));
            
            // Se o tipo não for especificado, tentar encontrar a atividade em todas as tabelas
            if (!$tipo) {
                // Primeiro tentar na tabela principal
                error_log("Tentando buscar na tabela atividadecomplementaracc...");
                $atividade = self::buscarPorId($id);
                if ($atividade) {
                    $atividade['tipo'] = 'acc';
                    error_log("Atividade encontrada na tabela atividadecomplementaracc");
                    return $atividade;
                }
                error_log("Atividade não encontrada na tabela atividadecomplementaracc");
                
                // Tentar na tabela de ensino
                error_log("Tentando buscar na tabela AtividadeComplementarEnsino...");
                $sql = "SELECT *, 'ensino' as tipo FROM AtividadeComplementarEnsino WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $atividade = $result->fetch_assoc();
                if ($atividade) {
                    error_log("Atividade encontrada na tabela AtividadeComplementarEnsino");
                    return $atividade;
                }
                error_log("Atividade não encontrada na tabela AtividadeComplementarEnsino");
                
                // Tentar na tabela de estágio
                error_log("Tentando buscar na tabela atividadecomplementarestagio...");
                $sql = "SELECT *, 'estagio' as tipo FROM atividadecomplementarestagio WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $atividade = $result->fetch_assoc();
                if ($atividade) {
                    error_log("Atividade encontrada na tabela atividadecomplementarestagio");
                    return $atividade;
                }
                error_log("Atividade não encontrada na tabela atividadecomplementarestagio");
                
                // Tentar na tabela de pesquisa
                error_log("Tentando buscar na tabela atividadecomplementarpesquisa...");
                $sql = "SELECT *, 'pesquisa' as tipo FROM atividadecomplementarpesquisa WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $atividade = $result->fetch_assoc();
                if ($atividade) {
                    error_log("Atividade encontrada na tabela atividadecomplementarpesquisa");
                    return $atividade;
                }
                error_log("Atividade não encontrada na tabela atividadecomplementarpesquisa");
                
                error_log("Atividade com ID " . $id . " não encontrada em nenhuma tabela");
                return null;
            }
            
            // Se o tipo for especificado, buscar na tabela correspondente
            switch ($tipo) {
                case 'acc':
                    return self::buscarPorId($id);
                    
                case 'ensino':
                    $sql = "SELECT *, 'ensino' as tipo FROM AtividadeComplementarEnsino WHERE id = ?";
                    break;
                    
                case 'estagio':
                    $sql = "SELECT *, 'estagio' as tipo FROM atividadecomplementarestagio WHERE id = ?";
                    break;
                    
                case 'pesquisa':
                    $sql = "SELECT *, 'pesquisa' as tipo FROM atividadecomplementarpesquisa WHERE id = ?";
                    break;
                    
                default:
                    error_log("Tipo de atividade inválido: " . $tipo);
                    return null;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarAtividadePorIdETipo: " . $e->getMessage());
            throw $e;
        }
    }

    public static function aprovarCertificado($atividade_id, $coordenador_id, $observacoes = '') {
        try {
            $db = Database::getInstance()->getConnection();

            // Primeiro, verificar se a atividade existe e se o coordenador tem permissão
            $sqlVerificar = "SELECT ac.id, ac.aluno_id, a.curso_id, c.curso_id as coordenador_curso_id
                            FROM atividadecomplementaracc ac
                            INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                            INNER JOIN Coordenador c ON c.usuario_id = ?
                            WHERE ac.id = ? AND a.curso_id = c.curso_id";
            
            $stmtVerificar = $db->prepare($sqlVerificar);
            $stmtVerificar->bind_param("ii", $coordenador_id, $atividade_id);
            $stmtVerificar->execute();
            $resultVerificar = $stmtVerificar->get_result();
            
            if ($resultVerificar->num_rows === 0) {
                error_log("Atividade não encontrada ou coordenador sem permissão. Atividade ID: " . $atividade_id . ", Coordenador ID: " . $coordenador_id);
                return false;
            }

            // Adiciona uma observação sobre a aprovação
            $observacao_aprovacao = "\n[CERTIFICADO APROVADO PELO COORDENADOR EM " . date('Y-m-d H:i:s') . "]";
            if ($observacoes) {
                $observacao_aprovacao .= "\nObservações do coordenador: " . $observacoes;
            }
            
            // Atualizar a atividade com status aprovado
            $sql = "UPDATE atividadecomplementaracc 
                    SET observacoes_Analise = CONCAT(
                        COALESCE(observacoes_Analise, ''), 
                        ?
                    ),
                    status = 'aprovado',
                    data_avaliacao = NOW(),
                    avaliador_id = ?
                    WHERE id = ?";
                    
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sii", $observacao_aprovacao, $coordenador_id, $atividade_id);
            
            $sucesso = $stmt->execute();
            
            if ($sucesso && $stmt->affected_rows > 0) {
                error_log("Certificado aprovado com sucesso para atividade ID: " . $atividade_id);
            } else {
                error_log("Erro ao aprovar certificado ou nenhuma linha afetada: " . $stmt->error);
                return false;
            }
            
            return $sucesso;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::aprovarCertificado: " . $e->getMessage());
            return false;
        }
    }

    public static function rejeitarCertificado($atividade_id, $coordenador_id, $observacoes = '') {
        try {
            $db = Database::getInstance()->getConnection();

            // Primeiro, verificar se a atividade existe e se o coordenador tem permissão
            $sqlVerificar = "SELECT ac.id, ac.aluno_id, a.curso_id, c.curso_id as coordenador_curso_id
                            FROM atividadecomplementaracc ac
                            INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                            INNER JOIN Coordenador c ON c.usuario_id = ?
                            WHERE ac.id = ? AND a.curso_id = c.curso_id";
            
            $stmtVerificar = $db->prepare($sqlVerificar);
            $stmtVerificar->bind_param("ii", $coordenador_id, $atividade_id);
            $stmtVerificar->execute();
            $resultVerificar = $stmtVerificar->get_result();
            
            if ($resultVerificar->num_rows === 0) {
                error_log("Atividade não encontrada ou coordenador sem permissão. Atividade ID: " . $atividade_id . ", Coordenador ID: " . $coordenador_id);
                return false;
            }

            // Adiciona uma observação sobre a rejeição
            $observacao_rejeicao = "\n[CERTIFICADO REJEITADO PELO COORDENADOR EM " . date('Y-m-d H:i:s') . "]";
            if ($observacoes) {
                $observacao_rejeicao .= "\nMotivo da rejeição: " . $observacoes;
            }
            
            // Atualizar a atividade com status rejeitado
            $sql = "UPDATE atividadecomplementaracc 
                    SET observacoes_Analise = CONCAT(
                        COALESCE(observacoes_Analise, ''), 
                        ?
                    ),
                    status = 'rejeitado',
                    data_avaliacao = NOW(),
                    avaliador_id = ?
                    WHERE id = ?";
                    
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sii", $observacao_rejeicao, $coordenador_id, $atividade_id);
            
            $sucesso = $stmt->execute();
            
            if ($sucesso && $stmt->affected_rows > 0) {
                error_log("Certificado rejeitado com sucesso para atividade ID: " . $atividade_id);
            } else {
                error_log("Erro ao rejeitar certificado ou nenhuma linha afetada: " . $stmt->error);
                return false;
            }
            
            return $sucesso;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::rejeitarCertificado: " . $e->getMessage());
            return false;
        }
    }

    public static function atualizarCertificado($atividade_id, $certificado_caminho) {
        try {
            $db = Database::getInstance()->getConnection();

            // Verificar qual coluna usar para certificado
            $sql = "SHOW COLUMNS FROM atividadecomplementaracc LIKE 'certificado%'";
            $result = $db->query($sql);
            
            $coluna_certificado = 'certificado';
            while ($row = $result->fetch_assoc()) {
                if ($row['Field'] === 'certificado_caminho') {
                    $coluna_certificado = 'certificado_caminho';
                    break;
                }
            }

            $sql = "UPDATE atividadecomplementaracc 
                    SET {$coluna_certificado} = ? 
                    WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("si", $certificado_caminho, $atividade_id);
            
            $sucesso = $stmt->execute();
            
            if ($sucesso) {
                error_log("Certificado atualizado para atividade ID: " . $atividade_id . " na coluna: " . $coluna_certificado);
            } else {
                error_log("Erro ao atualizar certificado: " . $stmt->error);
            }
            
            return $sucesso;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::atualizarCertificado: " . $e->getMessage());
            return false;
        }
    }

    // Método para verificar se certificado foi aprovado
    public static function certificadoAprovado($atividade_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT avaliador_id FROM atividadecomplementaracc WHERE id = ? AND avaliador_id IS NOT NULL");
            $stmt->bind_param("i", $atividade_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
            
        } catch (Exception $e) {
            error_log("Erro em certificadoAprovado: " . $e->getMessage());
            return false;
        }
    }

    public static function buscarCertificadosPendentesPorCoordenador($coordenador_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            error_log("=== INICIO buscarCertificadosPendentesPorCoordenador ===");
            error_log("Coordenador ID: " . $coordenador_id);

            // Buscar o curso_id do coordenador
            $sqlCurso = "SELECT curso_id FROM Coordenador WHERE usuario_id = ?";
            $stmtCurso = $db->prepare($sqlCurso);
            $stmtCurso->bind_param("i", $coordenador_id);
            $stmtCurso->execute();
            $resultCurso = $stmtCurso->get_result();

            if ($resultCurso->num_rows === 0) {
                error_log("Nenhum coordenador encontrado com ID: " . $coordenador_id);
                return [];
            }

            $coordenador = $resultCurso->fetch_assoc();
            $curso_id = $coordenador['curso_id'];
            error_log("Curso ID do coordenador: " . $curso_id);

            $atividades = [];

            // 1. Buscar atividades complementares (ACC) pendentes
            // Usar lógica dinâmica baseada na matrícula do aluno
            
            $sqlACC = "
                SELECT 
                    ac.id,
                    COALESCE(ac.curso_evento_nome, 'Atividade Complementar') as titulo,
                    CASE 
                        WHEN SUBSTR(a.matricula, 1, 4) BETWEEN '2017' AND '2022' THEN ad17.titulo
                        WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN ad23.titulo
                        ELSE COALESCE(ad23.titulo, ad17.titulo)
                    END as atividade_nome,
                    ac.horas_realizadas as carga_horaria_aprovada,
                    ac.status,
                    ac.data_submissao as data_envio,
                    ac.declaracao_caminho as certificado_caminho,
                    ac.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    ac.observacoes_avaliacao as observacoes_Analise,
                    ac.atividade_disponivel_id,
                    ac.categoria_id,
                    CASE 
                        WHEN SUBSTR(a.matricula, 1, 4) BETWEEN '2017' AND '2022' THEN cat17.descricao
                        WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN cat23.descricao
                        ELSE COALESCE(cat23.descricao, cat17.descricao)
                    END as categoria_nome,
                    'acc' as tipo,
                    ac.data_submissao
                FROM atividadecomplementaracc ac
                INNER JOIN aluno a ON ac.aluno_id = a.usuario_id
                INNER JOIN usuario u ON a.usuario_id = u.id
                INNER JOIN curso c ON a.curso_id = c.id
                LEFT JOIN categoriaatividadebcc17 cat17 ON ac.categoria_id = cat17.id
                LEFT JOIN categoriaatividadebcc23 cat23 ON ac.categoria_id = cat23.id
                LEFT JOIN atividadesdisponiveisbcc17 ad17 ON ac.atividade_disponivel_id = ad17.id
                LEFT JOIN atividadesdisponiveisbcc23 ad23 ON ac.atividade_disponivel_id = ad23.id
                WHERE c.id = ?
                  AND ac.status = 'Aguardando avaliação'
                  AND ac.declaracao_caminho IS NOT NULL
                  AND ac.declaracao_caminho != ''
            ";

            $stmt = $db->prepare($sqlACC);
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $countACC = 0;
            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
                $countACC++;
            }
            error_log("Atividades ACC encontradas: " . $countACC);

            // 2. Buscar atividades de Ensino pendentes
            $sqlEnsino = "
                SELECT 
                    ae.id,
                    COALESCE(ae.nome_disciplina, ae.nome_disciplina_laboratorio, 'Disciplinas em áreas correlatas cursadas em outras IES') as titulo,
                    CASE 
                        WHEN SUBSTR(a.matricula, 1, 4) BETWEEN '2017' AND '2022' THEN ad17.titulo
                        WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN ad23.titulo
                        ELSE COALESCE(ad23.titulo, ad17.titulo)
                    END as atividade_nome,
                    ae.carga_horaria as carga_horaria_aprovada,
                    ae.status,
                    ae.data_submissao as data_envio,
                    ae.declaracao_caminho as certificado_caminho,
                    ae.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    ae.observacoes_avaliacao as observacoes_Analise,
                    ae.categoria_id,
                    CASE 
                        WHEN SUBSTR(a.matricula, 1, 4) BETWEEN '2017' AND '2022' THEN ca17.descricao
                        WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN ca23.descricao
                        ELSE COALESCE(ca23.descricao, ca17.descricao)
                    END as categoria_nome,
                    'ensino' as tipo,
                    ae.data_submissao
                FROM atividadecomplementarensino ae
                INNER JOIN aluno a ON ae.aluno_id = a.usuario_id
                INNER JOIN usuario u ON a.usuario_id = u.id
                INNER JOIN curso c ON a.curso_id = c.id
                LEFT JOIN categoriaatividadebcc17 ca17 ON ae.categoria_id = ca17.id
                LEFT JOIN categoriaatividadebcc23 ca23 ON ae.categoria_id = ca23.id
                LEFT JOIN atividadesdisponiveisbcc17 ad17 ON ae.atividade_disponivel_id = ad17.id
                LEFT JOIN atividadesdisponiveisbcc23 ad23 ON ae.atividade_disponivel_id = ad23.id
                WHERE c.id = ?
                  AND ae.status = 'Aguardando avaliação'
            ";

            $stmt = $db->prepare($sqlEnsino);
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
            }

            // 3. Buscar atividades de Estágio pendentes
            $sqlEstagio = "
                SELECT 
                    aes.id,
                    CONCAT(aes.empresa, ' - ', aes.area) as titulo,
                    'Estágio curricular não obrigatório' as atividade_nome,
                    aes.horas as carga_horaria_aprovada,
                    aes.status,
                    aes.data_submissao as data_envio,
                    aes.declaracao_caminho as certificado_caminho,
                    aes.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    aes.observacoes_avaliacao as observacoes_Analise,
                    NULL as atividade_disponivel_id,
                    4 as categoria_id,
                    'Estágio' as categoria_nome,
                    'estagio' as tipo,
                    aes.data_submissao
                FROM atividadecomplementarestagio aes
                INNER JOIN aluno a ON aes.aluno_id = a.usuario_id
                INNER JOIN usuario u ON a.usuario_id = u.id
                INNER JOIN curso c ON a.curso_id = c.id
                WHERE c.id = ?
                  AND aes.status = 'Aguardando avaliação'
                  AND aes.declaracao_caminho IS NOT NULL
                  AND aes.declaracao_caminho != ''
            ";

            $stmt = $db->prepare($sqlEstagio);
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
            }

            // 4. Buscar atividades de Pesquisa pendentes
            $sqlPesquisa = "
                SELECT 
                    ap.id,
                    CASE 
                        WHEN ap.nome_evento IS NOT NULL AND ap.nome_evento != '' THEN ap.nome_evento
                        WHEN ap.nome_projeto IS NOT NULL AND ap.nome_projeto != '' THEN ap.nome_projeto
                        WHEN ap.nome_artigo IS NOT NULL AND ap.nome_artigo != '' THEN ap.nome_artigo
                        ELSE 'Atividade de Pesquisa'
                    END as titulo,
                    CASE 
                        WHEN SUBSTR(a.matricula, 1, 4) BETWEEN '2017' AND '2022' THEN ad17.titulo
                        WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN ad23.titulo
                        ELSE COALESCE(ad23.titulo, ad17.titulo)
                    END as atividade_nome,
                    ap.horas_realizadas as carga_horaria_aprovada,
                    ap.status,
                    ap.data_submissao as data_envio,
                    ap.declaracao_caminho as certificado_caminho,
                    ap.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    ap.observacoes_avaliacao as observacoes_Analise,
                    ap.atividade_disponivel_id,
                    2 as categoria_id,
                    'Pesquisa' as categoria_nome,
                    'pesquisa' as tipo,
                    ap.data_submissao
                FROM atividadecomplementarpesquisa ap
                INNER JOIN aluno a ON ap.aluno_id = a.usuario_id
                INNER JOIN usuario u ON a.usuario_id = u.id
                INNER JOIN curso c ON a.curso_id = c.id
                LEFT JOIN atividadesdisponiveisbcc17 ad17 ON ap.atividade_disponivel_id = ad17.id
                LEFT JOIN atividadesdisponiveisbcc23 ad23 ON ap.atividade_disponivel_id = ad23.id
                WHERE c.id = ?
                  AND ap.status = 'Aguardando avaliação'
                  AND ap.declaracao_caminho IS NOT NULL
                  AND ap.declaracao_caminho != ''
            ";

            $stmt = $db->prepare($sqlPesquisa);
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
            }

            // Remover busca por certificados avulsos (funcionalidade removida)

            // Ordenar por data de submissão (mais recente primeiro)
            usort($atividades, function($a, $b) {
                $dataA = $a['data_submissao'] ?? $a['data_envio'];
                $dataB = $b['data_submissao'] ?? $b['data_envio'];
                return strtotime($dataB) - strtotime($dataA);
            });
            
            error_log("Total de atividades pendentes encontradas: " . count($atividades));
            error_log("=== FIM buscarCertificadosPendentesPorCoordenador ===");

            return $atividades;
        } catch (Exception $e) {
            error_log("Erro em buscarCertificadosPendentesPorCoordenador: " . $e->getMessage());
            return [];
        }
    }

    public static function buscarCertificadosProcessadosPorCoordenador($coordenador_id) {
        try {
            $db = Database::getInstance()->getConnection();
    
            // Buscar o curso_id do coordenador
            $sqlCurso = "SELECT curso_id FROM Coordenador WHERE usuario_id = ?";
            $stmtCurso = $db->prepare($sqlCurso);
            $stmtCurso->bind_param("i", $coordenador_id);
            $stmtCurso->execute();
            $resultCurso = $stmtCurso->get_result();
    
            if ($resultCurso->num_rows === 0) {
                error_log("Coordenador não encontrado: " . $coordenador_id);
                return [];
            }
    
            $coordenador = $resultCurso->fetch_assoc();
            $curso_id = $coordenador['curso_id'];
            error_log("Buscando certificados para coordenador $coordenador_id, curso $curso_id");
    
            $atividades = [];

            // 1. Buscar atividades complementares processadas (aprovadas ou rejeitadas)
            $sqlACC = "SELECT 
                    ac.id,
                    COALESCE(ac.curso_evento_nome, 'Atividade Complementar') as titulo,
                    ac.horas_realizadas as horas_contabilizadas,
                    ac.status,
                    ac.data_submissao as data_envio,
                    ac.data_avaliacao as data_aprovacao,
                    ac.declaracao_caminho as certificado_caminho,
                    ac.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    CASE 
                        WHEN SUBSTR(a.matricula, 1, 4) BETWEEN '2017' AND '2022' THEN ad17.titulo
                        WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN ad23.titulo
                        ELSE COALESCE(ad23.titulo, ad17.titulo)
                    END as atividade_nome,
                    ac.observacoes_avaliacao as observacoes_Analise,
                    1 as categoria_id,
                    'Complementar' as categoria_nome,
                    'acc' as tipo,
                    ac.data_avaliacao
                FROM atividadecomplementaracc ac
                INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                INNER JOIN Usuario u ON a.usuario_id = u.id
                INNER JOIN Curso c ON a.curso_id = c.id
                LEFT JOIN atividadesdisponiveisbcc17 ad17 ON ac.atividade_disponivel_id = ad17.id
                LEFT JOIN atividadesdisponiveisbcc23 ad23 ON ac.atividade_disponivel_id = ad23.id
                WHERE c.id = ?
                  AND (ac.status = 'aprovado' OR ac.status = 'rejeitado')
                  AND ac.declaracao_caminho IS NOT NULL
                  AND ac.declaracao_caminho != ''
                  AND ac.data_avaliacao IS NOT NULL";
            
            $stmt = $db->prepare($sqlACC);
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $count_acc = $result->num_rows;
            error_log("Query ACC encontrou $count_acc registros para curso $curso_id");
        
            while ($row = $result->fetch_assoc()) {
                error_log("ACC encontrado: ID " . $row['id'] . ", Status: " . $row['status'] . ", Data avaliação: " . $row['data_avaliacao']);
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'curso_nome' => $row['curso_nome'],
                    'atividade_nome' => $row['atividade_nome'],
                    'titulo' => $row['titulo'],
                    'horas_contabilizadas' => (int)$row['horas_contabilizadas'],
                    'status' => ucfirst($row['status']),
                    'data_envio' => $row['data_envio'],
                    'data_aprovacao' => $row['data_aprovacao'],
                    'certificado_caminho' => $row['certificado_caminho'],
                    'tipo' => $row['tipo'],
                    'categoria_nome' => $row['categoria_nome']
                ];
            }

            // 2. Buscar atividades de ensino processadas
            $sqlEnsino = "SELECT 
                    ae.id,
                    COALESCE(ae.nome_disciplina, ae.nome_disciplina_laboratorio, 'Disciplinas em áreas correlatas cursadas em outras IES') as titulo,
                    ae.carga_horaria as horas_contabilizadas,
                    ae.status,
                    ae.data_submissao as data_envio,
                    ae.data_avaliacao as data_aprovacao,
                    ae.declaracao_caminho as certificado_caminho,
                    ae.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    CASE 
                        WHEN SUBSTR(a.matricula, 1, 4) BETWEEN '2017' AND '2022' THEN ad17.titulo
                        WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN ad23.titulo
                        ELSE COALESCE(ad23.titulo, ad17.titulo)
                    END as atividade_nome,
                    ae.observacoes_avaliacao as observacoes_Analise,
                    3 as categoria_id,
                    'Ensino' as categoria_nome,
                    'ensino' as tipo,
                    ae.data_avaliacao
                FROM atividadecomplementarensino ae
                INNER JOIN Aluno a ON ae.aluno_id = a.usuario_id
                INNER JOIN Usuario u ON a.usuario_id = u.id
                INNER JOIN Curso c ON a.curso_id = c.id
                LEFT JOIN atividadesdisponiveisbcc17 ad17 ON ae.atividade_disponivel_id = ad17.id
                LEFT JOIN atividadesdisponiveisbcc23 ad23 ON ae.atividade_disponivel_id = ad23.id
                WHERE c.id = ?
                  AND (ae.status = 'aprovado' OR ae.status = 'rejeitado')
                  AND ae.declaracao_caminho IS NOT NULL
                  AND ae.declaracao_caminho != ''
                  AND ae.data_avaliacao IS NOT NULL";
            
            $stmtEnsino = $db->prepare($sqlEnsino);
            $stmtEnsino->bind_param("i", $curso_id);
            $stmtEnsino->execute();
            $resultEnsino = $stmtEnsino->get_result();
        
            while ($row = $resultEnsino->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'curso_nome' => $row['curso_nome'],
                    'atividade_nome' => $row['atividade_nome'], // Usar o nome correto da atividade baseado no atividade_disponivel_id
                    'titulo' => $row['titulo'],
                    'horas_contabilizadas' => (int)$row['horas_contabilizadas'],
                    'status' => ucfirst($row['status']),
                    'data_envio' => $row['data_envio'],
                    'data_aprovacao' => $row['data_aprovacao'],
                    'certificado_caminho' => $row['certificado_caminho'],
                    'tipo' => $row['tipo'],
                    'categoria_nome' => $row['categoria_nome']
                ];
            }

            // 3. Buscar atividades de estágio processadas
            $sqlEstagio = "SELECT 
                    aes.id,
                    CONCAT('Estágio - ', aes.empresa, ' - ', aes.area) as titulo,
                    aes.horas as horas_contabilizadas,
                    aes.status,
                    aes.data_submissao as data_envio,
                    aes.data_avaliacao as data_aprovacao,
                    aes.declaracao_caminho as certificado_caminho,
                    aes.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    aes.observacoes_avaliacao as observacoes_Analise,
                    4 as categoria_id,
                    'Estágio' as categoria_nome,
                    'estagio' as tipo,
                    aes.data_avaliacao
                FROM atividadecomplementarestagio aes
                INNER JOIN Aluno a ON aes.aluno_id = a.usuario_id
                INNER JOIN Usuario u ON a.usuario_id = u.id
                INNER JOIN Curso c ON a.curso_id = c.id
                WHERE c.id = ?
                  AND (aes.status = 'aprovado' OR aes.status = 'rejeitado')
                  AND aes.declaracao_caminho IS NOT NULL
                  AND aes.declaracao_caminho != ''
                  AND aes.data_avaliacao IS NOT NULL";
            
            $stmtEstagio = $db->prepare($sqlEstagio);
            $stmtEstagio->bind_param("i", $curso_id);
            $stmtEstagio->execute();
            $resultEstagio = $stmtEstagio->get_result();
        
            while ($row = $resultEstagio->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'curso_nome' => $row['curso_nome'],
                    'atividade_nome' => 'Estágio curricular não obrigatório',
                    'titulo' => $row['titulo'],
                    'horas_contabilizadas' => (int)$row['horas_contabilizadas'],
                    'status' => ucfirst($row['status']),
                    'data_envio' => $row['data_envio'],
                    'data_aprovacao' => $row['data_aprovacao'],
                    'certificado_caminho' => $row['certificado_caminho'],
                    'tipo' => $row['tipo'],
                    'categoria_nome' => $row['categoria_nome']
                ];
            }

            // 4. Buscar atividades de pesquisa processadas
            $sqlPesquisa = "SELECT 
                    ap.id,
                    CASE 
                        WHEN ap.nome_evento IS NOT NULL AND ap.nome_evento != '' THEN ap.nome_evento
                        WHEN ap.nome_projeto IS NOT NULL AND ap.nome_projeto != '' THEN ap.nome_projeto
                        WHEN ap.nome_artigo IS NOT NULL AND ap.nome_artigo != '' THEN ap.nome_artigo
                        ELSE 'Atividade de Pesquisa'
                    END as titulo,
                    ap.horas_realizadas as horas_contabilizadas,
                    ap.status,
                    ap.data_submissao as data_envio,
                    ap.data_avaliacao as data_aprovacao,
                    ap.declaracao_caminho as certificado_caminho,
                    ap.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    CASE 
                        WHEN SUBSTR(a.matricula, 1, 4) BETWEEN '2017' AND '2022' THEN ad17.titulo
                        WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN ad23.titulo
                        ELSE COALESCE(ad23.titulo, ad17.titulo)
                    END as atividade_nome,
                    ap.observacoes_avaliacao as observacoes_Analise,
                    2 as categoria_id,
                    'Pesquisa' as categoria_nome,
                    'pesquisa' as tipo,
                    ap.data_avaliacao
                FROM atividadecomplementarpesquisa ap
                INNER JOIN Aluno a ON ap.aluno_id = a.usuario_id
                INNER JOIN Usuario u ON a.usuario_id = u.id
                INNER JOIN Curso c ON a.curso_id = c.id
                LEFT JOIN atividadesdisponiveisbcc17 ad17 ON ap.atividade_disponivel_id = ad17.id
                LEFT JOIN atividadesdisponiveisbcc23 ad23 ON ap.atividade_disponivel_id = ad23.id
                WHERE c.id = ?
                  AND (ap.status = 'aprovado' OR ap.status = 'rejeitado')
                  AND ap.declaracao_caminho IS NOT NULL
                  AND ap.declaracao_caminho != ''
                  AND ap.data_avaliacao IS NOT NULL";
            
            $stmtPesquisa = $db->prepare($sqlPesquisa);
            $stmtPesquisa->bind_param("i", $curso_id);
            $stmtPesquisa->execute();
            $resultPesquisa = $stmtPesquisa->get_result();
        
            while ($row = $resultPesquisa->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'curso_nome' => $row['curso_nome'],
                    'atividade_nome' => $row['atividade_nome'],
                    'titulo' => $row['titulo'],
                    'horas_contabilizadas' => (int)$row['horas_contabilizadas'],
                    'status' => ucfirst($row['status']),
                    'data_envio' => $row['data_envio'],
                    'data_aprovacao' => $row['data_aprovacao'],
                    'certificado_caminho' => $row['certificado_caminho'],
                    'tipo' => $row['tipo'],
                    'categoria_nome' => $row['categoria_nome']
                ];
            }

            // Ordenar por data de aprovação (mais recente primeiro)
            usort($atividades, function($a, $b) {
                return strtotime($b['data_aprovacao']) - strtotime($a['data_aprovacao']);
            });
        
            return $atividades;
        
        } catch (Exception $e) {
            error_log("Erro em buscarCertificadosProcessadosPorCoordenador: " . $e->getMessage());
            return [];
        }
    }

    public static function atualizarCertificadoProcessado($atividade_id, $certificado_caminho, $avaliador_id = null) {
        try {
            $db = Database::getInstance()->getConnection();

            if ($avaliador_id) {
                $sql = "UPDATE atividadecomplementaracc 
                        SET certificado_processado = ?, avaliador_id = ?, data_envio_certificado = NOW()
                        WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("sii", $certificado_caminho, $avaliador_id, $atividade_id);
            } else {
                $sql = "UPDATE atividadecomplementaracc 
                        SET certificado_processado = ?, data_envio_certificado = NOW()
                        WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("si", $certificado_caminho, $atividade_id);
            }
            $sucesso = $stmt->execute();
            
            if ($sucesso) {
                if ($avaliador_id) {
                    error_log("Certificado processado atualizado com avaliador para atividade ID: " . $atividade_id . ", avaliador: " . $avaliador_id);
                } else {
                    error_log("Certificado processado atualizado para atividade ID: " . $atividade_id);
                }
            } else {
                error_log("Erro ao atualizar certificado processado: " . $stmt->error);
            }
            
            return $sucesso;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::atualizarCertificadoProcessado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Determina as tabelas de atividades e categorias baseado na matrícula do aluno
     */
    private static function determinarTabelasPorMatricula($matricula) {
        if (!$matricula) {
            return [
                'atividades' => 'atividadesdisponiveisbcc23',
                'categorias' => 'categoriaatividadebcc23'
            ];
        }
        
        $anoMatricula = (int)substr($matricula, 0, 4);
        
        if ($anoMatricula >= 2017 && $anoMatricula <= 2022) {
            return [
                'atividades' => 'atividadesdisponiveisbcc17',
                'categorias' => 'categoriaatividadebcc17'
            ];
        } else {
            return [
                'atividades' => 'atividadesdisponiveisbcc23',
                'categorias' => 'categoriaatividadebcc23'
            ];
        }
    }

    public static function listarCategorias($userData = null) {
        try {
            // Log dos dados recebidos para debug
            error_log("=== DEBUG listarCategorias INICIADO ===");
            error_log("listarCategorias - userData recebido: " . json_encode($userData));
            
            $db = \backend\api\config\Database::getInstance()->getConnection();
            
            if (!$db) {
                error_log("ERRO: Conexão com banco de dados falhou");
                throw new \Exception("Erro de conexão com banco de dados");
            }
            
            error_log("Conexão com banco estabelecida com sucesso");
            
            // Determinar tabela baseada na matrícula do aluno
            $matricula = null;
            if ($userData && isset($userData['matricula'])) {
                $matricula = $userData['matricula'];
            }
            
            $tabelas = self::determinarTabelasPorMatricula($matricula);
            $tabelaCategoria = $tabelas['categorias'];
            
            error_log("Usando tabela de categoria: " . $tabelaCategoria);
            
            // Verificar se a tabela existe antes de fazer a query
            $checkTable = $db->query("SHOW TABLES LIKE '{$tabelaCategoria}'");
            if (!$checkTable || $checkTable->num_rows === 0) {
                error_log("AVISO: Tabela {$tabelaCategoria} não existe, usando fallback");
                $tabelaCategoria = 'categoriaatividadebcc23';
            }
            
            // Tentar a tabela determinada pela matrícula
            $sql = "SELECT id, descricao as nome FROM {$tabelaCategoria} ORDER BY descricao";
            error_log("Executando SQL: " . $sql);
            
            $result = $db->query($sql);
            
            // Se a query falhar, usar fallback apenas para tabelas existentes
            if (!$result) {
                error_log("ERRO na query principal: " . $db->error);
                error_log("Tabela {$tabelaCategoria} não encontrada, tentando categoriaatividadebcc23");
                $sql = "SELECT id, descricao as nome FROM categoriaatividadebcc23 ORDER BY descricao";
                error_log("Executando SQL fallback: " . $sql);
                $result = $db->query($sql);
                
                if (!$result) {
                    error_log("ERRO no fallback: " . $db->error);
                    error_log("Erro: Nenhuma tabela de categoria encontrada");
                    throw new \Exception("Erro ao acessar tabelas de categoria");
                }
            }
            
            $categorias = [];
            if ($result) {
                error_log("Query executada com sucesso, processando resultados...");
                $count = 0;
                while ($row = $result->fetch_assoc()) {
                    $categorias[] = [
                        'id' => (int)$row['id'],
                        'nome' => $row['nome']
                    ];
                    $count++;
                }
                error_log("Total de categorias encontradas: " . $count);
                error_log("Categorias: " . json_encode($categorias));
            } else {
                error_log("ERRO: Resultado da query é null");
            }
            
            // Aplicar filtro para alunos com matrícula 2023+
            if ($userData && isset($userData['tipo']) && $userData['tipo'] === 'aluno' && isset($userData['matricula'])) {
                $anoMatricula = (int)substr($userData['matricula'], 0, 4);
                error_log("Aluno detectado - Matrícula: {$userData['matricula']}, Ano: {$anoMatricula}");
                
                // Para alunos com matrícula 2023 ou posterior, filtrar "Ação Social e Comunitária"
                if ($anoMatricula >= 2023) {
                    error_log("Aplicando filtro para aluno 2023+");
                    $categorias = array_filter($categorias, function($categoria) {
                        // Filtrar categorias que contenham "Ação Social" ou "Social" no nome
                        $nome = strtolower($categoria['nome']);
                        return !(strpos($nome, 'ação social') !== false || 
                                strpos($nome, 'social e comunitária') !== false ||
                                strpos($nome, 'atividades sociais') !== false);
                    });
                    
                    // Reindexar o array após filtrar
                    $categorias = array_values($categorias);
                    error_log("Categorias após filtro: " . count($categorias));
                } else {
                    error_log("Aluno 2017-2022 - mantendo todas as categorias");
                }
                // Para alunos 2017-2022 e outros tipos de usuário (coordenador, admin), manter todas as categorias
            } else {
                error_log("Usuário não é aluno ou dados insuficientes - mantendo todas as categorias");
            }
            
            error_log("=== DEBUG listarCategorias FINALIZADO - Retornando " . count($categorias) . " categorias ===");
            return $categorias;
        } catch (Exception $e) {
            error_log("ERRO CRÍTICO em AtividadeComplementar::listarCategorias: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Fallback para a tabela padrão em caso de erro
            try {
                error_log("Tentando fallback de emergência...");
                $db = \backend\api\config\Database::getInstance()->getConnection();
                
                // Tentar fallbacks em ordem
                $tabelasFallback = ['categoriaatividadebcc23', 'categoriaatividadebcc17', 'categoriaatividade'];
                $result = null;
                
                foreach ($tabelasFallback as $tabela) {
                    error_log("Tentando tabela fallback: " . $tabela);
                    $sql = "SELECT id, descricao as nome FROM {$tabela} ORDER BY descricao";
                    $result = $db->query($sql);
                    if ($result) {
                        error_log("Fallback usando tabela: " . $tabela);
                        break;
                    } else {
                        error_log("Falha na tabela {$tabela}: " . $db->error);
                    }
                }
                
                $categorias = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $categorias[] = [
                            'id' => (int)$row['id'],
                            'nome' => $row['nome']
                        ];
                    }
                    error_log("Fallback bem-sucedido - " . count($categorias) . " categorias encontradas");
                }
                
                // Aplicar o mesmo filtro no fallback
                if ($userData && isset($userData['tipo']) && $userData['tipo'] === 'aluno' && isset($userData['matricula'])) {
                    $anoMatricula = (int)substr($userData['matricula'], 0, 4);
                    
                    if ($anoMatricula >= 2023) {
                        $categorias = array_filter($categorias, function($categoria) {
                            $nome = strtolower($categoria['nome']);
                            return !(strpos($nome, 'ação social') !== false || 
                                    strpos($nome, 'social e comunitária') !== false ||
                                    strpos($nome, 'atividades sociais') !== false);
                        });
                        
                        $categorias = array_values($categorias);
                    }
                }
                
                return $categorias;
            } catch (Exception $fallbackError) {
                error_log("ERRO CRÍTICO no fallback: " . $fallbackError->getMessage());
                throw $e;
            }
        }
    }

    public static function contarPorAtividadeDisponivel($atividade_disponivel_id) {
        try {
            $db = \backend\api\config\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM atividadecomplementaracc WHERE atividade_disponivel_id = ?");
            $stmt->bind_param("i", $atividade_disponivel_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::contarPorAtividadeDisponivel: " . $e->getMessage());
            return 0;
        }
    }

    public static function removerPorAtividadeDisponivel($atividade_disponivel_id) {
        try {
            $db = \backend\api\config\Database::getInstance()->getConnection();
            
            // Remover todas as atividades complementares vinculadas à atividade disponível
            $stmt = $db->prepare("DELETE FROM atividadecomplementaracc WHERE atividade_disponivel_id = ?");
            $stmt->bind_param("i", $atividade_disponivel_id);
            
            $sucesso = $stmt->execute();
            $linhas_afetadas = $stmt->affected_rows;
            
            error_log("Remoção de atividades vinculadas - Linhas afetadas: " . $linhas_afetadas);
            
            return $sucesso;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::removerPorAtividadeDisponivel: " . $e->getMessage());
            return false;
        }
    }

    public static function criarAtividadeAvulsaComCertificado($dados)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "INSERT INTO atividadecomplementaracc 
                (aluno_id, atividade_disponivel_id, titulo, descricao, carga_horaria_solicitada, status, certificado_caminho, avaliador_id, data_submissao)
                VALUES (?, ?, ?, ?, ?, 'Pendente', ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->bind_param(
                "iissisi",
                $dados['aluno_id'],
                $dados['atividade_disponivel_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['carga_horaria_solicitada'],
                $dados['certificado_caminho'],
                $dados['avaliador_id']
            );
            if ($stmt->execute()) {
                return $db->insert_id;
            }
            error_log("Erro ao inserir atividade avulsa: " . $stmt->error);
            return false;
        } catch (Exception $e) {
            error_log("Erro em criarAtividadeAvulsaComCertificado: " . $e->getMessage());
            return false;
        }
    }
}

