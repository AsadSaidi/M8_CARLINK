<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alquiler de Coches</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    :root {
      --verde-logo: #00bf63;
    }
  </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-gray-800">
  <?php include(../include/header.php)?>

  <!-- Buscador -->
  <section class="bg-white p-6 text-center shadow">
    <h2 class="text-2xl font-semibold mb-4">Encuentra tu coche ideal</h2>
    <input type="text" placeholder="Buscar coche, marca o modelo..." class="w-1/2 p-2 border border-gray-300 rounded">
    <button class="ml-2 px-4 py-2 bg-[var(--verde-logo)] text-white rounded hover:bg-green-600">Buscar</button>
  </section>

  <!-- Lista de Coches -->
  <main class="flex-1 p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="border rounded-lg shadow p-4 bg-white">
      <img src="car1.jpg" alt="Coche 1" class="w-full h-40 object-cover rounded">
      <h3 class="text-lg font-bold mt-2">Toyota Corolla</h3>
      <p>Desde 30€/día</p>
      <button class="mt-2 px-4 py-2 bg-[var(--verde-logo)] text-white rounded hover:bg-green-600">Alquilar</button>
    </div>
    <div class="border rounded-lg shadow p-4 bg-white">
      <img src="car2.jpg" alt="Coche 2" class="w-full h-40 object-cover rounded">
      <h3 class="text-lg font-bold mt-2">Ford Focus</h3>
      <p>Desde 28€/día</p>
      <button class="mt-2 px-4 py-2 bg-[var(--verde-logo)] text-white rounded hover:bg-green-600">Alquilar</button>
    </div>
    <div class="border rounded-lg shadow p-4 bg-white">
      <img src="car3.jpg" alt="Coche 3" class="w-full h-40 object-cover rounded">
      <h3 class="text-lg font-bold mt-2">Volkswagen Golf</h3>
      <p>Desde 35€/día</p>
      <button class="mt-2 px-4 py-2 bg-[var(--verde-logo)] text-white rounded hover:bg-green-600">Alquilar</button>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-[var(--verde-logo)] text-white text-center p-4">
    <p>&copy; 2025 Alquiler de Coches. Todos los derechos reservados.</p>
  </footer>
</body>
</html>