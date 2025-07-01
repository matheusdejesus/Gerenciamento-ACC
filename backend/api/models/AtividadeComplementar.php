<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;


class AtividadeComplementar {
    public static function create($dados) {
        try {
            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();

            $sql = "INSERT INTO AtividadeComplementar (
                        aluno_id, 
                        categoria_id, 
                        titulo, 
                        descricao, 
                        data_inicio, 
                        data_fim, 
                        carga_horaria_solicitada, 
                        declaracao,
                        declaracao_mime,
                        orientador_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            // Correção: usar 'b' para BLOB e 's' para string
            $stmt->bind_param(
                "iisssisbsi",
                $dados['aluno_id'],
                $dados['categoria_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['data_inicio'],
                $dados['data_fim'],
                $dados['carga_horaria_solicitada'],
                $dados['declaracao'], // BLOB
                $dados['declaracao_mime'],
                $dados['orientador_id']
            );

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
            return false;
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
                        ac.declaracao,
                        ca.descricao as categoria_nome,
                        u_orientador.nome as orientador_nome
                    FROM AtividadeComplementar ac
                    LEFT JOIN CategoriaAtividade ca ON ac.categoria_id = ca.id
                    LEFT JOIN Usuario u_orientador ON ac.orientador_id = u_orientador.id
                    WHERE ac.aluno_id = ?
                    ORDER BY ac.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
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
                    'tem_declaracao' => !empty($row['declaracao'])
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarPorAluno: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function buscarPorId($id) {
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
                        ac.data_submissao,
                        ac.data_avaliacao,
                        ac.status,
                        ac.observacoes_Analise,
                        ac.orientador_id,
                        ac.aluno_id,
                        u_aluno.nome as aluno_nome,
                        a.matricula as aluno_matricula,
                        u_aluno.email as aluno_email,
                        c.nome as curso_nome
                    FROM AtividadeComplementar ac
                    INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                    INNER JOIN Usuario u_aluno ON a.usuario_id = u_aluno.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    WHERE ac.id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function validarDados($dados) {
        $erros = [];
        
        // Validar campos obrigatórios
        if (empty($dados['atividade_id'])) {
            $erros[] = "ID da atividade é obrigatório";
        }
        
        if (empty($dados['data_inicio'])) {
            $erros[] = "Data de início é obrigatória";
        }
        
        if (empty($dados['data_fim'])) {
            $erros[] = "Data de término é obrigatória";
        }
        
        if (empty($dados['horas_solicitadas']) || $dados['horas_solicitadas'] <= 0) {
            $erros[] = "Carga horária deve ser maior que zero";
        }
        
        if (empty($dados['descricao_atividades'])) {
            $erros[] = "Descrição das atividades é obrigatória";
        }
        
        // Validar datas
        if (!empty($dados['data_inicio']) && !empty($dados['data_fim'])) {
            $dataInicio = new \DateTime($dados['data_inicio']);
            $dataFim = new \DateTime($dados['data_fim']);
            
            if ($dataInicio >= $dataFim) {
                $erros[] = "Data de término deve ser posterior à data de início";
            }
            
            // Verificar se não é acima de 1 ano
            $limiteFuturo = new \DateTime();
            $limiteFuturo->modify('+1 year');
            
            if ($dataFim > $limiteFuturo) {
                $erros[] = "Data de término não pode ser superior a 1 ano no futuro";
            }
        }
        
        return $erros;
    }
    
    public static function buscarOrientadores() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT u.id, u.nome, o.siape
                    FROM Usuario u
                    JOIN Orientador o ON u.id = o.usuario_id
                    WHERE u.tipo = 'orientador'
                    ORDER BY u.nome";
            
            $result = $db->query($sql);
            $orientadores = [];
            
            while ($row = $result->fetch_assoc()) {
                $orientadores[] = $row;
            }
            
            return $orientadores;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarOrientadores: " . $e->getMessage());
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
                        u_aluno.nome as aluno_nome,
                        ac.carga_horaria_solicitada,
                        ac.data_submissao,
                        ac.status,
                        ac.declaracao,
                        c.nome as curso_nome,
                        a.matricula as aluno_matricula,
                        u_aluno.email as aluno_email
                    FROM AtividadeComplementar ac
                    INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                    INNER JOIN Usuario u_aluno ON a.usuario_id = u_aluno.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    WHERE ac.orientador_id = ? 
                    AND ac.status = 'Pendente'
                    ORDER BY ac.data_submissao DESC";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $orientador_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $temDeclaracao = ($row['declaracao'] !== null && strlen($row['declaracao']) > 0);
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'aluno_nome' => $row['aluno_nome'],
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'data_submissao' => $row['data_submissao'],
                    'status' => $row['status'],
                    'curso_nome' => $row['curso_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'aluno_email' => $row['aluno_email'],
                    'tem_declaracao' => $temDeclaracao
                ];
            }
            return $atividades;
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarPendentesOrientador: " . $e->getMessage());
            throw $e;
        }
    }

    public static function avaliarAtividade($atividade_id, $orientador_id, $carga_horaria_aprovada, $observacoes_analise, $status) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $db->autocommit(false);
            $db->begin_transaction();
            
            // Verificar se a atividade existe e pertence ao orientador
            $sqlVerificar = "SELECT id, carga_horaria_solicitada FROM AtividadeComplementar 
                           WHERE id = ? AND orientador_id = ? AND status = 'Pendente'";
            $stmtVerificar = $db->prepare($sqlVerificar);
            $stmtVerificar->bind_param("ii", $atividade_id, $orientador_id);
            $stmtVerificar->execute();
            $resultado = $stmtVerificar->get_result();
            
            if ($resultado->num_rows === 0) {
                throw new Exception("Atividade não encontrada ou não autorizada para avaliação");
            }
            
            $atividade = $resultado->fetch_assoc();

            // Verificar se não está aprovando mais horas que o solicitado
            if ($status === 'Aprovada' && $carga_horaria_aprovada > $atividade['carga_horaria_solicitada']) {
                throw new Exception("Não é possível aprovar mais horas ({$carga_horaria_aprovada}h) do que o solicitado ({$atividade['carga_horaria_solicitada']}h)");
            }
            
            // Atualizar a atividade com os dados da avaliação
            $sqlAtualizar = "UPDATE AtividadeComplementar 
                           SET carga_horaria_aprovada = ?, 
                               observacoes_Analise = ?, 
                               status = ?, 
                               data_avaliacao = NOW() 
                           WHERE id = ?";
            
            $stmtAtualizar = $db->prepare($sqlAtualizar);
            $stmtAtualizar->bind_param("issi", $carga_horaria_aprovada, $observacoes_analise, $status, $atividade_id);
            
            if (!$stmtAtualizar->execute()) {
                throw new Exception("Erro ao atualizar atividade");
            }
            
            $db->commit();
            $db->autocommit(true);
            
            return true;
            
        } catch (\Exception $e) {
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro em AtividadeComplementar::avaliarAtividade: " . $e->getMessage());
            throw $e;
        }
    }

    public static function buscarAvaliadasOrientador($orientador_id, $limite = 10) {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT 
                        ac.id,
                        ac.titulo,
                        ac.descricao,
                        ac.data_inicio,
                        ac.data_fim,
                        u_aluno.nome as aluno_nome,
                        ac.carga_horaria_solicitada,
                        ac.carga_horaria_aprovada,
                        ac.data_submissao,
                        ac.data_avaliacao,
                        ac.status,
                        ac.observacoes_Analise,
                        ac.declaracao,
                        c.nome as curso_nome,
                        a.matricula as aluno_matricula,
                        u_aluno.email as aluno_email
                    FROM AtividadeComplementar ac
                    INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                    INNER JOIN Usuario u_aluno ON a.usuario_id = u_aluno.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    WHERE ac.orientador_id = ? 
                    AND ac.status IN ('Aprovada', 'Rejeitada')
                    ORDER BY ac.data_avaliacao DESC
                    LIMIT ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ii", $orientador_id, $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $temDeclaracao = ($row['declaracao'] !== null && strlen($row['declaracao']) > 0);
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'aluno_nome' => $row['aluno_nome'],
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'carga_horaria_aprovada' => $row['carga_horaria_aprovada'] ? (int)$row['carga_horaria_aprovada'] : 0,
                    'data_submissao' => $row['data_submissao'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'status' => $row['status'],
                    'observacoes_Analise' => $row['observacoes_Analise'],
                    'curso_nome' => $row['curso_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'aluno_email' => $row['aluno_email'],
                    'tem_declaracao' => $temDeclaracao
                ];
            }
            return $atividades;
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarAvaliadasOrientador: " . $e->getMessage());
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
            
            if (!$result || $result->num_rows === 0) {
                $sql = "SELECT u.id, u.nome, u.email
                        FROM Usuario u
                        WHERE u.tipo = 'orientador'
                        ORDER BY u.nome";
                
                $result = $db->query($sql);
            }
            
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
            
            error_log("Orientadores encontrados: " . count($orientadores));
            
            return $orientadores;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::listarOrientadores: " . $e->getMessage());
            
            try {
                $db = Database::getInstance()->getConnection();
                $sql = "SELECT id, nome, email FROM Usuario WHERE tipo = 'orientador' ORDER BY nome";
                $result = $db->query($sql);
                
                $orientadores = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $orientadores[] = [
                            'id' => (int)$row['id'],
                            'nome' => $row['nome'],
                            'email' => $row['email']
                        ];
                    }
                }
                
                return $orientadores;
                
            } catch (Exception $e2) {
                error_log("Erro no fallback de listarOrientadores: " . $e2->getMessage());
                return [];
            }
        }
    }
}
?>