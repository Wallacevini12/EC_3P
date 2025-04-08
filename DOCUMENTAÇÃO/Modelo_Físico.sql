CREATE SCHEMA IF NOT EXISTS learnhub_ep;
USE learnhub_ep;

/*DROP SCHEMA learnhub_ep;*/
-- Tabelas base
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    curso VARCHAR(100),
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('aluno', 'professor', 'monitor') NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS aluno (
    id INT PRIMARY KEY,
    FOREIGN KEY (id) REFERENCES usuarios(id)
);
CREATE TABLE IF NOT EXISTS professor (
    id INT PRIMARY KEY,
    FOREIGN KEY (id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS monitor (
    id INT PRIMARY KEY,
    FOREIGN KEY (id) REFERENCES usuarios(id)
);


CREATE TABLE curso (
    codigo_curso INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_curso VARCHAR(90) NOT NULL,
    duracao_curso INT NOT NULL,
    PRIMARY KEY (codigo_curso)
);

CREATE TABLE disciplinas (
    codigo_disciplina INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_disciplina VARCHAR(90) NOT NULL,
    modalidade_disciplina VARCHAR(45) NOT NULL,
    PRIMARY KEY (codigo_disciplina)
);

CREATE TABLE periodos (
    numero_periodo INT UNSIGNED NOT NULL,
    PRIMARY KEY (numero_periodo)
);

-- Tabela perguntas
CREATE TABLE perguntas (
    codigo_pergunta INT UNSIGNED NOT NULL AUTO_INCREMENT,
    enunciado TEXT NOT NULL,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_codigo INT NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (codigo_pergunta),
    FOREIGN KEY (usuario_codigo) REFERENCES usuarios(id),
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina)
);

-- Relacionamentos
CREATE TABLE alunos_possuem_disciplinas (
    aluno_codigo INT NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (aluno_codigo) REFERENCES usuarios(id),
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina)
);

CREATE TABLE alunos_possuem_cursos (
    aluno_codigo INT NOT NULL,
    curso_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (aluno_codigo) REFERENCES usuarios(id),
    FOREIGN KEY (curso_codigo) REFERENCES curso(codigo_curso)
);

CREATE TABLE cursos_possuem_professores (
    curso_codigo INT UNSIGNED NOT NULL,
    professor_codigo INT NOT NULL,
    FOREIGN KEY (curso_codigo) REFERENCES curso(codigo_curso),
    FOREIGN KEY (professor_codigo) REFERENCES usuarios(id)
);

CREATE TABLE professores_possuem_disciplinas (
    professor_codigo INT NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (professor_codigo) REFERENCES usuarios(id),
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina)
);

CREATE TABLE disciplinas_possuem_monitores (
    disciplina_codigo INT UNSIGNED NOT NULL,
    monitor_codigo INT NOT NULL,
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina),
    FOREIGN KEY (monitor_codigo) REFERENCES usuarios(id)
);

CREATE TABLE periodos_possuem_monitores (
    numero_periodo INT UNSIGNED NOT NULL,
    codigo_monitor INT NOT NULL,
    FOREIGN KEY (numero_periodo) REFERENCES periodos(numero_periodo),
    FOREIGN KEY (codigo_monitor) REFERENCES usuarios(id)
);

CREATE TABLE aluno_possui_pergunta (
    aluno_codigo INT NOT NULL,
    pergunta_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (aluno_codigo, pergunta_codigo),
    FOREIGN KEY (aluno_codigo) REFERENCES usuarios(id),
    FOREIGN KEY (pergunta_codigo) REFERENCES perguntas(codigo_pergunta)
);

CREATE TABLE pergunta_possui_disciplina (
    pergunta_codigo INT UNSIGNED NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (pergunta_codigo, disciplina_codigo),
    FOREIGN KEY (pergunta_codigo) REFERENCES perguntas(codigo_pergunta),
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina)
);


-- Inserts na tabela periodos
INSERT INTO periodos (numero_periodo) VALUES (1), (2), (3), (4);
INSERT INTO disciplinas VALUES ('1','Engenharia de software', 'EAD');

