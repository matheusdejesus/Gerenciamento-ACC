-- 1. Criação do banco de dados (opcional)
CREATE DATABASE IF NOT EXISTS ACC CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ACC;

CREATE TABLE aluno (
  usuario_id int(11) NOT NULL,
  matricula varchar(50) NOT NULL,
  curso_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE apikeys (
  id int(11) NOT NULL,
  usuario_id int(11) NOT NULL,
  nome_aplicacao varchar(255) NOT NULL,
  api_key varchar(64) NOT NULL,
  ativa tinyint(1) NOT NULL DEFAULT 1,
  criada_em datetime DEFAULT current_timestamp(),
  expira_em datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE atividadecomplementaracc (
  id int(11) NOT NULL,
  aluno_id int(11) NOT NULL,
  atividade_disponivel_id int(11) NOT NULL,
  categoria_id int(11) DEFAULT NULL,
  curso_evento_nome varchar(255) DEFAULT NULL,
  horas_realizadas int(11) NOT NULL,
  data_inicio date NOT NULL,
  data_fim date NOT NULL,
  local_instituicao varchar(255) NOT NULL,
  observacoes text DEFAULT NULL,
  declaracao_caminho varchar(500) NOT NULL,
  status enum('Aguardando avaliação','aprovado','rejeitado') DEFAULT 'Aguardando avaliação',
  data_submissao datetime DEFAULT current_timestamp(),
  data_avaliacao datetime DEFAULT NULL,
  observacoes_avaliacao text DEFAULT NULL,
  avaliador_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE atividadecomplementarensino (
  id int(11) NOT NULL,
  aluno_id int(11) NOT NULL,
  atividade_disponivel_id int(11) DEFAULT NULL,
  categoria_id int(11) NOT NULL,
  nome_disciplina varchar(255) DEFAULT NULL,
  nome_instituicao varchar(255) DEFAULT NULL,
  carga_horaria int(11) DEFAULT NULL,
  nome_disciplina_laboratorio varchar(255) DEFAULT NULL,
  monitor varchar(255) DEFAULT NULL,
  data_inicio date DEFAULT NULL,
  data_fim date DEFAULT NULL,
  declaracao_caminho varchar(255) DEFAULT NULL,
  status enum('Aguardando avaliação','aprovado','rejeitado') DEFAULT 'Aguardando avaliação',
  data_submissao datetime DEFAULT current_timestamp(),
  data_avaliacao datetime DEFAULT NULL,
  observacoes_avaliacao text DEFAULT NULL,
  avaliador_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE atividadecomplementarestagio (
  id int(11) NOT NULL,
  aluno_id int(11) NOT NULL,
  atividade_disponivel_id int(11) DEFAULT NULL,
  empresa varchar(255) NOT NULL,
  data_inicio date NOT NULL,
  data_fim date NOT NULL,
  horas int(11) NOT NULL,
  area varchar(255) NOT NULL,
  declaracao_caminho varchar(500) NOT NULL,
  status enum('Aguardando avaliação','aprovado','rejeitado') DEFAULT 'Aguardando avaliação',
  data_submissao datetime DEFAULT current_timestamp(),
  data_avaliacao datetime DEFAULT NULL,
  observacoes_avaliacao text DEFAULT NULL,
  avaliador_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE atividadecomplementarpesquisa (
  id int(11) NOT NULL,
  aluno_id int(11) NOT NULL,
  atividade_disponivel_id int(11) NOT NULL,
  tipo_atividade enum('apresentacao_evento','membro_evento','iniciacao_cientifica','publicacao_artigo') NOT NULL,
  horas_realizadas int(11) NOT NULL,
  local_instituicao varchar(255) DEFAULT NULL,
  declaracao_caminho varchar(500) NOT NULL,
  tema varchar(255) DEFAULT NULL,
  quantidade_apresentacoes int(11) DEFAULT NULL,
  nome_evento varchar(255) DEFAULT NULL,
  nome_projeto varchar(255) DEFAULT NULL,
  data_inicio date DEFAULT NULL,
  data_fim date DEFAULT NULL,
  nome_artigo varchar(255) DEFAULT NULL,
  quantidade_publicacoes int(11) DEFAULT NULL,
  status enum('Aguardando avaliação','aprovado','rejeitado') DEFAULT 'Aguardando avaliação',
  data_submissao datetime DEFAULT current_timestamp(),
  data_avaliacao datetime DEFAULT NULL,
  observacoes_avaliacao text DEFAULT NULL,
  avaliador_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE atividadessociaiscomunitarias (
  id int(11) NOT NULL,
  aluno_id int(11) NOT NULL,
  nome_projeto varchar(255) DEFAULT NULL,
  atividade_disponivel_id int(11) NOT NULL,
  categoria_id int(11) NOT NULL,
  horas_realizadas int(11) NOT NULL,
  local_instituicao varchar(255) NOT NULL,
  descricao text DEFAULT NULL,
  FOREIGN KEY (aluno_id) REFERENCES aluno (usuario_id),
  FOREIGN KEY (atividade_disponivel_id) REFERENCES atividadesdisponiveisbcc17 (id),
  FOREIGN KEY (categoria_id) REFERENCES categoriaatividadebcc17 (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE atividadesdisponiveisbcc17 (
  id int(11) NOT NULL,
  categoria_id int(11) NOT NULL,
  titulo varchar(255) NOT NULL,
  carga_horaria_maxima_por_atividade int(11) DEFAULT NULL,
  observacoes text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE atividadesdisponiveisbcc23 (
  id int(11) NOT NULL,
  categoria_id int(11) NOT NULL,
  titulo varchar(255) NOT NULL,
  carga_horaria_maxima_por_atividade int(11) DEFAULT NULL,
  observacoes text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE categoriaatividadebcc17 (
  id int(11) NOT NULL,
  descricao varchar(255) NOT NULL,
  carga_horaria_maxima_bcc17 int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE categoriaatividadebcc23 (
  id int(11) NOT NULL,
  descricao varchar(255) NOT NULL,
  carga_horaria_maxima_bcc23 int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE coordenador (
  usuario_id int(11) NOT NULL,
  siape varchar(50) NOT NULL,
  curso_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE curso (
  id int(11) NOT NULL,
  nome varchar(255) NOT NULL,
  codigo varchar(50) NOT NULL,
  instituto_id int(11) NOT NULL,
  campus varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE emailconfirm (
  id int(11) NOT NULL,
  usuario_id int(11) NOT NULL,
  codigo char(6) NOT NULL,
  criado_em datetime NOT NULL DEFAULT current_timestamp(),
  expiracao datetime NOT NULL,
  confirmado tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE instituto (
  id int(11) NOT NULL,
  nome varchar(100) NOT NULL,
  sigla varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE logacoes (
  id int(11) NOT NULL,
  usuario_id int(11) NOT NULL,
  acao varchar(100) NOT NULL,
  data_hora datetime DEFAULT current_timestamp(),
  descricao text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE orientador (
  usuario_id int(11) NOT NULL,
  siape varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE recuperarsenha (
  id int(11) NOT NULL,
  usuario_id int(11) NOT NULL,
  token varchar(100) NOT NULL,
  criacao datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tentativaslogin (
  id int(11) NOT NULL,
  email varchar(255) NOT NULL,
  ip_address varchar(45) NOT NULL,
  data_hora datetime DEFAULT current_timestamp(),
  sucesso tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE usuario (
  id int(11) NOT NULL,
  nome varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  senha varchar(255) NOT NULL,
  tipo enum('aluno','coordenador','orientador','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO Instituto (id, nome, sigla) VALUES
  (1, 'Instituto de Engenharia e Geociências', 'IEG'),

INSERT INTO Curso (id, nome, codigo, instituto_id, campus) VALUES
  -- Cursos do Instituto de Biodiversidade e Florestas (IBEF)
  (1, 'Bacharelado em Ciência da Computação', 'BCC', 1, 'Santarém'),
  (2, 'Bacharelado em Sistemas de Informação', 'SI', 1, 'Santarém'),

-- Inserindo dados na tabela CategoriaAtividadeBcc17
INSERT INTO categoriaatividadebcc17(id, descricao, carga_horaria_maxima) VALUES
(1, 'Ensino', 80),
(2, 'Pesquisa', 80),
(3, 'Atividades extracurriculares', 80),
(4, 'Estágio', 100);
(5, 'Atividades sociais e comunitárias', 30);

-- Inserindo dados na tabela CategoriaAtividadeBcc23
INSERT INTO categoriaatividadebcc23 (id, descricao, carga_horaria_maxima) VALUES
(1, 'Ensino', 40),
(2, 'Pesquisa', 40),
(3, 'Atividades extracurriculares', 40),
(4, 'Estágio', 90);


-- Inserindo atividades na tabela AtividadesDisponiveis
INSERT INTO atividadesdisponiveisbcc23 (titulo, categoria_id, carga_horaria_maxima_por_atividade, observacoes) VALUES

-- Atividades de Ensino
('Disciplinas em áreas correlatas cursadas em outras IES', 1, 15, ''),
('Disciplinas em áreas correlatas cursadas na UFOPA', 1, 30, ''),
('Monitoria em disciplina de graduação ou laboratório', 1, 40, ''),

-- Atividades de Pesquisa
('Apresentação em eventos científicos (por trabalho)', 2, 9, 'Cada apresentação equivale a 5 (cinco) horas para eventos locais, regionais e nacionais, e 7 (sete) horas para internacionais'),
('Publicação de artigo em anais, periódicos ou capítulo de livro (por trabalho)', 2, 10, 'Cada publicação equivale a 10 horas'),
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
('Estágio curricular não obrigatório', 4, 90, '')

INSERT INTO atividadesdisponiveisbcc17 (titulo, categoria_id, carga_horaria_maxima_por_atividade, observacoes) VALUES
('Disciplinas em áreas correlatas cursadas em outras IES', 1, 30, ''),
('Disciplinas em áreas correlatas cursadas na UFOPA', 1, 60, ''),
('Monitoria em disciplina de graduação ou laboratório', 1, 80, ''),

-- Atividades de Pesquisa
('Apresentação em eventos científicos (por trabalho)', 2, 20, 'Cada apresentação equivale a 10 (dez) horas para eventos locais, regionais e nacionais, e 15 (quinze) horas para internacionais'),
('Publicação de artigo em periódicos ou capítulo de livro (por trabalho)', 2, 40, 'Cada publicação equivale a 20 horas'),
('Atividades de iniciação científica (por semestre)', 2, 30, ''),

-- Atividades extracurriculares
('Curso de extensão em áreas afins', 3, 20, 'No máximo 20 horas contabilizadas por certificado apresentado'),
('Curso de extensão na área específica', 3, 40, 'No máximo 40 horas contabilizadas por certificado apresentado'),
('Curso de língua estrangeira', 3, 50, 'Limitada a uma validação por idioma.'),
('Participação em seminários, simpósios, convenções, conferências, palestras, congressos, jornadas, fóruns, debates, visitas técnicas, viagens de estudos, workshops, programas de treinamento e eventos promovidos pela UFOPA e/ou outras IES', 3, 80, 'Carga horária contabilizada conforme carga horária do evento'),
('Missões nacionais e internacionais', 3, 30, ''),

-- Atividades de Estágio
('Estágio curricular não obrigatório', 4, 100, ''),

-- Atividades sociais e comunitárias
('Ação social e comunitária', 5, 30, 'Participação em ações comunitárias/sociais');