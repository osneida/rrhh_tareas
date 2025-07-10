# RRHH Tareas

![Captura de la aplicación](public/sib.jpg)

Aplicación desarrollada con **Laravel 12** y **Livewire** para la gestión de tareas, empleados y clientes. Permite asignar tareas, calcular sueldos según horas trabajadas y soporta múltiples idiomas.

---

## Tabla de Contenido
- [Características](#características)
- [Vista General](#vista-general)
- [Instalación](#instalación)
- [Uso](#uso)
- [Tabla de Funcionalidades](#tabla-de-funcionalidades)
- [Licencia](#licencia)

---

## Características
- Gestión de tareas para clientes y empleados
- Creación de tareas individuales o en lote
- Cálculo automático de sueldo mensual por horas trabajadas
- Soporte para Español, Inglés y Catalán
- Dashboard para empleados con botones de inicio y fin de tarea

## Vista General

En el Dashboard de cada empleado se visualizan los botones para marcar el inicio y fin de la tarea diaria. Si no hay tarea asignada para el día, se muestra un mensaje indicativo.

---

## Instalación

Sigue estos pasos para instalar y ejecutar el proyecto:

```bash
# 1. Clonar el proyecto
git clone https://github.com/osneida/rrhh_tareas.git

# 2. Instalar dependencias de PHP
composer install

# 3. Instalar dependencias de Node.js
npm install

# 4. Generar la APP_KEY
php artisan key:generate

# 5. Copiar el archivo de entorno y configurar
cp .env.example .env
# Edita los siguientes valores en .env:
# DB_CONNECTION=sqlite|mysql|pgsql
# DB_HOST=...
# DB_PORT=...
# DB_DATABASE=...
# DB_USERNAME=...
# DB_PASSWORD=...
# Configura el idioma por defecto

# 6. Crear la base de datos (si es necesario)

# 7. Ejecutar migraciones y seeders
php artisan migrate --seed

# 8. Levanta el servidor
composer run dev

# 9. Iniciar sesión
# Email: admin@test.com
# Password: 12345678
```

---

## Uso

1. Crear empleados y clientes desde el panel de administración.
2. Crear tareas individuales o en grupo.
3. Los empleados pueden ver y marcar el inicio/fin de sus tareas diarias.

---

## Tabla de Funcionalidades

### Para Administradores
- Cambiar idioma: Español, Inglés, Catalán
- CRUD de Empleados
- CRUD de Clientes
- CRUD de Tareas independientes
- CRUD de Tareas por grupo
- Ver Jornadas Laborales (tareas concluidas por empleados)
- Resumen de horas trabajadas por empleado en el mes

### Para Empleados
- Ver mis tareas
- Ver mis jornadas
- Marcar inicio y fin de tarea diaria
- Mensaje si no hay tarea asignada para hoy

---

## Licencia

Este proyecto es de uso privado. Para más información, contacta al autor.

---

> _Puedes agregar más capturas de pantalla o gifs en la sección de Vista General para ilustrar el funcionamiento._





