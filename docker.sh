#!/bin/bash

# Deal Hunt - Docker Helper Script
# Este script facilita operações comuns com Docker Compose

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funções auxiliares
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

# Verificar se docker-compose está instalado
check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker não está instalado!"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose não está instalado!"
        exit 1
    fi
    
    print_success "Docker e Docker Compose encontrados"
}

# Comandos
case "$1" in
    "start"|"up")
        print_header "Iniciando Deal Hunt"
        check_docker
        docker-compose up -d
        print_success "Serviços iniciados!"
        print_info "Acesse: http://localhost:8000"
        ;;
        
    "stop"|"down")
        print_header "Parando Deal Hunt"
        docker-compose down
        print_success "Serviços parados!"
        ;;
        
    "restart")
        print_header "Reiniciando Deal Hunt"
        docker-compose restart
        print_success "Serviços reiniciados!"
        ;;
        
    "build")
        print_header "Construindo imagens"
        docker-compose build --no-cache
        print_success "Build concluído!"
        ;;
        
    "rebuild")
        print_header "Reconstruindo e reiniciando"
        docker-compose down
        docker-compose build --no-cache
        docker-compose up -d
        print_success "Serviços reconstruídos e iniciados!"
        ;;
        
    "logs")
        SERVICE=${2:-""}
        if [ -z "$SERVICE" ]; then
            print_header "Logs de todos os serviços"
            docker-compose logs -f
        else
            print_header "Logs do serviço: $SERVICE"
            docker-compose logs -f $SERVICE
        fi
        ;;
        
    "shell"|"bash")
        print_header "Acessando shell do container"
        docker-compose exec app bash
        ;;
        
    "tinker")
        print_header "Laravel Tinker"
        docker-compose exec app php artisan tinker
        ;;
        
    "migrate")
        print_header "Executando migrações"
        docker-compose exec app php artisan migrate --force
        print_success "Migrações executadas!"
        ;;
        
    "migrate:fresh")
        print_header "Recriando banco de dados"
        print_warning "ATENÇÃO: Todos os dados serão perdidos!"
        read -p "Tem certeza? (y/N) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            docker-compose exec app php artisan migrate:fresh --seed --force
            print_success "Banco recriado!"
        else
            print_info "Operação cancelada"
        fi
        ;;
        
    "seed")
        print_header "Executando seeders"
        docker-compose exec app php artisan db:seed --force
        print_success "Seeders executados!"
        ;;
        
    "cache:clear")
        print_header "Limpando cache"
        docker-compose exec app php artisan cache:clear
        docker-compose exec app php artisan config:clear
        docker-compose exec app php artisan route:clear
        docker-compose exec app php artisan view:clear
        print_success "Cache limpo!"
        ;;
        
    "optimize")
        print_header "Otimizando aplicação"
        docker-compose exec app php artisan config:cache
        docker-compose exec app php artisan route:cache
        docker-compose exec app php artisan view:cache
        print_success "Otimização concluída!"
        ;;
        
    "queue:restart")
        print_header "Reiniciando queue worker"
        docker-compose restart queue
        print_success "Queue worker reiniciado!"
        ;;
        
    "status"|"ps")
        print_header "Status dos serviços"
        docker-compose ps
        ;;
        
    "stats")
        print_header "Estatísticas de uso"
        docker stats --no-stream
        ;;
        
    "backup")
        print_header "Criando backup do banco de dados"
        TIMESTAMP=$(date +%Y%m%d_%H%M%S)
        BACKUP_FILE="backup_${TIMESTAMP}.sql"
        
        docker-compose exec -T mysql mysqldump -u root -p${DB_ROOT_PASSWORD} deal_hunt > "backups/$BACKUP_FILE"
        print_success "Backup criado: backups/$BACKUP_FILE"
        ;;
        
    "restore")
        if [ -z "$2" ]; then
            print_error "Especifique o arquivo de backup"
            print_info "Uso: ./docker.sh restore backup_20240101_120000.sql"
            exit 1
        fi
        
        print_header "Restaurando backup: $2"
        docker-compose exec -T mysql mysql -u root -p${DB_ROOT_PASSWORD} deal_hunt < "backups/$2"
        print_success "Backup restaurado!"
        ;;
        
    "artisan")
        shift
        print_header "Executando: php artisan $@"
        docker-compose exec app php artisan "$@"
        ;;
        
    "composer")
        shift
        print_header "Executando: composer $@"
        docker-compose exec app composer "$@"
        ;;
        
    "npm")
        shift
        print_header "Executando: npm $@"
        docker-compose exec app npm "$@"
        ;;
        
    "test")
        print_header "Executando testes"
        docker-compose exec app php artisan test
        ;;
        
    "clean")
        print_header "Limpando containers, volumes e imagens"
        print_warning "ATENÇÃO: Todos os dados dos volumes serão perdidos!"
        read -p "Tem certeza? (y/N) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            docker-compose down -v
            docker system prune -af
            print_success "Limpeza concluída!"
        else
            print_info "Operação cancelada"
        fi
        ;;
        
    "health")
        print_header "Verificando saúde da aplicação"
        
        # Verificar se os containers estão rodando
        if ! docker-compose ps | grep -q "Up"; then
            print_error "Containers não estão rodando"
            exit 1
        fi
        
        # Verificar health endpoint
        RESPONSE=$(curl -s http://localhost:8000/api/health)
        STATUS=$(echo $RESPONSE | grep -o '"status":"[^"]*"' | cut -d'"' -f4)
        
        if [ "$STATUS" = "ok" ]; then
            print_success "Aplicação está saudável!"
            echo "$RESPONSE" | jq '.'
        else
            print_error "Aplicação com problemas!"
            echo "$RESPONSE" | jq '.'
            exit 1
        fi
        ;;
        
    "help"|*)
        print_header "Deal Hunt - Docker Helper"
        echo ""
        echo "Uso: ./docker.sh [comando] [opções]"
        echo ""
        echo "Comandos disponíveis:"
        echo ""
        echo "  ${GREEN}Gerenciamento de Serviços:${NC}"
        echo "    start, up          Inicia todos os serviços"
        echo "    stop, down         Para todos os serviços"
        echo "    restart            Reinicia todos os serviços"
        echo "    build              Constrói as imagens"
        echo "    rebuild            Reconstrói tudo do zero"
        echo "    status, ps         Status dos serviços"
        echo "    stats              Estatísticas de uso"
        echo ""
        echo "  ${GREEN}Logs e Debug:${NC}"
        echo "    logs [serviço]     Mostra logs (app, queue, mysql, redis)"
        echo "    health             Verifica saúde da aplicação"
        echo "    shell, bash        Acessa shell do container"
        echo ""
        echo "  ${GREEN}Laravel:${NC}"
        echo "    artisan [cmd]      Executa comando artisan"
        echo "    tinker             Acessa Laravel Tinker"
        echo "    migrate            Executa migrações"
        echo "    migrate:fresh      Recria banco (CUIDADO!)"
        echo "    seed               Executa seeders"
        echo "    test               Executa testes"
        echo ""
        echo "  ${GREEN}Cache:${NC}"
        echo "    cache:clear        Limpa todo o cache"
        echo "    optimize           Otimiza aplicação (cache)"
        echo "    queue:restart      Reinicia queue worker"
        echo ""
        echo "  ${GREEN}Backup:${NC}"
        echo "    backup             Cria backup do banco"
        echo "    restore [file]     Restaura backup"
        echo ""
        echo "  ${GREEN}Ferramentas:${NC}"
        echo "    composer [cmd]     Executa comando composer"
        echo "    npm [cmd]          Executa comando npm"
        echo "    clean              Limpa tudo (CUIDADO!)"
        echo ""
        echo "  ${GREEN}Ajuda:${NC}"
        echo "    help               Mostra esta ajuda"
        echo ""
        echo "Exemplos:"
        echo "  ./docker.sh start"
        echo "  ./docker.sh logs app"
        echo "  ./docker.sh artisan migrate"
        echo "  ./docker.sh composer install"
        echo "  ./docker.sh backup"
        echo ""
        ;;
esac

