<?php
$archivo = __DIR__ . "/citas.csv";

$horariosPermitidos = [
    "08:00 AM","09:00 AM","10:00 AM","11:00 AM",
    "02:00 PM","03:00 PM","04:00 PM","05:00 PM"
];

$nombre  = $_POST['nombre']  ?? "";
$celular = $_POST['celular'] ?? "";
$fecha   = $_POST['fecha']   ?? "";
$hora    = $_POST['hora']    ?? "";

$horariosOcupados = [];
$mensajeEstado = "";

/* Bloquear sábados y domingos */
if ($fecha) {
    $dia = date("N", strtotime($fecha));
    if ($dia >= 6) {
        $fecha = "";
    }
}

/* Leer citas */
$citas = [];
if (file_exists($archivo)) {
    $f = fopen($archivo, "r");
    fgetcsv($f);
    while ($row = fgetcsv($f)) {
        $citas[] = $row;
        if ($fecha && $row[2] === $fecha) {
            $horariosOcupados[] = $row[3];
        }
    }
    fclose($f);
}

$horariosDisponibles = array_diff($horariosPermitidos, $horariosOcupados);

/* AGENDAR CITA */
if (isset($_POST['guardar'])) {

    $f = fopen($archivo, "a");
    fputcsv($f, [$nombre, $celular, $fecha, $hora]);
    fclose($f);

    $mensaje = "Hola $nombre 👋\n\n".
               "Tu asesoría virtual con *TEAM GROUP MJ* fue agendada con éxito.\n\n".
               "📅 Fecha: $fecha\n".
               "⏰ Hora: $hora";

    $whatsappURL = "https://wa.me/57$celular?text=" . urlencode($mensaje);
    $accion = "agendada";
}

/* CANCELAR CITA */
if (isset($_POST['cancelar'])) {

    $nuevo = [];
    $cancelada = false;

    foreach ($citas as $c) {
        if ($c[1] === $celular && $c[2] === $fecha && $c[3] === $hora) {
            $cancelada = true;
            continue;
        }
        $nuevo[] = $c;
    }

    if ($cancelada) {
        $f = fopen($archivo, "w");
        fputcsv($f, ["NOMBRE","CELULAR","FECHA","HORA"]);
        foreach ($nuevo as $n) {
            fputcsv($f, $n);
        }
        fclose($f);

        $mensaje = "Hola 👋\n\n".
                   "Tu cita con *TEAM GROUP MJ* fue *cancelada correctamente*.\n\n".
                   "📅 Fecha: $fecha\n".
                   "⏰ Hora: $hora";

        $whatsappURL = "https://wa.me/57$celular?text=" . urlencode($mensaje);
        $accion = "cancelada";
    } else {
        $mensajeEstado = "No se encontró la cita.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agendar Asesoría | Team Group MJ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#0f172a,#1e293b);
}
.contenedor{
    width:420px;
    padding:35px;
    border-radius:18px;
    background:#fff;
    box-shadow:0 30px 70px rgba(0,0,0,.35);
}
.logo{text-align:center;margin-bottom:15px;}
.logo img{width:90px;}
h2{text-align:center;margin-bottom:5px;}
p{text-align:center;color:#64748b;margin-bottom:25px;}
label{font-size:13px;color:#334155;}
input,select{
    width:100%;
    padding:12px;
    margin:8px 0 18px;
    border-radius:10px;
    border:1px solid #cbd5e1;
}
button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    font-weight:600;
    cursor:pointer;
}
.agendar{background:#2563eb;color:#fff;}
.cancelar{background:#dc2626;color:#fff;margin-top:10px;}
</style>
</head>

<body>
<div class="contenedor">

<div class="logo">
<img src="logo.png">
</div>

<h2>TEAM GROUP MJ</h2>
<p>Agendamiento de asesorías</p>

<form method="POST">
<label>Nombre completo</label>
<input type="text" name="nombre">

<label>Número de celular</label>
<input type="tel" name="celular" required>

<label>Fecha</label>
<input type="date" name="fecha" min="<?= date('Y-m-d') ?>" required>

<label>Hora</label>
<select name="hora" required>
<?php foreach($horariosPermitidos as $h): ?>
<option><?= $h ?></option>
<?php endforeach; ?>
</select>

<button class="agendar" name="guardar">Agendar cita</button>
<button class="cancelar" name="cancelar">Cancelar cita</button>
</form>

<?php if($mensajeEstado): ?>
<p style="color:red;text-align:center"><?= $mensajeEstado ?></p>
<?php endif; ?>

</div>

<?php if(isset($accion)): ?>
<script>
window.open("<?= $whatsappURL ?>","_blank");
document.querySelector("form").reset();
</script>
<?php endif; ?>

</body>
</html>
