# Business Network API

API REST para una red de networking empresarial desarrollada con Laravel 12 y Sanctum.

## Características

- 🔐 **Autenticación JWT** con Laravel Sanctum
- 👥 **Gestión de usuarios** con perfiles empresariales completos
- 🏢 **Sistema de empresas** con información fiscal y membresías
- 📝 **Posts y contenido** con likes y comentarios
- 🤝 **Sistema de conexiones** entre usuarios
- 📅 **Eventos empresariales** con registro de asistencia
- 👤 **Perfiles profesionales** estilo BNI con información completa
- 💼 **Gestión de membresías** y renovaciones
- 🔍 **Búsqueda avanzada** por palabras clave y especialidades
- 📊 **Estadísticas de perfil** y completitud
- 🤝 **Reuniones 1 a 1** entre empresarios con gestión completa
- 📋 **Fichas de referencia** estilo BNI con seguimiento (legacy)
- 💼 **Recomendaciones de negocio** - Usuario A recomienda Usuario C a Usuario B
- 📝 **Seguimiento Uno a Uno** - Registro detallado de reuniones BNI
- 📅 **Filtros por rango de fechas** en reuniones y referencias
- 🎯 **Niveles de interés** y prioridades configurables
- 🔒 **Sistema de roles y permisos** con Spatie Permission
- 📊 **Paginación y filtros** en todos los endpoints

## Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd business-network-api
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**
```bash
# Editar .env con tus credenciales de BD
php artisan migrate
php artisan db:seed
```

5. **Iniciar servidor**
```bash
php artisan serve
```

## Endpoints de la API

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth/register` | Registro de usuario |
| POST | `/api/auth/login` | Inicio de sesión |
| POST | `/api/auth/logout` | Cerrar sesión |
| GET | `/api/auth/me` | Perfil del usuario actual |

### Usuarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/users` | Listar usuarios |
| GET | `/api/users/suggestions` | Sugerencias de conexión |
| GET | `/api/users/{id}` | Ver perfil de usuario |
| PUT | `/api/users/{id}` | Actualizar perfil |

### Posts

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/posts` | Feed de posts |
| POST | `/api/posts` | Crear post |
| GET | `/api/posts/{id}` | Ver post |
| PUT | `/api/posts/{id}` | Actualizar post |
| DELETE | `/api/posts/{id}` | Eliminar post |
| POST | `/api/posts/{id}/like` | Like/Unlike post |

### Conexiones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/connections` | Mis conexiones |
| POST | `/api/connections` | Enviar solicitud |
| GET | `/api/connections/pending` | Solicitudes pendientes |
| GET | `/api/connections/sent` | Solicitudes enviadas |
| PUT | `/api/connections/{id}` | Aceptar/rechazar |
| DELETE | `/api/connections/{id}` | Eliminar conexión |

### Eventos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/events` | Listar eventos |
| POST | `/api/events` | Crear evento |
| GET | `/api/events/{id}` | Ver evento |
| PUT | `/api/events/{id}` | Actualizar evento |
| DELETE | `/api/events/{id}` | Eliminar evento |
| POST | `/api/events/{id}/attend` | Registrarse al evento |
| DELETE | `/api/events/{id}/attend` | Cancelar asistencia |

### Perfil Profesional

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/profile` | Ver perfil completo |
| PUT | `/api/profile/basic` | Actualizar info básica |
| PUT | `/api/profile/professional` | Actualizar info profesional |
| PUT | `/api/profile/tax` | Actualizar info fiscal |
| POST | `/api/profile/avatar` | Subir avatar |
| GET | `/api/profile/stats` | Estadísticas del perfil |
| GET | `/api/profile/search` | Buscar por palabras clave |

### Empresas

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/companies` | Listar empresas |
| POST | `/api/companies` | Crear empresa |
| GET | `/api/companies/{slug}` | Ver empresa |
| PUT | `/api/companies/{id}` | Actualizar empresa |
| POST | `/api/companies/{id}/logo` | Subir logo |
| GET | `/api/companies/{id}/members` | Miembros de la empresa |
| GET | `/api/companies/{id}/stats` | Estadísticas de empresa |

### Reuniones 1 a 1

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/meetings` | Listar reuniones |
| POST | `/api/meetings` | Solicitar reunión |
| GET | `/api/meetings/{id}` | Ver reunión |
| PUT | `/api/meetings/{id}` | Actualizar reunión |
| POST | `/api/meetings/{id}/accept` | Aceptar reunión |
| POST | `/api/meetings/{id}/decline` | Rechazar reunión |
| POST | `/api/meetings/{id}/complete` | Completar reunión |
| GET | `/api/meetings/stats` | Estadísticas de reuniones |

### Fichas de Referencia

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/referrals` | Listar fichas |
| POST | `/api/referrals` | Crear ficha |
| GET | `/api/referrals/{id}` | Ver ficha |
| PUT | `/api/referrals/{id}` | Actualizar ficha |
| POST | `/api/referrals/{id}/send` | Enviar ficha |
| POST | `/api/referrals/{id}/receive` | Marcar como recibida |
| POST | `/api/referrals/{id}/complete` | Completar ficha |
| GET | `/api/referrals/meeting/{id}` | Fichas por reunión |
| GET | `/api/referrals/stats` | Estadísticas de fichas |

### Recomendaciones de Negocio

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/recommendations` | Listar recomendaciones |
| POST | `/api/recommendations` | Crear recomendación |
| GET | `/api/recommendations/{id}` | Ver recomendación |
| PUT | `/api/recommendations/{id}` | Actualizar recomendación |
| POST | `/api/recommendations/{id}/contact` | Marcar como contactado |
| POST | `/api/recommendations/{id}/complete` | Completar recomendación |
| GET | `/api/recommendations/stats` | Estadísticas |
| GET | `/api/recommendations/network` | Red de recomendaciones |

### Seguimiento Uno a Uno

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/follow-ups` | Listar seguimientos |
| POST | `/api/follow-ups` | Crear seguimiento |
| GET | `/api/follow-ups/{id}` | Ver seguimiento |
| PUT | `/api/follow-ups/{id}` | Actualizar seguimiento |
| GET | `/api/follow-ups/stats` | Estadísticas |
| GET | `/api/follow-ups/upcoming` | Próximas reuniones |
| GET | `/api/follow-ups/opportunities` | Oportunidades de negocio |
| GET | `/api/follow-ups/referrals-summary` | Resumen de referencias |

## Ejemplos de uso

### Registro de usuario
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password",
    "password_confirmation": "password",
    "position": "CEO",
    "company_id": 1
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "password"
  }'
```

### Crear un post (requiere autenticación)
```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "¡Hola networking community!",
    "type": "text"
  }'
```

### Enviar solicitud de conexión
```bash
curl -X POST http://localhost:8000/api/connections \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 2,
    "message": "Me gustaría conectar contigo"
  }'
```

## Estructura de la Base de Datos

### Tablas principales:
- `users` - Usuarios del sistema
- `companies` - Empresas
- `posts` - Publicaciones
- `connections` - Conexiones entre usuarios
- `events` - Eventos empresariales
- `post_likes` - Likes en posts
- `post_comments` - Comentarios en posts
- `event_attendees` - Asistentes a eventos

## Tecnologías utilizadas

- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Autenticación API
- **Spatie Laravel Permission** - Gestión de roles y permisos
- **SQLite** - Base de datos (configurable)

## Próximos pasos para Flutter

Para integrar con Flutter, necesitarás:

1. **HTTP Client**: Usar `dio` o `http` package
2. **State Management**: Riverpod, Bloc, o Provider
3. **Secure Storage**: Para almacenar tokens
4. **Models**: Crear modelos Dart que coincidan con la API

### Ejemplo de modelo User en Flutter:
```dart
class User {
  final int id;
  final String name;
  final String email;
  final String? position;
  final Company? company;
  
  User({
    required this.id,
    required this.name,
    required this.email,
    this.position,
    this.company,
  });
  
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      position: json['position'],
      company: json['company'] != null 
        ? Company.fromJson(json['company']) 
        : null,
    );
  }
}
```

## Contribución

1. Fork el proyecto
2. Crea una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la licencia MIT.# business-network-api
