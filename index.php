<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Chapas</title>

   <link rel="stylesheet" href="css/style.css" type="text/css" media="all">

    <style>
        /* Aquí van tus estilos internos */
   
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <!-- Contenido -->
    <div class="container">
        <!-- Modal para mostrar la imagen ampliada -->
        <div id="modal" class="modal">
            <span id="closeModal" class="close">&times;</span>
            <img id="modalImage" class="modal-content" alt="Chapa ampliada">
        </div>

        <!-- Filtros -->
        <form method="GET" class="filter-form">
            <input type="hidden" name="view" value="<?= $_GET['view'] ?? 'todas' ?>">
            <label for="coleccion">Colección:</label>
            <select name="coleccion" id="coleccion">
                <option value="">Todas</option>
                <?php
                $colecciones = $conn->query("SELECT * FROM colecciones");
                $coleccion_filtro = $_GET['coleccion'] ?? '';
                while ($row = $colecciones->fetch_assoc()) {
                    $selected = $coleccion_filtro == $row['id'] ? "selected" : "";
                    echo "<option value='{$row['id']}' $selected>{$row['nombre_coleccion']}</option>";
                }
                ?>
            </select>
            <label for="buscar_id">Buscar por ID:</label>
            <input type="text" name="buscar_id" id="buscar_id" value="<?= $_GET['buscar_id'] ?? '' ?>">

            <label for="buscar_nombre">Buscar por Nombre:</label>
            <input type="text" name="buscar_nombre" id="buscar_nombre" value="<?= $_GET['buscar_nombre'] ?? '' ?>">

            <label for="ordenar">Ordenar por:</label>
            <select name="ordenar" id="ordenar">
                <option value="reciente" <?= ($_GET['ordenar'] ?? '') == 'reciente' ? 'selected' : '' ?>>Más reciente</option>
                <option value="antigua" <?= ($_GET['ordenar'] ?? '') == 'antigua' ? 'selected' : '' ?>>Más antigua</option>
            </select>

            <button type="submit">Filtrar</button>
        </form>

        <!-- Resultados -->
        <div class="results">
            <?php
            // Captura de parámetros de filtro
            $view = $_GET['view'] ?? 'todas';
            $coleccion_filtro = $_GET['coleccion'] ?? '';
            $buscar_id = $_GET['buscar_id'] ?? '';
            $buscar_nombre = $_GET['buscar_nombre'] ?? '';
            $ordenar = $_GET['ordenar'] ?? 'reciente';

            // Construcción dinámica de la consulta
            $conditions = [];
            if ($view === 'repetidas') {
                $conditions[] = "chapas.cantidad >= 1";
            }
            if (!empty($coleccion_filtro)) {
                $conditions[] = "chapas.coleccion_id = " . intval($coleccion_filtro);
            }
            if (!empty($buscar_id)) {
                $conditions[] = "chapas.id = " . intval($buscar_id);
            }
            if (!empty($buscar_nombre)) {
                $conditions[] = "chapas.nombre_chapa LIKE '%" . $conn->real_escape_string($buscar_nombre) . "%'";
            }

            // Base de la consulta
            $query = "SELECT chapas.*, colecciones.nombre_coleccion 
                      FROM chapas 
                      LEFT JOIN colecciones ON chapas.coleccion_id = colecciones.id";

            // Agregar condiciones si las hay
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            // Ordenar por ID (reciente o antigua)
            if ($ordenar === 'antigua') {
                $query .= " ORDER BY chapas.id ASC";
            } else {
                $query .= " ORDER BY chapas.id DESC";
            }

            // Ejecutar consulta
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='card' data-img='fotoschapas/{$row['id']}.jpg'>";
                    echo "<img src='fotoschapas/{$row['id']}.jpg' alt='Chapa {$row['id']}'>";
                    echo "<p>ID: {$row['id']}</p>";
                    echo "<p>" . (!empty($row['nombre_coleccion']) ? $row['nombre_coleccion'] : "&nbsp;") . "</p>";
                    if ($view === 'repetidas') echo "<p>Cantidad: {$row['cantidad']}</p>";
                    echo "</div>";
                }
            } else {
                echo "<div class='no-results'><p>No se encontraron chapas.</p></div>";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        const modal = document.getElementById("modal");
        const modalImage = document.getElementById("modalImage");
        const closeModal = document.getElementById("closeModal");
        const cards = document.querySelectorAll(".card");

        cards.forEach(card => {
            card.addEventListener("click", () => {
                const imgSrc = card.getAttribute("data-img");
                modalImage.src = imgSrc;
                modal.style.display = "flex";
            });
        });

        closeModal.addEventListener("click", () => modal.style.display = "none");
        modal.addEventListener("click", (e) => { if (e.target === modal) modal.style.display = "none"; });
    </script>
</body>
</html>
