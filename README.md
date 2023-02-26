# Gerenciador de Tarefas

# Instruções de Instalação

#### Faz o build e sobe os containers
``` bash
docker-compose up
```
#### Instala as dependências
``` bash
docker-compose exec app rm -rf vendor composer.lock \
&& docker-compose exec app composer install
```
#### Roda as migrations
``` bash
docker-compose exec app php artisan migrate
```
#### Roda o Worker para envio de e-mail
``` bash
docker-compose exec app php artisan queue:work
```
#### Roda os testes
``` bash
docker-compose exec app php artisan test
```
#### (opcional) Cria Tarefas e Usuários aleatórios
``` bash
docker-compose exec app php artisan db:seed
```
---
* Obs.: Na pasta **docs** encontra-se um JSON para importar no Insomnia. 
---

---
* Obs.: Para o recebiment de e-mail é preciso adicionar as credenciais no arquivo .env, ex.:

``` 
MAIL_USERNAME=
MAIL_PASSWORD=
```
---



#  Endpoints
#### Criar novo Usuário
```
POST http://localhost:8000/auth/register

Payload:

{
	"name": "Exemplo Silva",
	"email": "exemplo.silva@gmail.com",
	"password": "12345678"
}
```
#### Efetuar login
```
POST http://localhost:8000/auth/login

Payload:

{
	"email": "exemplo.silva@exemplo.com",
	"password": "password"
}
```
#### Criar nova tarefa
```
POST http://localhost:8000/tasks

Payload:

{
	"title": "Tarefa 1",
	"description": "Descrição Tarefa 1",
	"expected_start_date": "2023-12-01 01:00:00",
	"expected_completion_date": "2023-12-02 02:00:00",
	"status": "DONE",
	"user_id_assigned_to": 1
}

Observação:

- Validações de data (data de início não pode ser menor que hoje nem maior que data de final e vice versa) 
```
#### Editar tarefa
```
PUT http://localhost:8000/tasks/{id}

Payload:

{
	"title": "Tarefa 1",
	"description": "Descrição Tarefa 1",
	"expected_start_date": "2023-12-01 01:00:00",
	"expected_completion_date": "2023-12-02 02:00:00",
	"status": "DONE",
	"user_id_assigned_to": 1
}
```
#### Apagar tarefa
```
DELETE http://localhost:8000/tasks/{id}
```
#### Buscar tarefa
```
GET http://localhost:8000/tasks/{id}
```
#### Buscar tarefa por atribuição e status
```
GET http://localhost:8000/tasks?page=1&assigned-to=me&status=done

Parâmetros de rota: 

- page (página, int),
- assigned-to (tarefa atribuída, recebe "me" ou "others" mostrando as tarefas criadas pelo usuário que estão atribuídas a ele mesmo ou a outros usuários),
- status (status da tarefa, pode ser in_progress, done ou canceled).  
 
```
# Observações
- Apenas usuários logados podem buscar, criar, editar e excluir tarefas;
- Apenas tarefas criadas ou atribuídas ao usuário podem ser acessadas ou modificadas.
