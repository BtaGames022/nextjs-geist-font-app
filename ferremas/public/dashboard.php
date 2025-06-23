<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FERREMAS - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold">FERREMAS</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Bienvenido, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?></span>
                    <button onclick="logout()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <?php if (in_array($_SESSION['role'], ['administrator', 'warehouse'])): ?>
        <div class="mb-6">
            <button onclick="openProductModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Agregar Producto
            </button>
        </div>
        <?php endif; ?>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4">
                <h2 class="text-xl font-bold mb-4">Listado de Productos</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Código
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Precio
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Stock
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Categoría
                                </th>
                                <?php if (in_array($_SESSION['role'], ['administrator', 'warehouse'])): ?>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Acciones
                                </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="products-table-body">
                            <!-- Products will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="product-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modal-title">Agregar Producto</h3>
                <form id="product-form" class="space-y-4">
                    <input type="hidden" id="product-id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código</label>
                        <input type="text" id="product-code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" id="product-name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Precio</label>
                        <input type="number" id="product-price" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" id="product-stock" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Categoría</label>
                        <select id="product-category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <!-- Categories will be loaded here -->
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeProductModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancelar
                        </button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Load products on page load
        document.addEventListener('DOMContentLoaded', loadProducts);

        async function loadProducts() {
            try {
                const response = await fetch('/api/products/index.php');
                const data = await response.json();
                
                const tableBody = document.getElementById('products-table-body');
                tableBody.innerHTML = '';

                data.forEach(product => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            ${product.code}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            ${product.name}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            $${parseFloat(product.price).toFixed(2)}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            ${product.stock}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            ${product.category_name}
                        </td>
                        ${getActionButtons(product.id)}
                    `;
                    tableBody.appendChild(row);
                });
            } catch (error) {
                console.error('Error loading products:', error);
            }
        }

        function getActionButtons(productId) {
            if (!['administrator', 'warehouse'].includes('<?php echo $_SESSION['role']; ?>')) {
                return '';
            }
            
            return `
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <button onclick="editProduct(${productId})" class="text-blue-600 hover:text-blue-900 mr-3">
                        Editar
                    </button>
                    ${('<?php echo $_SESSION['role']; ?>' === 'administrator') ? 
                        `<button onclick="deleteProduct(${productId})" class="text-red-600 hover:text-red-900">
                            Eliminar
                        </button>` : ''
                    }
                </td>
            `;
        }

        function openProductModal(productId = null) {
            document.getElementById('product-modal').classList.remove('hidden');
            document.getElementById('modal-title').textContent = productId ? 'Editar Producto' : 'Agregar Producto';
            document.getElementById('product-id').value = productId || '';
        }

        function closeProductModal() {
            document.getElementById('product-modal').classList.add('hidden');
            document.getElementById('product-form').reset();
        }

        document.getElementById('product-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const productId = document.getElementById('product-id').value;
            const productData = {
                code: document.getElementById('product-code').value,
                name: document.getElementById('product-name').value,
                price: document.getElementById('product-price').value,
                stock: document.getElementById('product-stock').value,
                category_id: document.getElementById('product-category').value
            };

            try {
                const response = await fetch('/api/products/index.php', {
                    method: productId ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(productId ? { ...productData, id: productId } : productData)
                });

                if (response.ok) {
                    closeProductModal();
                    loadProducts();
                } else {
                    const data = await response.json();
                    alert(data.error || 'Error al guardar el producto');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
            }
        });

        async function deleteProduct(productId) {
            if (!confirm('¿Está seguro de eliminar este producto?')) {
                return;
            }

            try {
                const response = await fetch('/api/products/index.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: productId })
                });

                if (response.ok) {
                    loadProducts();
                } else {
                    const data = await response.json();
                    alert(data.error || 'Error al eliminar el producto');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
            }
        }

        async function logout() {
            try {
                await fetch('/api/auth/index.php?action=logout');
                window.location.href = 'login.php';
            } catch (error) {
                console.error('Error:', error);
            }
        }
    </script>
</body>
</html>
