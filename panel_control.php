<?php
// Archivo: panel_control.php

// Incluye el archivo de conexión y la función para verificar la sesión
include_once 'includes/conexion.php';
check_user_logged_in();

// Determina el perfil del usuario actual para mostrar las opciones correctas.
$user_perfil = $_SESSION['perfil'] ?? 'usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <!-- Incluye el CSS principal -->
    <link rel="stylesheet" href="css/style.css">
    <!-- CDN de Tailwind CSS para los estilos de la interfaz -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fuente Inter de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- CDN para Dragula.js para la funcionalidad de arrastrar y soltar -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }
        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.7);
        }
        .dark .navbar {
            background-color: rgba(26, 32, 44, 0.7);
        }
        .gu-mirror {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 0.75rem;
            opacity: 0.8;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
        }
        .dark .gu-mirror {
            background-color: rgba(45, 55, 72, 0.8);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
        }

        /* Estilos específicos para la vista de lista */
        .list-view .tile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-direction: row;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem; /* Menos redondeado para una lista */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); /* Sombra más sutil */
        }
        .list-view .tile h3 {
            font-size: 1.125rem; /* Tamaño de fuente más pequeño para la lista */
            margin-bottom: 0;
        }
        .list-view .tile p {
            display: none; /* Oculta la descripción en la vista de lista */
        }
        .list-view .tile .svg-icon {
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-500 text-gray-800 dark:text-white">

    <main class="max-w-7xl mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-4xl font-bold">
                Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>
            </h2>
            <!-- Botones para cambiar la vista y accesos rápidos -->
            <div class="flex gap-2">
                <!-- Botones de vista -->
                <button id="grid-view-btn" class="p-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200">
                    <!-- Icono de cuadrícula (grid) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z"/>
                    </svg>
                </button>
                <button id="list-view-btn" class="p-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200">
                    <!-- Icono de lista -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
                    </svg>
                </button>
                
                <!-- Nuevos botones de acceso rápido -->
                <a href="perfil.php" class="p-2 rounded-md bg-purple-600 text-white hover:bg-purple-700 transition-colors duration-200" title="Ver mi Perfil">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </a>
                <a href="logout.php" class="p-2 rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors duration-200" title="Cerrar Sesión">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h9V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h9v-2H4V5z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Contenedor de las tiles (arrastrables). Las clases cambian con JS -->
        <div id="tiles-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 cursor-grab">
            <?php if ($user_perfil == 'administrador'): ?>
                <!-- Tiles solo para ADMINISTRADORES -->
                <a href="items.php" class="tile block bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center text-blue-600 dark:text-blue-400 mb-4 svg-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21 5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5zM5 19V5h14l.01 14H5zm4-4h6V9H9v6z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Stock</h3>
                        <p class="text-gray-600 dark:text-gray-400">Gestiona los artículos en el inventario.</p>
                    </div>
                </a>
                <a href="actualizar_stock.php" class="tile block bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center text-blue-600 dark:text-blue-400 mb-4 svg-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21 5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5zM5 19V5h14l.01 14H5zm4-4h6V9H9v6z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Actualizar stock</h3>
                        <p class="text-gray-600 dark:text-gray-400">Actualiza el stock del inventario.</p>
                    </div>
                </a>
                <a href="admin.php" class="tile block bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center text-green-600 dark:text-green-400 mb-4 svg-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Gestión de Usuarios</h3>
                        <p class="text-gray-600 dark:text-gray-400">Administra los perfiles y permisos de los usuarios.</p>
                    </div>
                </a>

                <a href="pedidos_pasados.php" class="tile block bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center text-yellow-600 dark:text-yellow-400 mb-4 svg-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 10V4.5c0-.28-.22-.5-.5-.5s-.5.22-.5.5V10l-4.7 4.7c-.2.2-.2.51 0 .71.2.2.51.2.71 0L12 12.41l4.79 4.79c.2.2.51.2.71 0s.2-.51 0-.71L12 10zm-6 9v-2h12v2H6z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Pedidos Pasados</h3>
                        <p class="text-gray-600 dark:text-gray-400">Consulta el historial completo de todos los pedidos.</p>
                    </div>
                </a>
                
                <a href="ver_envios_admin.php" class="tile block bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center text-purple-600 dark:text-purple-400 mb-4 svg-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21 9v2h-2c-1.1 0-2-.9-2-2V7c0-1.1.9-2 2-2h2v2c0 1.1-.9 2-2 2zm-2 2h-2v2h2v-2zm-2 2v2c0 1.1-.9 2-2 2h-2v-2h2v-2h2v2zM5 5v2h2v2h-2v2h2v2H5v2H3V5h2z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Ver Todos los Envíos</h3>
                        <p class="text-gray-600 dark:text-gray-400">Consulta el estado de todos los envíos activos.</p>
                    </div>
                </a>

            <?php endif; ?>
            
            <!-- Tiles para todos los usuarios (incluidos los administradores) -->
            <a href="ver_envios.php" class="tile block bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center text-blue-600 dark:text-blue-400 mb-4 svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Mis Envíos</h3>
                    <p class="text-gray-600 dark:text-gray-400">Revisa el estado de tus envíos actuales.</p>
                </div>
            </a>   
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tilesContainer = document.getElementById('tiles-container');
            const gridViewBtn = document.getElementById('grid-view-btn');
            const listViewBtn = document.getElementById('list-view-btn');
            
            // Define las clases para cada vista
            const gridClasses = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 cursor-grab';
            const listClasses = 'list-view flex flex-col gap-3 cursor-grab';

            // Función para cambiar la vista
            const setView = (view) => {
                if (view === 'list') {
                    tilesContainer.classList.remove(...gridClasses.split(' '));
                    tilesContainer.classList.add(...listClasses.split(' '));
                    localStorage.setItem('viewMode', 'list');
                } else {
                    tilesContainer.classList.remove(...listClasses.split(' '));
                    tilesContainer.classList.add(...gridClasses.split(' '));
                    localStorage.setItem('viewMode', 'grid');
                }
            };

            // Cargar la vista guardada en localStorage
            const savedView = localStorage.getItem('viewMode');
            if (savedView) {
                setView(savedView);
            } else {
                // Vista por defecto
                setView('grid');
            }

            // Eventos para los botones
            gridViewBtn.addEventListener('click', () => setView('grid'));
            listViewBtn.addEventListener('click', () => setView('list'));

            // Lógica para el drag and drop de las tiles con Dragula
            dragula([tilesContainer]);
            
            // --- Lógica del modo claro/oscuro (sin cambios) ---
            const themeToggle = document.getElementById('theme-toggle');
            const sunIcon = document.getElementById('sun-icon');
            const moonIcon = document.getElementById('moon-icon');
            const htmlElement = document.documentElement;

            const applyTheme = (theme) => {
                if (theme === 'dark') {
                    htmlElement.classList.add('dark');
                    // sunIcon.classList.remove('hidden'); // Comentado porque no hay íconos en el panel de control
                    // moonIcon.classList.add('hidden');
                    localStorage.setItem('theme', 'dark');
                } else {
                    htmlElement.classList.remove('dark');
                    // sunIcon.classList.add('hidden');
                    // moonIcon.classList.remove('hidden');
                    localStorage.setItem('theme', 'light');
                }
            };

            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                applyTheme(savedTheme);
            } else {
                const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                applyTheme(systemTheme);
            }
        });
    </script>
</body>
</html>