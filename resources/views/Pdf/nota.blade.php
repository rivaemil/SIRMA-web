<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
    h1 { font-size: 20px; margin: 0 0 8px; }
    h2 { font-size: 16px; margin: 16px 0 8px; }
    .muted { color:#666; }
    .row { display:flex; justify-content:space-between; gap:12px; }
    .box { padding:8px; border:1px solid #ddd; border-radius:6px; margin-bottom:10px; }
    table { width:100%; border-collapse:collapse; margin-top:6px; }
    th, td { border:1px solid #ddd; padding:6px; }
    th { background:#f5f5f5; text-align:left; }
    .right { text-align:right; }
    .tot { font-weight:700; }
  </style>
</head>
<body>
  <div class="row">
    <div>
      <h1>Reporte de Servicio</h1>
      <div class="muted">Emitido: {{ $emitido }}</div>
    </div>
    <div class="box">
      <div><strong>Bitácora #</strong> {{ $bitacora->id }}</div>
      <div><strong>Estado</strong> {{ ucfirst($bitacora->estado_bitacora) }}</div>
      <div><strong>Fecha</strong> {{ \Carbon\Carbon::parse($bitacora->fecha)->format('d/m/Y H:i') }}</div>
    </div>
  </div>

  <div class="box">
    <div><strong>Cliente:</strong> {{ $bitacora->cliente }}</div>
    <div><strong>Mecánico:</strong> {{ $bitacora->mecanico }}</div>
    <div><strong>Vehículo:</strong> {{ $bitacora->vehiculo }}</div>
    <div><strong>Tipo de servicio:</strong> {{ ucfirst($bitacora->tipo_servicio) }}</div>
  </div>

  @if($bitacora->descripcion_general)
    <h2>Descripción general</h2>
    <div class="box">{{ $bitacora->descripcion_general }}</div>
  @endif

  <h2>Actividades</h2>
  <table>
    <thead><tr><th>Descripción</th><th class="right">Costo</th></tr></thead>
    <tbody>
      @forelse($actividades as $a)
        <tr><td>{{ $a->descripcion }}</td><td class="right">${{ number_format($a->costo,2) }}</td></tr>
      @empty
        <tr><td colspan="2" class="muted">Sin actividades registradas.</td></tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr><td class="right tot">Subtotal actividades</td><td class="right tot">${{ number_format($subtotalActiv,2) }}</td></tr>
    </tfoot>
  </table>

  <h2 style="margin-top:14px">Piezas</h2>
  <table>
    <thead><tr><th>Pieza</th><th class="right">Cantidad</th><th class="right">Costo unitario</th><th class="right">Subtotal</th></tr></thead>
    <tbody>
      @forelse($piezas as $p)
        <tr>
          <td>{{ $p->pieza }}</td>
          <td class="right">{{ $p->cantidad }}</td>
          <td class="right">${{ number_format($p->costo_unitario,2) }}</td>
          <td class="right">${{ number_format($p->subtotal,2) }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="muted">Sin piezas registradas.</td></tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr><td colspan="3" class="right tot">Subtotal piezas</td><td class="right tot">${{ number_format($subtotalPzas,2) }}</td></tr>
      <tr><td colspan="3" class="right tot">Total</td><td class="right tot">${{ number_format($total,2) }}</td></tr>
    </tfoot>
  </table>
</body>
</html>
