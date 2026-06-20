@php
$colores = [
    'Ingresado'           => 'bg-gray-100 text-gray-700',
    'En diagnóstico'      => 'bg-blue-100 text-blue-700',
    'En reparación'       => 'bg-yellow-100 text-yellow-700',
    'Esperando repuesto'  => 'bg-orange-100 text-orange-700',
    'Listo'               => 'bg-green-100 text-green-700',
    'Entregado'           => 'bg-teal-100 text-teal-700',
    'Sin reparación'      => 'bg-red-100 text-red-700',
];
$clase = $colores[$estado->nombre] ?? 'bg-gray-100 text-gray-600';
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $clase }}">
    {{ $estado->nombre }}
</span>
