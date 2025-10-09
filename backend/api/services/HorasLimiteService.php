<?php
namespace backend\api\services;

require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;
use Exception;

class HorasLimiteService {
    
    /**
     * Calcular total de horas de um aluno em todas as categorias
     */
    public static function calcularTotalHorasAluno($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            $totalHoras = 0;
            
            // Buscar matrícula do aluno para determinar as tabelas corretas
            $stmt = $db->prepare("SELECT matricula FROM Aluno WHERE usuario_id = ?");
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $aluno_data = $result->fetch_assoc();
            
            if (!$aluno_data) {
                error_log("HorasLimiteService: Aluno não encontrado para ID: " . $aluno_id);
                return 0;
            }
            
            $matricula = $aluno_data['matricula'];
            $anoMatricula = (int) substr($matricula, 0, 4);
            
            // Determinar sufixo da tabela baseado no ano de matrícula
            $sufixoTabela = '';
            if ($anoMatricula >= 2023) {
                $sufixoTabela = 'bcc23';
            } elseif ($anoMatricula >= 2017) {
                $sufixoTabela = 'bcc17';
            } else {
                $sufixoTabela = 'bcc17'; // Default para matrículas antigas
            }
            
            // Tabelas para calcular o total de horas
            $tabelas = [
                "atividadecomplementaracc{$sufixoTabela}",
                "atividadecomplementarpesquisa{$sufixoTabela}",
                "atividadecomplementarensino{$sufixoTabela}",
                "atividadecomplementarestagio{$sufixoTabela}",
                "atividadesocialcomunitaria{$sufixoTabela}"
            ];
            
            foreach ($tabelas as $tabela) {
                // Verificar se a tabela existe
                $checkTable = $db->prepare("SHOW TABLES LIKE ?");
                $checkTable->bind_param("s", $tabela);
                $checkTable->execute();
                $tableExists = $checkTable->get_result()->num_rows > 0;
                
                if (!$tableExists) {
                    error_log("HorasLimiteService: Tabela {$tabela} não existe, pulando...");
                    continue;
                }
                
                // Calcular horas aprovadas na tabela
                $sql = "SELECT SUM(horas_realizadas) as total_horas 
                        FROM {$tabela} 
                        WHERE aluno_id = ? AND status = 'aprovado'";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $aluno_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                $horasTabela = (int) ($row['total_horas'] ?? 0);
                $totalHoras += $horasTabela;
                
                error_log("HorasLimiteService: Tabela {$tabela} - {$horasTabela}h");
            }
            
            error_log("HorasLimiteService: Total calculado para aluno {$aluno_id}: {$totalHoras}h");
            return $totalHoras;
            
        } catch (Exception $e) {
            error_log("HorasLimiteService: Erro ao calcular total de horas: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Verificar se o aluno pode cadastrar mais atividades (limite 240h)
     */
    public static function podeAdicionarHoras($aluno_id, $horas_novas) {
        $totalAtual = self::calcularTotalHorasAluno($aluno_id);
        return ($totalAtual + $horas_novas) <= 240;
    }
    
    /**
     * Calcular horas restantes até o limite de 240h
     */
    public static function calcularHorasRestantes($aluno_id) {
        $totalAtual = self::calcularTotalHorasAluno($aluno_id);
        return max(0, 240 - $totalAtual);
    }
    
    /**
     * Calcular horas de uma categoria específica
     */
    public static function calcularHorasCategoria($aluno_id, $categoria) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Buscar matrícula do aluno para determinar as tabelas corretas
            $stmt = $db->prepare("SELECT matricula FROM Aluno WHERE usuario_id = ?");
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $aluno_data = $result->fetch_assoc();
            
            if (!$aluno_data) {
                return 0;
            }
            
            $matricula = $aluno_data['matricula'];
            $anoMatricula = (int) substr($matricula, 0, 4);
            
            // Determinar sufixo da tabela baseado no ano de matrícula
            $sufixoTabela = '';
            if ($anoMatricula >= 2023) {
                $sufixoTabela = 'bcc23';
            } elseif ($anoMatricula >= 2017) {
                $sufixoTabela = 'bcc17';
            } else {
                $sufixoTabela = 'bcc17';
            }
            
            // Mapear categoria para nome da tabela
            $tabelasCategoria = [
                'acc' => "atividadecomplementaracc{$sufixoTabela}",
                'pesquisa' => "atividadecomplementarpesquisa{$sufixoTabela}",
                'ensino' => "atividadecomplementarensino{$sufixoTabela}",
                'estagio' => "atividadecomplementarestagio{$sufixoTabela}",
                'social' => "atividadesocialcomunitaria{$sufixoTabela}"
            ];
            
            if (!isset($tabelasCategoria[$categoria])) {
                error_log("HorasLimiteService: Categoria inválida: {$categoria}");
                return 0;
            }
            
            $tabela = $tabelasCategoria[$categoria];
            
            // Verificar se a tabela existe
            $checkTable = $db->prepare("SHOW TABLES LIKE ?");
            $checkTable->bind_param("s", $tabela);
            $checkTable->execute();
            $tableExists = $checkTable->get_result()->num_rows > 0;
            
            if (!$tableExists) {
                error_log("HorasLimiteService: Tabela {$tabela} não existe");
                return 0;
            }
            
            // Calcular horas aprovadas na categoria
            $sql = "SELECT SUM(horas_realizadas) as total_horas 
                    FROM {$tabela} 
                    WHERE aluno_id = ? AND status = 'aprovado'";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $horasCategoria = (int) ($row['total_horas'] ?? 0);
            
            error_log("HorasLimiteService: Categoria {$categoria} - {$horasCategoria}h");
            return $horasCategoria;
            
        } catch (Exception $e) {
            error_log("HorasLimiteService: Erro ao calcular horas da categoria {$categoria}: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obter limite máximo de horas para uma categoria específica
     */
    public static function getLimiteCategoria($categoria) {
        $limites = [
            'acc' => 80,
            'ensino' => 80,
            'pesquisa' => 80,
            'estagio' => 100,
            'social' => 30
        ];
        
        return $limites[$categoria] ?? 0;
    }
    
    /**
     * Verificar se o aluno pode adicionar horas em uma categoria específica
     */
    public static function podeAdicionarHorasCategoria($aluno_id, $categoria, $horas_novas) {
        $horasAtual = self::calcularHorasCategoria($aluno_id, $categoria);
        $limiteCategoria = self::getLimiteCategoria($categoria);
        
        return ($horasAtual + $horas_novas) <= $limiteCategoria;
    }
    
    /**
     * Calcular quantas horas ainda podem ser adicionadas em uma categoria
     */
    public static function calcularHorasRestantesCategoria($aluno_id, $categoria) {
        $horasAtual = self::calcularHorasCategoria($aluno_id, $categoria);
        $limiteCategoria = self::getLimiteCategoria($categoria);
        
        return max(0, $limiteCategoria - $horasAtual);
    }
}