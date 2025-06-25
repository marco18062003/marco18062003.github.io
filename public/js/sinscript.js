// Selección de elementos
const productosTable = document.getElementById('productos-table').getElementsByTagName('tbody')[0];
const searchInput = document.getElementById('search-input');

// Cargar productos desde LocalStorage (si existen)
window.onload = () => {
  loadProductsFromStorage();
};

// Función para agregar productos
document.getElementById('add-product-btn').addEventListener('click', () => {
  const productName = prompt('Nombre del producto:');
  const reference = prompt('Referencia (por ejemplo, tamaño, color, etc.):');
  const unitPrice = parseFloat(prompt('Precio Unitario:'));

  if (productName && reference && unitPrice) {
    const product = {
      productName,
      reference,
      unitPrice,
      quantity: 1,
      isExtraChargeApplied: false,
    };

    // Guardar el producto en LocalStorage
    saveProductToStorage(product);
    addProductToTable(product);
  }
});

// Función para agregar un producto a la tabla
function addProductToTable(product) {
  const row = productosTable.insertRow();
  row.innerHTML = `
    <td>${product.productName}</td>
    <td>${product.reference}</td>
    <td>${formatCurrency(product.unitPrice)}</td>
    <td class="quantity">${product.quantity}</td>
    <td class="total-price">${formatCurrency(product.unitPrice * product.quantity)}</td>
    <td>
      <button class="increase-btn">Sumar</button>
      <button class="decrease-btn">Restar</button>
      <button class="extra-charge-btn">${product.isExtraChargeApplied ? 'Cargo Aplicado' : 'Cobrar Extra'}</button>
      <button class="delete-btn">Eliminar</button>
    </td>
  `;

  // Eventos para los botones
  row.querySelector('.increase-btn').addEventListener('click', () => {
    product.quantity += 1;
    updateProductInStorage(product);
    updateRow(row, product);
  });

  row.querySelector('.decrease-btn').addEventListener('click', () => {
    if (product.quantity > 1) {
      product.quantity -= 1;
      updateProductInStorage(product);
      updateRow(row, product);
    }
  });

  row.querySelector('.extra-charge-btn').addEventListener('click', () => {
    const extraCharge = 30000; // El cargo adicional
    if (product.isExtraChargeApplied) {
      product.isExtraChargeApplied = false;
      updateProductInStorage(product);
      updateRow(row, product);
    } else {
      product.isExtraChargeApplied = true;
      updateProductInStorage(product);
      updateRow(row, product);
    }
  });

  row.querySelector('.delete-btn').addEventListener('click', () => {
    if (confirm(`¿Estás seguro de que quieres eliminar el producto: ${product.productName}?`)) {
      deleteProductFromStorage(product);
      productosTable.deleteRow(row.rowIndex - 1);
    }
  });
}

// Función para actualizar una fila en la tabla
function updateRow(row, product) {
  const totalPrice = (product.unitPrice * product.quantity + (product.isExtraChargeApplied ? 30000 * product.quantity : 0)).toFixed(2);
  row.querySelector('.quantity').textContent = product.quantity;
  row.querySelector('.total-price').textContent = formatCurrency(totalPrice);
  row.querySelector('.extra-charge-btn').textContent = product.isExtraChargeApplied ? 'Cargo Aplicado' : 'Cobrar Extra';
}

// Función para guardar un producto en LocalStorage
function saveProductToStorage(product) {
  let products = JSON.parse(localStorage.getItem('products')) || [];
  products.push(product);
  localStorage.setItem('products', JSON.stringify(products));
}

// Función para actualizar un producto en LocalStorage
function updateProductInStorage(updatedProduct) {
  let products = JSON.parse(localStorage.getItem('products')) || [];
  products = products.map(product => {
    if (product.productName === updatedProduct.productName && product.reference === updatedProduct.reference) {
      return updatedProduct;
    }
    return product;
  });
  localStorage.setItem('products', JSON.stringify(products));
}

// Función para eliminar un producto de LocalStorage
function deleteProductFromStorage(deletedProduct) {
  let products = JSON.parse(localStorage.getItem('products')) || [];
  products = products.filter(product => product.productName !== deletedProduct.productName || product.reference !== deletedProduct.reference);
  localStorage.setItem('products', JSON.stringify(products));
}

// Función para cargar productos desde LocalStorage
function loadProductsFromStorage() {
  const products = JSON.parse(localStorage.getItem('products')) || [];
  products.forEach(product => {
    addProductToTable(product);
  });
}

// Función para formatear el precio con el signo "$" y comas
function formatCurrency(value) {
  return `$${parseFloat(value).toLocaleString()}`;
}

// Función de búsqueda en tiempo real
searchInput.addEventListener('input', function() {
  const filter = searchInput.value.toLowerCase();
  const rows = productosTable.getElementsByTagName('tr');

  // Iterar a través de las filas de la tabla
  Array.from(rows).forEach(row => {
    const productName = row.cells[0]?.textContent || '';
    if (productName.toLowerCase().includes(filter)) {
      row.style.display = ''; // Mostrar fila
    } else {
      row.style.display = 'none'; // Ocultar fila
    }
  });
});
