-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS roadmap_system;
USE roadmap_system;

-- Tabela de projetos/roadmaps
CREATE TABLE projects (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          name VARCHAR(255) NOT NULL,
                          description TEXT,
                          start_date DATE,
                          end_date DATE,
                          status ENUM('planejamento', 'em_andamento', 'pausado', 'concluido') DEFAULT 'planejamento',
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de marcos/milestones
CREATE TABLE milestones (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            project_id INT NOT NULL,
                            title VARCHAR(255) NOT NULL,
                            description TEXT,
                            due_date DATE,
                            status ENUM('pendente', 'em_progresso', 'concluido', 'atrasado') DEFAULT 'pendente',
                            priority ENUM('baixa', 'media', 'alta', 'critica') DEFAULT 'media',
                            progress INT DEFAULT 0, -- Porcentagem de 0 a 100
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Tabela de tarefas
CREATE TABLE tasks (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       milestone_id INT NOT NULL,
                       title VARCHAR(255) NOT NULL,
                       description TEXT,
                       assigned_to VARCHAR(100),
                       due_date DATE,
                       status ENUM('pendente', 'em_progresso', 'concluido') DEFAULT 'pendente',
                       priority ENUM('baixa', 'media', 'alta', 'critica') DEFAULT 'media',
                       estimated_hours INT,
                       actual_hours INT,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                       FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE
);

-- Tabela de comentários/notas
CREATE TABLE comments (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          project_id INT,
                          milestone_id INT,
                          task_id INT,
                          comment TEXT NOT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                          FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
                          FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Inserir alguns dados de exemplo
INSERT INTO projects (name, description, start_date, end_date, status) VALUES
                                                                           ('Sistema de E-commerce', 'Desenvolvimento de loja online completa', '2025-01-01', '2025-12-31', 'em_andamento'),
                                                                           ('App Mobile', 'Aplicativo para gestão pessoal', '2025-03-01', '2025-08-31', 'planejamento');

INSERT INTO milestones (project_id, title, description, due_date, status, priority, progress) VALUES
                                                                                                  (1, 'Análise de Requisitos', 'Levantamento completo dos requisitos do sistema', '2025-02-15', 'concluido', 'alta', 100),
                                                                                                  (1, 'Design e Prototipação', 'Criação do design e protótipos das telas', '2025-03-30', 'em_progresso', 'alta', 60),
                                                                                                  (1, 'Desenvolvimento Backend', 'Implementação da API e banco de dados', '2025-06-30', 'pendente', 'critica', 0),
                                                                                                  (2, 'Pesquisa de Mercado', 'Análise da concorrência e público-alvo', '2025-03-15', 'pendente', 'media', 0);

INSERT INTO tasks (milestone_id, title, description, due_date, status, priority, estimated_hours) VALUES
                                                                                                      (2, 'Criar wireframes principais', 'Desenhar wireframes das telas principais', '2025-03-15', 'concluido', 'alta', 16),
                                                                                                      (2, 'Design visual das telas', 'Aplicar identidade visual nos wireframes', '2025-03-25', 'em_progresso', 'alta', 24),
                                                                                                      (2, 'Protótipo interativo', 'Criar protótipo clicável no Figma', '2025-03-30', 'pendente', 'media', 8),
                                                                                                      (3, 'Setup do ambiente', 'Configurar servidor e banco de dados', '2025-04-15', 'pendente', 'alta', 8);