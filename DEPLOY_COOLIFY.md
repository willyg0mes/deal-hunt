# 🚀 Deploy do Deal Hunt no Coolify

Guia completo para fazer deploy do Deal Hunt no Coolify usando Docker Compose.

## 📋 Pré-requisitos

- [ ] Conta no Coolify configurada
- [ ] Servidor com Docker instalado
- [ ] Domínio configurado (opcional, mas recomendado)
- [ ] Chaves de API necessárias:
  - RAWG API Key (obrigatório)
  - Pusher credentials (para notificações)
  - Google Translate API Key (opcional)

---

## 🎯 Passo a Passo

### 1. Preparar o Repositório

Certifique-se que seu repositório Git tem os seguintes arquivos:
- ✅ `Dockerfile`
- ✅ `docker-compose.yml`
- ✅ `.dockerignore`
- ✅ `ENV_DOCKER_EXAMPLE.md`

### 2. Configurar Projeto no Coolify

#### 2.1. Criar Novo Projeto

1. Acesse o painel do Coolify
2. Clique em **"New Project"**
3. Escolha **"Docker Compose"** como tipo
4. Conecte seu repositório Git

#### 2.2. Configurar o Repositório

- **Repository**: `https://github.com/seu-usuario/deal-hunt.git`
- **Branch**: `main`
- **Build Pack**: Docker Compose
- **Docker Compose Location**: `/` (raiz do projeto)

### 3. Configurar Variáveis de Ambiente

No Coolify, vá em **Environment Variables** e adicione **TODAS** as variáveis do arquivo `ENV_DOCKER_EXAMPLE.md`.

#### 3.1. Variáveis Obrigatórias

```env
# APP
APP_NAME=Deal Hunt
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# DATABASE
DB_DATABASE=deal_hunt
DB_USERNAME=deal_hunt_user
DB_PASSWORD=SENHA_SEGURA_AQUI
DB_ROOT_PASSWORD=ROOT_PASSWORD_AQUI

# REDIS
REDIS_PASSWORD=REDIS_PASSWORD_AQUI

# APIs
RAWG_API_KEY=sua_rawg_api_key
```

#### 3.2. Gerar APP_KEY

Execute localmente ou use Docker:

```bash
# Localmente (se tiver PHP)
php artisan key:generate --show

# Ou via Docker
docker run --rm php:8.2-cli php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

Adicione o resultado em `APP_KEY`:
```env
APP_KEY=base64:resultado_aqui
```

### 4. Configurar Domínio e SSL

#### 4.1. Adicionar Domínio

1. No Coolify, vá em **Domains**
2. Adicione seu domínio: `dealhunt.com.br`
3. Configure DNS para apontar para o IP do servidor:
   ```
   A    @     SEU_IP_DO_SERVIDOR
   A    www   SEU_IP_DO_SERVIDOR
   ```

#### 4.2. Habilitar SSL (Let's Encrypt)

O Coolify configura SSL automaticamente. Certifique-se que:
- ✅ **"Enable SSL"** está marcado
- ✅ **"Force HTTPS"** está ativado

### 5. Configurar Portas

No Coolify, configure as portas:

- **App Port**: `8000` → Porta pública (80/443)
- **MySQL** (opcional): `3307` → Acesso externo ao banco
- **phpMyAdmin** (dev): `8080` → Interface do banco

### 6. Deploy Inicial

1. Clique em **"Deploy"**
2. Acompanhe os logs de build
3. Aguarde a conclusão (pode levar 5-10 minutos no primeiro deploy)

### 7. Verificar Saúde da Aplicação

Após o deploy, verifique:

```bash
# Health Check
curl https://seu-dominio.com/api/health

# Resposta esperada:
{"status":"ok"}
```

### 8. Executar Migrações (Se Necessário)

Se as migrações não rodarem automaticamente:

```bash
# Via Coolify Terminal
docker exec -it deal-hunt-app php artisan migrate --force

# Ou via SSH no servidor
docker exec deal-hunt-app php artisan migrate --force
```

### 9. Configurar Tarefas Agendadas (Opcional)

O container `scheduler` já está configurado no `docker-compose.yml` e roda automaticamente.

Para verificar:
```bash
docker logs deal-hunt-scheduler
```

### 10. Monitorar Logs

No Coolify ou via SSH:

```bash
# Logs da aplicação
docker logs -f deal-hunt-app

# Logs do queue worker
docker logs -f deal-hunt-queue

# Logs do scheduler
docker logs -f deal-hunt-scheduler

# Todos os logs
docker-compose logs -f
```

---

## 🔧 Configurações Avançadas

### Escalar Workers de Fila

Se precisar de mais workers para processar jobs:

```bash
# Via Coolify ou diretamente no servidor
docker-compose up -d --scale queue=3
```

### Backup do Banco de Dados

```bash
# Criar backup
docker exec deal-hunt-mysql mysqldump -u root -p'ROOT_PASSWORD' deal_hunt > backup.sql

# Restaurar backup
docker exec -i deal-hunt-mysql mysql -u root -p'ROOT_PASSWORD' deal_hunt < backup.sql
```

### Atualizar a Aplicação

1. Faça push das mudanças para o Git
2. No Coolify, clique em **"Redeploy"**
3. Ou configure auto-deploy em cada push

### Acessar Console do Laravel

```bash
# Via Coolify Terminal
docker exec -it deal-hunt-app php artisan tinker

# Ou
docker exec -it deal-hunt-app bash
php artisan tinker
```

---

## 🐛 Troubleshooting

### ❌ Erro: "Connection refused" (Database)

**Causa**: O app está tentando conectar antes do MySQL estar pronto.

**Solução**: O healthcheck do MySQL já está configurado. Aguarde mais tempo ou verifique:

```bash
docker logs deal-hunt-mysql
```

### ❌ Erro: "No application encryption key"

**Causa**: `APP_KEY` não está configurada.

**Solução**:
```bash
# Gerar key
docker exec deal-hunt-app php artisan key:generate

# Ou configure manualmente no Coolify
```

### ❌ Erro: "Permission denied" (Storage)

**Causa**: Permissões incorretas na pasta storage.

**Solução**:
```bash
docker exec deal-hunt-app chown -R www-data:www-data /var/www/html/storage
docker exec deal-hunt-app chmod -R 775 /var/www/html/storage
```

### ❌ Jobs não estão sendo processados

**Causa**: Queue worker não está rodando.

**Solução**:
```bash
# Verificar status
docker ps | grep queue

# Reiniciar queue
docker-compose restart queue
```

### ❌ Assets (CSS/JS) não carregam

**Causa**: Build do Vite não foi executado ou APP_URL está incorreta.

**Solução**:
1. Verifique `APP_URL` nas variáveis de ambiente
2. Rebuild da aplicação:
   ```bash
   docker-compose down
   docker-compose up -d --build
   ```

---

## 📊 Monitoramento

### Métricas Importantes

- **CPU/RAM**: Monitore no painel do Coolify
- **Espaço em Disco**: Volumes do Docker podem crescer
- **Logs**: Configure rotação de logs

### Limpeza de Logs

```bash
# Limpar logs antigos do Laravel
docker exec deal-hunt-app php artisan log:clear

# Limpar cache
docker exec deal-hunt-app php artisan cache:clear
```

---

## 🔐 Segurança

### Checklist de Segurança

- [ ] `APP_DEBUG=false` em produção
- [ ] Senhas fortes para DB e Redis
- [ ] SSL/HTTPS habilitado
- [ ] Firewall configurado (apenas portas 80, 443, 22)
- [ ] Backups automáticos configurados
- [ ] Variáveis de ambiente não commitadas no Git
- [ ] phpMyAdmin desabilitado em produção

### Desabilitar phpMyAdmin em Produção

Remova ou comente o serviço no `docker-compose.yml`:

```yaml
# Remova ou comente estas linhas:
# phpmyadmin:
#   image: phpmyadmin:latest
#   ...
```

---

## 🎯 Otimizações para Produção

### 1. Cache de Configuração

```bash
docker exec deal-hunt-app php artisan config:cache
docker exec deal-hunt-app php artisan route:cache
docker exec deal-hunt-app php artisan view:cache
```

### 2. Otimização do Composer

Já está configurado no Dockerfile com `--optimize-autoloader --no-dev`

### 3. Redis para Performance

Redis já está configurado para:
- ✅ Cache
- ✅ Sessions
- ✅ Queue

### 4. Monitorar Performance

Use Laravel Telescope (adicione se necessário):
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

---

## 📞 Suporte

- **Documentação Coolify**: https://coolify.io/docs
- **Documentação Laravel**: https://laravel.com/docs
- **Issues do Projeto**: GitHub Issues

---

## ✅ Checklist Final

Antes de considerar o deploy completo:

- [ ] Aplicação acessível via HTTPS
- [ ] SSL válido e funcionando
- [ ] Health check retorna `{"status":"ok"}`
- [ ] Login e registro funcionando
- [ ] Notificações em tempo real funcionando (Pusher)
- [ ] Jobs sendo processados (queue)
- [ ] Scheduler executando tarefas agendadas
- [ ] Emails sendo enviados
- [ ] Jogos sendo exibidos corretamente
- [ ] Wishlist funcionando
- [ ] Conversão de moedas funcionando
- [ ] Logs não apresentam erros críticos
- [ ] Backup configurado

---

<div align="center">
  <strong>🎮 Bom Deploy! 🚀</strong>
</div>

