<?php
session_start(); // <-- agrega esto al principio de carrito.php
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/carrito_ventas.css">
    <link rel="stylesheet" href="style/css/menu.css">
    <script src="js/carrito.js" defer></script> <!-- Carga JavaScript -->
    
</head>
<body>
    <aside class="aside active" id="aside">
        <div class="head">
            <div class="profile">
                <img src="style/images/logo_gestion.png" alt="Gestión de Inventario - Logo">
                <p id="logo-name">Pilidev</p>
            </div>
            <i class='bx bx-menu' id="menu"></i>
        </div>
        <div class="options">
            <a id="" href="ver_ventas.php">
                <div>
                    <i class='bx bx-receipt'></i>
                    <span class="option">Ventas</span>
                </div>
            </a>

            <a id="" href="ver_productos.php">
                <div>
                    <i class='bx bx-package'></i>
                    <span class="option">Productos</span>
                </div>
            </a>

            <a id="" href="ver_categorias.php">
                <div>
                    <i class='bx bx-category-alt'></i>
                    <span class="option">Categorias</span>
                </div>
            </a>

            <a id="" href="ver_clientes.php">
                <div>
                    <i class='bx bx-group'></i>
                    <span class="option">Clientes</span>
                </div>
            </a>
            
            <a id="menuUsuarios" href="ver_usuarios.php">
                <div>
                    <i class='bx bx-user'></i>
                    <span class="option">Usuarios</span>
                </div>
            </a>

            <a class="links" href="logout.php">
                <div>
                    <i class='bx bx-log-out'></i>
                    <span class="option">Cerrar sesión</span>
                </div>
            </a>
        </div>     
    </aside>

    <main id="inicio">

        <section id="barra">
            <div id="atras">
                <a href="ver_ventas.php">
                    <i class='bx bx-left-arrow-alt'></i>
                </a>
                <h4>VENDER</h4>
            </div>  
        </section>
        
        <section id="datos">
            <div id="content_venta">
                <!-- <input type="hidden" id="documento_operador" value="<?php echo $_SESSION['documento']; ?>"> -->
                

                <!-- Campo de búsqueda de productos -->
                <div id="select_prod">
                    <div id="buscador">
                        <i class='bx bx-search-alt-2'></i>
                        <input type="text" id="buscarProducto" placeholder="Buscar producto" autofocus="" tabindex="1">
                        <div id="sugerencias" class="sugerencias" ></div>
                    </div>
                    
                    <div id="cantidad">
                        <input type="number" id="cantidadProducto" placeholder="Cantidad" inputmode="numeric" min="1" tabindex="2">
                        <button id="agregarProducto" tabindex="3"><i class='bx bx-plus-circle'></i>AGREGAR</button>
                    </div>
                    
                    
                </div>

                <div id="info_producto">
                    <!-- esta tabla no va -->
                    <table id="seleccion_prod">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Disponible</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span id="nombreProducto"></span></td>
                                <td><span id="disponibleProducto"></span></td>
                                <td><span id="precioProducto"></span></td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- hasta aquí -->
                    
                
                </div>
                
                
                <div id="venta_resumen" class="resumen">
                    <div class="total">Total Items: <span id="total_items">0</span></div>
                    <table cellpadding="5" id="resumenTabla" class="datos_venta">
                        <tr>
                            <th>Producto</th>
                            <th>Subtotal</th>
                            <th>Cantidad</th>
                        </tr>
                    </table>
                    <div class="carrito" id="carrito"></div>
                    
                    <div class="total">Total Venta: <span id="totalVenta">0.00</span></div>
                </div>
                <div id="fin_venta">
                    <!-- Seleccionar método de pago -->
                    <div id="medio_pago">
                        <label for="metodo_pago">Método de Pago:</label>
                    <select id="metodo_pago" name="metodo_pago" required tabindex="4">
                        <option value="">Elige una opción</option>
                        <option value="efectivo">EFECTIVO</option>
                        <option value="nequi">NEQUI</option>
                        <option value="transferencia">TRANSFERENCIA</option>
                        <option value="credito">CREDITO</option>
                    </select>

                    </div>
                    
                    <!-- Campo "Recibido" (se muestra solo si se elige "Efectivo") -->
                    <div id="campoRecibido" style="display: none;">
                        <label for="recibido">Recibido: </label>
                        <input type="text" id="recibido" inputmode="numeric" placeholder="Monto recibido">
                    </div>

                    <!-- Campo de descuento en pesos -->
                    <div id="campoDescuento" style="display: none;">
                        <label for="descuento">Descuento ($): </label>
                        <input type="number" id="descuento" min="0" step="1" inputmode="numeric" placeholder="$">
                    </div>

                </div>

                    <!-- Mostrar resumen con descuento -->
                    <div id="resumenDescuento" style="display: none;">
                        <p id="total_precio">Subtotal: <span id="subtotalAntesDescuento">0.00</span></p>
                        <p>Descuento: <span id="valorDescuento">0.00</span></p>
                        <p class="total-con-descuento">Total con descuento: <span id="totalConDescuento">0.00</span></p>
                    </div>

                    <!-- Mostrar Cambio -->
                    <div id="campoCambio" style="display: none;">
                        <p>Cambio: <span id="cambio">0.00</span></p>
                    </div>

            </div>

            <button id="finalizarCompra" tabindex="5">CONFIRMAR</button>
           
        </section>

        <!-- Modal de Selección Cliente -->
        <div id="modalSeleccionCliente" class="modal" style="display: none;">
            <div class="modal-content">
            <h3>¿Quién realiza la compra?</h3>
        
            <!-- ✅ Campo nuevo para búsqueda -->
            <input type="text" id="buscarClienteInput" placeholder="Buscar cliente por documento..." autocomplete="off">
            <div id="sugerenciasCliente" class="sugerencias"></div>

            <!-- Botón para registrar si no existe -->
            <div id="botonRegistrarCliente" style="display: none; margin-top: 10px;">
                <p>⚠ Cliente no encontrado.</p>
                <button id="abrirRegistroCliente">Registrar cliente</button>
            </div>

        
            <button id="btnClienteGenerico">Consumidor Final</button>
            <button id="cerrarModalSeleccion">Cancelar</button>
            </div>
        </div>
  

        <!-- Modal Cliente Crédito -->
        <div id="modalCreditoCliente" class="modal" style="display: none;">
            <div class="modal-content">
                <h3>Datos del Cliente Crédito</h3>


                <!-- Selección del plazo del crédito -->
                <label for="plazoCredito">Plazo del Crédito (días):</label>
                <select id="plazoCredito">
                    <option value="">Elige una Opción</option>
                    <option value="15">15 días</option>
                    <option value="30">30 días</option>
                    <option value="60">60 días</option>
                    <option value="90">90 días</option>
                </select>

                <!-- Observaciones -->
                <label for="observacionesCredito">Observaciones:</label>
                <textarea id="observacionesCredito" rows="3" placeholder="Observaciones para el crédito..."></textarea>

                <button id="confirmarCreditoVenta">Confirmar Datos del Crédito</button>
                <button id="cerrarModalCredito">Cancelar</button>
            </div>
        </div>

        <!-- Formulario de nuevo cliente -->
        <div id="formNuevoCliente" style="display: none; margin-top: 15px;">
            <h4>Registrar Nuevo Cliente</h4>

            <label for="nuevoNombre">Nombre(s)</label>
            <input type="text" id="nuevoNombre" placeholder="Nombre">

            <label for="nuevoApellido">Apellido(s)</label>
            <input type="text" id="nuevoApellido" placeholder="Apellido">

            <label for="nuevaFechaNacimiento">Fecha de Nacimiento</label>
            <input type="date" id="nuevaFechaNacimiento">

            <label for="nuevoTipoDocumento">Tipo de Documento</label>
            <select id="nuevoTipoDocumento" required>
                <option value="">Elige una opción</option>
                <option value="cedula de ciudadania">Cédula de Ciudadanía</option>
                <option value="cedula de extranjeria">Cédula de Extranjería</option>
                <option value="pasaporte">Pasaporte</option>
                <option value="nit">NIT</option>
                <option value="estatus de proteccion temporal">Estatus de Protección Temporal</option>
            </select>

            <label for="nuevoDocumento">Documento Identificaciòn</label>
            <input type="text" id="nuevoDocumento" placeholder="Documento">

            <label for="nuevoTipoPersona">Tipo de Persona</label>
            <select id="nuevoTipoPersona" required>
                <option value="">Elige una opción</option>
                <option value="natural">Natural</option> 
                <option value="juridica">Jurídica</option>
            </select>
           
            <label for="nuevoNombreComercial">Nombre Comercial</label>
            <input type="text" id="nuevoNombreComercial" placeholder="Nombre Comercial (opcional)">

            <label for="nuevoDepartamento">Departamento</label>
            <select name="nuevoDepartamento" id="nuevoDepartamento">
                <option value="">Sellecione un nuevoDepartamento</option>
            </select>

             <label for="nuevoMunicipio">Municipio</label>
            <select id="nuevoMunicipio" required>
            <option value="">Selecciona uno</option>
            <!-- Aquí se inyectan los municipios desde el JS -->
            </select>

            <label for="nuevaDireccion">Dirección</label>
            <input type="text" id="nuevaDireccion" placeholder="Dirección">

            <label for="nuevoCelular">Celular</label>
            <input type="tel" id="nuevoCelular" placeholder="Celular">

            <label for="nuevoTelefono">Teléfono Fijo</label>
            <input type="tel" id="nuevoTelefono" placeholder="Teléfono Fijo">

            <label for="nuevoCorreo">Correo Electronico</label>
            <input type="email" id="nuevoCorreo" placeholder="Correo Electronico">

            <label for="nuevoTributo">Regimen Tributario</label>
            <select id="nuevoTributo" required>
                <option value="">Selecciona uno</option>
                <option value="21">Régimen Común</option>
                <option value="22">Régimen Simplificado</option>
                <option value="49">Gran Contribuyente</option>
            </select>

            <button id="registrarNuevoCliente">Registrar Cliente</button>
        </div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const selDepto = document.getElementById("nuevoDepartamento");
  const selMuni  = document.getElementById("nuevoMunicipio");

  // 1) Estado inicial
  resetMunicipios(true); // deshabilitado hasta que se elija depto

  // 2) Cargar JSON con departamentos y municipios
  //    Ajusta la ruta si tu JSON está en otra carpeta.
  fetch("departamentos_municipios.json")
    .then(r => {
      if (!r.ok) throw new Error("No se pudo cargar departamentos_municipios.json");
      return r.json();
    })
    .then(data => {
      // data es un objeto: { "Antioquia": ["Medellín", ...], "Caldas": ["Manizales", ...], ... }

      // Llenar departamentos ordenados
      const departamentos = Object.keys(data).sort((a, b) => a.localeCompare(b, 'es'));
      // Limpia (pero conserva la primera opción placeholder existente)
      keepOnlyFirstOption(selDepto);
      departamentos.forEach(dep => {
        const opt = document.createElement("option");
        opt.value = dep;        // valor que enviarás al backend
        opt.textContent = dep;  // texto visible
        selDepto.appendChild(opt);
      });

      // 3) Al cambiar de departamento → llenar municipios
      selDepto.addEventListener("change", () => {
        const dep = selDepto.value;
        const municipios = data[dep] || [];
        fillMunicipios(municipios);
      });
    })
    .catch(err => {
      console.error(err);
      alert("⚠ No se pudo cargar la lista de departamentos/municipios.");
    });

  // ----- utilidades -----
  function keepOnlyFirstOption(selectEl) {
    // deja solo la primera <option> (placeholder)
    while (selectEl.options.length > 1) {
      selectEl.remove(1);
    }
  }

  function resetMunicipios(disabled = false) {
    keepOnlyFirstOption(selMuni);
    selMuni.disabled = !!disabled;
  }

  function fillMunicipios(lista) {
    resetMunicipios(false);
    // ordenar alfabéticamente
    lista.slice() // copiar por seguridad
         .sort((a, b) => a.localeCompare(b, 'es'))
         .forEach(m => {
            const opt = document.createElement("option");
            opt.value = m;       // valor que enviarás al backend
            opt.textContent = m; // texto visible
            selMuni.appendChild(opt);
         });
  }
});
</script>

    </main>
    <script src="js/reconocer_rol.js"></script>
</body>
</html>


