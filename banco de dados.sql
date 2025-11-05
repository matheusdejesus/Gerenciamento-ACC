-- 1. Criação do banco de dados
CREATE DATABASE IF NOT EXISTS ACC CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ACC;

-- Tabela de usuários
CREATE TABLE usuario (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  nome varchar(255) NOT NULL,
  email varchar(255) NOT NULL UNIQUE,
  senha varchar(255) NOT NULL,
  tipo enum('aluno','coordenador','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de institutos
CREATE TABLE instituto (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  nome varchar(100) NOT NULL,
  sigla varchar(10) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de cursos
CREATE TABLE curso (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  nome varchar(255) NOT NULL,
  codigo varchar(50) NOT NULL UNIQUE,
  instituto_id int(11) NOT NULL,
  FOREIGN KEY (instituto_id) REFERENCES instituto(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de disciplinas
CREATE TABLE disciplina (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  curso_id int(11) NOT NULL,
  nome varchar(255) NOT NULL,
  codigo varchar(50) NOT NULL,
  carga_horaria int(11) NOT NULL,
  FOREIGN KEY (curso_id) REFERENCES curso(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de alunos
CREATE TABLE aluno (
  usuario_id int(11) PRIMARY KEY,
  matricula varchar(50) NOT NULL UNIQUE,
  curso_id int(11) NOT NULL,
  ano_ingresso int(4) NOT NULL,
  FOREIGN KEY (curso_id) REFERENCES curso(id),
  FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de coordenadores
CREATE TABLE coordenador (
  usuario_id int(11) NOT NULL PRIMARY KEY,
  siape varchar(50) NOT NULL UNIQUE,
  curso_id int(11) NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
  FOREIGN KEY (curso_id) REFERENCES curso(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela unificada de logs
CREATE TABLE log_sistema (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  usuario_id int(11) NULL,
  tipo_evento enum('login_sucesso','login_falha','acao_usuario') NOT NULL,
  acao varchar(100) NOT NULL,
  descricao text,
  ip_address varchar(45),
  email varchar(255),
  data_hora datetime DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de resoluções
CREATE TABLE resolucao (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  nome varchar(100) NOT NULL,
  descricao text,
  curso_id int(11) NOT NULL,
  ativo tinyint(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (curso_id) REFERENCES curso(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de tipos de atividade
CREATE TABLE tipo_atividade (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  nome varchar(255) NOT NULL,
  descricao text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de atividades
CREATE TABLE atividades_complementares (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  tipo_atividade_id int(11) NOT NULL,
  titulo varchar(255) NOT NULL,
  descricao text,
  observacoes text,
  FOREIGN KEY (tipo_atividade_id) REFERENCES tipo_atividade(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de junção entre resolução e tipo de atividade
CREATE TABLE resolucao_tipo_atividade (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  res_id int(11) NOT NULL,
  tipo_atv_id int(11) NOT NULL,
  carga_horaria_maxima int(11) NOT NULL,
  FOREIGN KEY (res_id) REFERENCES resolucao(id),
  FOREIGN KEY (tipo_atv_id) REFERENCES tipo_atividade(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de atividades por resolução
CREATE TABLE atividades_por_resolucao (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  resolucao_tipo_atividade_res_id int(11) NOT NULL,
  resolucao_tipo_atividade_tipo_atv_id int(11) NOT NULL,
  atividades_complementares_id int(11) NOT NULL,
  carga_horaria_maxima_por_atividade int(11) NOT NULL,
  FOREIGN KEY (resolucao_tipo_atividade_res_id) REFERENCES resolucao_tipo_atividade(res_id),
  FOREIGN KEY (resolucao_tipo_atividade_tipo_atv_id) REFERENCES resolucao_tipo_atividade(tipo_atv_id),
  FOREIGN KEY (atividades_complementares_id) REFERENCES atividades_complementares(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de atividades enviadas pelos alunos
CREATE TABLE atividade_enviada (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  aluno_id int(11) NOT NULL,
  atividades_por_resolucao_resolucao_tipo_atividade_res_id int(11) NOT NULL,
  atividades_por_resolucao_resolucao_tipo_atividade_tipo_atv_id int(11) NOT NULL,
  titulo varchar(255) NOT NULL,
  descricao text,
  ch_solicitada int(11) NOT NULL,
  ch_atribuida int(11) NOT NULL DEFAULT 0,
  caminho_declaracao varchar(255),
  status enum('Aguardando avaliação','aprovado','rejeitado') NOT NULL DEFAULT 'Aguardando avaliação',
  observacoes_avaliador text,
  avaliado_por int(11) NULL,
  data_avaliacao datetime NULL,
  avaliado tinyint(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE,
  FOREIGN KEY (atividades_por_resolucao_resolucao_tipo_atividade_res_id) REFERENCES atividades_por_resolucao(resolucao_tipo_atividade_res_id),
  FOREIGN KEY (atividades_por_resolucao_resolucao_tipo_atividade_tipo_atv_id) REFERENCES atividades_por_resolucao(resolucao_tipo_atividade_tipo_atv_id),
  FOREIGN KEY (avaliado_por) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de API Keys
CREATE TABLE apikeys (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  usuario_id int(11),
  nome_aplicacao varchar(255) NOT NULL,
  api_key varchar(64) NOT NULL UNIQUE,
  ativa tinyint(1) NOT NULL DEFAULT 1,
  criada_em datetime DEFAULT current_timestamp(),
  expira_em datetime DEFAULT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de confirmação de email
CREATE TABLE emailconfirm (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  usuario_id int(11) NOT NULL,
  codigo char(6) NOT NULL,
  criado_em datetime NOT NULL DEFAULT current_timestamp(),
  expiracao datetime NOT NULL,
  confirmado tinyint(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de recuperação de senha
CREATE TABLE recuperarsenha (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  usuario_id int(11) NOT NULL,
  token varchar(100) NOT NULL UNIQUE,
  criacao datetime NOT NULL DEFAULT current_timestamp(),
  expiracao datetime NOT NULL,
  usado tinyint(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir instituto
INSERT INTO instituto (id, nome, sigla) VALUES
  (1, 'Instituto de Engenharia e Geociências', 'IEG');

-- Inserir cursos
INSERT INTO curso (id, nome, codigo, instituto_id) VALUES
  (1, 'Bacharelado em Ciência da Computação', 'BCC', 1),
  (2, 'Bacharelado em Sistemas de Informação', 'SI', 1);

-- Inserir disciplinas exemplo para o curso BCC
INSERT INTO disciplina (curso_id, nome, codigo, carga_horaria) VALUES
(1, 'Algoritmos e Estruturas de Dados I', 'BCC001', 60),
(1, 'Programação Orientada a Objetos', 'BCC002', 60),
(1, 'Banco de Dados', 'BCC003', 60),
(1, 'Engenharia de Software', 'BCC004', 60),
(1, 'Redes de Computadores', 'BCC005', 60);

-- Inserir resoluções
INSERT INTO resolucao (id, nome, descricao, curso_id) VALUES
  (1, 'Resolução BCC 2017', 'Resolução para alunos ingressantes até 2022', 1),
  (2, 'Resolução BCC 2023', 'Resolução para alunos ingressantes a partir de 2023', 1),
  (3, 'Resolução SI 2018', 'Resolução para alunos ingressantes a partir de 2018', 2);

-- Inserir tipos de atividade BCC
INSERT INTO tipo_atividade (id, nome, descricao) VALUES
  (1, 'Ensino', 'Atividades relacionadas ao ensino'),
  (2, 'Pesquisa', 'Atividades de pesquisa científica'),
  (3, 'Atividades extracurriculares', 'Atividades de extensão e extracurriculares'),
  (4, 'Estágio', 'Estágios não obrigatórios'),
  (5, 'Atividades sociais e comunitárias', 'Ações sociais e comunitárias');

-- Inserir tipos de atividade BSI
  INSERT INTO tipo_atividade (id, nome, descricao) VALUES
  (1, 'Ensino', 'Atividades relacionadas ao ensino'),
  (2, 'Pesquisa', 'Atividades de pesquisa científica'),
  (3, 'Atividades extracurriculares', 'Atividades de extensão e extracurriculares'),
  (4, 'Estágio', 'Estágios não obrigatórios'),
  (5, 'Atividades sociais e comunitárias', 'Ações sociais e comunitárias'),
  (6, 'PET', 'Participação em Programas de Educação Tutorial (PET) da UFOPA');

-- Inserir relações entre resoluções e tipos de atividade para BCC17
INSERT INTO resolucao_tipo_atividade (res_id, tipo_atv_id, carga_horaria_maxima) VALUES
  (1, 1, 80),  -- Ensino BCC17
  (1, 2, 80),  -- Pesquisa BCC17
  (1, 3, 80),  -- Atividades extracurriculares BCC17
  (1, 4, 100), -- Estágio BCC17
  (1, 5, 30);  -- Atividades sociais e comunitárias BCC17

-- Inserir relações entre resoluções e tipos de atividade para BCC23
INSERT INTO resolucao_tipo_atividade (res_id, tipo_atv_id, carga_horaria_maxima) VALUES
  (2, 1, 40),  -- Ensino BCC23
  (2, 2, 40),  -- Pesquisa BCC23
  (2, 3, 40),  -- Atividades extracurriculares BCC23
  (2, 4, 90);  -- Estágio BCC23

-- Inserir relações entre resoluções e tipos de atividade para SI18
INSERT INTO resolucao_tipo_atividade (res_id, tipo_atv_id, carga_horaria_maxima) VALUES
  (3, 1, 90),  -- Ensino SI18
  (3, 2, 90),  -- Pesquisa SI18
  (3, 3, 90),  -- Atividades extracurriculares SI18
  (3, 4, 225),  -- Estágio SI18
  (3, 5, 30),  -- Atividades sociais e comunitárias SI18
  (3, 6, 90);  -- PET SI18

-- Inserir atividades genéricas BCC
INSERT INTO atividades_complementares (id, tipo_atividade_id, titulo, descricao, observacoes) VALUES
-- Atividades de Ensino
(1, 1, 'Disciplinas em áreas correlatas cursadas em outras IES', 'Disciplinas cursadas em outras instituições de ensino superior', ''),
(2, 1, 'Disciplinas em áreas correlatas cursadas na UFOPA', 'Disciplinas cursadas na própria UFOPA', ''),
(3, 1, 'Monitoria em disciplina de graduação ou laboratório', 'Atividade de monitoria acadêmica', ''),

-- Atividades de Pesquisa
(4, 2, 'Apresentação em eventos científicos', 'Apresentação de trabalhos em eventos científicos', 'Cada apresentação equivale a horas conforme o evento'),
(5, 2, 'Publicação de artigo em anais, periódicos ou capítulo de livro', 'Publicação científica', 'Cada publicação equivale a horas conforme o ppc do discente'),
(6, 2, 'Membro efetivo em eventos científicos e profissionais', 'Participação como membro de eventos', 'Carga horária conforme o evento'),
(7, 2, 'Participação em projeto de Iniciação Científica', 'Atividades de iniciação científica', ''),

-- Atividades Extracurriculares
(8, 3, 'Curso de extensão em áreas afins', 'Cursos de extensão relacionados à área', ''),
(9, 3, 'Curso de extensão na área específica', 'Cursos de extensão específicos da área', ''),
(10, 3, 'Curso de língua estrangeira', 'Cursos de idiomas', 'Limitada a uma validação por idioma'),
(11, 3, 'Participação em seminários, simpósios, convenções, conferências, palestras, congressos, jornadas, fóruns, debates, visitas técnicas, viagens de estudos, workshops, programas de treinamento', 'Participação em eventos acadêmicos', 'Carga horária conforme evento'),
(12, 3, 'Missões nacionais e internacionais', 'Participação em missões acadêmicas', ''),
(13, 3, 'Eventos de educação ambiental e diversidade cultural', 'Participação em eventos temáticos', 'Carga horária conforme evento'),
(14, 3, 'Membro efetivo e/ou assistente em eventos de extensão e profissionais', 'Participação como membro de eventos', 'Carga horária conforme o evento'),
(15, 3, 'PET – Programa de Educação Tutorial', 'Participação no programa PET', ''),

-- Atividades de Estágio
(16, 4, 'Estágio curricular não obrigatório', 'Estágio não obrigatório', ''),

-- Atividades Sociais e Comunitárias
(17, 5, 'Ação social e comunitária', 'Participação em ações comunitárias/sociais', '');

-- Inserir atividades genéricas BSI
INSERT INTO atividades_complementares (id, tipo_atividade_id, titulo, descricao, observacoes) VALUES

-- Atividades de Ensino
(18, 1, 'Disciplinas em áreas correlatas cursadas em outras IES', 'Disciplinas cursadas em outras instituições de ensino superior', ''),
(19, 1, 'Disciplinas em áreas correlatas cursadas na UFOPA', 'Disciplinas cursadas na própria UFOPA', ''),
(20, 1, 'Monitoria em disciplina de graduação ou laboratório', 'Atividade de monitoria acadêmica', ''),
(21, 1, 'Estágios/Bolsas extracurriculares alinhadas à área do curso', 'Estágios ou bolsas extracurriculares alinhadas à área do curso', ''),

--Atividades de Pesquisa
(22, 2, 'Atividades de iniciação científica', 'Participação em projetos de iniciação científica', ''),
(23, 2, 'Apresentação em eventos científicos', 'Apresentação de trabalhos em eventos científicos', 'Cada apresentação equivale a horas conforme o evento'),
(24, 2, 'Publicação de artigo em periódicos ou capítulo de livro', 'Publicação científica', 'Cada publicação equivale a horas conforme o ppc do discente'),

--Atividades Extracurriculares
(25, 3, 'Curso de extensão em áreas afins', 'Cursos de extensão relacionados à área', ''),
(26, 3, 'Curso de extensão na área específica', 'Cursos de extensão específicos da área', ''),
(27, 3, 'Curso de língua estrangeira', 'Cursos de idiomas', 'Limitada a uma validação por idioma'),
(28, 3, 'Seminários, simpósios, convenções, conferências, palestras, congressos, jornadas, fóruns, debates, visitas técnicas, viagens de estudos, workshops, programas de treinamento e eventos promovidos pela UFOPA e/ou outras IES', 'Participação em eventos acadêmicos', 'Carga horária conforme evento'),
(29, 3, 'Missões nacionais e internacionais', 'Participação em missões acadêmicas', ''),

--Atividades de Estágio
(30, 4, 'Cumprimento de estágio supervisionado', 'Estágio supervisionado', '')

-- Atividades sociais e comunitárias
(31, 5, 'Ação social e comunitária', 'Participação em ações comunitárias/sociais', '')

-- Programa de Educação Tutorial
(32, 6, 'PET – Programa de Educação Tutorial', 'Participação no programa PET', '')

-- Configurações para BCC17
INSERT INTO atividades_por_resolucao (resolucao_tipo_atividade_res_id, resolucao_tipo_atividade_tipo_atv_id, atividades_complementares_id, carga_horaria_maxima_por_atividade) VALUES
-- Ensino BCC17 (resolucao_tipo_atividade_id = 1)
(1, 1, 1, 30), -- Disciplinas outras IES
(1, 1, 2, 60), -- Disciplinas UFOPA
(1, 1, 3, 80), -- Monitoria

-- Pesquisa BCC17 (resolucao_tipo_atividade_id = 2)
(1, 2, 4, 20), -- Apresentação eventos
(1, 2, 5, 40), -- Publicação artigos
(1, 2, 7, 30), -- Iniciação científica

-- Extracurriculares BCC17 (resolucao_tipo_atividade_id = 3)
(1, 3, 8, 20),  -- Curso extensão afins
(1, 3, 9, 40),  -- Curso extensão específica
(1, 3, 10, 50), -- Língua estrangeira
(1, 3, 11, 80), -- Seminários/eventos
(1, 3, 12, 30), -- Missões

-- Estágio BCC17 (resolucao_tipo_atividade_id = 4)
(1, 4, 16, 100), -- Estágio não obrigatório

-- Sociais BCC17 (resolucao_tipo_atividade_id = 5)
(1, 5, 17, 30); -- Ação social

-- Configurações para BCC23
INSERT INTO atividades_por_resolucao (resolucao_tipo_atividade_res_id, resolucao_tipo_atividade_tipo_atv_id, atividades_complementares_id, carga_horaria_maxima_por_atividade) VALUES
-- Ensino BCC23 (resolucao_tipo_atividade_id = 6)
(2, 1, 1, 15), -- Disciplinas outras IES
(2, 1, 2, 30), -- Disciplinas UFOPA
(2, 1, 3, 40), -- Monitoria

-- Pesquisa BCC23 (resolucao_tipo_atividade_id = 7)
(2, 2, 4, 9),  -- Apresentação eventos
(2, 2, 5, 10), -- Publicação artigos
(2, 2, 6, 40), -- Membro eventos científicos
(2, 2, 7, 40), -- Iniciação científica

-- Extracurriculares BCC23 (resolucao_tipo_atividade_id = 8)
(2, 3, 8, 10),  -- Curso extensão afins
(2, 3, 9, 20),  -- Curso extensão específica
(2, 3, 10, 25), -- Língua estrangeira
(2, 3, 11, 40), -- Seminários/eventos
(2, 3, 12, 15), -- Missões
(2, 3, 13, 40), -- Eventos educação ambiental
(2, 3, 14, 40), -- Membro eventos extensão
(2, 3, 15, 40), -- PET

-- Estágio BCC23 (resolucao_tipo_atividade_id = 9)
(2, 4, 16, 90); -- Estágio não obrigatório

--Configurações para BSI18
INSERT INTO atividades_por_resolucao (resolucao_tipo_atividade_res_id, resolucao_tipo_atividade_tipo_atv_id, atividades_complementares_id, carga_horaria_maxima_por_atividade) VALUES
-- Ensino BSI18 (resolucao_tipo_atividade_id = 10)
(3, 1, 18, 45), -- Disciplinas outras IES
(3, 1, 19, 90), -- Disciplinas UFOPA
(3, 1, 20, 90), -- Estágios/Bolsas extracurriculares alinhadas à área do curso
(3, 1, 21, 90), -- Monitoria

-- Pesquisa BSI18 (resolucao_tipo_atividade_id = 11)
(3, 2, 22, 45), -- Iniciação científica
(3, 2, 23, 30), -- Evento Científico
(3, 2, 24, 60), -- Iniciação científica

-- Extracurriculares BSI18 (resolucao_tipo_atividade_id = 12)
(3, 3, 25, 30),  -- Curso extensão afins
(3, 3, 26, 60),  -- Curso extensão específica
(3, 3, 27, 75), -- Língua estrangeira
(3, 3, 28, 90), -- Seminários/eventos
(3, 3, 29, 45), -- Missões

-- Estagio BSI18 (resolucao_tipo_atividade_id = 13)
(3, 4, 30, 225), -- Estágio supervisionado

-- Ação social e comunitária
(3, 5, 31, 30); -- Ação social e comunitária

-- PET BSI18 (resolucao_tipo_atividade_id = 14)
(3, 6, 32, 90), -- PET
