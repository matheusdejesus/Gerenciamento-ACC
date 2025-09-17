-- 1. Criação do banco de dados (opcional)
CREATE DATABASE IF NOT EXISTS ACC CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ACC;

-- 2. Tabela Usuario
CREATE TABLE Usuario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('aluno','coordenador','orientador','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 
CREATE TABLE Instituto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE,
  sigla VARCHAR(10) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabela Curso
CREATE TABLE Curso (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  codigo VARCHAR(50) NOT NULL UNIQUE,
  instituto_id INT NOT NULL,
  campus VARCHAR(100) NOT NULL,
  FOREIGN KEY (instituto_id) REFERENCES Instituto(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabela Aluno
CREATE TABLE Aluno (
  usuario_id INT PRIMARY KEY,
  matricula VARCHAR(50) NOT NULL UNIQUE,
  curso_id INT NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE,
  FOREIGN KEY (curso_id) REFERENCES Curso(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabela Coordenador
CREATE TABLE Coordenador (
  usuario_id INT PRIMARY KEY,
  siape VARCHAR(50) NOT NULL UNIQUE,
  curso_id INT NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE,
  FOREIGN KEY (curso_id) REFERENCES Curso(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabela Orientador
CREATE TABLE Orientador (
  usuario_id INT PRIMARY KEY,
  siape VARCHAR(50) NOT NULL UNIQUE,
  FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabela CategoriaAtividade
CREATE TABLE CategoriaAtividade (
  id INT AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(255) NOT NULL,
  carga_horaria_maxima INT NOT NULL,
  observacoes TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create table AtividadesDisponiveis(
    id int auto_increment primary key,
    categoria_id INT NOT NULL,
    titulo varchar(255) not null,
    carga_horaria_maxima_por_atividade int,
    observacoes TEXT,
    FOREIGN KEY (categoria_id) REFERENCES CategoriaAtividade(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabela AtividadeComplementarEnsino
CREATE TABLE AtividadeComplementarEnsino (
  id INT AUTO_INCREMENT PRIMARY KEY,
  aluno_id INT NOT NULL,
  categoria_id INT NOT NULL,
  
  -- Campos específicos para Disciplinas em outras IES
  nome_disciplina VARCHAR(255) NULL,
  nome_instituicao VARCHAR(255) NULL,
  carga_horaria INT NULL,
  
  -- Campos específicos para Monitoria
  nome_disciplina_laboratorio VARCHAR(255) NULL,
  monitor VARCHAR(255) NULL,
  data_inicio DATE NULL,
  data_fim DATE NULL,
  
  -- Campos para arquivos
  declaracao_caminho VARCHAR(255) NULL,
  
  FOREIGN KEY (aluno_id) REFERENCES Aluno(usuario_id) ON DELETE CASCADE,
  FOREIGN KEY (categoria_id) REFERENCES CategoriaAtividade(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabela LogAcoes
CREATE TABLE LogAcoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  acao VARCHAR(100) NOT NULL,
  data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
  descricao TEXT,
  FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Tabela para armazenar tokens de confirmação
CREATE TABLE EmailConfirm (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  codigo CHAR(6) NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expiracao DATETIME NOT NULL,
  confirmado TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE RecuperarSenha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(100) NOT NULL,
    criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE TentativasLogin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    sucesso TINYINT(1) DEFAULT 0,
    INDEX idx_email_ip (email, ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ApiKeys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_aplicacao VARCHAR(255) NOT NULL,
    api_key VARCHAR(64) NOT NULL UNIQUE,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    criada_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    expira_em DATETIME NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE certificadoavulso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    coordenador_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    observacao TEXT,
    horas INT NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    status ENUM('Pendente', 'Aprovado', 'Rejeitado') DEFAULT 'Pendente',
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_avaliacao DATETIME NULL,
    FOREIGN KEY (aluno_id) REFERENCES Aluno(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (coordenador_id) REFERENCES Coordenador(usuario_id) ON DELETE CASCADE
);

-- Tabela para armazenar atividades complementares
CREATE TABLE atividadecomplementaracc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    atividade_disponivel_id INT NOT NULL,
    categoria_id INT NULL,
    curso_nome VARCHAR(255) NULL,
    evento_nome VARCHAR(255) NULL,
    horas_realizadas INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    local_instituicao VARCHAR(255) NOT NULL,
    observacoes TEXT NULL,
    declaracao_caminho VARCHAR(500) NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    data_submissao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_avaliacao DATETIME NULL,
    observacoes_avaliacao TEXT NULL,
    avaliador_id INT NULL,
    FOREIGN KEY (aluno_id) REFERENCES Aluno(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (atividade_disponivel_id) REFERENCES AtividadesDisponiveis(id) ON DELETE RESTRICT,
    FOREIGN KEY (categoria_id) REFERENCES CategoriaAtividade(id) ON DELETE SET NULL,
    FOREIGN KEY (avaliador_id) REFERENCES Coordenador(usuario_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Instituto (id, nome, sigla) VALUES
  (1, 'Instituto de Biodiversidade e Florestas', 'IBEF'),           -- :contentReference[oaicite:1]{index=1}
  (2, 'Instituto de Ciências da Educação', 'ICED'),               -- :contentReference[oaicite:2]{index=2}
  (3, 'Instituto de Ciências da Sociedade', 'ICS'),               -- :contentReference[oaicite:3]{index=3}
  (4, 'Instituto de Ciências e Tecnologia das Águas', 'ICTA'),     -- :contentReference[oaicite:4]{index=4}
  (5, 'Instituto de Engenharia e Geociências', 'IEG'),            -- :contentReference[oaicite:5]{index=5}
  (6, 'Instituto de Saúde Coletiva', 'ISCO');                     -- :contentReference[oaicite:6]{index=6}

INSERT INTO Curso (id, nome, codigo, instituto_id, campus) VALUES
  -- Cursos do Instituto de Biodiversidade e Florestas (IBEF)
  ( 1, 'Bacharelado em Agronomia',                                           'AGR',      1, 'Santarém'),  -- :contentReference[oaicite:8]{index=8}
  ( 2, 'Bacharelado em Zootecnia',                                            'ZOO',      1, 'Santarém'),  -- :contentReference[oaicite:9]{index=9}
  ( 3, 'Bacharelado Interdisciplinar em Ciências Agrárias',                   'BICA',     1, 'Santarém'),  -- :contentReference[oaicite:10]{index=10}
  ( 4, 'Bacharelado Interdisciplinar em Ciências Agrárias - Produção Animal', 'BICA_PA',  1, 'Santarém'),  -- :contentReference[oaicite:11]{index=11}
  ( 5, 'Bacharelado Interdisciplinar em Ciências Agrárias - Produção Vegetal', 'BICA_PV',  1, 'Santarém'),  -- :contentReference[oaicite:12]{index=12}
  ( 6, 'Bacharelado Interdisciplinar em Ciências Agrárias - Produtos Naturais', 'BICA_PN',  1, 'Santarém'),  -- :contentReference[oaicite:13]{index=13}
  ( 7, 'Bacharelado Interdisciplinar em Ciências Agrárias - Recursos Florestais','BICA_RF',  1, 'Santarém'),  -- :contentReference[oaicite:14]{index=14}
  ( 8, 'Bacharelado Profissional em Engenharia Florestal',                     'ENG_FLR',  1, 'Santarém'),  -- :contentReference[oaicite:15]{index=15}
  ( 9, 'Bacharelado em Biotecnologia',                                         'BIOT',     1, 'Santarém'),  -- :contentReference[oaicite:16]{index=16}

  -- Cursos do Instituto de Ciências da Educação (ICED)
  (10, 'Bacharelado Interdisciplinar em Ciências Biológicas e Conservação',     'BICBC',    2, 'Santarém'),  -- :contentReference[oaicite:17]{index=17}
  (11, 'Licenciatura em Geografia',                                            'GEO',      2, 'Santarém'),  -- :contentReference[oaicite:18]{index=18}
  (12, 'Licenciatura em História',                                             'HIS',      2, 'Santarém'),  -- :contentReference[oaicite:19]{index=19}
  (13, 'Licenciatura em Letras - Português/Inglês',                            'LET',      2, 'Santarém'),  -- :contentReference[oaicite:20]{index=20}
  (14, 'Licenciatura em Pedagogia',                                            'PED',      2, 'Santarém'),  -- :contentReference[oaicite:21]{index=21}
  (15, 'Licenciatura Integrada em Matemática e Física',                        'LIMF',     2, 'Santarém'),  -- :contentReference[oaicite:22]{index=22}
  (16, 'Licenciatura em Informática Educacional',                               'LIE',      2, 'Santarém'),  -- :contentReference[oaicite:23]{index=23}

  -- Cursos do Instituto de Ciências da Sociedade (ICS)
  (17, 'Bacharelado em Arqueologia',                                           'ARQ',      3, 'Santarém'),  -- :contentReference[oaicite:24]{index=24}
  (18, 'Bacharelado em Antropologia',                                           'ANT',      3, 'Santarém'),  -- :contentReference[oaicite:25]{index=25}
  (19, 'Bacharelado em Ciências Econômicas',                                    'ECO',      3, 'Santarém'),  -- :contentReference[oaicite:26]{index=26}
  (20, 'Bacharelado em Direito',                                                'DIR',      3, 'Santarém'),  -- :contentReference[oaicite:27]{index=27}
  (21, 'Bacharelado em Gestão Pública e Desenvolvimento Regional',              'GPD',      3, 'Santarém'),  -- :contentReference[oaicite:28]{index=28}

  -- Cursos do Instituto de Ciências e Tecnologia das Águas (ICTA)
  (22, 'Bacharelado em Ciências Biológicas',                                     'CIB',      4, 'Santarém'),  -- :contentReference[oaicite:29]{index=29}
  (23, 'Bacharelado em Engenharia de Pesca',                                     'ENG_PE',   4, 'Santarém'),  -- :contentReference[oaicite:30]{index=30}
  (24, 'Bacharelado Interdisciplinar em Ciência e Tecnologia das Águas',         'BICTA',    4, 'Santarém'),  -- :contentReference[oaicite:31]{index=31}
  (25, 'Bacharelado em Engenharia Sanitária e Ambiental',                       'ESA',      4, 'Santarém'),  -- :contentReference[oaicite:32]{index=32}

  -- Cursos do Instituto de Engenharia e Geociências (IEG)
  (26, 'Bacharelado Interdisciplinar em Ciência da Terra',                       'BIT',      5, 'Santarém'),  -- :contentReference[oaicite:33]{index=33}
  (27, 'Bacharelado Profissional em Ciência da Computação',                      'BCC',      5, 'Santarém'),  -- :contentReference[oaicite:34]{index=34}
  (28, 'Bacharelado em Ciência e Tecnologia',                                    'CT',       5, 'Santarém'),  -- :contentReference[oaicite:35]{index=35}
  (29, 'Bacharelado em Sistemas de Informação',                                 'SI',       5, 'Santarém'),  -- :contentReference[oaicite:36]{index=36}

  -- Cursos do Instituto de Saúde Coletiva (ISCO)
  (30, 'Bacharelado em Farmácia',                                               'FAR',      6, 'Santarém'),  -- :contentReference[oaicite:37]{index=37}
  (31, 'Bacharelado Interdisciplinar em Saúde',                                  'BIS',      6, 'Santarém'),  -- :contentReference[oaicite:38]{index=38}
  (32, 'Bacharelado Profissional em Farmácia',                                   'PFAR',     6, 'Santarém');  -- :contentReference[oaicite:39]{index=39}

-- Inserindo dados na tabela CategoriaAtividade
INSERT INTO categoriaatividade (id, descricao, carga_horaria_maxima) VALUES
(1, 'Ensino', 40),
(2, 'Pesquisa', 40),
(3, 'Atividades extracurriculares', 40),
(4, 'Estágio', 90);

-- Inserindo atividades na tabela AtividadesDisponiveis
INSERT INTO atividadesdisponiveis (titulo, categoria_id, carga_horaria_maxima_por_atividade, observacoes) VALUES

-- Atividades de Ensino
('Disciplinas em áreas correlatas cursadas em outras IES', 1, 15, ''),
('Disciplinas em áreas correlatas cursadas na UFOPA', 1, 30, ''),
('Monitoria em disciplina de graduação ou laboratório', 1, 40, ''),

-- Atividades de Pesquisa
('Apresentação em eventos científicos (por trabalho)', 2, 9, ''),
('Publicação de artigo em anais, periódicos ou capítulo de livro (por trabalho)', 2, 10, ''),
('Membro efetivo e/ou assistente em eventos científicos e profissionais', 2, 40, 'Carga horária contabilizada conforme a carga horária do evento'),
('Participação em projeto de Iniciação Científica', 2, 40, ''),

-- Atividades extracurriculares
('Curso de extensão em áreas afins', 3, 10,'No máximo 10 horas contabilizadas por certificado apresentado'),
('Curso de extensão na área específica', 3, 20, 'No máximo 20 horas contabilizadas por certificado apresentado'),
('Curso de língua estrangeira', 3, 25, 'Limitada a uma validação por idioma.'),
('Participação em seminários, simpósios, convenções, conferências, palestras, congressos, jornadas, fóruns, debates, visitas técnicas, viagens de estudos, workshops, programas de treinamento e eventos promovidos pela UFOPA e/ou outras IES', 3, 40, 'Carga horária contabilizada conforme carga horária do evento'),
('Missões nacionais e internacionais', 3, 15, ''),
('Eventos e ações relacionados à educação ambiental e diversidade cultural.', 3, 40, 'Carga horária contabilizada conforme a carga horária do evento'),
('Membro efetivo e/ou assistente em eventos de extensão e profissionais.', 3, 40, 'Carga horária contabilizada conforme a carga horária do evento'),
('PET – Programa de Educação Tutorial', 3, 40, ''),

-- Atividades de Estágio
('Estágio curricular não obrigatório', 4, 40, '')