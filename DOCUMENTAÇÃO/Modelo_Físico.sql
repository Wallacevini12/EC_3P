CREATE SCHEMA IF NOT EXISTS learnhub_ep;
USE learnhub_ep ;

DROP SCHEMA learnhub_ep;
-- -----------------------------------------------------

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    curso VARCHAR(100),
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('aluno', 'professor', 'monitor') NOT NULL
);


CREATE TABLE IF NOT EXISTS curso (
  codigo_curso INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome_curso VARCHAR(90) NOT NULL,
  duracao_curso INT NOT NULL,
  PRIMARY KEY (codigo_curso));



CREATE TABLE IF NOT EXISTS disciplina (
  codigo_disciplina INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome_disciplina VARCHAR(90) NOT NULL,
  modalidade_disciplina VARCHAR(45) NOT NULL,
  PRIMARY KEY (codigo_disciplina));


CREATE TABLE IF NOT EXISTS periodos (
  numero_periodo INT UNSIGNED NOT NULL,
  PRIMARY KEY (numero_periodo));


CREATE TABLE IF NOT EXISTS pergunta (
  codigo_pergunta INT UNSIGNED NOT NULL AUTO_INCREMENT,
  enunciado TEXT NOT NULL,
  data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  aluno_codigo INT UNSIGNED NOT NULL,
  disciplina_codigo INT UNSIGNED NOT NULL,
  PRIMARY KEY (codigo_pergunta),
  FOREIGN KEY (aluno_codigo) REFERENCES aluno (codigo_aluno),
  FOREIGN KEY (disciplina_codigo) REFERENCES disciplina (codigo_disciplina)
);




CREATE TABLE IF NOT EXISTS alunos_possuem_disciplinas (
  aluno_codigo INT UNSIGNED NOT NULL,
  disciplina_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (aluno_codigo)
		REFERENCES aluno (codigo_aluno),
    FOREIGN KEY (disciplina_codigo)
		REFERENCES disciplina (codigo_disciplina)
);


CREATE TABLE IF NOT EXISTS alunos_possuem_cursos (
  aluno_codigo INT UNSIGNED NOT NULL,
  curso_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (aluno_codigo)
		REFERENCES aluno (codigo_aluno),
    FOREIGN KEY (curso_codigo)
		REFERENCES curso (codigo_curso)
);


CREATE TABLE IF NOT EXISTS cursos_possuem_professores (
  curso_codigo INT UNSIGNED NOT NULL,
  professor_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (curso_codigo)
		REFERENCES curso (codigo_curso),
    FOREIGN KEY (professor_codigo)
		REFERENCES professor (codigo_professor)
);


CREATE TABLE IF NOT EXISTS professores_possuem_disciplinas (
  professor_codigo INT UNSIGNED NOT NULL,
  disciplina_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (professor_codigo)
		REFERENCES professor (codigo_professor),
    FOREIGN KEY (disciplina_codigo)
		REFERENCES disciplina (codigo_disciplina)
);


CREATE TABLE IF NOT EXISTS disciplinas_possuem_monitores (
  disciplina_codigo INT UNSIGNED NOT NULL,
  monitor_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (disciplina_codigo)
		REFERENCES disciplina (codigo_disciplina),
    FOREIGN KEY (monitor_codigo)
		REFERENCES monitor (codigo_monitor)
);

CREATE TABLE IF NOT EXISTS períodos_possuem_monitores (
  numero_periodo INT UNSIGNED NOT NULL,
  codigo_monitor INT UNSIGNED NOT NULL,
    FOREIGN KEY (numero_periodo)
		REFERENCES periodos (numero_periodo),
    FOREIGN KEY (codigo_monitor)
		REFERENCES monitor (codigo_monitor)
);


CREATE TABLE IF NOT EXISTS pergunta (
  codigo_pergunta INT UNSIGNED NOT NULL AUTO_INCREMENT,
  enunciado TEXT NOT NULL,
  data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  aluno_codigo INT UNSIGNED NOT NULL,
  disciplina_codigo INT UNSIGNED NOT NULL,
  PRIMARY KEY (codigo_pergunta),
  FOREIGN KEY (aluno_codigo) REFERENCES aluno (codigo_aluno),
  FOREIGN KEY (disciplina_codigo) REFERENCES disciplina (codigo_disciplina)
);


CREATE TABLE IF NOT EXISTS aluno_possui_pergunta (
  aluno_codigo INT UNSIGNED NOT NULL,
  pergunta_codigo INT UNSIGNED NOT NULL,
  PRIMARY KEY (aluno_codigo, pergunta_codigo),
  FOREIGN KEY (aluno_codigo) REFERENCES aluno (codigo_aluno),
  FOREIGN KEY (pergunta_codigo) REFERENCES pergunta (codigo_pergunta)
);

CREATE TABLE IF NOT EXISTS pergunta_possui_disciplina (
  pergunta_codigo INT UNSIGNED NOT NULL,
  disciplina_codigo INT UNSIGNED NOT NULL,
  PRIMARY KEY (pergunta_codigo, disciplina_codigo),
  FOREIGN KEY (pergunta_codigo) REFERENCES pergunta (codigo_pergunta),
  FOREIGN KEY (disciplina_codigo) REFERENCES disciplina (codigo_disciplina)
);


# ------------------------------------------------------ I N S E R Ç Õ E S ------------------------------------------------------------------

INSERT INTO professor (nome_professor, email_professor) VALUES
('João Silva', 'joao.silva@example.com'),
('Maria Oliveira', 'maria.oliveira@example.com');

-- Inserts na tabela curso
INSERT INTO curso (nome_curso, duracao_curso) VALUES
('Ciência da Computação', 8),
('Engenharia Elétrica', 10);

-- Inserts na tabela disciplina
INSERT INTO disciplina (nome_disciplina, modalidade_disciplina) VALUES
('Banco de Dados', 'Presencial'),
('Programação Web', 'EAD');

-- Inserts na tabela periodos
INSERT INTO periodos (numero_periodo) VALUES (1), (2), (3), (4);

-- Inserts na tabela monitor
INSERT INTO monitor (nome_monitor, email_monitor, bolsa_monitor, curso_codigo) VALUES
('Lucas Santos', 'lucas.santos@example.com', 500.00, 1),
('Ana Lima', 'ana.lima@example.com', 600.00, 2);


-- Inserts na tabela aluno
INSERT INTO aluno (nome_aluno, email_aluno, periodo_numero) VALUES
('Carlos Mendes', 'carlos.mendes@example.com', 1),
('Beatriz Souza', 'beatriz.souza@example.com', 2);

-- Inserts na tabela pergunta
INSERT INTO pergunta (enunciado) VALUES
('O que é normalização em banco de dados?'),
('Como funciona o protocolo HTTP?');

-- Inserts na tabela aluno_possui_pergunta
INSERT INTO aluno_possui_pergunta (aluno_codigo, pergunta_codigo) VALUES
(1, 1),
(2, 2);

-- Inserts na tabela pergunta_possui_disciplina
INSERT INTO pergunta_possui_disciplina (pergunta_codigo, disciplina_codigo) VALUES
(1, 1),
(2, 2);

-- Inserts na tabela alunos_possuem_disciplinas
INSERT INTO alunos_possuem_disciplinas (aluno_codigo, disciplina_codigo) VALUES
(1, 1),
(2, 2);

-- Inserts na tabela alunos_possuem_cursos
INSERT INTO alunos_possuem_cursos (aluno_codigo, curso_codigo) VALUES
(1, 1),
(2, 2);

-- Inserts na tabela cursos_possuem_professores
INSERT INTO cursos_possuem_professores (curso_codigo, professor_codigo) VALUES
(1, 1),
(2, 2);

-- Inserts na tabela professores_possuem_disciplinas
INSERT INTO professores_possuem_disciplinas (professor_codigo, disciplina_codigo) VALUES
(1, 1),
(2, 2);

-- Inserts na tabela disciplinas_possuem_monitores
INSERT INTO disciplinas_possuem_monitores (disciplina_codigo, monitor_codigo) VALUES
(1, 1),
(2, 2);

-- Inserts na tabela pergunta
INSERT INTO pergunta (enunciado, aluno_codigo, disciplina_codigo) VALUES
('O que é normalização em banco de dados?', 1, 1),
('Como funciona o protocolo HTTP?', 2, 2),
('Quais são os tipos de joins em SQL?', 1, 1),
('O que é um índice em um banco de dados relacional?', 2, 1);