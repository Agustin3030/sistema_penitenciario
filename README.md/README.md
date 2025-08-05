# 📌 Sistema de Gestión Penitenciaria

Este proyecto es un sistema web desarrollado en PHP y MySQL que permite registrar y gestionar novedades asociadas a personas privadas de la libertad. Incluye funcionalidades de login, roles, registro de notas por tipo y severidad, historial de acciones, y filtrado de novedades.

---

## 🧠 Funcionalidades principales

- ✅ Autenticación de usuarios (con roles)
- 📝 Registro de novedades (conducta, sanciones, visitas, incidentes, atención médica, etc.)
- ⚠️ Manejo de severidad (leve, moderada, grave)
- 📊 Filtro por tipo de nota o interno
- 🕓 Registro de fecha del incidente y fecha de carga
- 👮 Historial de acciones realizadas por los usuarios
- 🔒 Seguridad con `password_hash`, sesiones y validaciones

---

## 🛠️ Tecnologías utilizadas

- **Lenguaje backend:** PHP
- **Base de datos:** MySQL
- **Frontend:** HTML + CSS
- **Servidor local recomendado:** XAMPP o WAMP

---

## 📂 Estructura del proyecto

# 📌 Sistema de Gestión Penitenciaria

Este es un sistema web desarrollado en **PHP** y **MySQL**, pensado para registrar y gestionar información relacionada con personas privadas de la libertad. Está diseñado para ser utilizado en contextos institucionales como servicios penitenciarios, y cuenta con autenticación de usuarios, control de roles, gestión de novedades, estados legales, sanciones y más.

---

## 🚀 Funcionalidades Principales

- 🔐 Autenticación segura de usuarios con control de sesiones
- 👥 Gestión de personas privadas de la libertad (internos)
- 📝 Registro de novedades por tipo y severidad
- ⚠️ Clasificación por niveles de riesgo (bajo, medio, alto, máximo)
- 📅 Registro de fecha del incidente y fecha de carga
- 🔎 Filtros avanzados por tipo de nota, interno y fecha
- 📊 Panel con estadísticas y reportes
- 📂 Historial de acciones del sistema

---

## 👤 Roles de Usuario

- **Administrador**: Acceso total al sistema, usuarios e internos
- **Dirección**: Lectura de registros y novedades sin edición
- **Celador**: Registro y seguimiento de novedades

---

## 🛠️ Tecnologías Utilizadas

| Tecnología     | Uso Principal                       |
|----------------|-------------------------------------|
| PHP 7+         | Lógica backend                      |
| MySQL          | Base de datos relacional            |
| HTML/CSS/JS    | Interfaz de usuario (frontend)      |
| Font Awesome   | Iconografía                         |
| Google Fonts   | Tipografía                          |
| Git            | Control de versiones                |

---

## 📁 Estructura del Proyecto

sistema_penitenciario/
├── assets/ # Estilos y scripts
│ ├── css/
│ └── js/
├── includes/
│ └── conexion.php # Conexión a la base de datos
├── sistema/
│ ├── actualizar_estado.php
│ ├── agregarpersona.php
│ ├── cargar_nota.php
│ ├── ficha_interno.php
│ ├── listadealojados.php
│ ├── registrar_usuario.php
│ └── ver_notas.php
├── index.php # Panel principal
├── login.php # Formulario de acceso
├── logout.php # Cierre de sesión
└── README.md # Documentación

yaml
Copiar
Editar

---

## ⚙️ Requisitos del Sistema

- Servidor web con **PHP 7.0+**
- **MySQL 5.7+**
- Extensiones PHP:
  - `mysqli`
  - `session`
  - `json`

---

## 🧪 Instalación

1. Cloná este repositorio:

```bash
git clone https://github.com/Agustin3030/sistema_penitenciario.git
Importá la base de datos:

Abrí phpMyAdmin u otra herramienta

Importá el archivo SQL desde /database/schema.sql

Configurá la conexión:

Editá el archivo includes/conexion.php con tus datos locales

Accedé al sistema:

h
Copiar
Editar
http://localhost/sistema_penitenciario/
🔐 Credenciales Iniciales
Usuario	Contraseña	Rol
admin	admin123	Administrador

⚠️ Se recomienda cambiar la contraseña luego del primer login.

🛡️ Seguridad
Contraseñas encriptadas con password_hash (BCRYPT)

Uso de consultas preparadas (evita SQL Injection)

Validación en formularios

Control de sesiones y cierre automático

Protección básica CSRF en formularios críticos

📈 Roadmap (Próximas Mejoras)
Exportación de reportes en PDF

Dashboard con gráficos estadísticos

Módulo de alertas por reincidencia

Agendamiento de visitas

API REST para consumo externo

🤝 Contribuciones
¡Tu ayuda es bienvenida!

Fork del repositorio

Creá una nueva rama: git checkout -b feature/NuevaFuncionalidad

Hacé tus cambios y commit: git commit -m 'Agrego nueva funcionalidad'

Push a tu rama: git push origin feature/NuevaFuncionalidad

Abrí un Pull Request

📄 Licencia
Este proyecto está bajo la Licencia MIT. Ver el archivo LICENSE para más información.

✉️ Contacto
📧 agussantana30@gmail.com
🔗 https://www.linkedin.com/in/agustinesantana/
💻 Proyecto en GitHub: Agustin3030/sistema_penitenciario