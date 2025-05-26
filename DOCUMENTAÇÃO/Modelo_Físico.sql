-- Criação do schema
CREATE SCHEMA IF NOT EXISTS learnhub_ep;
USE learnhub_ep;


-- Tabela de usuários (base para especializações)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    curso VARCHAR(100),
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('aluno', 'professor', 'monitor') NOT NULL
);

-- Especializações de usuário
CREATE TABLE aluno (
    id INT PRIMARY KEY,
    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE professor (
    id INT PRIMARY KEY,
    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE monitor (
    id INT PRIMARY KEY,
    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Cursos e Disciplinas
CREATE TABLE curso (
    codigo_curso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_curso VARCHAR(90) NOT NULL,
    duracao_curso INT NOT NULL
);

CREATE TABLE disciplinas (
    codigo_disciplina INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_disciplina VARCHAR(90) NOT NULL,
    modalidade_disciplina VARCHAR(45) NOT NULL
);

-- Perguntas e respostas
CREATE TABLE perguntas (
    codigo_pergunta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enunciado TEXT NOT NULL,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('aguardando resposta', 'respondida') NOT NULL DEFAULT 'aguardando resposta',
    usuario_codigo INT NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    FOREIGN KEY (usuario_codigo) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina) ON DELETE CASCADE
);

CREATE TABLE respostas (
    codigo_resposta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_pergunta INT UNSIGNED NOT NULL,
    monitor_id INT NOT NULL,
    resposta TEXT NOT NULL,
    data_resposta DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (codigo_pergunta) REFERENCES perguntas(codigo_pergunta) ON DELETE CASCADE,
    FOREIGN KEY (monitor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Relacionamentos N:N

-- Alunos em disciplinas
CREATE TABLE alunos_possuem_disciplinas (
    aluno_codigo INT NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (aluno_codigo, disciplina_codigo),
    FOREIGN KEY (aluno_codigo) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina) ON DELETE CASCADE
);

CREATE TABLE monitores_possuem_disciplinas (
    monitor_codigo INT NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (monitor_codigo, disciplina_codigo),
    FOREIGN KEY (monitor_codigo) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina) ON DELETE CASCADE
);

-- Alunos em cursos
CREATE TABLE alunos_possuem_cursos (
    aluno_codigo INT NOT NULL,
    curso_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (aluno_codigo, curso_codigo),
    FOREIGN KEY (aluno_codigo) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_codigo) REFERENCES curso(codigo_curso) ON DELETE CASCADE
);

-- Professores em cursos
CREATE TABLE cursos_possuem_professores (
    curso_codigo INT UNSIGNED NOT NULL,
    professor_codigo INT NOT NULL,
    PRIMARY KEY (curso_codigo, professor_codigo),
    FOREIGN KEY (curso_codigo) REFERENCES curso(codigo_curso) ON DELETE CASCADE,
    FOREIGN KEY (professor_codigo) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Professores em disciplinas
CREATE TABLE professores_possuem_disciplinas (
    professor_codigo INT NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (professor_codigo, disciplina_codigo),
    FOREIGN KEY (professor_codigo) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina) ON DELETE CASCADE
);

-- Monitores em disciplinas
CREATE TABLE disciplinas_possuem_monitores (
    disciplina_codigo INT UNSIGNED NOT NULL,
    monitor_codigo INT NOT NULL,
    PRIMARY KEY (disciplina_codigo, monitor_codigo),
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina) ON DELETE CASCADE,
    FOREIGN KEY (monitor_codigo) REFERENCES usuarios(id) ON DELETE CASCADE
);


-- Aluno fez uma pergunta
CREATE TABLE aluno_possui_pergunta (
    aluno_codigo INT NOT NULL,
    pergunta_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (aluno_codigo, pergunta_codigo),
    FOREIGN KEY (aluno_codigo) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pergunta_codigo) REFERENCES perguntas(codigo_pergunta) ON DELETE CASCADE
);

-- Pergunta associada a disciplina
CREATE TABLE pergunta_possui_disciplina (
    pergunta_codigo INT UNSIGNED NOT NULL,
    disciplina_codigo INT UNSIGNED NOT NULL,
    PRIMARY KEY (pergunta_codigo, disciplina_codigo),
    FOREIGN KEY (pergunta_codigo) REFERENCES perguntas(codigo_pergunta) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_codigo) REFERENCES disciplinas(codigo_disciplina) ON DELETE CASCADE
);

CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    resposta_id INT UNSIGNED NOT NULL,
    nota INT CHECK (nota BETWEEN 0 AND 5),
    data_avaliacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (aluno_id, resposta_id),
    FOREIGN KEY (aluno_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (resposta_id) REFERENCES respostas(codigo_resposta) ON DELETE CASCADE
);

ALTER TABLE perguntas ADD COLUMN encaminhada BOOLEAN DEFAULT FALSE;
ALTER TABLE perguntas ADD COLUMN respondida TINYINT(1) DEFAULT 0;
ALTER TABLE respostas 
    CHANGE COLUMN monitor_id respondente_id INT NOT NULL;
ALTER TABLE respostas 
    ADD COLUMN respondente_tipo ENUM('monitor', 'professor') NOT NULL DEFAULT 'monitor';




INSERT INTO curso (nome_curso, duracao_curso) VALUES
('Engenharia de Software', 8),
('Sistemas de Informação', 8),
('Análise e Desenvolvimento de Sistemas', 6),
('Ciência da Computação', 8),
('Redes de Computadores', 6);

INSERT INTO disciplinas (nome_disciplina, modalidade_disciplina) VALUES
('Algoritmos e Lógica de Programação', 'Presencial'),
('Estrutura de Dados', 'Presencial'),
('Banco de Dados', 'Presencial'),
('Engenharia de Software', 'Online'),
('Redes de Computadores', 'Online'),
('Programação Orientada a Objetos', 'Presencial'),
('Sistemas Operacionais', 'Presencial');







DELIMITER $$

CREATE TRIGGER atualizar_status_pergunta
AFTER INSERT ON respostas
FOR EACH ROW
BEGIN
    UPDATE perguntas
    SET status = 'respondida'
    WHERE codigo_pergunta = NEW.codigo_pergunta;
END $$

DELIMITER ;
