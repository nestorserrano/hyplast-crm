# Hyplast CRM - Sistema de Gestión de Relaciones con Clientes

## Descripción
Sistema CRM completo para gestión de leads, clientes, oportunidades de ventas, seguimiento de actividades y análisis de conversaciones.

## Características Principales
- 📊 Gestión de Leads con estados personalizables
- 📝 Sistema de tareas y seguimiento
- 💬 Integración con WhatsApp para conversaciones
- 📧 Gestión de emails y actividades
- 📈 Dashboard de ventas y métricas
- 🔔 Sistema de notificaciones en tiempo real
- 🌍 Soporte multi-empresa
- 🎯 Asignación inteligente de leads a vendedores

## Estructura del Proyecto
```
hyplast-crm/
├── app/
│   ├── Http/Controllers/
│   │   ├── LeadController.php
│   │   ├── CrmDashboardController.php
│   │   ├── ConversationController.php
│   │   └── LeadTaskController.php
│   ├── Models/
│   │   ├── Lead.php
│   │   ├── LeadStatus.php
│   │   ├── LeadTask.php
│   │   ├── CrmConversation.php
│   │   └── CrmMessage.php
│   └── Services/
│       └── CrmService.php
├── database/migrations/
├── resources/views/
└── routes/
```

## Requisitos
- PHP >= 8.1
- Laravel >= 10.x
- MySQL/MariaDB
- Composer

## Instalación
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=CrmSeeder
```

## Configuración
Editar el archivo `.env` con las credenciales de tu base de datos y servicios externos.

## Modelos Principales
- **Lead**: Prospectos y clientes potenciales
- **LeadStatus**: Estados del embudo de ventas
- **LeadTask**: Tareas asignadas a los leads
- **LeadNote**: Notas y comentarios
- **LeadActivity**: Registro de actividades
- **CrmConversation**: Conversaciones con clientes
- **CrmMessage**: Mensajes de conversaciones

## API Endpoints
```
GET    /api/crm/leads              # Listar leads
POST   /api/crm/leads              # Crear lead
GET    /api/crm/leads/{id}         # Ver lead
PUT    /api/crm/leads/{id}         # Actualizar lead
DELETE /api/crm/leads/{id}         # Eliminar lead
GET    /api/crm/dashboard          # Métricas del dashboard
```

## Permisos Requeridos
- `view-crm`: Ver módulo CRM
- `create-leads`: Crear leads
- `edit-leads`: Editar leads
- `delete-leads`: Eliminar leads
- `assign-leads`: Asignar leads a vendedores
- `view-all-leads`: Ver todos los leads (gerentes)

## Integraciones
- WhatsApp Business API
- Email (SMTP/Microsoft)
- Sistema de permisos Hyplast

## Desarrollo
```bash
# Ejecutar tests
php artisan test --filter=Crm

# Limpiar cache
php artisan cache:clear
php artisan config:clear
```

## Autor y Propietario
**Néstor Serrano**  
Desarrollador Full Stack  
GitHub: [@nestorserrano](https://github.com/nestorserrano)

## Licencia
Propietario - © 2026 Néstor Serrano. Todos los derechos reservados.

## Documentación Adicional
Ver archivos en el proyecto principal:
- CRM_README.md
- CRM_MULTIEMPRESA_README.md
- CRM_SISTEMA_NOTAS.md
- CRM_KANBAN_BOARD.md
