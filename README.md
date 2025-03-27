# 🚗 CARLINK

## 📌 Descripción del Proyecto
CARLINK es una plataforma que permite a los usuarios alquilar coches de manera segura y flexible, conectando a propietarios que desean rentar sus vehículos con personas que necesitan uno por un período determinado.

## 🛠️ Roles de Usuarios
- **Propietarios 🏠🚗** → Publican sus coches, establecen precios y gestionan reservas.
- **Arrendatarios 👤🔑** → Buscan coches disponibles, reservan y pagan a través de la plataforma.

## 🎨 Paleta de Colores
- **Color principal:** `#10c875`

## 🌟 Características Principales
✅ Búsqueda avanzada de coches 🔍
✅ Pagos seguros con Stripe 💳
✅ Mensajería interna entre usuarios 📩
✅ Sistema de calificaciones y reseñas ⭐
✅ Dashboard de gestión de coches y reservas 📊

---

## 📑 Vistas de la Plataforma

### 🔹 **Vistas Generales** (Para todos los usuarios)
- **Página de Inicio (Home) 🏠** → Ofertas destacadas, barra de búsqueda.
- **Página de Registro / Inicio de Sesión 🔑** → Registro con email o redes sociales.
- **Explorar Coches 🚗** → Listado de coches disponibles con filtros.
- **Detalle del Coche 📄** → Información detallada y botón de reserva.

### 🔸 **Vistas para Arrendatarios**
- **Reserva de Coche 📆** → Selección de fechas y pago seguro.
- **Dashboard del Usuario 📊** → Historial y gestión de reservas.
- **Página de Reseñas y Calificación ⭐** → Opiniones sobre coches y propietarios.
- **Mensajería 📩** → Chat con propietarios.

### 🔹 **Vistas para Propietarios**
- **Dashboard del Propietario 🚗** → Listado de coches y ganancias.
- **Publicar un Coche 📤** → Formulario para añadir coches.
- **Gestión de Reservas 📋** → Aceptar o rechazar solicitudes.
- **Pagos y Ganancias 💰** → Historial de ingresos y métodos de retiro.

---

## 🔧 Tecnologías Utilizadas

### 🖥️ **Frontend**
✅ React.js + Next.js (SSR para mejor SEO y rendimiento)
✅ Tailwind CSS (Diseño moderno y rápido)
✅ ShadCN/UI o Material UI (Componentes preconstruidos)

### ⚙️ **Backend**
✅ Node.js + Express.js (Rápido y escalable)
✅ PostgreSQL (Base de datos relacional)
✅ Prisma ORM (Gestión simplificada de la BD)
✅ Autenticación con Firebase Auth o JWT
✅ Pagos con Stripe

### 📡 **Hosting y Despliegue**
✅ **Frontend:** Vercel o Netlify
✅ **Backend:** Railway o DigitalOcean
✅ **Base de Datos:** Supabase o AWS RDS

---

## 🚀 **MVP - Mínimo Producto Viable**

### 📌 **Fases del MVP**

#### **Sprint 1: Configuración y Desarrollo Base (Semana 1-2)**
- ✅ Configurar repositorio en GitHub.
- ✅ Implementar wireframes y mockups.
- ✅ Backend: Configurar servidor con Express.js y PostgreSQL.
- ✅ Frontend: Crear estructura base con Next.js y Tailwind.
- ✅ Implementar autenticación con Firebase Auth o JWT.

#### **Sprint 2: Listado de Coches y Reservas (Semana 3-4)**
- ✅ Backend: Endpoints para coches y reservas.
- ✅ Frontend: Página de exploración de coches con filtros.
- ✅ Implementar conexión API para mostrar coches en tiempo real.

#### **Sprint 3: Proceso de Reservas y Pagos (Semana 5-6)**
- ✅ Backend: Integración de pagos con Stripe.
- ✅ Frontend: Página de reserva de coche y confirmación de pago.
- ✅ Base de datos: Estado de la reserva (Pendiente, Confirmada, Rechazada).

#### **Sprint 4: Dashboards y Gestión de Reservas (Semana 7-8)**
- ✅ Backend: Endpoints para aceptar/rechazar reservas y ver historial.
- ✅ Frontend: Dashboard del usuario y propietario.
- ✅ Página de gestión de reservas con opciones de aprobación.

---

## 📍 **Funcionalidades Extra** (Futuras Mejoras)
- 🔹 Google Maps API (Geolocalización y distancia)
- 🔹 Twilio / WhatsApp API (Notificaciones)
- 🔹 Socket.io (Chat en tiempo real)
- 🔹 Cloudinary o Firebase Storage (Almacenamiento de imágenes)

