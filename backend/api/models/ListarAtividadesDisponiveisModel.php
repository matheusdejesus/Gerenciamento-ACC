<?php

namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;
use Exception;

class ListarAtividadesDisponiveisModel
{

    // Mapeamento dos tipos de atividades para nomes na base de dados
    const TIPOS_ATIVIDADES = [
        'ensino' => 'Ensino',
        'estagio' => 'Estágio',
        'extracurriculares' => 'Atividades extracurriculares',
        'pesquisa' => 'Pesquisa',
        'acao_social' => 'Atividades sociais e comunitárias',
        'pet' => 'PET'
    ];

    // IDs estáveis de tipo_atividade na base (evita problemas de acentuação)
    const TIPOS_ATIVIDADES_ID = [
        'ensino' => 1,
        'pesquisa' => 2,
        'extracurriculares' => 3,
        'estagio' => 4,
        'acao_social' => 5,
        'pet' => 6,
    ];

    // Mapeamento das resoluções por tipo de atividade
    const RESOLUCOES_POR_TIPO = [
        'ensino' => [
            '2017-2022' => 1,
            '2023+' => 6
        ],
        'estagio' => [
            '2017-2022' => 4,
            '2023+' => 9
        ],
        'extracurriculares' => [
            '2017-2022' => 3,
            '2023+' => 8
        ],
        'pesquisa' => [
            '2017-2022' => 2,
            '2023+' => 7
        ],
        'acao_social' => [
            '2017-2022' => 5,
            '2023+' => 6
        ]
    ];

    /**
     * Lista atividades por tipo e resolução com paginação e filtros
     * @param string $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa)
     * @param int $resolucaoTipoAtividadeId ID da resolução
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'nome')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'ASC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarAtividades($tipo, $resolucaoTipoAtividadeId, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '')
    {
        // Este método é um alias para listarPorTipoEResolucao para compatibilidade
        return self::listarPorTipoEResolucao($tipo, $resolucaoTipoAtividadeId, $pagina, $limite, $ordenacao, $direcao, $busca);
    }

    /**
     * Lista atividades por tipo e resolução com paginação e filtros
     * @param string $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa)
     * @param int $resolucaoTipoAtividadeId ID da resolução
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'nome')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'ASC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarPorTipoEResolucao($tipo, $resolucaoTipoAtividadeId, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '')
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            // Validar tipo de atividade
            if (!isset(self::TIPOS_ATIVIDADES[$tipo])) {
                throw new Exception("Tipo de atividade inválido: $tipo");
            }

            $nomeAtividade = self::TIPOS_ATIVIDADES[$tipo];

            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;

            // Mapear campos de ordenação
            $camposOrdenacao = [
                'nome' => 'ac.titulo',
                'categoria' => 'ta.nome',
                'carga_horaria_maxima' => 'apr.carga_horaria_maxima_por_atividade'
            ];

            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ac.titulo';
            $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';

            // Construir condição de busca
            $condicaoBusca = '';
            $parametros = [$resolucaoTipoAtividadeId];
            $tipos = 'i';

            if (!empty($busca)) {
                $condicaoBusca = " AND (ac.titulo LIKE ? OR ac.descricao LIKE ? OR ta.nome LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tipos .= 'sss';
            }

            // Query para contar total de registros
            $sqlCount = "SELECT COUNT(*) as total
                        FROM atividades_por_resolucao apr
                        JOIN resolucao_tipo_atividade rta 
                            ON rta.resolucao_id = apr.resolucao_id 
                            AND rta.tipo_atividade_id = apr.tipo_atividade_id
                        JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        WHERE rta.id = ? 
                        AND ta.nome = '$nomeAtividade' $condicaoBusca";

            error_log("SQL Count Query ($tipo): " . $sqlCount);
            error_log("Parameters: " . json_encode($parametros));

            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                error_log("Falha ao preparar consulta de contagem de pendentes: " . $conn->error);
                return [
                    'atividades' => [],
                    'paginacao' => [
                        'pagina_atual' => $pagina,
                        'total_paginas' => 0,
                        'total_registros' => 0,
                        'limite' => $limite,
                        'tem_proxima' => false,
                        'tem_anterior' => false
                    ]
                ];
            }

            $stmtCount->bind_param($tipos, ...$parametros);

            if (!$stmtCount->execute()) {
                error_log("Falha ao executar consulta de contagem de pendentes: " . $stmtCount->error);
                return [
                    'atividades' => [],
                    'paginacao' => [
                        'pagina_atual' => $pagina,
                        'total_paginas' => 0,
                        'total_registros' => 0,
                        'limite' => $limite,
                        'tem_proxima' => false,
                        'tem_anterior' => false
                    ]
                ];
            }

            $resultCount = $stmtCount->get_result();
            $total = $resultCount->fetch_assoc()['total'];
            $stmtCount->close();

            // Query principal com paginação
            $sql = "SELECT 
                        apr.id as atividades_por_resolucao_id,
                        ac.id as atividade_complementar_id,
                        ac.titulo as nome,
                        ac.descricao,
                        ac.observacoes,
                        ta.nome as categoria,
                        apr.carga_horaria_maxima_por_atividade as carga_horaria_maxima,
                        ta.nome as tipo,
                        apr.carga_horaria_maxima_por_atividade as horas_max,
                        '$tipo' as tipo_atividade,
                        rta.id as resolucao_tipo_atividade_id
                    FROM atividades_por_resolucao apr
                    JOIN resolucao_tipo_atividade rta 
                        ON rta.resolucao_id = apr.resolucao_id 
                        AND rta.tipo_atividade_id = apr.tipo_atividade_id
                    JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    WHERE rta.id = ? 
                    AND ta.nome = '$nomeAtividade' $condicaoBusca
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";

            error_log("SQL Main Query ($tipo): " . $sql);

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Falha ao preparar consulta de pendentes: " . $conn->error);
                return [
                    'atividades' => [],
                    'paginacao' => [
                        'pagina_atual' => $pagina,
                        'total_paginas' => 0,
                        'total_registros' => 0,
                        'limite' => $limite,
                        'tem_proxima' => false,
                        'tem_anterior' => false
                    ]
                ];
            }

            // Adicionar parâmetros de paginação
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= 'ii';

            $stmt->bind_param($tipos, ...$parametros);

            if (!$stmt->execute()) {
                error_log("Falha ao executar consulta de pendentes: " . $stmt->error);
                return [
                    'atividades' => [],
                    'paginacao' => [
                        'pagina_atual' => $pagina,
                        'total_paginas' => 0,
                        'total_registros' => 0,
                        'limite' => $limite,
                        'tem_proxima' => false,
                        'tem_anterior' => false
                    ]
                ];
            }

            $result = $stmt->get_result();
            $atividades = [];

            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'tipo' => $row['tipo'],
                    'horas_max' => (int)$row['horas_max'],
                    'tipo_atividade' => $row['tipo_atividade'],
                    'resolucao_tipo_atividade_id' => (int)$row['resolucao_tipo_atividade_id']
                ];
            }

            $stmt->close();

            // Calcular metadados de paginação
            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;

            error_log("ListarAtividadesDisponiveisModel::listarPorTipoEResolucao - Encontradas " . count($atividades) . " atividades de $tipo (página $pagina de $totalPaginas) para resolução " . $resolucaoTipoAtividadeId);

            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarPorTipoEResolucao: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista atividades por tipo sem filtrar por resolução/curso (BCC/BSI)
     * @param string $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa, acao_social, pet)
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'nome')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'ASC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarPorTipoSemFiltro($tipo, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '')
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            if (!isset(self::TIPOS_ATIVIDADES[$tipo])) {
                throw new Exception("Tipo de atividade inválido: $tipo");
            }
            $nomeAtividade = self::TIPOS_ATIVIDADES[$tipo];

            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;

            $camposOrdenacao = [
                'nome' => 'ac.titulo',
                'categoria' => 'ta.nome',
                'carga_horaria_maxima' => 'carga_horaria_maxima'
            ];
            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ac.titulo';
            $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';

            $condicaoBusca = '';
            $parametros = [];
            $tiposBind = '';

            // Condição por ID estável do tipo_atividade para evitar problemas de acentuação
            // Para 'extracurriculares', também aceitar variações por nome
            $condicaoTipo = '';
            $tipoId = self::TIPOS_ATIVIDADES_ID[$tipo] ?? null;
            if ($tipo === 'extracurriculares') {
                $condicaoTipo = "(ta.id = ? OR LOWER(ta.nome) = LOWER(?) OR LOWER(ta.nome) LIKE ? OR LOWER(ta.nome) LIKE ?)";
                $parametros[] = $tipoId;                  // id 3
                $parametros[] = $nomeAtividade;           // 'Atividades extracurriculares'
                $parametros[] = '%extracurricular%';       // variações contendo 'extracurricular'
                $parametros[] = '%extens%';               // variações contendo 'extens' (extensão/extensao)
                $tiposBind .= 'isss';
            } else {
                $condicaoTipo = "ta.id = ?";
                $parametros[] = $tipoId;
                $tiposBind .= 'i';
            }

            // Logs de debug
            error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - Tipo: $tipo | Nome mapeado: $nomeAtividade");
            error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - Condição tipo: " . $condicaoTipo);

            if (!empty($busca)) {
                $condicaoBusca = " AND (ac.titulo LIKE ? OR ac.descricao LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tiposBind .= 'ss';
            }

            error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - Condição busca: " . ($condicaoBusca ?: 'SEM BUSCA'));
            error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - Parâmetros: " . json_encode($parametros));

            // Count
            // Para extracurriculares, deduplicar por título para evitar entradas duplicadas entre cursos
            if ($tipo === 'extracurriculares') {
                $sqlCount = "SELECT COUNT(*) AS total
                             FROM (
                                SELECT ac.titulo
                                FROM atividades_complementares ac
                                JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                                LEFT JOIN atividades_por_resolucao apr ON apr.atividades_complementares_id = ac.id
                                WHERE $condicaoTipo $condicaoBusca
                                GROUP BY ac.titulo
                             ) t";
            } else {
                $sqlCount = "SELECT COUNT(*) AS total
                             FROM (
                                SELECT ac.id
                                FROM atividades_complementares ac
                                JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                                LEFT JOIN atividades_por_resolucao apr ON apr.atividades_complementares_id = ac.id
                                WHERE $condicaoTipo $condicaoBusca
                                GROUP BY ac.id
                             ) t";
            }

            error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - SQL COUNT: " . $sqlCount);

            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }
            $stmtCount->bind_param($tiposBind, ...$parametros);
            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }
            $resultCount = $stmtCount->get_result();
            $rowCount = $resultCount->fetch_assoc();
            $total = $rowCount && isset($rowCount['total']) ? (int)$rowCount['total'] : 0;
            $stmtCount->close();

            error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - Total encontrados: " . $total);

            // Main
            // Para extracurriculares, agrupar por título para remover duplicações entre cursos (ex.: BCC/BSI)
            if ($tipo === 'extracurriculares') {
                $sql = "SELECT 
                            MAX(apr.id) AS atividades_por_resolucao_id,
                            MIN(ac.id) AS atividade_complementar_id,
                            ac.titulo AS nome,
                            MAX(ac.descricao) AS descricao,
                            MAX(ac.observacoes) AS observacoes,
                            ta.nome AS categoria,
                            COALESCE(MAX(apr.carga_horaria_maxima_por_atividade), 0) AS carga_horaria_maxima,
                            ta.nome AS tipo,
                            COALESCE(MAX(apr.carga_horaria_maxima_por_atividade), 0) AS horas_max
                        FROM atividades_complementares ac
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        LEFT JOIN atividades_por_resolucao apr ON apr.atividades_complementares_id = ac.id
                        WHERE $condicaoTipo $condicaoBusca
                        GROUP BY ac.titulo, ta.nome
                        ORDER BY $campoOrdenacao $direcao
                        LIMIT ? OFFSET ?";
            } else {
                $sql = "SELECT 
                            MAX(apr.id) AS atividades_por_resolucao_id,
                            ac.id AS atividade_complementar_id,
                            ac.titulo AS nome,
                            ac.descricao,
                            ac.observacoes,
                            ta.nome AS categoria,
                            COALESCE(MAX(apr.carga_horaria_maxima_por_atividade), 0) AS carga_horaria_maxima,
                            ta.nome AS tipo,
                            COALESCE(MAX(apr.carga_horaria_maxima_por_atividade), 0) AS horas_max
                        FROM atividades_complementares ac
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        LEFT JOIN atividades_por_resolucao apr ON apr.atividades_complementares_id = ac.id
                        WHERE $condicaoTipo $condicaoBusca
                        GROUP BY ac.id, ac.titulo, ac.descricao, ac.observacoes, ta.nome
                        ORDER BY $campoOrdenacao $direcao
                        LIMIT ? OFFSET ?";
            }

            error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - SQL MAIN: " . $sql);

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }

            $paramExec = $parametros;
            $tiposExec = $tiposBind;
            $paramExec[] = $limite;
            $paramExec[] = $offset;
            $tiposExec .= 'ii';

            $stmt->bind_param($tiposExec, ...$paramExec);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                // Mapear tipo_atividade chave
                $tipoChave = array_search($row['tipo'], self::TIPOS_ATIVIDADES) ?: 'outros';
                $atividades[] = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'tipo' => $row['tipo'],
                    'horas_max' => (int)$row['horas_max'],
                    'tipo_atividade' => $tipoChave
                ];
            }
            $stmt->close();

            error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - Registros retornados: " . count($atividades));
            if (!empty($atividades)) {
                $preview = array_slice($atividades, 0, 3);
                error_log("ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro - Amostra: " . json_encode($preview));
            }

            $totalPaginas = $limite > 0 ? (int)ceil($total / $limite) : 0;
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;

            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista atividades por tipo filtrando por um resolucao_id específico
     * @param string $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa)
     * @param int $resolucaoId ID da resolução (ex.: 1 para BCC 2017-2022)
     * @param int $pagina Página atual
     * @param int $limite Limite por página
     * @param string $ordenacao Campo de ordenação
     * @param string $direcao Direção de ordenação
     * @param string $busca Termo de busca
     * @return array
     */
    public static function listarPorTipoComResolucaoId($tipo, $resolucaoId, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '')
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            if (!isset(self::TIPOS_ATIVIDADES[$tipo])) {
                throw new Exception("Tipo de atividade inválido: $tipo");
            }
            $nomeAtividade = self::TIPOS_ATIVIDADES[$tipo];
            $tipoId = self::TIPOS_ATIVIDADES_ID[$tipo] ?? null;
            if ($tipoId === null) {
                throw new Exception("ID de tipo de atividade não mapeado para: $tipo");
            }

            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;

            // Quando consolidado por tipo, usar aliases agregados para ordenação
            $camposOrdenacao = [
                'nome' => 'nome',
                'categoria' => 'ta.nome',
                'carga_horaria_maxima' => 'carga_horaria_maxima'
            ];
            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'nome';
            $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';

            $condicaoBusca = '';
            $parametros = [$resolucaoId, $tipoId];
            $tiposBind = 'ii';
            if (!empty($busca)) {
                $condicaoBusca = " AND (ac.titulo LIKE ? OR ac.descricao LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tiposBind .= 'ss';
            }

            // Filtro extra para evitar duplicação de estágios no BCC 2017–2022 (resolução_id = 1)
            // Mapeamento: rta.id=4 corresponde à resolução BCC17 para Estágio
            $filtroExtraApr = '';
            if ($tipo === 'estagio' && (int)$resolucaoId === 4) {
                // Garantir que apenas a atividade (1, 4, 16, 100) apareça
                $filtroExtraApr = ' AND apr.atividades_complementares_id = 16 AND apr.carga_horaria_maxima_por_atividade = 100';
            }

            // Definir consultas de COUNT e MAIN conforme caso especial (Estágio BCC17) ou padrão
            $sqlCount = '';
            $sql = '';

            $isCasoEspecialEstagioBCC17 = ($tipo === 'estagio' && (int)$resolucaoId === 4);
            $isDedupAcaoSocialBCC17 = ($tipo === 'acao_social' && (int)$resolucaoId === 5);

            if ($isCasoEspecialEstagioBCC17) {
                // COUNT consolidando por tipo (evita duplicadas no BCC17 estágio)
                $sqlCount = "SELECT COUNT(*) AS total
                             FROM (
                                SELECT ta_apr.id AS tipo_atividade_id
                                FROM atividades_por_resolucao apr
                                JOIN resolucao_tipo_atividade rta 
                                  ON rta.resolucao_id = apr.resolucao_id 
                                 AND rta.tipo_atividade_id = apr.tipo_atividade_id
                                JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                                JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                                JOIN tipo_atividade ta_apr ON apr.tipo_atividade_id = ta_apr.id
                                WHERE rta.id = ? 
                                  AND ta_apr.id = ? 
                                  $condicaoBusca $filtroExtraApr
                                GROUP BY ta_apr.id
                             ) t";
            } else if ($isDedupAcaoSocialBCC17) {
                // COUNT deduplicando por título para Ação Social BCC17
                $sqlCount = "SELECT COUNT(*) AS total
                             FROM (
                                SELECT ac.titulo
                                FROM atividades_por_resolucao apr
                                JOIN resolucao_tipo_atividade rta 
                                  ON rta.resolucao_id = apr.resolucao_id 
                                 AND rta.tipo_atividade_id = apr.tipo_atividade_id
                                JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                                JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                                JOIN tipo_atividade ta_apr ON apr.tipo_atividade_id = ta_apr.id
                                WHERE rta.id = ? 
                                  AND ta_apr.id = ? 
                                  $condicaoBusca
                                GROUP BY ac.titulo
                             ) t";
            } else {
                // COUNT padrão: contar atividades distintas dentro do rta.id e tipo
                $sqlCount = "SELECT COUNT(*) AS total
                             FROM (
                                SELECT apr.id
                                FROM atividades_por_resolucao apr
                                JOIN resolucao_tipo_atividade rta 
                                  ON rta.resolucao_id = apr.resolucao_id 
                                 AND rta.tipo_atividade_id = apr.tipo_atividade_id
                                JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                                JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                                JOIN tipo_atividade ta_apr ON apr.tipo_atividade_id = ta_apr.id
                                WHERE rta.id = ? 
                                  AND ta_apr.id = ? 
                                  $condicaoBusca
                                GROUP BY apr.id
                             ) t";
            }

            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }
            $stmtCount->bind_param($tiposBind, ...$parametros);
            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }
            $resultCount = $stmtCount->get_result();
            $rowCount = $resultCount->fetch_assoc();
            $total = $rowCount && isset($rowCount['total']) ? (int)$rowCount['total'] : 0;
            $stmtCount->close();

            if ($isCasoEspecialEstagioBCC17) {
                // MAIN consolidado por tipo para evitar duplicatas (ex.: dois estágios para a mesma resolução)
                $sql = "SELECT 
                            MIN(apr.id) AS atividades_por_resolucao_id,
                            MIN(ac.id) AS atividade_complementar_id,
                            MIN(ac.titulo) AS nome,
                            MIN(ac.descricao) AS descricao,
                            MIN(ac.observacoes) AS observacoes,
                            ta.nome AS categoria,
                            MIN(apr.carga_horaria_maxima_por_atividade) AS carga_horaria_maxima,
                            ta_apr.nome AS tipo,
                            MIN(apr.carga_horaria_maxima_por_atividade) AS horas_max
                        FROM atividades_por_resolucao apr
                        JOIN resolucao_tipo_atividade rta 
                          ON rta.resolucao_id = apr.resolucao_id 
                         AND rta.tipo_atividade_id = apr.tipo_atividade_id
                        JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        JOIN tipo_atividade ta_apr ON apr.tipo_atividade_id = ta_apr.id
                        WHERE rta.id = ? 
                          AND ta_apr.id = ? 
                          $condicaoBusca $filtroExtraApr
                        GROUP BY ta_apr.id, ta.nome
                        ORDER BY $campoOrdenacao $direcao
                        LIMIT ? OFFSET ?";
            } else if ($isDedupAcaoSocialBCC17) {
                // MAIN deduplicado por título para Ação Social BCC17
                $sql = "SELECT 
                            MIN(apr.id) AS atividades_por_resolucao_id,
                            MIN(ac.id) AS atividade_complementar_id,
                            ac.titulo AS nome,
                            MIN(ac.descricao) AS descricao,
                            MIN(ac.observacoes) AS observacoes,
                            ta.nome AS categoria,
                            MIN(apr.carga_horaria_maxima_por_atividade) AS carga_horaria_maxima,
                            ta_apr.nome AS tipo,
                            MIN(apr.carga_horaria_maxima_por_atividade) AS horas_max
                        FROM atividades_por_resolucao apr
                        JOIN resolucao_tipo_atividade rta 
                          ON rta.resolucao_id = apr.resolucao_id 
                         AND rta.tipo_atividade_id = apr.tipo_atividade_id
                        JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        JOIN tipo_atividade ta_apr ON apr.tipo_atividade_id = ta_apr.id
                        WHERE rta.id = ? 
                          AND ta_apr.id = ? 
                          $condicaoBusca
                        GROUP BY ac.titulo, ta_apr.id, ta.nome
                        ORDER BY $campoOrdenacao $direcao
                        LIMIT ? OFFSET ?";
            } else {
                // MAIN padrão: listar cada atividade normalmente
                $sql = "SELECT 
                            apr.id AS atividades_por_resolucao_id,
                            ac.id AS atividade_complementar_id,
                            ac.titulo AS nome,
                            ac.descricao AS descricao,
                            ac.observacoes AS observacoes,
                            ta.nome AS categoria,
                            apr.carga_horaria_maxima_por_atividade AS carga_horaria_maxima,
                            ta_apr.nome AS tipo,
                            apr.carga_horaria_maxima_por_atividade AS horas_max
                        FROM atividades_por_resolucao apr
                        JOIN resolucao_tipo_atividade rta 
                          ON rta.resolucao_id = apr.resolucao_id 
                         AND rta.tipo_atividade_id = apr.tipo_atividade_id
                        JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        JOIN tipo_atividade ta_apr ON apr.tipo_atividade_id = ta_apr.id
                        WHERE rta.id = ? 
                          AND ta_apr.id = ? 
                          $condicaoBusca
                        ORDER BY $campoOrdenacao $direcao
                        LIMIT ? OFFSET ?";
            }

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }

            $paramExec = $parametros;
            $tiposExec = $tiposBind;
            $paramExec[] = $limite;
            $paramExec[] = $offset;
            $tiposExec .= 'ii';

            $stmt->bind_param($tiposExec, ...$paramExec);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                // Mapear tipo_atividade via ID conhecido
                $inverseTipoId = array_flip(self::TIPOS_ATIVIDADES_ID);
                $tipoChave = $inverseTipoId[$tipoId] ?? $tipo;
                $atividades[] = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'tipo' => $nomeAtividade,
                    'horas_max' => (int)$row['horas_max'],
                    'tipo_atividade' => $tipoChave
                ];
            }
            $stmt->close();

            $totalPaginas = $limite > 0 ? (int)ceil($total / $limite) : 0;
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;

            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarPorTipoComResolucaoId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista todas as atividades disponíveis (todos os tipos) por resolução
     * @param int $resolucaoTipoAtividadeId ID da resolução
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'nome')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'ASC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarTodasPorResolucao($resolucaoTipoAtividadeId, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '')
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;

            // Mapear campos de ordenação
            $camposOrdenacao = [
                'nome' => 'ac.titulo',
                'categoria' => 'ta.nome',
                'carga_horaria_maxima' => 'apr.carga_horaria_maxima_por_atividade'
            ];

            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ac.titulo';
            $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';

            // Construir condição de busca
            $condicaoBusca = '';
            $parametros = [$resolucaoTipoAtividadeId];
            $tipos = 'i';

            if (!empty($busca)) {
                $condicaoBusca = " AND (ac.titulo LIKE ? OR ac.descricao LIKE ? OR ta.nome LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tipos .= 'sss';
            }

            // Lista dos tipos de atividades válidos
            $tiposValidos = "'" . implode("', '", array_values(self::TIPOS_ATIVIDADES)) . "'";

            // Query para contar total de registros
            $sqlCount = "SELECT COUNT(*) as total
                        FROM atividades_por_resolucao apr
                        JOIN resolucao_tipo_atividade rta 
                            ON rta.resolucao_id = apr.resolucao_id 
                            AND rta.tipo_atividade_id = apr.tipo_atividade_id
                        JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        WHERE rta.id = ? 
                        AND ta.nome IN ($tiposValidos) $condicaoBusca";

            error_log("SQL Count Query (Todas): " . $sqlCount);
            error_log("Parameters: " . json_encode($parametros));

            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }

            $stmtCount->bind_param($tipos, ...$parametros);

            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }

            $resultCount = $stmtCount->get_result();
            $total = $resultCount->fetch_assoc()['total'];
            $stmtCount->close();

            // Query principal com paginação
            $sql = "SELECT 
                        apr.id as atividades_por_resolucao_id,
                        ac.id as atividade_complementar_id,
                        ac.titulo as nome,
                        ac.descricao,
                        ac.observacoes,
                        ta.nome as categoria,
                        apr.carga_horaria_maxima_por_atividade as carga_horaria_maxima,
                        ta.nome as tipo,
                        apr.carga_horaria_maxima_por_atividade as horas_max,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            WHEN ta.nome = 'PET' THEN 'pet'
                            WHEN ta.nome = 'Atividades sociais e comunitárias' THEN 'acao_social'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividades_por_resolucao apr
                    JOIN resolucao_tipo_atividade rta 
                        ON rta.resolucao_id = apr.resolucao_id 
                        AND rta.tipo_atividade_id = apr.tipo_atividade_id
                    JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    WHERE rta.id = ? 
                    AND ta.nome IN ($tiposValidos) $condicaoBusca
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";

            error_log("SQL Main Query (Todas): " . $sql);

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }

            // Adicionar parâmetros de paginação
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= 'ii';

            $stmt->bind_param($tipos, ...$parametros);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $atividades = [];

            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'tipo' => $row['tipo'],
                    'horas_max' => (int)$row['horas_max'],
                    'resolucao_tipo_atividade_id' => (int)$row['resolucao_tipo_atividade_id'],
                    'tipo_atividade' => $row['tipo_atividade']
                ];
            }

            $stmt->close();

            // Calcular metadados de paginação
            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;

            error_log("ListarAtividadesDisponiveisModel::listarTodasPorResolucao - Encontradas " . count($atividades) . " atividades (página $pagina de $totalPaginas) para resolução " . $resolucaoTipoAtividadeId);

            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarTodasPorResolucao: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista todas as atividades disponíveis sem filtrar por resolução (todas as categorias para todos os cursos)
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'nome')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'ASC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarTodasSemFiltro($pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '')
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;

            $camposOrdenacao = [
                'nome' => 'ac.titulo',
                'categoria' => 'ta.nome',
                'carga_horaria_maxima' => 'carga_horaria_maxima'
            ];
            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ac.titulo';
            $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';

            $condicaoBusca = '';
            $parametros = [];
            $tipos = '';
            if (!empty($busca)) {
                $condicaoBusca = " AND (ac.titulo LIKE ? OR ac.descricao LIKE ? OR ta.nome LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tipos .= 'sss';
            }

            $tiposValidos = "'" . implode("', '", array_values(self::TIPOS_ATIVIDADES)) . "'";

            $sqlCount = "SELECT COUNT(*) AS total
                        FROM (
                            SELECT ac.id
                            FROM atividades_complementares ac
                            JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                            LEFT JOIN atividades_por_resolucao apr ON apr.atividades_complementares_id = ac.id
                            WHERE ta.nome IN ($tiposValidos) $condicaoBusca
                            GROUP BY ac.id
                        ) t";

            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }
            if ($tipos) {
                $stmtCount->bind_param($tipos, ...$parametros);
            }
            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }
            $resultCount = $stmtCount->get_result();
            $total = (int)$resultCount->fetch_assoc()['total'];
            $stmtCount->close();

            $sql = "SELECT 
                        MAX(apr.id) as atividades_por_resolucao_id,
                        ac.id as atividade_complementar_id,
                        ac.titulo as nome,
                        ac.descricao,
                        ac.observacoes,
                        ta.nome as categoria,
                        COALESCE(MAX(apr.carga_horaria_maxima_por_atividade), 0) as carga_horaria_maxima,
                        ta.nome as tipo,
                        COALESCE(MAX(apr.carga_horaria_maxima_por_atividade), 0) as horas_max,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            WHEN ta.nome = 'PET' THEN 'pet'
                            WHEN ta.nome = 'Atividades sociais e comunitárias' THEN 'acao_social'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividades_complementares ac
                    JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    LEFT JOIN atividades_por_resolucao apr ON apr.atividades_complementares_id = ac.id
                    WHERE ta.nome IN ($tiposValidos) $condicaoBusca
                    GROUP BY ac.id, ac.titulo, ac.descricao, ac.observacoes, ta.nome
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }

            $parametrosExec = $parametros;
            $tiposExec = $tipos;
            $parametrosExec[] = $limite;
            $parametrosExec[] = $offset;
            $tiposExec .= 'ii';

            $stmt->bind_param($tiposExec, ...$parametrosExec);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'tipo' => $row['tipo'],
                    'horas_max' => (int)$row['horas_max'],
                    'tipo_atividade' => $row['tipo_atividade']
                ];
            }
            $stmt->close();

            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;

            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarTodasSemFiltro: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca uma atividade específica por ID
     * @param int $id ID da atividade
     * @return array|null Dados da atividade ou null se não encontrada
     */
    public static function buscarPorId($id)
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            // Lista dos tipos de atividades válidos
            $tiposValidos = "'" . implode("', '", array_values(self::TIPOS_ATIVIDADES)) . "'";

            $sql = "SELECT 
                        apr.id as atividades_por_resolucao_id,
                        ac.id as atividade_complementar_id,
                        ac.titulo as nome,
                        ac.descricao,
                        ac.observacoes,
                        ta.nome as categoria,
                        apr.carga_horaria_maxima_por_atividade as carga_horaria_maxima,
                        rta.id as resolucao_tipo_atividade_id,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            WHEN ta.nome = 'PET' THEN 'pet'
                            WHEN ta.nome = 'Atividades sociais e comunitárias' THEN 'acao_social'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividades_complementares ac
                    JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    JOIN atividades_por_resolucao apr ON apr.atividades_complementares_id = ac.id
                    JOIN resolucao_tipo_atividade rta 
                        ON rta.resolucao_id = apr.resolucao_id 
                        AND rta.tipo_atividade_id = apr.tipo_atividade_id
                    WHERE ac.id = ? AND ta.nome IN ($tiposValidos)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }

            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $atividade = null;

            if ($row = $result->fetch_assoc()) {
                $atividade = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'resolucao_tipo_atividade_id' => (int)$row['resolucao_tipo_atividade_id'],
                    'tipo_atividade' => $row['tipo_atividade']
                ];
            }

            $stmt->close();

            error_log("ListarAtividadesDisponiveisModel::buscarPorId - Atividade ID $id: " . ($atividade ? 'encontrada' : 'não encontrada'));

            return $atividade;
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Determina a resolução baseada na matrícula do aluno e tipo de atividade
     * @param string $matricula Matrícula do aluno
     * @param string $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa)
     * @return int|null ID da resolução ou null se não encontrada
     */
    public static function determinarResolucaoPorMatricula($matricula, $tipo = null)
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            // Tentar identificar curso pelo número de matrícula
            $cursoCodigo = null;
            $cursoNome = null;
            try {
                $stmtCurso = $conn->prepare("SELECT c.codigo, c.nome FROM Aluno a LEFT JOIN Curso c ON a.curso_id = c.id WHERE a.matricula = ?");
                $stmtCurso->bind_param("s", $matricula);
                $stmtCurso->execute();
                $resCurso = $stmtCurso->get_result();
                if ($resCurso && $resCurso->num_rows > 0) {
                    $rowCurso = $resCurso->fetch_assoc();
                    $cursoCodigo = $rowCurso['codigo'];
                    $cursoNome = $rowCurso['nome'];
                    error_log("Curso detectado pela matrícula: codigo=" . ($cursoCodigo ?? 'null') . ", nome=" . ($cursoNome ?? 'null'));
                }
                $stmtCurso->close();
            } catch (Exception $e) {
                error_log("Falha ao identificar curso pela matrícula: " . $e->getMessage());
            }

            // Extrair ano da matrícula (primeiros 4 dígitos)
            $ano = (int)substr($matricula, 0, 4);

            error_log("ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula - Matrícula: $matricula, Ano extraído: $ano, Tipo: $tipo");

            // Se o curso for Sistemas de Informação (SI), usar resoluções da SI18 (resolução_id = 3)
            $isSI = false;
            if ($cursoCodigo) {
                $isSI = strtoupper($cursoCodigo) === 'SI';
            } elseif ($cursoNome) {
                $isSI = stripos($cursoNome, 'Sistemas de Informação') !== false;
            }

            if ($isSI) {
                // Consulta dinâmica ao resolucao_tipo_atividade para resolução 3 (SI18)
                if ($tipo) {
                    if (!isset(self::TIPOS_ATIVIDADES[$tipo])) {
                        error_log("Tipo de atividade inválido para SI: $tipo");
                        return null;
                    }
                    $nomeAtividade = self::TIPOS_ATIVIDADES[$tipo];
                    try {
                        $sql = "SELECT rta.id FROM resolucao_tipo_atividade rta JOIN tipo_atividade ta ON ta.id = rta.tipo_atividade_id WHERE rta.resolucao_id = 3 AND ta.nome = ? LIMIT 1";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $nomeAtividade);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        if ($res && $res->num_rows > 0) {
                            $row = $res->fetch_assoc();
                            $rtaId = (int)$row['id'];
                            error_log("Resolução SI18 para tipo '$tipo' encontrada: rta.id=$rtaId");
                            return $rtaId;
                        } else {
                            error_log("Nenhum rta.id encontrado para SI18 e tipo '$tipo' (nomeAtividade='$nomeAtividade')");
                            return null;
                        }
                    } catch (Exception $e) {
                        error_log("Erro ao buscar rta.id para SI18 e tipo '$tipo': " . $e->getMessage());
                        return null;
                    }
                }

                // Sem tipo específico: retornar mapa de todos os tipos válidos para SI
                try {
                    $sql = "SELECT rta.id, ta.nome FROM resolucao_tipo_atividade rta JOIN tipo_atividade ta ON ta.id = rta.tipo_atividade_id WHERE rta.resolucao_id = 3";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $map = [];
                    // Construir inverso de TIPOS_ATIVIDADES ('nome' => 'chave')
                    $inverse = array_flip(self::TIPOS_ATIVIDADES);
                    while ($row = $res->fetch_assoc()) {
                        $nome = $row['nome'];
                        if (isset($inverse[$nome])) {
                            $map[$inverse[$nome]] = (int)$row['id'];
                        }
                    }
                    $stmt->close();
                    error_log("Mapa de resoluções SI18 por tipo: " . json_encode($map));
                    return $map;
                } catch (Exception $e) {
                    error_log("Erro ao listar rta.ids para SI18: " . $e->getMessage());
                    return null;
                }
            }

            // Caso não seja SI: Determinar período baseado no ano (BCC)
            $periodo = ($ano >= 2017 && $ano <= 2022) ? '2017-2022' : '2023+';

            // Se tipo específico foi fornecido, retornar resolução específica
            if ($tipo && isset(self::RESOLUCOES_POR_TIPO[$tipo][$periodo])) {
                $resolucaoId = self::RESOLUCOES_POR_TIPO[$tipo][$periodo];
                error_log("ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula - Resolução específica para $tipo ($periodo): $resolucaoId");
                return $resolucaoId;
            }

            // Se não foi especificado tipo, retornar array com todas as resoluções do período
            $resolucoes = [];
            foreach (self::RESOLUCOES_POR_TIPO as $tipoAtiv => $periodos) {
                if (isset($periodos[$periodo])) {
                    $resolucoes[$tipoAtiv] = $periodos[$periodo];
                }
            }

            error_log("ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula - Resoluções (BCC) para período $periodo: " . json_encode($resolucoes));

            return $resolucoes;
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtém informações de resoluções por tipo para a matrícula fornecida
     * @param string $matricula Matrícula do aluno
     * @return array|null Mapa de resoluções por tipo e metadados ou null em caso de falha
     */
    public static function obterInfoResolucoes($matricula)
    {
        try {
            if (!$matricula || !is_string($matricula)) {
                error_log("ListarAtividadesDisponiveisModel::obterInfoResolucoes - Matrícula inválida");
                return null;
            }

            // Reutiliza a lógica de determinação de resoluções baseada na matrícula
            $resolucoes = self::determinarResolucaoPorMatricula($matricula);

            if (!$resolucoes || !is_array($resolucoes) || empty($resolucoes)) {
                error_log("ListarAtividadesDisponiveisModel::obterInfoResolucoes - Não foi possível determinar resoluções para a matrícula $matricula");
                return null;
            }

            $ano = (int)substr($matricula, 0, 4);
            $tiposDisponiveis = array_keys($resolucoes);

            $info = [
                'matricula' => $matricula,
                'ano_matricula' => $ano,
                'tipos_disponiveis' => $tiposDisponiveis,
                'resolucoes_por_tipo' => $resolucoes
            ];

            error_log("ListarAtividadesDisponiveisModel::obterInfoResolucoes - Info: " . json_encode($info));
            return $info;
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::obterInfoResolucoes: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valida se um tipo de atividade é válido
     * @param string $tipo Tipo de atividade
     * @return bool True se válido, false caso contrário
     */
    public static function tipoAtividadeValido($tipo)
    {
        return isset(self::TIPOS_ATIVIDADES[$tipo]);
    }

    /**
     * Obtém todos os tipos de atividades disponíveis
     * @return array Array com os tipos de atividades
     */
    public static function obterTiposAtividades()
    {
        return array_keys(self::TIPOS_ATIVIDADES);
    }

    /**
     * Lista atividades enviadas por um aluno específico com paginação e filtros
     * @param int $aluno_id ID do aluno
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'id')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'DESC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarAtividadesEnviadasPorAluno($aluno_id, $pagina = 1, $limite = 20, $ordenacao = 'id', $direcao = 'DESC', $busca = '')
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            // Garantir que a coluna data_submissao existe
            try {
                $checkCol = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'atividade_enviada' AND COLUMN_NAME = 'data_submissao'");
                if ($checkCol) {
                    $rowCol = $checkCol->fetch_assoc();
                    if ((int)$rowCol['cnt'] === 0) {
                        $conn->query("ALTER TABLE atividade_enviada ADD COLUMN data_submissao datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
                    }
                }
            } catch (\Exception $e) {
                error_log("[WARN] Falha ao garantir coluna data_submissao: " . $e->getMessage());
            }

            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;

            // Mapear campos de ordenação
            $camposOrdenacao = [
                'id' => 'ae.id',
                'titulo' => 'ae.titulo',
                'data_avaliacao' => 'ae.data_avaliacao',
                'data_submissao' => 'ae.data_submissao',
                'status' => 'ae.status',
                'ch_solicitada' => 'ae.ch_solicitada',
                'ch_atribuida' => 'ae.ch_atribuida',
                'categoria' => 'ta.nome'
            ];

            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ae.id';
            $direcao = strtoupper($direcao) === 'ASC' ? 'ASC' : 'DESC';

            // Construir condição de busca
            $condicaoBusca = '';
            $parametros = [$aluno_id];
            $tipos = 'i';

            if (!empty($busca)) {
                $condicaoBusca = " AND (ae.titulo LIKE ? OR ae.descricao LIKE ? OR ac.titulo LIKE ? OR ta.nome LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tipos .= 'ssss';
            }

            // Query para contar total de registros
            $sqlCount = "SELECT COUNT(DISTINCT ae.id) as total
                        FROM atividade_enviada ae
                        LEFT JOIN atividades_por_resolucao apr 
                            ON apr.resolucao_id = ae.resolucao_id
                            AND apr.tipo_atividade_id = ae.tipo_atividade_id
                            AND apr.atividades_complementares_id = ae.atividades_complementares_id
                        LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        WHERE ae.aluno_id = ? $condicaoBusca";

            error_log("SQL Count Query (Atividades Enviadas): " . $sqlCount);
            error_log("Parameters: " . json_encode($parametros));

            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }

            $stmtCount->bind_param($tipos, ...$parametros);

            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }

            $resultCount = $stmtCount->get_result();
            $total = $resultCount->fetch_assoc()['total'];
            $stmtCount->close();

            // Query principal com paginação
            $sql = "SELECT 
                        ae.id,
                        ae.titulo,
                        ae.descricao,
                        ae.ch_solicitada,
                        ae.ch_atribuida,
                        ae.status,
                        ae.data_avaliacao,
                        ae.data_submissao,
                        ae.caminho_declaracao,
                        ac.titulo as atividade_titulo,
                        ta.nome as categoria_nome,
                        apr.id as atividades_por_resolucao_id,
                        apr.carga_horaria_maxima_por_atividade as carga_horaria_maxima_por_atividade,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            WHEN ta.nome = 'PET' THEN 'pet'
                            WHEN ta.nome = 'Atividades sociais e comunitárias' THEN 'acao_social'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividade_enviada ae
                    LEFT JOIN atividades_por_resolucao apr 
                        ON apr.resolucao_id = ae.resolucao_id
                        AND apr.tipo_atividade_id = ae.tipo_atividade_id
                        AND apr.atividades_complementares_id = ae.atividades_complementares_id
                    LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    WHERE ae.aluno_id = ? $condicaoBusca
                    GROUP BY ae.id
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";

            error_log("SQL Main Query (Atividades Enviadas): " . $sql);

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }

            // Adicionar parâmetros de paginação
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= 'ii';

            $stmt->bind_param($tipos, ...$parametros);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $atividades = [];

                while ($row = $result->fetch_assoc()) {
                    $atividades[] = [
                        'id' => (int)$row['id'],
                        'titulo' => $row['titulo'],
                        'descricao' => $row['descricao'],
                        'ch_solicitada' => (int)$row['ch_solicitada'],
                        'ch_atribuida' => $row['ch_atribuida'] ? (int)$row['ch_atribuida'] : null,
                        'status' => $row['status'],
                        'data_avaliacao' => $row['data_avaliacao'],
                        'data_submissao' => $row['data_submissao'],
                        'caminho_declaracao' => $row['caminho_declaracao'],
                        'atividade_titulo' => $row['atividade_titulo'],
                        'categoria_nome' => $row['categoria_nome'],
                        'atividades_por_resolucao_id' => (int)$row['atividades_por_resolucao_id'],
                        'carga_horaria_maxima_por_atividade' => isset($row['carga_horaria_maxima_por_atividade']) ? (int)$row['carga_horaria_maxima_por_atividade'] : null,
                        'tipo_atividade' => $row['tipo_atividade']
                    ];
                }

            $stmt->close();

            // Calcular metadados de paginação
            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;

            error_log("ListarAtividadesDisponiveisModel::listarAtividadesEnviadasPorAluno - Encontradas " . count($atividades) . " atividades (página $pagina de $totalPaginas) para aluno " . $aluno_id);

            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarAtividadesEnviadasPorAluno: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista todas as atividades enviadas por todos os alunos (para coordenadores)
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'id')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'DESC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarTodasAtividadesEnviadas($pagina = 1, $limite = 20, $ordenacao = 'id', $direcao = 'DESC', $busca = '', $coordenadorId = null)
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            // Garantir que a coluna data_submissao existe
            try {
                $checkCol = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'atividade_enviada' AND COLUMN_NAME = 'data_submissao'");
                if ($checkCol) {
                    $rowCol = $checkCol->fetch_assoc();
                    if ((int)$rowCol['cnt'] === 0) {
                        $conn->query("ALTER TABLE atividade_enviada ADD COLUMN data_submissao datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
                    }
                }
            } catch (\Exception $e) {
                error_log("[WARN] Falha ao garantir coluna data_submissao: " . $e->getMessage());
            }

            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;

            // Mapear campos de ordenação
            $camposOrdenacao = [
                'id' => 'id_agrupado',
                'titulo' => 'ae.titulo',
                'data_avaliacao' => 'ae.data_avaliacao',
                'status' => 'ae.status',
                'ch_solicitada' => 'ae.ch_solicitada',
                'ch_atribuida' => 'ae.ch_atribuida',
                'categoria' => 'ta.nome',
                'aluno_nome' => 'u.nome',
                'data_submissao' => 'data_submissao_agrupada'
            ];

            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ae.id';
            $direcao = strtoupper($direcao) === 'ASC' ? 'ASC' : 'DESC';

            // Construir condição de busca - SEMPRE filtrar apenas atividades pendentes
            $condicaoBusca = "WHERE ae.status = 'Aguardando avaliação'";
            $parametros = [];
            $tipos = '';

            // Se for coordenador, filtrar por curso do coordenador
            $cursoIdCoordenador = null;
            if (!empty($coordenadorId)) {
                $stmtCurso = $conn->prepare("SELECT curso_id FROM coordenador WHERE usuario_id = ? LIMIT 1");
                if ($stmtCurso) {
                    $stmtCurso->bind_param("i", $coordenadorId);
                    if ($stmtCurso->execute()) {
                        $resultCurso = $stmtCurso->get_result();
                        $rowCurso = $resultCurso->fetch_assoc();
                        if ($rowCurso && isset($rowCurso['curso_id'])) {
                            $cursoIdCoordenador = (int)$rowCurso['curso_id'];
                            error_log("Filtrando atividades pendentes pelo curso do coordenador: curso_id=" . $cursoIdCoordenador);
                        } else {
                            error_log("Coordenador sem curso associado (usuario_id=" . $coordenadorId . ")");
                        }
                    } else {
                        error_log("Erro ao obter curso do coordenador: " . $stmtCurso->error);
                    }
                    $stmtCurso->close();
                } else {
                    error_log("Erro ao preparar consulta de curso do coordenador: " . $conn->error);
                }
            }

            if (!empty($cursoIdCoordenador)) {
                $condicaoBusca .= " AND a.curso_id = ?";
                $parametros[] = $cursoIdCoordenador;
                $tipos .= 'i';
            }

            if (!empty($busca)) {
                $condicaoBusca .= " AND (ae.titulo LIKE ? OR ae.descricao LIKE ? OR ac.titulo LIKE ? OR ta.nome LIKE ? OR u.nome LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tipos .= 'sssss';
            }

            // Contagem baseada em grupos (deduplicação visual por conteúdo)
            $sqlCount = "SELECT COUNT(*) as total FROM (
                        SELECT MIN(ae.id) as id_agrupado
                        FROM atividade_enviada ae
                        LEFT JOIN atividades_por_resolucao apr 
                            ON apr.resolucao_id = ae.resolucao_id
                            AND apr.tipo_atividade_id = ae.tipo_atividade_id
                            AND apr.atividades_complementares_id = ae.atividades_complementares_id
                        LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        LEFT JOIN aluno a ON ae.aluno_id = a.usuario_id
                        LEFT JOIN usuario u ON a.usuario_id = u.id
                        LEFT JOIN curso c ON a.curso_id = c.id
                        $condicaoBusca
                        GROUP BY u.nome, c.nome, ae.titulo, ae.descricao, ae.ch_solicitada, ac.titulo, ta.nome, ae.status
                        ) sub";

            error_log("SQL Count Query (Todas Enviadas): " . $sqlCount);
            error_log("Parameters: " . json_encode($parametros));

            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }
            // Somente fazer bind de parâmetros se houver termos de busca
            if (!empty($parametros)) {
                $stmtCount->bind_param($tipos, ...$parametros);
            }

            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }

            $resultCount = $stmtCount->get_result();
            $total = $resultCount->fetch_assoc()['total'];
            $stmtCount->close();

            // Query principal com paginação (deduplicação visual por conteúdo)
            $sql = "SELECT 
                        MIN(ae.id) as id_agrupado,
                        ae.titulo,
                        ae.descricao,
                        ae.ch_solicitada,
                        MIN(ae.ch_atribuida) as ch_atribuida,
                        ae.status,
                        MIN(ae.data_avaliacao) as data_avaliacao,
                        MIN(ae.data_submissao) as data_submissao_agrupada,
                        MIN(ae.caminho_declaracao) as caminho_declaracao,
                        ac.titulo as atividade_titulo,
                        ta.nome as categoria_nome,
                        MIN(apr.id) as atividades_por_resolucao_id,
                        u.nome as aluno_nome,
                        c.nome as curso_nome,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            WHEN ta.nome = 'PET' THEN 'pet'
                            WHEN ta.nome = 'Atividades sociais e comunitárias' THEN 'acao_social'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividade_enviada ae
                    LEFT JOIN atividades_por_resolucao apr 
                        ON apr.resolucao_id = ae.resolucao_id
                        AND apr.tipo_atividade_id = ae.tipo_atividade_id
                        AND apr.atividades_complementares_id = ae.atividades_complementares_id
                    LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    LEFT JOIN aluno a ON ae.aluno_id = a.usuario_id
                    LEFT JOIN usuario u ON a.usuario_id = u.id
                    LEFT JOIN curso c ON a.curso_id = c.id
                    $condicaoBusca
                    GROUP BY u.nome, c.nome, ae.titulo, ae.descricao, ae.ch_solicitada, ac.titulo, ta.nome, ae.status
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";

            error_log("SQL Main Query (Todas Enviadas): " . $sql);

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }

            // Adicionar parâmetros de paginação
            $parametrosPaginacao = [$limite, $offset];
            $tiposPaginacao = 'ii';

            if (!empty($parametros)) {
                $stmt->bind_param($tipos . $tiposPaginacao, ...array_merge($parametros, $parametrosPaginacao));
            } else {
                $stmt->bind_param($tiposPaginacao, ...$parametrosPaginacao);
            }

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $atividades = [];

            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id_agrupado'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'ch_solicitada' => (int)$row['ch_solicitada'],
                    'ch_atribuida' => (int)$row['ch_atribuida'],
                    'status' => $row['status'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'data_submissao' => $row['data_submissao_agrupada'],
                    'caminho_declaracao' => $row['caminho_declaracao'],
                    'atividade_titulo' => $row['atividade_titulo'],
                    'categoria_nome' => $row['categoria_nome'],
                    'aluno_nome' => $row['aluno_nome'],
                    'curso_nome' => $row['curso_nome'],
                    'tipo_atividade' => $row['tipo_atividade']
                ];
            }

            $stmt->close();

            // Calcular metadados de paginação
            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;

            error_log("ListarAtividadesDisponiveisModel::listarTodasAtividadesEnviadas - Encontradas " . count($atividades) . " atividades (página $pagina de $totalPaginas)");

            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarTodasAtividadesEnviadas: " . $e->getMessage());
            return [
                'atividades' => [],
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => 0,
                    'total_registros' => 0,
                    'limite' => $limite,
                    'tem_proxima' => false,
                    'tem_anterior' => false
                ]
            ];
        }
    }
}
