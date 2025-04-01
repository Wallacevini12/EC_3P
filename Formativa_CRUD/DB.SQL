CREATE DATABASE IF NOT EXISTS learnhub_ep;
USE learnhub_ep;

 CREATE TABLE usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255)
);


INSERT INTO usuario (nome, email, senha) VALUES
('Alice Silva', 'alice@email.com', 'senha123'),
('Bruno Souza', 'bruno@email.com', 'senha456'),
('Carla Mendes', 'carla@email.com', 'senha789');