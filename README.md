# ☕ Café del Tiempo

> **Bóveda digital privada para proteger, preservar y recuperar la información que más importa.**  
> *A private digital vault for protecting, preserving, and recovering the information that matters most.*

[![Laravel](https://img.shields.io/badge/Laravel-Framework-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Pest](https://img.shields.io/badge/Tests-Pest_PHP-F68512?style=for-the-badge)](https://pestphp.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

---

## 🌟 La Idea

En la era del almacenamiento en la nube masivo y los servicios de suscripción opacos, perdemos de vista dónde están nuestros secretos más íntimos y quién tiene acceso a ellos.

**Café del Tiempo** nace con una premisa simple y serena: **tomarse el tiempo de asegurar lo que de verdad importa**. Es un santuario digital auto-alojado (*self-hosted*), personal y enfocado en la privacidad que combina:

1. **Una Bóveda de Seguridad**: Para salvaguardar credenciales, contraseñas, notas cifradas, llaves de recuperación y secretos bajo tu propio control (*Zero-Knowledge*).
2. **Cápsulas del Tiempo**: Mensajes, reflexiones, memorias y documentos programados para desbloquearse en una fecha futura o bajo condiciones específicas.
3. **Preservación y Legado Digital**: Protocolos de rescate y continuidad para garantizar que tus personas de confianza o tú mismo puedan acceder a información vital ante imprevistos.

---

## 🛡️ Pilares del Proyecto

- 🔐 **Privacidad de Extremo a Extremo**: Diseñado bajo el principio de *Zero-Knowledge*. Tus datos se cifran con claves que sólo tú posees.
- 🏠 **100% Self-Hosted & Soberano**: Sin intermediarios, sin rastreo de actividad, sin anuncios y sin cuotas recurrentes. Corre en tu servidor o en tu máquina local.
- ⏳ **Cápsulas Temporales**: Escribe al futuro. Conserva pensamientos, secretos o documentos que sólo verán la luz cuando el momento indicado llegue.
- 🗝️ **Protocolos de Emergencia y Rescate**: Establece contactos de confianza o llaves maestras de recuperación para proteger tu legado digital.
- ☕ **Experiencia Serena y Cuidada**: Una interfaz minimalista, cálida y sin ruido, pensada para trabajar con tranquilidad.

---

## 🛠️ Stack Tecnológico

- **Framework Backend**: [Laravel](https://laravel.com) (PHP 8.3+)
- **Estilos & UI**: [Tailwind CSS v4](https://tailwindcss.com) & [Vite](https://vite.dev)
- **Tipografía**: Instrument Sans
- **Base de Datos**: SQLite (por defecto, cero configuración) / Compatible con PostgreSQL y MySQL
- **Suite de Pruebas**: [Pest PHP](https://pestphp.com)
- **Linter & Estilo de Código**: [Laravel Pint](https://laravel.com/docs/pint)

---

## 🚀 Instalación y Puesta en Marcha

### Requisitos Previos

- **PHP** >= 8.3 con extensiones recomendadas (`pdo_sqlite`, `mbstring`, `openssl`, etc.)
- **Composer** >= 2.x
- **Node.js** >= 20.x & **npm**

### Pasos

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/xenthrall/cafe-del-tiempo.git
   cd cafe-del-tiempo
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Frontend:**
   ```bash
   npm install
   ```

4. **Configurar el entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Ejecutar migraciones:**
   ```bash
   php artisan migrate
   ```

6. **Compilar recursos o iniciar Vite:**
   ```bash
   # Para desarrollo en caliente (HMR)
   npm run dev

   # O para compilar para producción
   npm run build
   ```

7. **Iniciar el servidor local:**
   ```bash
   php artisan serve
   ```
   Abre [http://localhost:8000](http://localhost:8000) en tu navegador.

---

## 🧪 Pruebas y Calidad de Código

Ejecutar la suite de pruebas con Pest:
```bash
php artisan test --compact
```

Verificar y corregir el estilo del código con Laravel Pint:
```bash
vendor/bin/pint --format agent
```

---

## 🗺️ Hoja de Ruta (Roadmap)

- [x] Arquitectura base Laravel + Tailwind CSS v4 + Pest
- [x] Identidad visual y concepto de Bóveda / Cápsula del Tiempo
- [ ] Módulo de autenticación segura con 2FA y claves de respaldo
- [ ] Bóveda de credenciales y notas con cifrado del lado del cliente (AES-256-GCM)
- [ ] Sistema de Cápsulas del Tiempo con temporizador criptográfico y notificación
- [ ] Contactos de emergencia y protocolo de legado digital (Dead Man's Switch / Claves divididas)
- [ ] Exportación e importación cifrada de respaldos locales
- [ ] PWA (Progressive Web App) y soporte offline

---

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](LICENSE).

---

<p align="center">
  Hecho con ☕ y dedicación para cuidar lo que verdaderamente importa en el tiempo.
</p>