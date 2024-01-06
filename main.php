<?php
// Conexión a la base de datos
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "prueba";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta SQL para obtener los usuarios
$sql = "SELECT ID, Nombre, Fecha, Genero FROM Usuarios";
$result = $conn->query($sql);

// Cerrar la conexión
$conn->close();
?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["enviar_formulario"])) {
    // Conectar a la base de datos (reemplaza con tus propios datos)
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";
    $dbname = "prueba";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Obtener datos del formulario
    $nombre = $_POST["introducir_nombre"];
    $fechaNacimiento = $_POST["introducir_fecha"];
    $genero = ($_POST["genero"] == "masculino") ? hex2bin('00') : hex2bin('01'); 

    // Insertar datos en la base de datos (reemplaza con tu propia consulta SQL)
    $sql = "INSERT INTO Usuarios (Nombre, Fecha, Genero) VALUES ('$nombre', '$fechaNacimiento', '$genero')";

    if ($conn->query($sql) === TRUE) {
        echo "Datos insertados correctamente";
        // Redirigir a la misma página después de 1 segundo
        header("refresh:1;url=".$_SERVER['PHP_SELF']);
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Cerrar la conexión
    $conn->close();
}
?>

<?php
require('tcpdf/tcpdf.php');
require('vendor/autoload.php');

if (isset($_POST["enviar_reporte"])) {
    echo "Descargando";

    // Conectar a la base de datos (reemplaza con tus propios datos)
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";
    $dbname = "prueba";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Obtener datos de la base de datos
    $sql = "SELECT Nombre, Fecha FROM Usuarios";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Generar PDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(60, 10, 'Nombre', 1);
        $pdf->Cell(60, 10, 'Edad', 1);
        $pdf->Ln();
        
        while ($row = $result->fetch_assoc()) {
            $nombre = $row["Nombre"];
            $fechaNacimiento = $row["Fecha"];
            $edad = calcularEdad($fechaNacimiento);

            $pdf->Cell(60, 10, $nombre, 1);
            $pdf->Cell(60, 10, $edad, 1);
            $pdf->Ln();
        }

        // Guardar el PDF en un buffer

        // Guardar el PDF en un buffer
        ob_start();
        $pdf->Output('php://output', 'F');
        $pdfBuffer = ob_get_clean();

        // Descargar el PDF
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="reporte.pdf"');
        header('Content-Length: ' . strlen($pdfBuffer));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        echo $pdfBuffer;

        // Generar Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Usuarios");
        $sheet->setCellValue('A1', 'Nombre');
        $sheet->setCellValue('B1', 'Edad');

        $rowNumber = 2;
        $result = $conn->query($sql); // Reiniciar el puntero del resultado para recorrerlo nuevamente
        while ($row = $result->fetch_assoc()) {
            $nombre = $row["Nombre"];
            $fechaNacimiento = $row["Fecha"];
            $edad = calcularEdad($fechaNacimiento);

            $sheet->setCellValue('A'.$rowNumber, $nombre);
            $sheet->setCellValue('B'.$rowNumber, $edad);
            $rowNumber++;
        }
        // Crear un objeto de escritura de Excel y enviar el contenido al navegador
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('reporte_excel.xlsx');

        echo "Reporte generado y descargado correctamente";
    } else {
        echo "No hay usuarios registrados para generar el reporte";
    }

        // Cerrar la conexión
        $conn->close();
    }

function calcularEdad($fechaNacimiento) {
    $fechaNacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimiento);
    return $edad->y;
}
?>





<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Formulario</title>

  <link rel="stylesheet" type="text/css" href="styles.css">

  </head>


<body>  

  <div class="contact_form">

    <div class="formulario">      
      <h1>Formulario de Usuarios</h1>
        <h3>A continuacion te peridemos algunos datos importantes</h3>


          <form action="" method="post">       

            
                <p>
                  <label for="nombre" class="colocar_nombre">Nombre
                  </label>
                    <input type="text" name="introducir_nombre" id="nombre" required="obligatorio" placeholder="Escribe tu nombre">
                </p>
              
                <p>
                  <label for="date">Fecha de nacimiento
                  </label>
                    <input type="date" name="introducir_fecha" id="fecha_nacimiento"  placeholder="Ingresa tu fecha de nacimiento">
                </p>
            
                <p>
                  <label for="telefone" class="colocar_telefono">Genero
                  <select id="genero" name="genero" required>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                 </select>
                </p>    
                               
                <button type="submit" name="enviar_formulario" id="enviar"><p>Enviar</p></button>
            
          </form>
    </div>  
  </div>

   <!-- Tabla de Usuarios -->
   <div class="contact_form">
        <h2>Tabla de Usuarios</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Fecha de Nacimiento</th>
                <th>Genero</th>
            </tr>
            <?php
            // Mostrar los datos de la base de datos en la tabla
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["ID"] . "</td>";
                    echo "<td>" . $row["Nombre"] . "</td>";
                    echo "<td>" . $row["Fecha"] . "</td>";
                    echo "<td>" . ($row["Genero"] == hex2bin('00') ? 'Masculino' : 'Femenino') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No hay usuarios registrados</td></tr>";
            }
            ?>
        </table>

        <form method="post" action="">
        <button type="submit" name="enviar_reporte"><p>Descargar Reporte</p></button>
        </form>
    </div>
</body>
</html>