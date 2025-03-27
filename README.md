 PROYECTO M08: 
GRUPO: Asad Saidi, Adrià Sbert, Alex Sanchez y Gerard Calvo
Nombre: CARLINK
Color paletas: #10c875
Nuestra plataforma permite a los usuarios alquilar coches de manera segura y flexible, conectando a propietarios que desean rentar sus vehículos con personas que necesitan uno por un período determinado.
Los usuarios pueden registrarse en dos roles:
Propietarios 🏠🚗 → Publican sus coches, establecen precios y gestionan reservas.
Arrendatarios 👤🔑 → Buscan coches disponibles, reservan y pagan a través de la plataforma.
La web ofrecerá búsqueda avanzada, pagos seguros, mensajería interna, y calificaciones, garantizando confianza y una experiencia fluida.
Ideal para quienes buscan una alternativa más flexible y económica al alquiler tradicional de coches.
Views
📌 Vistas Generales (para todos los usuarios)
Página de Inicio (Home) 🏠
Banner con ofertas o coches destacados.
Barra de búsqueda para encontrar coches por ubicación, precio, marca, etc.
Call-to-action para registrarse o iniciar sesión.
Página de Registro / Inicio de Sesión 🔑
Opción para registrarse como propietario o arrendatario.
Inicio de sesión con email o redes sociales.
Página de Explorar Coches 🚗
Listado de coches disponibles para alquilar.
Filtros avanzados (ubicación, precio, modelo, tipo de combustible, etc.).
Opción de ver detalles del coche.
Página de Detalle del Coche 📄
Fotos y características del vehículo.
Precio por día.
Información del propietario.
Botón para reservar.


🔹 Vistas para el Usuario que Alquila (Arrendatario)
Página de Reserva de Coche 📆
Selección de fechas de alquiler.
Métodos de pago.
Confirmación de la reserva.
Dashboard del Usuario 📊
Historial de reservas.
Reservas activas y futuras.
Estado de pagos y facturación.
Página de Reseñas y Calificación ⭐
Opción para calificar el coche y el propietario.
Ver reseñas de otros usuarios antes de alquilar.
Página de Mensajería 📩
Chat con el propietario del coche para coordinar entrega y devolución.

🔸 Vistas para el Propietario del Coche
Dashboard del Propietario 🚗
Listado de coches puestos en alquiler.
Estado de reservas y ganancias.
Opciones para modificar la disponibilidad del coche.
Página de Publicar un Coche 📤
Formulario para agregar un coche (fotos, descripción, precio, condiciones).
Opciones de disponibilidad.
Página de Gestión de Reservas 📋
Aceptar o rechazar solicitudes de alquiler.
Historial de alquileres.
Página de Pagos y Ganancias 💰
Resumen de ingresos.
Métodos de retiro de dinero.
Historial de transacciones.
Página de Mensajería 📩
Comunicación con los arrendatarios.




TECNOLOGIAS:
🖥️ Frontend (Interfaz de Usuario)
Opciones recomendadas
✅ React.js + Next.js (SSR para mejorar SEO y rendimiento)
✅ Vue.js + Nuxt.js (otra opción similar a Next.js)
✅ Tailwind CSS (para un diseño rápido y moderno)
✅ ShadCN/UI o Material UI (componentes preconstruidos)

⚙️ Backend (Lógica de Negocio y API)
Opciones recomendadas
✅ Node.js + Express.js (rápido y escalable)
✅ NestJS (una opción más estructurada en Node.js)
✅ Django (Python) (rápido de desarrollar, con mucha seguridad integrada)
✅ Spring Boot (Java) (robusto para proyectos grandes)
Si quieres una API escalable, puedes hacerla en GraphQL en vez de REST.

💾 Base de Datos
Opciones recomendadas
✅ PostgreSQL (relacional, ideal para gestionar usuarios y transacciones)
✅ MongoDB (NoSQL, flexible si necesitas escalabilidad rápida)
✅ Redis (para caché y mejorar rendimiento en búsquedas)
Si decides usar PostgreSQL, puedes usar Prisma ORM para simplificar consultas.

🔐 Autenticación y Seguridad
Opciones recomendadas
✅ Firebase Auth (rápido para implementar con Google/Facebook login)
✅ Auth0 (más personalizable para autenticación segura)
✅ JWT (JSON Web Token) (para manejar sesiones de usuarios en tu API)
Si necesitas verificar identidades, puedes integrar Onfido o Stripe Identity.

💳 Pagos y Monetización
Opciones recomendadas
✅ Stripe (fácil integración con suscripciones y pagos por uso)
✅ PayPal (opción extra para más métodos de pago)
✅ MercadoPago (si operas en Latinoamérica)

📡 Hosting y Despliegue
✅ Frontend: Vercel o Netlify (para hosting gratuito con CI/CD)
✅ Backend: AWS (EC2, Lambda) o DigitalOcean (para mayor control)
✅ Base de datos: Supabase o Firebase (si no quieres manejar servidores)
✅ Docker + Kubernetes (si el proyecto escala mucho)

📍 Funcionalidades Extra
🔹 Google Maps API (para geolocalización y calcular distancias)
🔹 Twilio / WhatsApp API (para enviar notificaciones)
🔹 Socket.io (si necesitas chat en tiempo real)
🔹 Cloudinary o Firebase Storage (para almacenar imágenes de coches)

🚀 Stack Recomendado para un MVP Rápido
Si quieres lanzar algo rápido y funcional, te recomiendo:
Frontend: React + Next.js + Tailwind
Backend: Node.js + Express + PostgreSQL
Autenticación: Firebase Auth
Pagos: Stripe
Hosting: Vercel (frontend) + Railway o Supabase (backend + BD)



MINIMO PRODUCTO VIABLE:
🚀 TAREAS INDISPENSABLES (MVP - MÍNIMO PRODUCTO VIABLE)
Estas tareas son esenciales para lanzar la primera versión funcional de CARLINK.
🔹 1. Planificación y Configuración Inicial
✅ Definir funcionalidades clave y alcance del MVP.
 ✅ Configurar repositorio en GitHub.
 ✅ Diseñar wireframes y mockups básicos.

🖥️ 2. Desarrollo del Frontend (HTML, CSS, JavaScript)
📌 Páginas esenciales:
 ✅ Home (Página principal con barra de búsqueda).
 ✅ Registro e Inicio de Sesión (Formulario básico con validación).
 ✅ Explorar Coches (Listado de coches disponibles).
 ✅ Detalle del Coche (Mostrar características y botón para reservar).
📌 Funciones clave:
 ✅ Implementar barra de búsqueda y filtros básicos.
 ✅ Conectar con el backend para mostrar datos reales.
 ✅ Estilizar con CSS (Tailwind o Bootstrap).

⚙️ 3. Desarrollo del Backend (Node.js, Express, PostgreSQL)
✅ Configurar servidor con Express.js.
 ✅ Conectar PostgreSQL con Prisma o Sequelize.
 ✅ Implementar autenticación de usuarios (JWT o Firebase Auth).
 ✅ Desarrollar API REST para usuarios, coches y reservas.
 ✅ Integrar Stripe para pagos (procesos básicos de reserva).

💾 4. Base de Datos (PostgreSQL - Tablas esenciales)
✅ Usuarios → ID, nombre, email, rol (propietario/arrendatario).
 ✅ Coches → ID, propietario_id, marca, modelo, precio, fotos.
 ✅ Reservas → ID, arrendatario_id, coche_id, fecha_inicio, fecha_fin, estado.

📡 5. Integración con WordPress para Diseño
✅ Configurar WordPress y elegir plantilla adecuada.
 ✅ Integrar frontend (HTML, CSS, JS) con API REST de WordPress.

🚀 6. Despliegue y Hosting (para MVP)
✅ Frontend en Vercel o Netlify.
 ✅ Backend en Railway o DigitalOcean.
 ✅ Base de datos en Supabase o AWS RDS.

Aquí tienes las views necesarias para las tareas indispensables (MVP) de CARLINK, organizadas por rol de usuario.

🚀 VIEWS INDISPENSABLES (MVP - MÍNIMO PRODUCTO VIABLE)
📌 Tecnologías: HTML, CSS, JavaScript (frontend) + Node.js, Express, PostgreSQL (backend).

🔹 Vistas Generales (Para todos los usuarios)
✅ 1. Página de Inicio (Home) 🏠
Barra de búsqueda (filtrar coches por ubicación, precio, marca).
Listado de coches destacados.
Botón para registrarse o iniciar sesión.
✅ 2. Página de Registro / Inicio de Sesión 🔑
Formulario para crear cuenta o iniciar sesión.
Opción para elegir rol: propietario o arrendatario.
Validación en tiempo real con JavaScript.
✅ 3. Página de Exploración de Coches 🚗
Listado de coches disponibles.
Filtros básicos (ubicación, precio, marca).
Enlace a la vista Detalle del Coche.
✅ 4. Página de Detalle del Coche 📄
Fotos y descripción del coche.
Precio por día.
Datos básicos del propietario.
Botón de "Reservar".

🔸 Vistas para el Arrendatario (Usuario que alquila)
✅ 5. Página de Reserva de Coche 📆
Selección de fechas de alquiler.
Integración con Stripe para pagos.
Confirmación de reserva.
✅ 6. Dashboard del Usuario (Arrendatario) 📊
Historial de reservas.
Reservas activas y futuras.
Estado de pagos y facturación.

🔹 Vistas para el Propietario (Usuario que pone coches en alquiler)
✅ 7. Dashboard del Propietario 🚗
Listado de coches en alquiler.
Estado de reservas y ganancias.
✅ 8. Página de Publicar un Coche 📤
Formulario para agregar un coche (fotos, descripción, precio).
Opciones básicas de disponibilidad.
Guardar coche en base de datos.
✅ 9. Página de Gestión de Reservas 📋
Listado de solicitudes de alquiler.
Botón para Aceptar / Rechazar reservas.
Historial de alquileres.

📌 RESUMEN FINAL
View
Usuarios
Descripción
Home
Todos
Búsqueda y coches destacados
Registro / Login
Todos
Crear cuenta e iniciar sesión
Explorar Coches
Todos
Ver lista de coches disponibles
Detalle del Coche
Todos
Ver información y reservar
Reserva de Coche
Arrendatario
Seleccionar fechas y pagar
Dashboard del Usuario
Arrendatario
Ver historial y pagos
Dashboard del Propietario
Propietario
Ver coches y ganancias
Publicar un Coche
Propietario
Subir coche con fotos y precio
Gestión de Reservas
Propietario
Aceptar o rechazar reservas


Esto cubre las vistas esenciales para lanzar el MVP.
 🔹 ¿Quieres que diseñemos un flujo de navegación o estructura de rutas para estas views? 😊


Sprint 1: Configuración y Desarrollo Base (Semana 1-2)
Objetivo: Tener la base del proyecto lista (repositorios, autenticación, base de datos, primeras páginas del frontend).
🔹 Configuración Inicial
Definir roles y responsabilidades en el equipo.


Configurar repositorio en GitHub.


Configurar entorno de desarrollo local (Node.js, PostgreSQL, etc.).


Implementar wireframes y mockups.


🔹 Backend (Node.js + Express + PostgreSQL)
Configurar servidor con Express.js.


Crear estructura de rutas en Express.js.


Configurar base de datos PostgreSQL con Prisma o Sequelize.


Definir modelos de base de datos: Usuarios, Coches, Reservas.


Implementar autenticación con Firebase Auth o JWT.


🔹 Frontend (React + Next.js + Tailwind)
Crear estructura base del frontend en Next.js.


Diseñar y desarrollar la Página de Inicio (Home) con barra de búsqueda y coches destacados.


Diseñar y desarrollar la Página de Registro / Inicio de Sesión con validaciones.


Integrar autenticación con Firebase Auth en frontend.


🔹 Base de Datos
Crear tablas en PostgreSQL: Usuarios, Coches, Reservas.


Configurar conexión con Prisma o Sequelize.



Sprint 2: Listado de Coches y Reservas (Semana 3-4)
Objetivo: Mostrar coches en el frontend y permitir reservas básicas.
🔹 Backend
Implementar endpoints para gestionar coches: GET /coches, POST /coches.


Implementar endpoints para gestionar reservas: POST /reservas, GET /reservas.


🔹 Frontend
Diseñar y desarrollar la Página de Exploración de Coches con filtros básicos.


Diseñar y desarrollar la Página de Detalle del Coche con imágenes y botón de reserva.


Implementar conexión con API para mostrar coches en tiempo real.


🔹 Base de Datos
Mejorar queries para filtrar coches por ubicación, precio, modelo.


🔹 Extra
Testear API con Postman o Insomnia.



Sprint 3: Proceso de Reservas y Pagos (Semana 5-6)
Objetivo: Permitir a los usuarios realizar reservas y pagar con Stripe.
🔹 Backend
Implementar lógica para crear reservas.


Implementar integración con Stripe para pagos.


Endpoint para confirmar reserva después del pago.


🔹 Frontend
Diseñar y desarrollar la Página de Reserva de Coche con selección de fechas.


Integrar Stripe en frontend para pagos.


Implementar confirmación de reserva.


🔹 Base de Datos
Agregar estado de la reserva: Pendiente, Confirmada, Rechazada.


🔹 Extra
Testear pagos en entorno de prueba de Stripe.



Sprint 4: Dashboards y Gestión de Reservas (Semana 7-8)
Objetivo: Permitir a propietarios y arrendatarios gestionar sus reservas.
🔹 Backend
Endpoint para que propietarios acepten o rechacen reservas.


Endpoint para ver historial de reservas.


🔹 Frontend
Diseñar y desarrollar Dashboard del Usuario (Historial de reservas).


Diseñar y desarrollar Dashboard del Propietario (Listado de coches y reservas).


Diseñar y desarrollar Página de Gestión de Reservas con opciones para aceptar/rechazar.


🔹 Base de Datos
Mejorar consultas para mostrar historial de reservas.


🔹 Extra
Optimizar rendimiento de API.


Desplegar en Vercel (Frontend) y Railway o DigitalOcean (Backend).


Pruebas finales y feedback.



