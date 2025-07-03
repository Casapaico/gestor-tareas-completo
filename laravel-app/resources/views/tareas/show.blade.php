<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"> {{-- Meta etiqueta para responsividad en moviles,etc--}}
    <title>Detalle de Tarea</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h1>Detalle de Tarea</h1>
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                <div class="card-header">
                    <strong>{{ $tarea->titulo }}</strong>
                </div>
                <div class="card-body">
                    <p><strong>Descripción:</strong> {{ $tarea->descripcion }}</p>
                    <p><strong>Fecha y Hora:</strong> {{ $tarea->fecha_hora }}</p>
                    <p><strong>Estado:</strong> {{ $tarea->completado ? '✅ Completado' : '⏳ Pendiente' }}</p>
                </div>
                </div>
                <a href="{{ route('tareas.index') }}" class="btn btn-primary mt-3">Volver a la lista</a>
            </div>
        </div>
</body>
</html>
