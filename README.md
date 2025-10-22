# 🛣️ Sistema de Roadmap

Um sistema completo de gerenciamento de roadmaps desenvolvido em PHP com MariaDB, ideal para organização pessoal e profissional de projetos.

## 📸 Screenshots

### Dashboard Principal
- Visão geral com estatísticas dos projetos
- Cards informativos com métricas em tempo real
- Lista organizada de todos os projetos

### Timeline de Marcos
- Visualização temporal dos marcos do projeto
- Barras de progresso individuais
- Sistema de prioridades com cores

### Gestão de Tarefas
- Controle detalhado de tarefas por marco
- Atribuição de responsáveis
- Controle de tempo estimado vs realizado

## ✨ Funcionalidades

### 🎯 **Gestão de Projetos**
- ✅ Criar e editar projetos
- ✅ Definir datas de início e fim
- ✅ Controle de status (planejamento, em andamento, pausado, concluído)
- ✅ Descrições detalhadas
- ✅ Cálculo automático de progresso

### 🎌 **Marcos (Milestones)**
- ✅ Organização em timeline visual
- ✅ Sistema de prioridades (baixa, média, alta, crítica)
- ✅ Controle de progresso individual
- ✅ Status específicos (pendente, em progresso, concluído, atrasado) com filtros
- ✅ Prazos e entregas

### ✏️ **Tarefas**
- ✅ Tarefas vinculadas aos marcos
- ✅ Atribuição de responsáveis
- ✅ Controle de horas estimadas vs realizadas
- ✅ Priorização e status
- ✅ Descrições detalhadas

### 💬 **Sistema de Comentários**
- ✅ Notas em projetos, marcos e tarefas
- ✅ Histórico temporal
- ✅ Fácil adição de observações

### 📊 **Dashboard e Estatísticas**
- ✅ Métricas em tempo real
- ✅ Contadores automáticos
- ✅ Visualização de progresso
- ✅ Cards informativos

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Banco de Dados**: MariaDB/MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.1.3
- **Ícones**: Font Awesome 6.0.0
- **PDO**: Para conexões seguras com banco

## 📋 Requisitos do Sistema

- PHP 7.4 ou superior
- MariaDB 10.3+ ou MySQL 5.7+
- Servidor web (Apache/Nginx)
- Extensão PDO MySQL habilitada

## 🚀 Instalação

### 1. Clone o repositório
```bash
git clone https://github.com/isoazevedo/sistema-roadmap.git
cd sistema-roadmap
```

### 2. Configure o banco de dados
```sql
-- 1. Crie o banco de dados
CREATE DATABASE roadmap_system;

-- 2. Execute o script SQL fornecido (schema.sql)
mysql -u root -p roadmap_system < schema.sql
```

### 3. Configure a conexão
Edite o arquivo `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'roadmap_system');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 4. Configure o servidor web
Aponte seu servidor web para a pasta do projeto:
```apache
# Apache Virtual Host exemplo
<VirtualHost *:80>
    ServerName roadmap.local
    DocumentRoot /caminho/para/sistema-roadmap
    DirectoryIndex index.php
</VirtualHost>
```

### 5. Acesse o sistema
Abra seu navegador em: `http://roadmap.local` ou `http://localhost/sistema-roadmap`

## 📁 Estrutura do Projeto

```
sistema-roadmap/
├── 📄 config.php              # Configurações do banco
├── 🏠 index.php               # Dashboard principal
├── 📊 projeto_form.php        # Formulário de projetos
├── 📋 projeto_detalhes.php    # Detalhes do projeto
├── 🎯 marco_form.php          # Formulário de marcos
├── 📝 marco_detalhes.php      # Detalhes do marco
├── ✅ tarefa_form.php         # Formulário de tarefas
├── 🗑️ projeto_excluir.php     # Exclusão de projeto
├── 🗑️ marco_excluir.php       # Exclusão de marco  
├── 🗑️ tarefa_excluir.php      # Exclusão de tarefa
├── 💬 comentario_add.php      # Adicionar comentários
├── ⚙️ .htaccess               # URLs amigáveis (opcional)
├── 🗂️ schema.sql              # Script do banco de dados
└── 📖 README.md               # Este arquivo
```

## 💾 Estrutura do Banco de Dados

### Tabelas Principais

#### `projects` - Projetos
- `id` - ID único
- `name` - Nome do projeto
- `description` - Descrição
- `start_date` - Data de início
- `end_date` - Data de término
- `status` - Status do projeto

#### `milestones` - Marcos
- `id` - ID único
- `project_id` - Referência ao projeto
- `title` - Título do marco
- `description` - Descrição
- `due_date` - Data de entrega
- `status` - Status do marco
- `priority` - Prioridade
- `progress` - Progresso (0-100%)

#### `tasks` - Tarefas
- `id` - ID único
- `milestone_id` - Referência ao marco
- `title` - Título da tarefa
- `description` - Descrição
- `assigned_to` - Responsável
- `due_date` - Prazo
- `status` - Status da tarefa
- `priority` - Prioridade
- `estimated_hours` - Horas estimadas
- `actual_hours` - Horas realizadas

#### `comments` - Comentários
- `id` - ID único
- `project_id` - Ref. projeto (opcional)
- `milestone_id` - Ref. marco (opcional)
- `task_id` - Ref. tarefa (opcional)
- `comment` - Texto do comentário
- `created_at` - Data/hora

## 🎨 Personalizações

### Modificar Cores e Estilos
O sistema usa Bootstrap 5. Para personalizar:

1. **Cores de Status**: Edite as funções em `config.php`
```php
function getStatusColor($status) {
    $colors = [
        'pendente' => 'secondary',
        'em_progresso' => 'warning',
        'concluido' => 'success',
        // Adicione suas cores
    ];
    return $colors[$status] ?? 'secondary';
}
```

2. **CSS Customizado**: Adicione seus estilos nos arquivos HTML
```html
<style>
    .custom-timeline {
        /* Seus estilos personalizados */
    }
</style>
```

### Adicionar Novos Campos
Para adicionar campos personalizados:

1. **Altere o banco**: Adicione colunas nas tabelas
2. **Atualize formulários**: Inclua os novos campos
3. **Modifique queries**: Atualize os SQLs

## 🔒 Segurança

### Medidas Implementadas
- ✅ **PDO Prepared Statements** - Prevenção de SQL Injection
- ✅ **HTML Escaping** - Prevenção de XSS
- ✅ **Validação de Input** - Dados sanitizados
- ✅ **Redirecionamentos Seguros** - Prevenção de ataques

### Recomendações Adicionais
- Use HTTPS em produção
- Configure backup automático do banco
- Implemente autenticação se necessário
- Mantenha logs de acesso

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:
- 💻 **Desktop** - Interface completa
- 📱 **Tablets** - Layout adaptado
- 📲 **Mobile** - Interface otimizada

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## 📝 Roadmap de Melhorias

### 🎯 Versão 2.0 (Planejado)
- [ ] Sistema de autenticação e usuários
- [ ] Notificações por email
- [ ] Exportação para PDF
- [ ] API REST
- [ ] Gráficos avançados
- [ ] Sistema de templates
- [ ] Integração com calendário
- [ ] Modo escuro

### 🔮 Futuras Versões
- [ ] App mobile nativo
- [ ] Integração com Slack/Teams
- [ ] Relatórios avançados
- [ ] Sistema de aprovações
- [ ] Gestão de recursos
- [ ] Time tracking avançado

## ❓ Suporte

### Problemas Comuns

**Erro de Conexão com Banco**
```
Solução: Verifique as credenciais em config.php
```

**Erro 404 nas URLs**
```
Solução: Configure o .htaccess ou use URLs com .php
```

**Erro de Permissão**
```
Solução: Configure as permissões da pasta do projeto
```

### Onde Buscar Ajuda
- 📖 [Documentação PHP](https://www.php.net/docs.php)
- 🗄️ [Documentação MariaDB](https://mariadb.com/kb/en/)
- 🎨 [Documentação Bootstrap](https://getbootstrap.com/docs/)

## 📜 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👨‍💻 Autor

**Seu Nome**
- GitHub: [@isoazevedo](https://github.com/isoazevedo)
- LinkedIn: [israel-azevedo](https://linkedin.com/in/israel-azevedo-10237a100)
- Email: israel@aztell.com.br

## 🙏 Agradecimentos

- Bootstrap pela framework CSS
- Font Awesome pelos ícones
- Comunidade PHP pelas referências

---

⭐ **Se este projeto foi útil, deixe uma estrela!** ⭐

<p align="center">
  Feito para organizar melhor seus projetos
</p>
