<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;


class AtividadeComplementar {
    public static function create($dados) {
        try {
            // Validar dados obrigatórios
            $camposObrigatorios = ['aluno_id', 'atividade_disponivel_id', 'titulo', 'data_inicio', 'data_fim', 'carga_horaria_solicitada'];
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

            // Montar SQL para orientador_id ou avaliador_id
            $campos = "aluno_id, atividade_disponivel_id, titulo, descricao, data_inicio, data_fim, carga_horaria_solicitada, declaracao_caminho";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, ?";
            $tipos = "iissssis";
            $valores = [
                $dados['aluno_id'],
                $dados['atividade_disponivel_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['data_inicio'],
                $dados['data_fim'],
                $dados['carga_horaria_solicitada'],
                $dados['declaracao_caminho']
            ];

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
                    WHERE ac.orientador_id = ? AND ac.status = 'Pendente'
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

    public static function aprovarCertificado($atividade_id, $coordenador_id, $observacoes = '') {
        try {
            $db = Database::getInstance()->getConnection();

            // Adiciona uma observação sobre a aprovação
            $observacao_aprovacao = "\n[CERTIFICADO APROVADO PELO COORDENADOR EM " . date('Y-m-d H:i:s') . "]";
            if ($observacoes) {
                $observacao_aprovacao .= "\nObservações do coordenador: " . $observacoes;
            }
            
            $sql = "UPDATE atividadecomplementaracc 
                    SET observacoes_Analise = CONCAT(
                        COALESCE(observacoes_Analise, ''), 
                        ?
                    ),
                    data_avaliacao = NOW()
                    WHERE id = ? AND avaliador_id = ?";
                    
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sii", $observacao_aprovacao, $atividade_id, $coordenador_id);
            
            $sucesso = $stmt->execute();
            
            if ($sucesso) {
                error_log("Certificado aprovado com sucesso para atividade ID: " . $atividade_id);
            } else {
                error_log("Erro ao aprovar certificado: " . $stmt->error);
            }
            
            return $sucesso;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::aprovarCertificado: " . $e->getMessage());
            return false;
        }
    }

    public static function rejeitarCertificado($atividade_id, $coordenador_id, $observacoes) {
        try {
            $db = Database::getInstance()->getConnection();

            // Adiciona uma observação sobre a rejeição
            $observacao_rejeicao = "\n[CERTIFICADO REJEITADO PELO COORDENADOR EM " . date('Y-m-d H:i:s') . "]";
            $observacao_rejeicao .= "\nMotivo da rejeição: " . $observacoes;
            
            // Remove o certificado processado e adiciona a observação
            $sql = "UPDATE atividadecomplementaracc 
                    SET certificado_processado = NULL,
                        observacoes_Analise = CONCAT(
                            COALESCE(observacoes_Analise, ''), 
                            ?
                        )
                    WHERE id = ? AND avaliador_id = ?";
                    
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sii", $observacao_rejeicao, $atividade_id, $coordenador_id);
            
            $sucesso = $stmt->execute();
            
            if ($sucesso) {
                error_log("Certificado rejeitado com sucesso para atividade ID: " . $atividade_id);
            } else {
                error_log("Erro ao rejeitar certificado: " . $stmt->error);
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

            // Buscar o curso_id do coordenador
            $sqlCurso = "SELECT curso_id FROM Coordenador WHERE usuario_id = ?";
            $stmtCurso = $db->prepare($sqlCurso);
            $stmtCurso->bind_param("i", $coordenador_id);
            $stmtCurso->execute();
            $resultCurso = $stmtCurso->get_result();

            if ($resultCurso->num_rows === 0) {
                return [];
            }

            $coordenador = $resultCurso->fetch_assoc();
            $curso_id = $coordenador['curso_id'];

            $atividades = [];

            // Buscar certificados pendentes de atividades complementares
            $sql = "
                SELECT 
                    ac.id,
                    ac.titulo,
                    ac.carga_horaria_aprovada,
                    ac.status,
                    ac.data_envio_certificado as data_envio,
                    ac.certificado_processado as certificado_caminho,
                    ac.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    ac.observacoes_Analise,
                    ac.atividade_disponivel_id,
                    ac.categoria_id,
                    cat.descricao as categoria_nome,
                    'complementar' as tipo
                FROM atividadecomplementaracc ac
                INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                INNER JOIN Usuario u ON a.usuario_id = u.id
                INNER JOIN Curso c ON a.curso_id = c.id
                LEFT JOIN CategoriaAtividade cat ON ac.categoria_id = cat.id
                WHERE c.id = ?
                  AND ac.certificado_processado IS NOT NULL
                  AND (ac.status = 'Aprovada' OR ac.status = 'Pendente')
                  AND (ac.observacoes_Analise IS NULL OR ac.observacoes_Analise NOT LIKE '%[CERTIFICADO APROVADO PELO COORDENADOR%')
                  AND (ac.observacoes_Analise IS NULL OR ac.observacoes_Analise NOT LIKE '%[CERTIFICADO REJEITADO PELO COORDENADOR%')
            ";

            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $curso_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
            }

            // Buscar certificados avulsos pendentes
            $sqlAvulso = "
                SELECT 
                    ca.id,
                    ca.titulo,
                    ca.horas as carga_horaria_aprovada,
                    ca.status,
                    ca.data_envio as data_envio,
                    ca.caminho_arquivo as certificado_caminho,
                    ca.aluno_id,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    ca.observacao as observacoes_Analise,
                    NULL as categoria_id,
                    'Certificado Avulso' as categoria_nome,
                    'avulso' as tipo
                FROM certificadoavulso ca
                INNER JOIN Aluno a ON ca.aluno_id = a.usuario_id
                INNER JOIN Usuario u ON a.usuario_id = u.id
                INNER JOIN Curso c ON a.curso_id = c.id
                WHERE c.id = ? AND ca.coordenador_id = ? AND ca.status = 'Pendente'
            ";

            $stmtAvulso = $db->prepare($sqlAvulso);
            $stmtAvulso->bind_param("ii", $curso_id, $coordenador_id);
            $stmtAvulso->execute();
            $resultAvulso = $stmtAvulso->get_result();

            while ($row = $resultAvulso->fetch_assoc()) {
                $atividades[] = $row;
            }

            // Ordenar por data de envio
            usort($atividades, function($a, $b) {
                return strtotime($b['data_envio']) - strtotime($a['data_envio']);
            });

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
                return [];
            }
    
            $coordenador = $resultCurso->fetch_assoc();
            $curso_id = $coordenador['curso_id'];
    
            $atividades = [];

            // Buscar certificados processados de atividades complementares
            $sql = "SELECT 
                    ac.id,
                    ac.titulo,
                    ac.carga_horaria_aprovada as horas_contabilizadas,
                    'Aprovado' as status,
                    ac.data_avaliacao as data_envio,
                    ac.data_avaliacao as data_aprovacao,
                    ac.certificado_processado as certificado_caminho,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    ca.descricao as atividade_nome,
                    'complementar' as tipo
                FROM atividadecomplementaracc ac
                INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                INNER JOIN Usuario u ON a.usuario_id = u.id
                INNER JOIN Curso c ON a.curso_id = c.id
                LEFT JOIN CategoriaAtividade ca ON ac.categoria_id = ca.id
                WHERE a.curso_id = ? 
                  AND ac.status = 'Aprovada'
                  AND ac.certificado_processado IS NOT NULL 
                  AND ac.certificado_processado != ''
                  AND ac.avaliador_id = ?
                  AND ac.observacoes_Analise LIKE '%[CERTIFICADO APROVADO PELO COORDENADOR%'";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ii", $curso_id, $coordenador_id);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'curso_nome' => $row['curso_nome'],
                    'atividade_nome' => $row['atividade_nome'],
                    'horas_contabilizadas' => (int)$row['horas_contabilizadas'],
                    'status' => $row['status'],
                    'data_envio' => $row['data_envio'],
                    'data_aprovacao' => $row['data_aprovacao'],
                    'certificado_caminho' => $row['certificado_caminho'],
                    'tipo' => $row['tipo']
                ];
            }

            // Buscar certificados avulsos processados
            $sqlAvulso = "SELECT 
                    ca.id,
                    ca.titulo,
                    ca.horas as horas_contabilizadas,
                    ca.status,
                    ca.data_envio as data_envio,
                    ca.data_avaliacao as data_aprovacao,
                    ca.caminho_arquivo as certificado_caminho,
                    u.nome as aluno_nome,
                    a.matricula as aluno_matricula,
                    c.nome as curso_nome,
                    'Certificado Avulso' as atividade_nome,
                    'avulso' as tipo
                FROM certificadoavulso ca
                INNER JOIN Aluno a ON ca.aluno_id = a.usuario_id
                INNER JOIN Usuario u ON a.usuario_id = u.id
                INNER JOIN Curso c ON a.curso_id = c.id
                WHERE c.id = ? AND ca.coordenador_id = ? 
                  AND (ca.status = 'Aprovado' OR ca.status = 'Rejeitado')";
            
            $stmtAvulso = $db->prepare($sqlAvulso);
            $stmtAvulso->bind_param("ii", $curso_id, $coordenador_id);
            $stmtAvulso->execute();
            $resultAvulso = $stmtAvulso->get_result();
        
            while ($row = $resultAvulso->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'curso_nome' => $row['curso_nome'],
                    'atividade_nome' => $row['atividade_nome'],
                    'horas_contabilizadas' => (int)$row['horas_contabilizadas'],
                    'status' => $row['status'],
                    'data_envio' => $row['data_envio'],
                    'data_aprovacao' => $row['data_aprovacao'],
                    'certificado_caminho' => $row['certificado_caminho'],
                    'tipo' => $row['tipo']
                ];
            }

            // Ordenar por data de aprovação
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

    public static function listarCategorias() {
        try {
            $db = \backend\api\config\Database::getInstance()->getConnection();
            $sql = "SELECT id, descricao as nome FROM CategoriaAtividade ORDER BY descricao";
            $result = $db->query($sql);
            
            $categorias = [];
            while ($row = $result->fetch_assoc()) {
                $categorias[] = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome']
                ];
            }
            return $categorias;
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::listarCategorias: " . $e->getMessage());
            throw $e;
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

