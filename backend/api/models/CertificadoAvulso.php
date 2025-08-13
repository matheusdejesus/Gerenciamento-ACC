<?php

namespace backend\api\models;
use backend\api\config\Database;
use Exception;

class CertificadoAvulso
{
    public static function create(array $data)
    {
        $conn = Database::getConnection();
        $sql = "INSERT INTO certificadoavulso 
                (aluno_id, coordenador_id, titulo, observacao, horas, caminho_arquivo) 
                VALUES 
                (:aluno_id, :coordenador_id, :titulo, :observacao, :horas, :caminho_arquivo)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':aluno_id',         $data['aluno_id']);
        $stmt->bindParam(':coordenador_id',   $data['coordenador_id']);
        $stmt->bindParam(':titulo',           $data['titulo']);
        $stmt->bindParam(':observacao',       $data['observacao']);
        $stmt->bindParam(':horas',            $data['horas']);
        $stmt->bindParam(':caminho_arquivo',  $data['caminho_arquivo']);
        if ($stmt->execute()) {
            return $conn->lastInsertId();
        }
        return false;
    }

    public static function buscarPorAluno($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ca.id,
                        ca.titulo,
                        ca.observacao as descricao,
                        ca.horas as carga_horaria_solicitada,
                        ca.horas as carga_horaria_aprovada,
                        ca.status,
                        ca.data_envio as data_submissao,
                        ca.data_avaliacao,
                        ca.caminho_arquivo as certificado_caminho,
                        'Certificado Avulso' as categoria_nome,
                        u.nome AS coordenador_nome,
                        'avulso' as tipo
                    FROM certificadoavulso ca
                    LEFT JOIN Usuario u ON ca.coordenador_id = u.id
                    WHERE ca.aluno_id = ?
                    ORDER BY ca.data_envio DESC";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $certificados = [];
            
            while ($row = $result->fetch_assoc()) {
                $certificados[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => null,
                    'data_fim' => null,
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'carga_horaria_aprovada' => $row['status'] === 'Aprovado' ? (int)$row['carga_horaria_aprovada'] : null,
                    'status' => $row['status'] === 'Aprovado' ? 'Aprovada' : ($row['status'] === 'Rejeitado' ? 'Rejeitada' : 'Pendente'),
                    'data_submissao' => $row['data_submissao'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'observacoes_Analise' => null,
                    'categoria_nome' => $row['categoria_nome'],
                    'orientador_nome' => $row['coordenador_nome'],
                    'tem_declaracao' => false,
                    'declaracao_caminho' => null,
                    'certificado_caminho' => 'uploads/certificados_avulsos/' . $row['certificado_caminho'],
                    'tipo' => $row['tipo']
                ];
            }
            
            return $certificados;
            
        } catch (Exception $e) {
            error_log("Erro em CertificadoAvulso::buscarPorAluno: " . $e->getMessage());
            throw $e;
        }
    }

    public static function aprovar($certificado_id, $coordenador_id, $observacoes = '') {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar se o certificado existe e está pendente
            $sqlVerificar = "SELECT * FROM certificadoavulso WHERE id = ? AND status = 'Pendente' AND coordenador_id = ?";
            $stmtVerificar = $db->prepare($sqlVerificar);
            $stmtVerificar->bind_param("ii", $certificado_id, $coordenador_id);
            $stmtVerificar->execute();
            $resultado = $stmtVerificar->get_result();
            
            if ($resultado->num_rows === 0) {
                error_log("Certificado não encontrado ou não está pendente");
                return false;
            }
            
            // Atualizar status para aprovado
            $sql = "UPDATE certificadoavulso SET status = 'Aprovado', data_avaliacao = NOW(), observacao = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("si", $observacoes, $certificado_id);
            
            if ($stmt->execute()) {
                error_log("Certificado avulso aprovado com sucesso. ID: " . $certificado_id);

                // Adicionar log na tabela LogAcoes
                $sqlLog = "INSERT INTO LogAcoes (usuario_id, acao, descricao, data_hora) VALUES (?, 'aprovar_certificado_avulso', ?, NOW())";
                $stmtLog = $db->prepare($sqlLog);
                $descricao = "Aprovou certificado avulso ID: " . $certificado_id . ($observacoes ? " com observações: " . $observacoes : "");
                $stmtLog->bind_param("is", $coordenador_id, $descricao);
                $stmtLog->execute();

                return true;
            } else {
                error_log("Erro ao aprovar certificado avulso: " . $stmt->error);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Erro em CertificadoAvulso::aprovar: " . $e->getMessage());
            return false;
        }
    }

    public static function rejeitar($certificado_id, $coordenador_id, $observacoes) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar se o certificado existe e está pendente
            $sqlVerificar = "SELECT * FROM certificadoavulso WHERE id = ? AND status = 'Pendente' AND coordenador_id = ?";
            $stmtVerificar = $db->prepare($sqlVerificar);
            $stmtVerificar->bind_param("ii", $certificado_id, $coordenador_id);
            $stmtVerificar->execute();
            $resultado = $stmtVerificar->get_result();
            
            if ($resultado->num_rows === 0) {
                error_log("Certificado não encontrado ou não está pendente");
                return false;
            }
            
            // Atualizar status para rejeitado
            $sql = "UPDATE certificadoavulso SET status = 'Rejeitado', data_avaliacao = NOW(), observacao = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("si", $observacoes, $certificado_id);
            
            if ($stmt->execute()) {
                error_log("Certificado avulso rejeitado com sucesso. ID: " . $certificado_id);

                // Adicionar log na tabela LogAcoes
                $sqlLog = "INSERT INTO LogAcoes (usuario_id, acao, descricao, data_hora) VALUES (?, 'rejeitar_certificado_avulso', ?, NOW())";
                $stmtLog = $db->prepare($sqlLog);
                $descricao = "Rejeitou certificado avulso ID: " . $certificado_id . " com observações: " . $observacoes;
                $stmtLog->bind_param("is", $coordenador_id, $descricao);
                $stmtLog->execute();

                return true;
            } else {
                error_log("Erro ao rejeitar certificado avulso: " . $stmt->error);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Erro em CertificadoAvulso::rejeitar: " . $e->getMessage());
            return false;
        }
    }
}